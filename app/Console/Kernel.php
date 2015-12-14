<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		'App\Console\Commands\Inspire',
        'App\Console\Commands\UpdateCompetitions',
        'App\Console\Commands\UpdateMotmWinners',
        'App\Console\Commands\ProcessWikiRevisions',
        'App\Console\Commands\ProcessComments',
        'App\Console\Commands\ProcessWikiUploads',
        'App\Console\Commands\ProcessVaultScreenshots',
        'App\Console\Commands\ProcessVault',
        'App\Console\Commands\ProcessJournals',
        'App\Console\Commands\ProcessNews',
        'App\Console\Commands\ProcessVaultUploads'
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('update:competitions')->hourly();
        $schedule->command('update:motm')->daily();
	}

}
