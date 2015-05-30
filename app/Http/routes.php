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

    'wiki' => 'Wiki\WikiController',
    'vault' => 'Vault\VaultController',
    'comment' => 'Comments\CommentController',
    'news' => 'News\NewsController',
    'journal' => 'Journals\JournalController',
    'shout' => 'Shout\ShoutController',

    'api' => 'Api\ApiController',
]);
