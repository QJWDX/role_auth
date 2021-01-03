<?php


namespace Dx\Role\Observers;


use Dx\Role\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionObserver
{
    public function deleted(Permission $permission){
        DB::table('permission_menu')->where('permission_id', $permission['id'])->delete();
        DB::table('permission_role')->where('permission_id', $permission['id'])->delete();
    }
}
