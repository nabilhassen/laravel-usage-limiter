<?php

return [

    /*
     * When using the "HasLimits" trait from this package, we need to know which
     * Eloquent model should be used to retrieve your limits. Of course, it
     * is often just the "Permission" model but you may use whatever you like.
     *
     * The model you want to use as a Limit model needs to implement the
     * `Nabilhassen\LaravelUsageLimiter\Contracts\Limit` contract.
     */

    'models' => [

        'limit' => Nabilhassen\LaravelUsageLimiter\Models\Limit::class,

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

        /*
         * Change this if you want to name the related model primary key other than
         * `model_id`.
         */

        'model_morph_key' => 'model_id',
    ],
];
