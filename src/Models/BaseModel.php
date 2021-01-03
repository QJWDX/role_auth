<?php


namespace Dx\Role\Models;


use Dx\Role\Traits\PaginateTrait;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use PaginateTrait;
}
