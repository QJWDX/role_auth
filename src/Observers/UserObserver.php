<?php


namespace Dx\Role\Observers;


use Dx\Role\Models\User;
use Illuminate\Support\Facades\DB;

class UserObserver
{
    public function deleted(User $user)
    {
        DB::table('role_user')->where('user_id', $user['id'])->delete();
    }
}
