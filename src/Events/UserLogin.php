<?php

namespace Dx\Role\Events;
use Dx\Role\Models\LoginLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLogin implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct()
    {
        $user = auth()->user();
        $user_id = $user->id;
        $login_time = date('Y-m-d H:i:s');
        $ip = request()->header('x-real-ip', request()->ip());
        $login_address = '本地';
        if($ip !== '127.0.0.1'){
            $instance = \App\Handlers\BaiDuHandler::getInstance();
            $login_address = $instance::getLocationByIp($ip);
        }
        $is_success = 1;
        $user->increment('login_count');
        $user->save();
        LoginLog::query()->create(compact('user_id', 'ip', 'login_address', 'login_time', 'is_success'));
    }
}
