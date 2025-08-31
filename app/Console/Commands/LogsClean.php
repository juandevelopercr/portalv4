<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class LogsClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old activity logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Activity::where('created_at', '<', now()->subYear())->delete();
        $this->info('Logs older than a year have been successfully deleted.');
    }
}
