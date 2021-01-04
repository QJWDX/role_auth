<?php

namespace Dx\Role\Observers;

use Dx\Role\Models\Menus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MenuObserver
{

    public function created(Menus $menus)
    {
        //
    }


    public function updated(Menus $menus)
    {
        //1. 父级状态为关闭时，子集全部关闭，
        //2. 子集全部关闭时， 父级关闭
        if ($menus['parent_id'] == 0 and $menus['is_show'] == 0) {
            //父级关闭
            $menus->childMenus()->update([
                'is_show' => 0
            ]);
        }
        //子集关闭
        if ($menus['parent_id'] > 0) {
            //获取所有同子集的menus，是否全为关闭状态
            $parent_id = $menus['parent_id'];
            $status_list = $menus->refresh()->newQuery()->where('parent_id', $parent_id)->pluck('is_show')->unique();
            if ($status_list->count() === 1 && $status_list->first() === 0) {
                //子集全部关闭。关闭父级
                $menus->refresh()->newQuery()->where('id', $parent_id)->update([
                    'is_show' => 0
                ]);
            }
        }
    }


    public function deleted(Menus $menus)
    {
        DB::table('role_menus')->where('menus_id', $menus->id)->delete();
        DB::table('permission_menu')->where('menus_id', $menus->id)->delete();
    }
}
