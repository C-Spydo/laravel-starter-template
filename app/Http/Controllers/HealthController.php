<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    /**
     * Basic health check endpoint
     */
    public function basic()
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
        ]);
    }

    /**
     * Detailed health check endpoint
     */
    public function detailed()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $allHealthy = collect($checks)->every(fn($check) => $check['status'] === 'healthy');

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
            'checks' => $checks,
        ], $allHealthy ? 200 : 503);
    }

    /**
     * Check database connectivity
     */
    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => 'healthy',
                'message' => 'Database connection successful',
            ];
        } catch (\Exception $e) {
            Log::error('Database health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'unhealthy',
                'message' => 'Database connection failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache functionality
     */
    private function checkCache()
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 60);
            $value = Cache::get($testKey);
            Cache::forget($testKey);

            if ($value === 'test') {
                return [
                    'status' => 'healthy',
                    'message' => 'Cache is working properly',
                ];
            }

            return [
                'status' => 'unhealthy',
                'message' => 'Cache read/write test failed',
            ];
        } catch (\Exception $e) {
            Log::error('Cache health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'unhealthy',
                'message' => 'Cache is not working',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage permissions
     */
    private function checkStorage()
    {
        $paths = [
            storage_path('logs'),
            storage_path('app'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];

        $issues = [];
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                $issues[] = "Directory does not exist: {$path}";
            } elseif (!is_writable($path)) {
                $issues[] = "Directory not writable: {$path}";
            }
        }

        if (empty($issues)) {
            return [
                'status' => 'healthy',
                'message' => 'All storage directories are accessible',
            ];
        }

        return [
            'status' => 'unhealthy',
            'message' => 'Storage directory issues detected',
            'issues' => $issues,
        ];
    }
} 