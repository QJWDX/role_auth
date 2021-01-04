<?php


namespace Dx\Role\Http\Controllers;
use Dx\Role\Http\Requests\DeleteRequest;
use Dx\Role\Models\LoginLog;
use Illuminate\Http\Request;

class LoginLogController extends Controller
{
    public function index(Request $request, LoginLog $loginLog){
        $where = $request->all();
        $list = $loginLog->getLogList($where);
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
