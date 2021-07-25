<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Core\Lock\Active as LockActive;
use App\Models\User\UserRole;
use App\Models\Core\Role;
use App\Models\User;
use App\Models\User\Pointshop\PointshopItem as UserPointshopItem;
use App\Models\Economy\Pointshop\PointshopItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('redis:tag-update')->everyMinute();
        $schedule->command('pointshop.commissions:update')->everyMinute();
        $schedule->command('pointshop:cron-head-admin-hokage-hat')->hourly();

        $schedule->call(function() {
            $data = file_get_contents('https://blog.shaft.cc/ghost/api/v3/content/posts/?key=&fields=title,url,published_at&limit=1');

            $data = json_decode($data);

            Cache::set('last_news', $data->posts[0]);
        })->everyMinute();

        $schedule->call(function() {
            $keys = Redis::keys('premium_crate:*');
            foreach($keys as $key) {
                Redis::del($key);
            }
        })->daily();

        $schedule->call(function() {
			User::where('premium_at', '<', now())
				->update([
					'premium_at' => null
				]);
		})->everyMinute();

        $schedule->call(function() {
            UserRole::whereHas(
                'timeable',
                function($query) {
                    $query->where('ended_at', '<', now());
                }
            )->delete();
        })->everyMinute();

        $schedule->call(function() {
            $active = LockActive::get();
            foreach($active as $key => $act) {
                $locked_at = $act->locked_at->addSeconds($act->length);
                if (!is_null($act->unlock_at)) {
                    $act->delete();
                    continue;
                }

                if ($act->length == 0) {
                    continue;
                }

                if ($locked_at->diffInSeconds(now(), false) > 0) {
                    $act->delete();
                    // unset($active[$key]);
                }
            }
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
