<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Laravel Starter Template API",
 *     description="A comprehensive Laravel starter template with authentication, health endpoints, and more.",
 *     @OA\Contact(
 *         email="csamsonok@gmail.com",
 *         name="API Support"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 */
class HealthController extends Controller
{
    /**
     * Basic health check endpoint
     * 
     * @OA\Get(
     *     path="/health",
     *     summary="Basic health check",
     *     description="Returns basic application health status",
     *     tags={"Health"},
     *     @OA\Response(
     *         response=200,
     *         description="Application is healthy",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="healthy"),
     *             @OA\Property(property="timestamp", type="string", format="date-time"),
     *             @OA\Property(property="version", type="string", example="1.0.0")
     *         )
     *     )
     * )
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
     * 
     * @OA\Get(
     *     path="/health/detailed",
     *     summary="Detailed health check",
     *     description="Returns detailed health status including database, cache, and storage checks",
     *     tags={"Health"},
     *     @OA\Response(
     *         response=200,
     *         description="All systems are healthy",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="healthy"),
     *             @OA\Property(property="timestamp", type="string", format="date-time"),
     *             @OA\Property(property="version", type="string", example="1.0.0"),
     *             @OA\Property(
     *                 property="checks",
     *                 type="object",
     *                 @OA\Property(
     *                     property="database",
     *                     type="object",
     *                     @OA\Property(property="status", type="string", example="healthy"),
     *                     @OA\Property(property="message", type="string", example="Database connection successful")
     *                 ),
     *                 @OA\Property(
     *                     property="cache",
     *                     type="object",
     *                     @OA\Property(property="status", type="string", example="healthy"),
     *                     @OA\Property(property="message", type="string", example="Cache is working properly")
     *                 ),
     *                 @OA\Property(
     *                     property="storage",
     *                     type="object",
     *                     @OA\Property(property="status", type="string", example="healthy"),
     *                     @OA\Property(property="message", type="string", example="All storage directories are accessible")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=503,
     *         description="One or more systems are unhealthy",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="unhealthy"),
     *             @OA\Property(property="timestamp", type="string", format="date-time"),
     *             @OA\Property(property="version", type="string", example="1.0.0"),
     *             @OA\Property(property="checks", type="object")
     *         )
     *     )
     * )
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