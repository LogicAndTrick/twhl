<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Data migration to delete all reactions except for the first for each article
        DB::unprepared("
            delete
            from reactions
            where id in (
                select id from (
                    select id, entity_type, entity_id, user_id,
                           row_number() over (partition by entity_type, entity_id, user_id order by id asc) as rn
                    from reactions
                ) q
                where rn > 1
            )
        ");
        
        // Unique index on reactions
        Schema::table('reactions', function (Blueprint $table) {
            $table->unique(['entity_type', 'entity_id', 'user_id'], 'reactions_entity_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reactions', function (Blueprint $table) {
            $table->dropUnique('reactions_entity_user_unique');
        });
    }
};
