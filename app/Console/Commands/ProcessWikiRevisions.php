<?php namespace App\Console\Commands;

use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionMeta;
use Illuminate\Console\Command;
use DB;

class ProcessWikiRevisions extends Command {

	protected $name = 'process:wiki';
	protected $description = 'Process all wiki revisions with blank html content.';

	public function fire()
	{
        $revs = WikiRevision::where('content_html', '=', '')->get();
        $count = 1;
        $total = count($revs);
        foreach ($revs as $rev) {
            $parse_result = app('bbcode')->ParseResult($rev->content_text);
            $rev->content_html = $parse_result->text;
            $meta = [];
            foreach ($parse_result->meta as $c => $v) {
                if ($c == 'WikiLink') {
                    foreach ($v as $val) $meta[] = new WikiRevisionMeta([ 'key' => WikiRevisionMeta::LINK, 'value' => $val ]);
                } else if ($c == 'WikiImage') {
                    foreach ($v as $val) $meta[] = new WikiRevisionMeta([ 'key' => WikiRevisionMeta::LINK, 'value' => 'upload:' . $val ]);
                } else if ($c == 'WikiCategory') {
                    foreach ($v as $val) $meta[] = new WikiRevisionMeta([ 'key' => WikiRevisionMeta::CATEGORY, 'value' => str_replace(' ', '_', $val) ]);
                }
            }
            $rev->save();
            $rev->wiki_revision_metas()->saveMany($meta);
            DB::statement('CALL update_wiki_object(?);', [$rev->object_id]);

            $this->comment("Done ({$count}/{$total}): {$rev->title}");
            $count++;
        }
	}
}
