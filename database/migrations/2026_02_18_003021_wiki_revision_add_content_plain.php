<?php

use App\Models\Wiki\WikiRevision;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wiki_revisions', function (Blueprint $table) {
            // Versions of the text without WikiCode or HTML, for example for use as og:description.
            $table->string('content_plain')->after('content_html');
        });

        // Turn off statistics updates (optimization)
        DB::unprepared("SET @disable_wiki_revisions_update_statistics_on_update = 1");


        // Update old revisions with a simple content_plain=content_text
        DB::unprepared("UPDATE wiki_revisions SET content_plain=content_text");

        // Process the WikiCode for all the active revisions
        foreach (WikiRevision::where('is_active', '=', 1)->get() as $revision) {
            $content_plain = $revision->content_text;
            try {
                $content_plain = bbcode_result($revision->content_text)->ToPlainText();
            } catch (\Throwable $e) { }
            $revision->content_plain = $content_plain;
            $revision->save();
        }

        DB::unprepared("SET @disable_wiki_revisions_update_statistics_on_update = NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wiki_revisions', function (Blueprint $table) {
            $table->dropColumn('content_plain');
        });
    }
};
