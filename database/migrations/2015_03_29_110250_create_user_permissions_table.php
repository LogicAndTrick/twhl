<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPermissionsTable extends Migration {

	public function up()
	{
		Schema::create('user_permissions', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('permission_id');

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('permission_id')->references('id')->on('permissions');

            $table->unique(['user_id', 'permission_id']);
		});

        DB::unprepared("
            CREATE TRIGGER users_add_default_permissions_on_insert AFTER INSERT ON users
            FOR EACH ROW BEGIN
                INSERT INTO user_permissions (user_id, permission_id)
                SELECT NEW.id, id FROM permissions WHERE is_default = 1;
            END;");

        DB::unprepared("
            CREATE TRIGGER permissions_add_default_permission_to_all_users_on_insert AFTER INSERT ON permissions
            FOR EACH ROW BEGIN
                IF NEW.is_default = 1 THEN
                    INSERT INTO user_permissions (user_id, permission_id)
                    SELECT id, NEW.id FROM users WHERE deleted_at IS NULL;
                END IF;
            END;");
	}

	public function down()
	{
        DB::unprepared("DROP TRIGGER IF EXISTS users_add_default_permissions_on_insert");
        DB::unprepared("DROP TRIGGER IF EXISTS permissions_add_default_permission_to_all_users_on_insert");
		Schema::drop('user_permissions');
	}

}
