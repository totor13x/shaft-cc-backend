<?php

namespace App\Console\Commands;

use App\Models\User\UserRole;
use App\Models\Core\Role;
use App\Models\User\Pointshop as UserPointshop;
use App\Models\User\Pointshop\PointshopItem as UserPointshopItem;
use App\Models\Economy\Pointshop\PointshopItem;

use Illuminate\Console\Command;

class AddHokageItemToHeadAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pointshop:cron-head-admin-hokage-hat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $hokageItem = PointshopItem::whereName('Хокаге')->first();

        // Удаляем, если роль не главного админа
        $lastItems = UserPointshopItem::where('pointshop_item_id', $hokageItem->id)
            ->with('pointshop.user')
            ->get()
            ->filter(function($o) {
                return !$o->pointshop->user->hasRole('head-admin');
            })
            ->each(function($item) {
                $item->delete();
            });

        $role = Role::whereSlug('head-admin')->first();
        UserRole::whereRoleId($role->id)
            ->with('serverable')
            ->get()
            ->each(function($uRole) use ($hokageItem) {
                if ($hokageItem->servers->contains('id', $uRole->serverable->server_id)) {
                    $userPointshop = UserPointshop::whereUserId($uRole->user_id)
                        ->whereServerId($uRole->serverable->server_id)
                        ->first();

                    if ($userPointshop) {
                        if (
                            !$userPointshop->items
                                ->contains('pointshop_item_id', $hokageItem->id)
                        ) {
                            $userPointshop->user
                                ->pointshopAddItem(
                                    $userPointshop->server_id,
                                    $hokageItem->id
                                );
                        }
                    }
                }
            });
    }
}
