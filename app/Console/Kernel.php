<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        // $schedule->command('inspire')
        //          ->hourly();
        //$schedule->command('tpl:ordersReport')->daily();
        $schedule->command('logiwa:ordersReport')->daily();
        //$schedule->command('ShopifyIntegration:createOrders')->everyFiveMinutes()->withoutOverlapping(60);
        //$schedule->command('ShopifyIntegration:fulfillOrders')->everyFiveMinutes()->withoutOverlapping(60);
        //$schedule->command('ShopifyIntegration:cancelOrders')->everyFiveMinutes()->withoutOverlapping(60);
        $schedule->command('LogiwaShopifyIntegration:createOrders')->everyTenMinutes()->withoutOverlapping(60);
        $schedule->command('LogiwaShopifyIntegration:cancelOrders')->everyTenMinutes()->withoutOverlapping(60);
        $schedule->command('LogiwaShopifyIntegration:fulfillOrders')->everyTenMinutes()->withoutOverlapping(60);
        $schedule->command('BorboletaPanoplySync orders')->hourly()->withoutOverlapping(60);
        $schedule->command('BorboletaPanoplySync inventory')->cron('0 */4 * * *');
        $schedule->command('BorboletaPanoplySync inventory-history')->cron('30 */4 * * *');
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
