<?php namespace App\Console\Commands;

use App\Models\Comments\Comment;
use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionMeta;
use Illuminate\Console\Command;
use DB;
use Psy\Exception\ErrorException;

class ProcessComments extends Command {

	protected $name = 'process:comments';
	protected $description = 'Process all comments with blank html content.';

	public function fire()
	{
        $comments = Comment::where('content_html', '=', '')->get();
        $count = 1;
        $total = count($comments);
        foreach ($comments as $com) {
            try {
            $com->content_html = app('bbcode')->Parse($com->content_text);
            } catch (\Exception $ex) {
                $this->comment("ERROR processing ({$count}/{$total}): {$com->id}");
                throw $ex;
            }
            $com->save();

            $this->comment("Done ({$count}/{$total}): {$com->id}");
            $count++;
        }
	}
}
