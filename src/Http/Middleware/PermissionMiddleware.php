<?php

namespace Dx\Role\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Dx\Role\Exceptions\RoleException;
use Dx\Role\Models\Permission;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    protected $exceptedClass = [];
    protected $exceptedActions = [];

    public function __construct()
    {
        $this->exceptedClass = config("role.except.class");
        $this->exceptedActions = config("role.except.action");
    }

    public function handle($request, Closure $next)
    {
        /** @var Request $request */
        // don't check the options method, because cross-domain request need to pass
        $response = $next($request);
        if ($request->method() == 'OPTIONS') {
            return $response;
        }
        if ($this->shouldPassThrough($request)) {
            return $response;
        }

        $permission = new Permission();

        if (!$permission->shouldPassThrough($request)) {
            throw new RoleException('无权限访问');
        }

        return $response;
    }

    /**
     * @param $request
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        //去除某一些类
        if (strpos(app()->version(), "Lumen") !== false) {
            list($class, $method) = explode('@', $request->route()[1]['uses']);
        } else {
            list($class, $method) = explode('@', $request->route()->getActionName());
        }
        if (in_array($class, $this->exceptedClass) or in_array($method, $this->exceptedActions)) {
            return true;
        }

        return false;
    }
}
