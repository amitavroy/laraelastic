<?php

return [

    /**
     * The connection strings for the Elastic host config.
     */
    'hosts' => [
        'http://localhost:6200',
    ],

    /**
     * Prefix for the Elastic index.
     * It is important to have a prefix for each index because
     * based on this package and Elastic 6.0 recommendation,
     * we are going to create individual index per model
     * and it can clash with index of some other app running
     * on same elastic instance.
     */
    'prefix' => strtolower(config('app.name', '')),

];
