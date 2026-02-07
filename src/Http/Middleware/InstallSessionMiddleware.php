<?php

namespace Gopos\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class InstallSessionMiddleware
{
    /**
     * Handle an incoming request.
     * Forces file-based sessions during installation to avoid database dependency.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if sessions table exists, if not force file driver
        if (! $this->sessionsTableExists()) {
            Config::set('session.driver', 'file');
        }

        return $next($request);
    }

    /**
     * Check if the sessions table exists in the database.
     */
    protected function sessionsTableExists(): bool
    {
        try {
            return Schema::hasTable('sessions');
        } catch (\Exception $e) {
            return false;
        }
    }
}
