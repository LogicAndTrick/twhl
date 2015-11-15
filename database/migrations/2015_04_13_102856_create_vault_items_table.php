<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVaultItemsTable extends Migration {

	public function up()
	{
		Schema::create('vault_items', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('engine_id');
            $table->unsignedInteger('game_id');
            $table->unsignedInteger('category_id');
            $table->unsignedInteger('type_id');
            $table->unsignedInteger('license_id');
            $table->string('name');
            $table->text('content_text');
            $table->text('content_html');
            $table->boolean('is_hosted_externally');
            $table->string('file_location', 512);
            $table->integer('file_size');
            $table->boolean('flag_notify');
            $table->boolean('flag_ratings');
            $table->integer('stat_views');
            $table->integer('stat_downloads');
            $table->integer('stat_ratings');
            $table->integer('stat_comments');
            $table->decimal('stat_average_rating');
			$table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('engine_id')->references('id')->on('engines');
            $table->foreign('game_id')->references('id')->on('games');
            $table->foreign('category_id')->references('id')->on('vault_categories');
            $table->foreign('type_id')->references('id')->on('vault_types');
            $table->foreign('license_id')->references('id')->on('licenses');
		});

        DB:unprepared("
            CREATE PROCEDURE update_user_vault_statistics(uid INT)
            BEGIN
                UPDATE users
                SET stat_vault_items = (SELECT COUNT(*) FROM vault_items WHERE user_id = uid AND deleted_at IS NULL)
                WHERE id = uid;
            END;
        ");

        DB::unprepared("
            CREATE TRIGGER vault_items_update_statistics_on_insert AFTER INSERT ON vault_items
            FOR EACH ROW BEGIN
                CALL update_user_vault_statistics(NEW.user_id);
            END;");

        DB::unprepared("
            CREATE TRIGGER vault_items_update_statistics_on_update AFTER UPDATE ON vault_items
            FOR EACH ROW BEGIN
                CALL update_user_vault_statistics(NEW.user_id);

                IF NEW.user_id != OLD.user_id THEN
                    CALL update_user_vault_statistics(OLD.user_id);
                END IF;
            END;");
	}

	public function down()
	{
        DB::unprepared("DROP TRIGGER IF EXISTS vault_items_update_statistics_on_update");
        DB::unprepared("DROP TRIGGER IF EXISTS vault_items_update_statistics_on_insert");
        DB::unprepared("DROP PROCEDURE IF EXISTS update_user_vault_statistics");
		Schema::drop('vault_items');
	}

}
