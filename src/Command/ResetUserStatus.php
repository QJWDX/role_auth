<?php

namespace Dx\Role\Command;

use Illuminate\Console\Command;
use Dx\Role\Models\User;

class ResetUserStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重置用户状态';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle(User $user)
    {
        $user->newQuery()->where("status", 2)->update(['status' => 1]);
    }
}
