<?php

return [

    /*
     * When using the "HasLimits" trait from this package, we need to know which
     * Eloquent model should be used to retrieve your limits. Of course, it
     * is often just the "Permission" model but you may use whatever you like.
     *
     * The model you want to use as a Limit model needs to implement the
     * `NabilHassen\LaravelUsageLimiter\Contracts\Limit` contract.
     */

    'models' => [

        'limit' => NabilHassen\LaravelUsageLimiter\Models\Limit::class,

    ],

    /*
     * Change the relationship method name if you already have used for other purposes.
     */

    'relationship' => 'limits',

    'tables' => [

        /*
         * When using the "HasLimits" trait from this package, we need to know which
         * table should be used to retrieve your permissions. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'limits' => 'limits',

        /*
         * When using the "HasLimits" trait from this package, we need to know which
         * table should be used to retrieve your models limits. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'model_has_limits' => 'model_has_limits',
    ],

    'columns' => [

        /*
         * Change this if you want to name the related pivots other than defaults
         */

        'limit_pivot_key' => 'limit_id',
    ],

    /* Cache-specific settings */

    'cache' => [

        /*
         * By default all limits are cached for 24 hours to speed up performance. When
         * limits are created/updated/deleted the cache is flushed automatically.
         */

        'expiration_time' => \DateInterval::createFromDateString('24 hours'),

        /*
         * The cache key used to store all limits.
         */

        'key' => 'nabilhassen.limits.cache',

        /*
         * You may optionally indicate a specific cache driver/store to use for limits
         * caching using any of the `store` drivers listed in the cache.php config
         * file. Using 'default' here means to use the `default` set in cache.php.
         */

        'store' => 'default',
    ],
];
