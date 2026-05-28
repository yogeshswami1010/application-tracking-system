<?php namespace App;

use Shanmuga\LaravelEntrust\Models\EntrustPermission;

class Permission extends EntrustPermission
{
    protected $guarded = ['id'];
}
