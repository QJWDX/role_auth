<?php


namespace Dx\Role\Handlers;


class CategoryHandler
{

    /**
     * 多级下拉框
     * @param $data
     * @param int $parent_id
     * @param null $parentName
     * @return array
     */
    public function select($data, $parent_id = 0, $parentName = null)
    {
        static $select = [];
        foreach($data as $category){
            if($category->parent_id == $parent_id){
                $category->parentName = $parentName ? ($parentName . ' / ' . $category->name) : $category->name;
                $select[$category->id] = $category->parentName;
                call_user_func_array([$this, __FUNCTION__],[$data, $category->id, $category->parentName]);
            }
        }
        return $select;
    }
}
