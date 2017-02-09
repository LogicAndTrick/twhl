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

Route::controllers([
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
]);

// Swagger convention suggests that the api definition should be available at /swagger.json
Route::get('/swagger.json', 'Api\ApiController@getIndex');


header('Access-Control-Allow-Origin:  *');
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization, X-XSRF-TOKEN');