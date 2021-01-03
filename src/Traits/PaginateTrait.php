<?php


namespace Dx\Role\Traits;


use Illuminate\Database\Eloquent\Builder;

Trait PaginateTrait
{
    /**
     * @param Builder $builder
     * @return array
     */
    public function paginateForApi(Builder $builder)
    {
        $request = request();
        $perPage = 10;
        if ($request->has("perPage")) {
            $perPage = $request->get("perPage");
        }
        if ($request->get("export", 0)) {
            $api = $builder->get();
        } else {
            $paginate = $builder->paginate($perPage);
            $api = [
                'current_page' => 0,
                'total' => 0,
                'last_page' => 0,
                'per_page' => 0,
                'items' => []
            ];
            $api['current_page'] = $paginate->currentPage();
            $api['total'] = $paginate->total();
            $api['last_page'] = $paginate->lastPage();
            $api['items'] = $paginate->items();
            $api['per_page'] = intval($paginate->perPage());
        }
        return $api;
    }

    /**
     * 树形结构（数据少的时候使用）
     * @param $arr
     * @param $id
     * @param $level
     * @return array
     */
    public function relationTree($arr, $id, $level)
    {
        $list = array();
        foreach ($arr as $k => $v) {
            if ($v['parent_id'] == $id) {
                $v['level'] = $level;
                $v['children'] = $this->relationTree($arr, $v['id'], $level + 1);
                $list[] = $v;
            }
        }
        return $list;
    }

    /**
     * 树形结构（数据少的时候使用）,自定义level值，键
     * @param $arr
     * @param $id
     * @param $level
     * @return array
     */
    public function multipleRuleRelationTree($arr, $id, $level, $level_filed = 'level', $is_fixed_filed = false)
    {
        $list = array();
        foreach ($arr as $k => $v) {
            if ($v['parent_id'] == $id) {
                $v[$level_filed] = $level;
                if ($is_fixed_filed) {
                    $next_level = $level;
                } else {
                    $next_level = $level + 1;
                }
                $v['children'] = $this->multipleRuleRelationTree($arr, $v['id'], $next_level, $level_filed, $is_fixed_filed);
                $list[] = $v;
            }
        }
        return $list;
    }
}
