<?php

namespace App\Listeners;

use App\Events\WikiTitleRenamed;
use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionMeta;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class UpdateWikiTitleLinks
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param WikiTitleRenamed $event
     * @return void
     */
    public function handle(WikiTitleRenamed $event)
    {
        $oldTitle = $event->originalTitle;
        $newTitle = $event->revision->title;

        $metas = WikiRevisionMeta::with(['wiki_revision'])
            ->join('wiki_revisions as wr', 'wr.id', '=', 'revision_id')
            ->where('key', '=', WikiRevisionMeta::LINK)
            ->where('wr.is_active', '=', 1)
            ->whereIn('value', [$oldTitle, 'upload:' . $oldTitle])
            ->get();
        foreach ($metas as $m) {
            $rev = $m->wiki_revision;
            $text = $rev->content_text;

            $text = preg_replace_callback('/\[file:' . preg_quote($oldTitle) . '(?:(\|[^\]]*?))?\]/im', function ($groups) use ($newTitle) {
                return '[file:' . $newTitle . (isset($groups[1]) ? $groups[1] : '') . ']';
            }, $text);

            $text = preg_replace_callback('/\[\[' . preg_quote($oldTitle) . '(?:(\|[^\]]*?))?\]\]/im', function ($groups) use ($newTitle) {
                return '[[' . $newTitle . (isset($groups[1]) ? $groups[1] : '') . ']]';
            }, $text);

            $text = preg_replace_callback('/\[\[upload:' . preg_quote($oldTitle) . '(?:(\|[^\]]*?))?\]\]/im', function ($groups) use ($newTitle) {
                return '[[upload:' . $newTitle . (isset($groups[1]) ? $groups[1] : '') . ']]';
            }, $text);

            // Don't create a new revision, just change the existing one
            if ($text != $rev->content_text) {
                $parse_result = bbcode_result($text);
                $rev->update([
                    'content_text' => $text,
                    'content_html' => $parse_result->ToHtml(),
                ]);
                $m->update([
                    'value' => $newTitle
                ]);
            }
        }
    }
}
