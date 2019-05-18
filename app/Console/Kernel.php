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
        'App\Console\Commands\ProcessWikiUploads',
        'App\Console\Commands\ProcessVaultScreenshots',
        'App\Console\Commands\ProcessVaultUploads',
        'App\Console\Commands\DeployFormat',
        'App\Console\Commands\DeployImages',
        'App\Console\Commands\DeployTutorialImages',

        'App\Console\Commands\FormatWiki',
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
