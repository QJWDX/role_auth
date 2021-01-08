<?php


namespace Dx\Role\Models;


use Dx\Role\Traits\PaginateTrait;
use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{
    use PaginateTrait;
    protected $table = 'roles';
    protected $guarded = [];

    public function menus(){
        return $this->hasMany(RoleMenu::class, 'role_id', 'id')->pluck('menu_id');
    }

    public function roleList($params = []){
        $builder = $this->builderQuery($params);
        return $this->paginateForApi($builder);
    }

    public function getRole($params = []){
        if(!$params) return false;
        $builder = $this->newQuery()->where($params);
        return $builder->first();
    }

    public function builderQuery($params = [], $field = ['*']){
        $builder = $this->newQuery();
        $builder->when($params['name'], function ($query) use($params){
            $query->where('name', 'like', '%' . $params['name'] . '%');
        });
        $builder->select($field);
        return $builder;
    }

    public function isSuperRole($ids = []){
        return $this->newQuery()->whereIn('id', $ids)->where('is_super', 1)->exists();
    }

    public function del($ids = array()){
        if(empty($ids)){
            return false;
        }
        $instances = $this->newQuery()->whereIn('id', $ids)->get('id');
        foreach ($instances as $instance){
            $instance->delete();
        }
        return true;
    }

    public function getAll($params = [], $field = ['*']){
        return $this->newQuery()->where($params)->select($field)->get();
    }
}
