<?php
namespace Dx\Role;
use Dx\Role\Command\ResetUserStatus;
use Dx\Role\Http\Middleware\PermissionMiddleware;
use Dx\Role\Models\Menus;
use Dx\Role\Models\Permission;
use Dx\Role\Models\Role;
use Dx\Role\Observers\MenuObserver;
use Dx\Role\Observers\PermissionObserver;
use Dx\Role\Observers\RoleObserver;
use Illuminate\Support\ServiceProvider;

class RoleAuthServiceProvider extends ServiceProvider
{

    protected $routeMiddleware = [
        'role.role' => PermissionMiddleware::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'role' => [
            'role.role'
        ],
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->registerRouteMiddleware();

        $this->registerCommand();
    }
    /**
     * @inheritdoc
     */
    public function boot()
    {
//        require __DIR__.'/helpers.php';
        if (!$this->app->routesAreCached()) {

            require __DIR__.'/routes/api.php';

        }
        $this->publishes(
            [
                __DIR__.'/../config/role.php' => config_path('role.php'),
                __DIR__.'/../config/rsa.php' => config_path('rsa.php'),
                __DIR__.'/../config/login.php' => config_path('login.php'),
                __DIR__.'/../config/baidu.php' => config_path('baidu.php'),
            ],
            'config'
        );
        $this->publishes(
            [
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ],
            'migrations'
        );
        //face
        $this->registerFacades();
        $this->commands('runone.role.resetStatus');

        //注册观测器
        $this->registerObserver();
    }

    /**
     * Register the route middleware.
     *
     * @return void
     */
    protected function registerRouteMiddleware()
    {
        // register global middleware
//        foreach ($this->routeMiddleware as $middleware) {
//            app(\Illuminate\Contracts\Http\Kernel::class)->pushMiddleware($middleware);
//        }

        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }

        foreach ($this->middlewareGroups as $key => $middleware) {
            app('router')->middlewareGroup($key, $middleware);
        }
    }

    protected function registerFacades()
    {

    }

    protected function registerCommand()
    {
        app()->singleton('runone.role.resetStatus', function($app){
            return new ResetUserStatus();
        });
    }

    public function registerObserver()
    {
        Menus::observe(MenuObserver::class);
        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);
    }
}
