# 系统设置公共包
## Requirements
- PHP >= 7.0.0
- Laravel >= 5.6.0
- zizaco/entrust >= 1.9

## Installation

### 发布配置文件

```
php artisan vendor:publish --provider="Dx\Role\RolePermissionsServiceProvider"
```

>配置User表与类，`config/role.php`


```
return [
    'user' => 'App\Models\Base\User',         //类名
    'user_table' => 'users'                   //表名
];
```

> 添加Trait到`UserController`,提供添加用户权限等


```
use Runone\Role\Traits\RoleUserTrait;

class UserController {
    user RoleUserTrait;
    ...
}
```

> 添加Trait到`User`模型中

```
use Runone\Role\Traits\RoleUserModelTrait;

class User extents Model {
    user RoleUserModelTrait;
    ...
}
```

### 运行迁移

```
php artisan migrate
```

### 开启权限控制

> 配置`role` 到相对应的`middleware`即可，例如`app/Http/Kerenl.php`

```
protected $middlewareGroups = [
    'api' => [
        'role'
    ]
]
```

即可将所有api进行权限控制。




