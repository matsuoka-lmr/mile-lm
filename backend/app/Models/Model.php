<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d');
    }
}
