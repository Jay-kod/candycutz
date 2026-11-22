<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class SecurityServerQuarantineTest extends TestCase
{
    public function test_uploads_directory_has_execution_quarantine_htaccess(): void
    {
        $htaccessPath = public_path('uploads/.htaccess');
        $this->assertFileExists($htaccessPath, 'Expected uploads/.htaccess to exist for script execution quarantine');

        $content = file_get_contents($htaccessPath);
        $this->assertStringContainsString('php_flag engine off', $content);
        $this->assertStringContainsString('Require all denied', $content);
        $this->assertStringContainsString('Options -Indexes -ExecCGI', $content);
        $this->assertMatchesRegularExpression('/php|phar|sh|py|cgi|asp/i', $content);
    }

    public function test_public_storage_directory_has_execution_quarantine_htaccess(): void
    {
        $htaccessPath = storage_path('app/public/.htaccess');
        $this->assertFileExists($htaccessPath, 'Expected storage/app/public/.htaccess to exist for script execution quarantine');

        $content = file_get_contents($htaccessPath);
        $this->assertStringContainsString('php_flag engine off', $content);
        $this->assertStringContainsString('Require all denied', $content);
        $this->assertStringContainsString('Options -Indexes -ExecCGI', $content);
        $this->assertMatchesRegularExpression('/php|phar|sh|py|cgi|asp/i', $content);
    }

    public function test_public_htaccess_blocks_dotfiles_and_sensitive_file_extensions(): void
    {
        $htaccessPath = public_path('.htaccess');
        $this->assertFileExists($htaccessPath);

        $content = file_get_contents($htaccessPath);
        $this->assertStringContainsString('(?!well-known)', $content);
        $this->assertStringContainsString('env|bak|config|sql|ini|log|sh|inc|swp|dist|yml|yaml|lock', $content);
        $this->assertStringContainsString('Require all denied', $content);
    }

    public function test_docker_nginx_configuration_contains_execution_deny_rules(): void
    {
        $nginxPath = base_path('../infrastructure/docker/nginx/nginx.conf');
        $this->assertFileExists($nginxPath);

        $content = file_get_contents($nginxPath);
        $this->assertStringContainsString('location ^~ /storage/', $content);
        $this->assertStringContainsString('location ^~ /uploads/', $content);
        $this->assertStringContainsString('deny all', $content);
        $this->assertStringContainsString('Cross-Origin-Opener-Policy', $content);
        $this->assertStringContainsString('Cross-Origin-Resource-Policy', $content);
        $this->assertStringContainsString('Strict-Transport-Security', $content);
    }

    public function test_docker_mysql_configuration_contains_security_hardening(): void
    {
        $mysqlPath = base_path('../infrastructure/docker/mysql/my.cnf');
        $this->assertFileExists($mysqlPath);

        $content = file_get_contents($mysqlPath);
        $this->assertStringContainsString('local_infile = 0', $content);
        $this->assertStringContainsString('skip-symbolic-links = 1', $content);
        $this->assertStringContainsString('sql_mode', $content);
    }

    public function test_docker_php_configuration_contains_execution_and_cookie_hardening(): void
    {
        $phpPath = base_path('../infrastructure/docker/php/php.ini');
        $this->assertFileExists($phpPath);

        $content = file_get_contents($phpPath);
        $this->assertStringContainsString('disable_functions', $content);
        $this->assertStringContainsString('session.cookie_secure = 1', $content);
        $this->assertStringContainsString('session.cookie_httponly = 1', $content);
    }
}
