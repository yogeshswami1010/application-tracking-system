<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobLocation extends Model
{
    protected $guarded = ['id'];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'location_id');
    }
}
