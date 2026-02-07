<?php

namespace Gopos\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstalled
{
    /**
     * Routes that are part of the installation process.
     */
    protected array $installRoutes = ['install', 'install.*'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isInstalled = $this->isInstalled();
        $isInstallRoute = $request->routeIs(...$this->installRoutes);

        // If not installed and not on install route, redirect to install
        if (! $isInstalled && ! $isInstallRoute) {
            return redirect()->route('install');
        }

        // If installed and on install route, redirect to home
        if ($isInstalled && $isInstallRoute) {
            return redirect('/');
        }

        return $next($request);
    }

    /**
     * Check if the application is installed.
     */
    protected function isInstalled(): bool
    {
        return file_exists(storage_path('app/.installed'));
    }
}
