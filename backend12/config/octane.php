<?php

use Laravel\Octane\Contracts\OperationTerminated;
use Laravel\Octane\Events\TickTerminated;
use Laravel\Octane\Events\WorkerErrorOccurred;
use Laravel\Octane\Events\WorkerStarting;
use Laravel\Octane\Events\WorkerStopping;

return [

    /*
    |--------------------------------------------------------------------------
    | Octane Server
    |--------------------------------------------------------------------------
    |
    | This value determines the default "server" that will be used by Octane
    | when starting, stopping, or restarting your server. The "swoole",
    | "roadrunner", and "frankenphp" servers are supported out of the box.
    |
    */

    'server' => env('OCTANE_SERVER', 'swoole'),

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS
    |--------------------------------------------------------------------------
    |
    | When this configuration value is set to "true", Octane will inform the
    | framework that all incoming requests should be treated as though they
    | are coming over HTTPS. This is useful when behind a load balancer.
    |
    */

    'https' => env('OCTANE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Octane Listeners
    |--------------------------------------------------------------------------
    |
    | All of the event listeners for Octane's events are defined below. These
    | listeners are responsible for handling various events that occur during
    | the lifetime of your Octane application.
    |
    */

    'listeners' => [
        WorkerStarting::class => [
            \Laravel\Octane\Listeners\EnsureUploadedFilesAreValid::class,
            \Laravel\Octane\Listeners\EnsureUploadedFilesCanBeMoved::class,
        ],

        WorkerErrorOccurred::class => [
            \Laravel\Octane\Listeners\ReportException::class,
            \Laravel\Octane\Listeners\StopWorkerIfNecessary::class,
        ],

        WorkerStopping::class => [],

        OperationTerminated::class => [
            \Laravel\Octane\Listeners\FlushOnce::class,
            \Laravel\Octane\Listeners\FlushTemporaryContainerInstances::class,
        ],

        TickTerminated::class => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Warmers
    |--------------------------------------------------------------------------
    |
    | Warmers allow you to "warm up" various aspects of your application so
    | that they are already loaded when the worker starts. This can help
    | to reduce the latency of the first request to your application.
    |
    */

    'warm' => [
        ...\Laravel\Octane\Octane::defaultServicesToWarm(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Flush List
    |--------------------------------------------------------------------------
    |
    | The following services will be flushed from the container after each
    | request that is handled by Octane. This helps to prevent memory leaks
    | and ensures that each request has a fresh instance of these services.
    |
    */

    'flush' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Garbage Collection Threshold
    |--------------------------------------------------------------------------
    |
    | When Octane is running, it will automatically perform garbage collection
    | after handling a certain number of requests. This helps to keep your
    | application's memory usage in check.
    |
    */

    'gc_threshold' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Swoole Configuration
    |--------------------------------------------------------------------------
    |
    | The following configuration options are specific to the Swoole server.
    | You may adjust these values as needed for your application.
    |
    */

    'swoole' => [
        'options' => [
            'log_file' => storage_path('logs/swoole_http.log'),
            'worker_num' => env('OCTANE_WORKERS', 'auto'),
            'task_worker_num' => env('OCTANE_TASK_WORKERS', 'auto'),
            'max_request' => env('OCTANE_MAX_REQUESTS', 0),
            'package_max_length' => 10 * 1024 * 1024,
        ],
    ],

];
