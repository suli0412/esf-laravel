<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Hier kannst du Commands explizit registrieren.
     * Dank Auto-Discovery ist das i. d. R. nicht nötig,
     * solange die Commands in app/Console/Commands liegen
     * und eine $signature haben.
     */
    protected $commands = [
        \App\Console\Commands\DbErdCommand::class,
    ];

    /**
     * Schedule für periodische Tasks (optional).
     */
    protected function schedule(Schedule $schedule): void
    {
        // z. B. $schedule->command('inspire')->hourly();
    }

    /**
     * Commands laden (inkl. Auto-Discovery des Ordners Commands).
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
