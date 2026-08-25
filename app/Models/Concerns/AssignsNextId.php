<?php

namespace App\Models\Concerns;

trait AssignsNextId
{
    protected static function bootAssignsNextId()
    {
        static::creating(function ($model) {
            if (empty($model->getKey())) {
                $next = (int) static::query()->max($model->getKeyName());
                $model->setAttribute($model->getKeyName(), $next + 1);
            }

            // Hostinger tables often have no AUTO_INCREMENT. Laravel's insertGetId
            // then returns 0 and overwrites the real id — items save with order_id=0.
            $model->incrementing = false;
        });
    }
}
