<?php


namespace Dx\Role\Models;

use Carbon\Carbon;

class LoginLog extends BaseModel
{
    protected $table = 'login_log';

    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id')->select(['id','username']);
    }

    public function getLogList($params = []){
        return $this->paginateForApi($this->builderQuery($params));
    }

    public function getLogInfo($where = array()){
        $builder = $this->newQuery();
        if(!$where){
            return false;
        }
        return $builder->where($where)->first();
    }


    public function builderQuery($params = [], $field = ['*']){
        $builder = $this->newQuery()->with('user')->select($field);
        $builder = $builder->when($params['startTime'], function ($query) use($params){
            $query->where('login_time', '>', $params['startTime']);
        })->when($params['endTime'], function ($query) use($params){
            $query->where('login_time', '<', $params['endTime']);
        });
        return $builder;
    }


    public function del($ids = []){
        if(empty($ids)){
            return false;
        }
        $instances = $this->newQuery()->whereIn('id', $ids)->get('id');
        foreach ($instances as $instance){
            $instance->delete();
        }
        return true;
    }

    public function createLog($log)
    {
        $this->newQuery()->create($log);
    }
}
