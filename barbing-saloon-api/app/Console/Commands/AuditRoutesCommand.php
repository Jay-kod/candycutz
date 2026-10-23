<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route as RouteFacade;

class AuditRoutesCommand extends Command
{
    protected $signature = 'routes:audit {--fail-on-violation : Return non-zero exit code on unprotected routes}';

    protected $description = 'Audit all registered API routes against the public whitelist and security gate requirements';

    public function handle(): int
    {
        $whitelist = config('public_routes.routes', []);
        $allRoutes = RouteFacade::getRoutes()->getRoutes();

        $rows = [];
        $violations = 0;
        $totalRoutes = 0;

        foreach ($allRoutes as $route) {
            $uri = $route->uri();
            $methods = implode('|', array_filter($route->methods(), fn ($m) => $m !== 'HEAD'));

            // Filter internal routes or telescope/ignition if any
            if (str_starts_with($uri, '_ignition') || str_starts_with($uri, '_debugbar')) {
                continue;
            }

            $totalRoutes++;
            $middleware = $route->gatherMiddleware();
            $middlewareStr = implode(', ', $middleware);

            $isWhitelisted = $this->isWhitelisted($uri, $whitelist);

            $hasAuth = false;
            $roles = [];

            foreach ($middleware as $m) {
                if (str_contains($m, 'Authenticate:sanctum') || str_contains($m, 'auth:sanctum')) {
                    $hasAuth = true;
                }
                if (str_starts_with($m, 'check.role:') || str_starts_with($m, 'App\Http\Middleware\CheckRole:')) {
                    $roles[] = str_replace(['check.role:', 'App\Http\Middleware\CheckRole:'], '', $m);
                }
            }

            if ($isWhitelisted) {
                $status = '<info>PUBLIC (WHITELISTED)</info>';
            } elseif ($hasAuth) {
                $status = '<comment>PROTECTED (SANCTUM)</comment>';
            } else {
                $status = '<error>UNPROTECTED VIOLATION</error>';
                $violations++;
            }

            $roleStr = ! empty($roles) ? implode(', ', $roles) : ($hasAuth ? 'authenticated' : 'public');

            $rows[] = [
                $methods,
                $uri,
                $status,
                $roleStr,
                $this->formatMiddlewareBrief($middleware),
            ];
        }

        $this->table(
            ['Method', 'URI', 'Gate Status', 'Allowed Roles', 'Middleware Stack'],
            $rows
        );

        $this->newLine();
        $this->info("Total audited routes: {$totalRoutes}");

        if ($violations > 0) {
            $this->error("Found {$violations} unprotected route(s) not listed in config/public_routes.php!");
            if ($this->option('fail-on-violation')) {
                return Command::FAILURE;
            }
        } else {
            $this->info('All routes strictly adhere to authentication gates and the public whitelist.');
        }

        return Command::SUCCESS;
    }

    /**
     * Check if a given URI matches the whitelist.
     *
     * @param  array<string>  $whitelist
     */
    private function isWhitelisted(string $uri, array $whitelist): bool
    {
        if (in_array($uri, $whitelist, true)) {
            return true;
        }

        foreach ($whitelist as $pattern) {
            if (str_contains($pattern, '{') || str_contains($pattern, '*')) {
                $regex = preg_replace('/\{[^}]+\}/', '[^/]+', $pattern);
                $regex = '#^'.str_replace('\*', '.*', preg_quote($regex, '#')).'$#';
                if (preg_match($regex, $uri)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<string>  $middleware
     */
    private function formatMiddlewareBrief(array $middleware): string
    {
        $short = [];
        foreach ($middleware as $m) {
            $parts = explode('\\', $m);
            $short[] = end($parts);
        }

        return implode(', ', array_slice($short, 0, 4));
    }
}
