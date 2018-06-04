<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'HomeController@index');

Route::get('/home', 'HomeController@index');


$controllers = [
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
    'ban' => 'Auth\BanController',

    'forum' => 'Forum\ForumController',
    'thread' => 'Forum\ThreadController',
    'post' => 'Forum\PostController',

    'competition' => 'Competitions\CompetitionController',
    'competition-admin' => 'Competitions\CompetitionAdminController',
    'competition-entry' => 'Competitions\CompetitionEntryController',
    'competition-group' => 'Competitions\CompetitionGroupController',
    'competition-restriction' => 'Competitions\CompetitionRestrictionController',
    'competition-judging' => 'Competitions\CompetitionJudgingController',

    'panel' => 'User\PanelController',
    'message' => 'User\MessageController',
    'user' => 'User\UserController',

    'vault' => 'Vault\VaultController',
    'vault-review' => 'Vault\VaultReviewController',

    'wiki' => 'Wiki\WikiController',
    'comment' => 'Comments\CommentController',
    'news' => 'News\NewsController',
    'journal' => 'Journals\JournalController',
    'poll' => 'Polls\PollController',

    'api' => 'Api\ApiController',
    'search' => 'Search\SearchController'
];

\App\Helpers\Routing::controllers($controllers);


$this->get('password/reset', 'Auth\PasswordController@getReset')->name('password.request');
$this->post('password/email', 'Auth\PasswordController@postEmail')->name('password.email');
$this->get('password/reset/{token}', 'Auth\PasswordController@getReset')->name('password.reset');

// Important redirects
$legacy = [
    'forums.php' => function() {
        $page = array_get($_GET, 'page', 'last', 301);
        if (isset($_GET['thread'])) return redirect("/thread/view/{$_GET['thread']}?page={$page}", 301);
       	elseif (isset($_GET['id'])) return redirect("/forum/id/{$_GET['id']}", 301);
       	else return redirect('/forum', 301);
    },
    'competitions.php' => function() {
        if (isset($_GET['comp'])) return redirect("/competition/brief/{$_GET['comp']}", 301);
        elseif (isset($_GET['results'])) return redirect("competition/brief/{$_GET['results']}", 301);
        else return redirect('/competition', 301);
    },
    'vault.php' => function() {
        if (isset($_GET['map'])) return redirect("/vault/view/{$_GET['map']}", 301);
        else return redirect('/vault', 301);
    },
    'mapvault_map.php' => function() {
        if (isset($_GET['id'])) return redirect("/vault/view/{$_GET['id']}", 301);
        else return redirect('/vault', 301);
    },
    'user.php' => function () {
        if (isset($_GET['id'])) return redirect("/user/view/{$_GET['id']}", 301);
        else return redirect('/user', 301);
    },
    'journals.php' => function () {
        if (isset($_GET['id'])) return redirect("/journal/view/{$_GET['id']}", 301);
        else return redirect('/journal', 301);
    },
    'news.php' => function () {
        if (isset($_GET['id'])) return redirect("/news/view/{$_GET['id']}", 301);
        else return redirect('/news', 301);
    },
    'articulator.php' => function() {
        return redirect('/wiki/page/category:VERC_Archive');
    },
    'Legacy' => function() {
        return redirect('/');
    }
];

foreach ($legacy as $page => $fn) {
    Route::get($page, $fn);
    Route::get('Legacy/'.$page, $fn);
}

// Swagger convention suggests that the api definition should be available at /swagger.json
Route::get('/swagger.json', 'Api\ApiController@getIndex');

// Debug - add CORS
if (app('config')->get('app.debug')) {
    header('Access-Control-Allow-Origin:  *');
    header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
    header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization, X-XSRF-TOKEN');
}
