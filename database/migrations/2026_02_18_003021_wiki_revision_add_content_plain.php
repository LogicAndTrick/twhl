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
            $table->mediumText('content_plain')->after('content_html');
        });
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
