<?php

namespace App\Http\Middleware;

use App\Helpers\SystemLogHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogSystemActions
{
    /**
     * Handle an incoming request and log the action
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log if user is authenticated
        if (!Auth::check()) {
            return $response;
        }

        // Don't log certain routes
        if ($this->shouldSkipLogging($request)) {
            return $response;
        }

        // Log the action
        $this->logAction($request, $response);

        return $response;
    }

    /**
     * Determine if the request should be logged
     */
    private function shouldSkipLogging(Request $request): bool
    {
        // Skip logging for these routes
        $skipRoutes = [
            'notifications.',              // Skip all notification routes
            'profile.',                    // Skip profile routes
            'password.',                   // Skip password routes
            'api.',                        // Skip API routes
            'livewire',                    // Skip Livewire requests
            'telescope',                   // Skip Laravel Telescope
            'admin.logs',                  // Skip logs viewing itself
        ];

        $routeName = $request->route()?->getName() ?? '';

        foreach ($skipRoutes as $skip) {
            if (str_contains($routeName, $skip)) {
                return true;
            }
        }

        // Skip GET requests (view-only, not actions)
        if ($request->method() === 'GET') {
            return true;
        }

        // Skip HEAD and OPTIONS
        if (in_array($request->method(), ['HEAD', 'OPTIONS'])) {
            return true;
        }

        return false;
    }

    /**
     * Log the user action
     */
    private function logAction(Request $request, Response $response): void
    {
        try {
            $method = $request->method();
            $path = $request->path();
            $routeName = $request->route()?->getName() ?? 'unknown';

            // Determine action type based on HTTP method
            $action = match ($method) {
                'POST' => 'created',
                'PUT', 'PATCH' => 'updated',
                'DELETE' => 'deleted',
                default => strtolower($method),
            };

            // Extract model and ID from route if possible
            $modelInfo = $this->extractModelInfo($request, $routeName);

            // Get response status
            $statusCode = $response->getStatusCode();
            $isSuccess = $statusCode >= 200 && $statusCode < 300;

            // Build description
            $description = sprintf(
                '%s %s (Status: %d) - %s',
                $method,
                $path,
                $statusCode,
                $isSuccess ? 'Success' : 'Failed'
            );

            // Capture request data (sensitive fields excluded)
            $changes = $this->sanitizeRequestData($request);

            // Create the log entry
            SystemLogHelper::log(
                action: $action,
                description: $description,
                model: $modelInfo['model'],
                modelId: $modelInfo['id'],
                changes: $changes
            );
        } catch (\Exception $e) {
            // Silently fail - don't disrupt application if logging fails
            Log::error('System log middleware error: ' . $e->getMessage());
        }
    }

    /**
     * Extract model information from route parameters
     */
    private function extractModelInfo(Request $request, string $routeName): array
    {
        $model = null;
        $id = null;

        // Parse model and ID from route name (e.g., "quotations.update" -> "Quotation", "1")
        $routeParts = explode('.', $routeName);

        if (count($routeParts) >= 1) {
            $resourceName = $routeParts[0];

            // Map route resource to model class
            $modelMap = [
                'quotations' => 'App\Models\Quotation',
                'projects' => 'App\Models\Project',
                'materials' => 'App\Models\Material',
                'clients' => 'App\Models\Client',
                'users' => 'App\Models\User',
                'comments' => 'App\Models\Comment',
                'quotation-comments' => 'App\Models\QuotationComment',
            ];

            if (isset($modelMap[$resourceName])) {
                $model = $modelMap[$resourceName];

                // Try to get ID from route parameters
                $routeParameters = $request->route()?->parameters() ?? [];
                
                // Handle typical route patterns
                foreach (['id', $resourceName, Str::singular($resourceName)] as $key) {
                    if (isset($routeParameters[$key])) {
                        $id = $routeParameters[$key];
                        
                        // If it's an object, get the ID
                        if (is_object($id)) {
                            $id = $id->id ?? null;
                        }
                        
                        break;
                    }
                }
            }
        }

        return [
            'model' => $model,
            'id' => $id,
        ];
    }

    /**
     * Sanitize request data - remove sensitive fields
     */
    private function sanitizeRequestData(Request $request): ?array
    {
        // Fields to exclude from logging
        $excludedFields = [
            'password',
            'password_confirmation',
            'token',
            'api_token',
            'secret',
            'credit_card',
            'cvv',
            'ssn',
            'social_security',
        ];

        $data = $request->except($excludedFields);

        // Only return if there's meaningful data
        if (empty($data) || count($data) === 0) {
            return null;
        }

        // Limit size to prevent bloat
        if (json_encode($data) && strlen(json_encode($data)) > 5000) {
            return ['_notice' => 'Request data exceeded size limit'];
        }

        return $data;
    }
}
