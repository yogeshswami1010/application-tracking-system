<?php

namespace App;

use Shanmuga\LaravelEntrust\Models\EntrustRole;

class Role extends EntrustRole
{
    public function perms()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }

    // Backward-compatible alias. Some parts of the codebase use `permissions()`.
    public function permissions()
    {
        return $this->perms();
    }

    public function roleuser(){
        return $this->hasMany(RoleUser::class, 'role_id');
    }
}
