<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // See https://github.com/LogicAndTrick/twhl/issues/133

        $brokenObjects = DB::select("SELECT obj.id AS id FROM wiki_objects AS obj INNER JOIN wiki_revisions AS rev ON obj.current_revision_id=rev.id WHERE rev.is_active=0 AND obj.deleted_at IS NULL");

        foreach ($brokenObjects as $bo) {
            $id = $bo->id;
            DB::statement('CALL update_wiki_object(?);', [$id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
