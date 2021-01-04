<?php

namespace Dx\Role\Observers;
use Dx\Role\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleObserver
{
    public function deleted(Role $role){
        DB::table('role_menus')->where('role_id', $role['id'])->delete();
    }
}
