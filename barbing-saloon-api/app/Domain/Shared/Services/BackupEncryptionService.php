<?php

declare(strict_types=1);

namespace App\Domain\Shared\Services;

use RuntimeException;

class BackupEncryptionService
{
    protected const MAGIC_HEADER = 'CC_ENC_V1';
    protected const CIPHER = 'aes-256-cbc';

    /**
     * Encrypt a database backup file at rest using AES-256-CBC with HMAC-SHA256 authentication.
     */
    public function encryptFile(string $sourcePath, string $targetPath, ?string $key = null): bool
    {
        $sourcePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $sourcePath);
        $targetPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $targetPath);

        clearstatcache(true, $sourcePath);
        if (! file_exists($sourcePath) || ! is_file($sourcePath)) {
            throw new RuntimeException("Source file not found or not readable: {$sourcePath}");
        }

        $stream = @fopen($sourcePath, 'rb');
        if ($stream === false) {
            throw new RuntimeException("Source file not found or not readable: {$sourcePath}");
        }
        fclose($stream);

        $encryptionKey = $this->deriveKey($key);
        $iv = random_bytes(16);

        $plaintext = file_get_contents($sourcePath);
        if ($plaintext === false) {
            throw new RuntimeException("Failed reading file: {$sourcePath}");
        }

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($ciphertext === false) {
            throw new RuntimeException('OpenSSL encryption failed: '.(openssl_error_string() ?: 'unknown error'));
        }

        // Encrypt-then-MAC (EtM) to prevent padding oracle and ciphertext tampering
        $hmac = hash_hmac('sha256', $iv.$ciphertext, $encryptionKey, true);

        $payload = self::MAGIC_HEADER.$hmac.$iv.$ciphertext;

        $targetDir = dirname($targetPath);
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0700, true);
        }

        $bytesWritten = file_put_contents($targetPath, $payload, LOCK_EX);
        if ($bytesWritten === false) {
            throw new RuntimeException("Failed writing encrypted backup to: {$targetPath}");
        }

        // Enforce owner-only read/write permissions (0600)
        @chmod($targetPath, 0600);

        return true;
    }

    /**
     * Decrypt an encrypted database backup file.
     */
    public function decryptFile(string $sourcePath, string $targetPath, ?string $key = null): bool
    {
        $sourcePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $sourcePath);
        $targetPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $targetPath);

        clearstatcache(true, $sourcePath);
        if (! file_exists($sourcePath) || ! is_file($sourcePath)) {
            throw new RuntimeException("Encrypted backup not found: {$sourcePath}");
        }

        $stream = @fopen($sourcePath, 'rb');
        if ($stream === false) {
            throw new RuntimeException("Encrypted backup is not readable: {$sourcePath}");
        }
        fclose($stream);

        $raw = file_get_contents($sourcePath);
        if ($raw === false) {
            throw new RuntimeException("Failed reading encrypted backup: {$sourcePath}");
        }

        $magicLen = strlen(self::MAGIC_HEADER);
        if (substr($raw, 0, $magicLen) !== self::MAGIC_HEADER) {
            throw new RuntimeException("Invalid encrypted backup header. File is not an encrypted CandyCutz snapshot.");
        }

        $encryptionKey = $this->deriveKey($key);

        // Header: Magic (9 bytes) + HMAC (32 bytes) + IV (16 bytes) + Ciphertext
        $hmac = substr($raw, $magicLen, 32);
        $iv = substr($raw, $magicLen + 32, 16);
        $ciphertext = substr($raw, $magicLen + 48);

        $expectedHmac = hash_hmac('sha256', $iv.$ciphertext, $encryptionKey, true);
        if (! hash_equals($expectedHmac, $hmac)) {
            throw new RuntimeException("Integrity check failed: HMAC mismatch. Backup has been corrupted or tampered with.");
        }

        $decrypted = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            throw new RuntimeException("Decryption failed. Ensure the encryption key matches the one used during backup.");
        }

        $targetDir = dirname($targetPath);
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0700, true);
        }

        $written = file_put_contents($targetPath, $decrypted, LOCK_EX);
        if ($written === false) {
            throw new RuntimeException("Failed writing decrypted backup to: {$targetPath}");
        }

        @chmod($targetPath, 0600);

        return true;
    }

    /**
     * Check if a backup file has the encrypted magic header.
     */
    public function isEncrypted(string $filePath): bool
    {
        $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
        if (! file_exists($filePath) || ! is_file($filePath)) {
            return false;
        }

        $handle = @fopen($filePath, 'rb');
        if (! $handle) {
            return false;
        }

        $header = fread($handle, strlen(self::MAGIC_HEADER));
        fclose($handle);

        return $header === self::MAGIC_HEADER;
    }

    /**
     * Derive a 256-bit binary encryption key.
     */
    protected function deriveKey(?string $key = null): string
    {
        $rawKey = $key ?: (string) config('app.key');
        if ($rawKey === '') {
            throw new RuntimeException('Application key or backup encryption key must be configured.');
        }

        if (str_starts_with($rawKey, 'base64:')) {
            $rawKey = base64_decode(substr($rawKey, 7), true) ?: $rawKey;
        }

        return hash('sha256', $rawKey, true);
    }
}
