<?php namespace App\Console\Commands;

use App\Models\Comments\Comment;
use App\Models\Journal;
use App\Models\News;
use App\Models\Vault\VaultItem;
use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionMeta;
use Illuminate\Console\Command;
use DB;
use Psy\Exception\ErrorException;

class ProcessNews extends Command {

	protected $name = 'process:news';
	protected $description = 'Process all news posts with blank html content.';

	public function fire()
	{
        $items = News::where('content_html', '=', '')->get();
        $count = 1;
        $total = count($items);
        foreach ($items as $item) {
            try {
                $item->content_html = app('bbcode')->Parse($item->content_text);
            } catch (\Exception $ex) {
                $this->comment("ERROR processing ({$count}/{$total}): {$item->id}");
                throw $ex;
            }
            $item->save();

            $this->comment("Done ({$count}/{$total}): {$item->id}");
            $count++;
        }
	}
}
