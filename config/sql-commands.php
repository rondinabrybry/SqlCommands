<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Practice Database Path
    |--------------------------------------------------------------------------
    |
    | The path to the SQLite database file used for SQL practice.
    | This should be separate from your main application database.
    |
    */
    'practice_database_path' => database_path('practice.sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Allowed Operations
    |--------------------------------------------------------------------------
    |
    | Define which SQL operations are allowed in the practice environment.
    | Remove operations that you don't want students to use.
    |
    */
    'allowed_operations' => [
        'SELECT',
        'INSERT',
        'UPDATE',
        'DELETE',
        'CREATE',
        'DROP',
        'ALTER',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-create Sample Data
    |--------------------------------------------------------------------------
    |
    | Whether to automatically create sample tables and data when
    | the simulator is first used.
    |
    */
    'auto_create_samples' => true,

    /*
    |--------------------------------------------------------------------------
    | Maximum Query Execution Time
    |--------------------------------------------------------------------------
    |
    | Maximum time (in seconds) allowed for a single query to execute.
    | This helps prevent runaway queries in a learning environment.
    |
    */
    'max_execution_time' => 30,

    /*
    |--------------------------------------------------------------------------
    | Enable Web Interface
    |--------------------------------------------------------------------------
    |
    | Whether to enable the built-in web interface for SQL practice.
    | Set to false if you want to build your own interface.
    |
    */
    'enable_web_interface' => true,

    /*
    |--------------------------------------------------------------------------
    | Web Interface Route Prefix
    |--------------------------------------------------------------------------
    |
    | The route prefix for the web interface endpoints.
    |
    */
    'route_prefix' => 'sql-practice',

    /*
    |--------------------------------------------------------------------------
    | Web Interface Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to apply to the web interface routes.
    | You might want to add authentication middleware here.
    |
    */
    'middleware' => ['web'],
];