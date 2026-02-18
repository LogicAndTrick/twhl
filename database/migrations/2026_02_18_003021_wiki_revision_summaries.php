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
            // Contains summaries - short versions of the text without WikiCode or HTML
            // for use as og:description.
            $table->string('summary', 2 ** 16 - 1);
        });

        foreach (WikiRevision::where('is_active', '=', 1)->get() as $revision) {
            $summary = '';
            try {
                $summary = WikiRevision::summaryFromParseResult(bbcode_result($revision->content_text));
            } catch (\Throwable $e) { }
            $revision->summary = $summary;
            $revision->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wiki_revisions', function (Blueprint $table) {
            $table->dropColumn('summary');
        });
    }
};
