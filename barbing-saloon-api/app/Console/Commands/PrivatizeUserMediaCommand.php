<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PrivatizeUserMediaCommand extends Command
{
    protected $signature = 'user-media:privatize {--apply : Copy legacy files to private storage and update references}';

    protected $description = 'Move legacy account avatars and cover images from public to private storage';

    public function handle(): int
    {
        $publicDisk = Storage::disk('public');
        $privateDisk = Storage::disk('local');
        $changes = 0;
        $skipped = 0;

        User::query()
            ->where(fn ($query) => $query->whereNotNull('avatar')->orWhereNotNull('cover_image'))
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($publicDisk, $privateDisk, &$changes, &$skipped): void {
                foreach ($users as $user) {
                    foreach (['avatar' => 'avatar', 'cover_image' => 'cover'] as $field => $type) {
                        $storedValue = $user->{$field};
                        $sourcePath = $this->publicPath($storedValue);
                        if ($sourcePath === null || str_starts_with($sourcePath, "user-media/{$user->id}/")) {
                            continue;
                        }

                        $legacyDirectory = $field === 'avatar' ? 'uploads/avatars/' : 'uploads/covers/';
                        if (! str_starts_with($sourcePath, $legacyDirectory) || ! $publicDisk->exists($sourcePath)) {
                            $this->warn("Skipping user {$user->id} {$field}: legacy public file not found or path is unsupported.");
                            $skipped++;

                            continue;
                        }

                        $targetPath = "user-media/{$user->id}/{$type}/".basename($sourcePath);
                        $this->line("User {$user->id} {$field}: {$sourcePath} -> {$targetPath}");
                        $changes++;
                        if (! $this->option('apply')) {
                            continue;
                        }

                        $privateDisk->put($targetPath, $publicDisk->get($sourcePath));
                        $user->{$field} = $targetPath;
                        $user->save();

                        if ($field === 'avatar') {
                            Testimonial::query()
                                ->where('customer_id', $user->id)
                                ->whereIn('client_avatar', [$storedValue, $sourcePath, '/storage/'.$sourcePath])
                                ->update(['client_avatar' => $targetPath]);
                        }

                        $stillReferenced = User::query()
                            ->where('avatar', $storedValue)
                            ->orWhere('cover_image', $storedValue)
                            ->orWhere('avatar', $sourcePath)
                            ->orWhere('cover_image', $sourcePath)
                            ->orWhere('avatar', '/storage/'.$sourcePath)
                            ->orWhere('cover_image', '/storage/'.$sourcePath)
                            ->exists();
                        $testimonialReference = Testimonial::query()
                            ->whereIn('client_avatar', [$storedValue, $sourcePath, '/storage/'.$sourcePath])
                            ->exists();

                        if (! $stillReferenced && ! $testimonialReference) {
                            $publicDisk->delete($sourcePath);
                        }
                    }
                }
            });

        if (! $this->option('apply')) {
            $this->info("Dry run: {$changes} account image(s) would be moved; {$skipped} skipped. Re-run with --apply to execute.");

            return self::SUCCESS;
        }

        $this->info("Moved {$changes} account image(s) to private storage; {$skipped} skipped.");

        return self::SUCCESS;
    }

    private function publicPath(?string $value): ?string
    {
        if (! $value || preg_match('/^https?:\/\//i', $value)) {
            return null;
        }

        $path = ltrim($value, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return $path;
    }
}
