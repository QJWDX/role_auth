<?php


namespace Dx\Role\Models;

use Dx\Role\Traits\PaginateTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Dx\Role\Exceptions\RoleException;
use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission
{
    use PaginateTrait;
    protected $table = 'permissions';
    protected $guarded = [];

    // 权限检查
    public function shouldPassThrough(Request $request)
    {
        //获取当前登录得用户
        $user = Auth::guard('api')->user();
        if (!$user) {
            throw new RoleException('请登录', 401);
        }
        if ($user['is_super']) return true;

        //获取用户的角色与权限
        /** @var User $user */
        $roles = $user->cachedRoles();
        //无角色
        if ($roles->count() == 0) return false;

        //是超级管理员的角色
        if ($this->isAdministrator($roles)) return true;

        //获取path与method的name
        $path = $request->route()->uri;
        $method = $request->method();
        //去除不必要的参数
        $name = $this->newQuery()->where("path", $path)->where("method", 'like', '%' . $method . "%")->value("name");
        return $user->can($name);
    }

    public function permissionList($params = []){
        $builder = $this->builderQuery($params);
        return $this->paginateForApi($builder);
    }

    public function getPermission($params = []){
        if(!$params) return false;
        $builder = $this->newQuery()->where($params);
        return $builder->first();
    }

    public function builderQuery($params = [], $field = ['*']){
        $builder = $this->newQuery();
        $builder->when(isset($params['name']) && $params['name'], function ($query) use($params){
            $query->where('name', 'like', '%'. $params['name'].'%');
        })->when(isset($params['path']) && $params['path'], function ($query) use($params){
            $query->where('path', 'like', '%'. $params['path'].'%');
        })->when(isset($params['method']) && $params['method'], function ($query) use($params){
            $query->where('method', $params['method']);
        });
        $builder->select($field);
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

    /**
     * 判断是否为超级管理员
     * @param Collection $roles
     * @return mixed
     */
    protected function isAdministrator($roles)
    {
        return $roles->where("is_super", 1)->first();
    }
}
