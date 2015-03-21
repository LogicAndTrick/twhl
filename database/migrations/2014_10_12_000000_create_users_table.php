<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
            // Login
			$table->increments('id');
			$table->string('name');
			$table->string('email')->unique();
			$table->string('password', 60);
            $table->string('legacy_password', 60);
			$table->rememberToken();

            $table->dateTime('last_login_time');
            $table->dateTime('last_access_time');
            $table->string('last_access_page');
            $table->string('last_access_ip', 15);

            // Options
            $table->integer('timezone');
            $table->boolean('show_email');

            // Avatar
            $table->boolean('avatar_custom');
            $table->string('avatar_file', 40);

            // Title
            $table->boolean('title_custom');
            $table->string('title_text');

            // Info
            $table->string('info_name');
            $table->string('info_website');
            $table->string('info_occupation');
            $table->string('info_interests');
            $table->string('info_location');
            $table->string('info_languages');
            $table->string('info_steam_profile');
            $table->integer('info_birthday');

            // Skills
            $table->boolean('skill_map');
            $table->boolean('skill_model');
            $table->boolean('skill_code');
            $table->boolean('skill_music');
            $table->boolean('skill_voice');
            $table->boolean('skill_animate');
            $table->boolean('skill_texture');

            // Stats
            $table->integer('stat_logins');
            $table->integer('stat_profile_hits');
            $table->integer('stat_forum_posts');
            $table->integer('stat_shouts');
            $table->integer('stat_maps');
            $table->integer('stat_journals');
            $table->integer('stat_wiki_edits');
            $table->integer('stat_comments');

            //
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
