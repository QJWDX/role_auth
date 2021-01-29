<?php


namespace Dx\Role\Models;


class EmailLog extends BaseModel
{
    protected $table = 'email_log';
    protected $fillable = [
        'user_id',
        'email',
        'code'
    ];

    public function createLog($params = []){
        return $this->newQuery()->create($params);
    }

    public function getRecord($params = []){
        if(!$params){
            return false;
        }
        return $this->newQuery()->where($params)->select('code')->orderByDesc('created_at')->first();
    }
}
