<?php


namespace Dx\Role\Http\Controllers;
use Dx\Role\Http\Request\DeleteRequest;
use Dx\Role\Models\LoginLog;
use Illuminate\Http\Request;
use Dx\Role\Models\User;
class LoginLogController extends Controller
{
    public function index(Request $request, LoginLog $loginLog, User $user){
        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');
        $username = $request->get('username');
        $user_id = [];
        if($username){
            $user_id = $user->newQuery()->where('username', 'like', '%'.$username.'%')->pluck('id')->toArray();
        }
        $list = $loginLog->getLogList(compact('startTime', 'endTime', 'user_id'));
        return $this->success($list);
    }

    public function show($id){
        $loginLog = new LoginLog();
        $menu = $loginLog->getLogInfo(['id' => $id]);
        return $this->success($menu);
    }

    public function destroy($id)
    {
        $loginLog = new LoginLog();
        $result = $loginLog->newQuery()->where('id', $id)->delete();
        if($result){
            return $this->success('删除日志成功');
        }
        return $this->error('删除日志失败');
    }

    public function delLoginLog(DeleteRequest $request, LoginLog $loginLog){
        $ids = $request->get('ids');
        if($loginLog->del($ids)){
            return $this->success('删除日志成功');
        }
        return $this->error('删除日志失败');
    }
}
