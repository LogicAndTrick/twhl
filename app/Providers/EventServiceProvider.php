<?php namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {

	/**
	 * The event handler mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [
	    'App\Events\WikiRevisionCreated' => [ 'App\Listeners\NotifyWikiWatchers', ],
	    'App\Events\CommentCreated' => [ 'App\Listeners\NotifyCommentWatchers', ],
	    'App\Events\ForumPostCreated' => [ 'App\Listeners\NotifyThreadWatchers', ],
	    'App\Events\VaultItemCreated' => [ 'App\Listeners\NotifyVaultWatchers', ],
	    'App\Events\MessageCreated' => [ 'App\Listeners\NotifyMessageThreadUsers', ],
        'App\Events\WikiTitleRenamed' => [ 'App\Listeners\UpdateWikiTitleLinks', ]
	];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        //
    }

}
