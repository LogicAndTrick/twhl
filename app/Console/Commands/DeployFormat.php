<?php

namespace App\Console\Commands;

use App\Models\Comments\Comment;
use App\Models\Competitions\Competition;
use App\Models\Competitions\CompetitionEntry;
use App\Models\Competitions\CompetitionRestriction;
use App\Models\Competitions\CompetitionResult;
use App\Models\Forums\ForumPost;
use App\Models\Journal;
use App\Models\Messages\Message;
use App\Models\News;
use App\Models\Polls\Poll;
use App\Models\Vault\VaultItem;
use App\Models\Vault\VaultItemReview;
use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionMeta;
use App\Models\Wiki\WikiType;
use App\Models\Wiki\WikiUpload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User;

class DeployFormat extends Command
{
    protected $signature = 'deploy:format {entity}';
    protected $description = 'Post deployment, process any records that need to be parsed with bbcode.';

    public function handle()
    {
        // Stop some triggers because they're pretty slow
        DB::unprepared("DROP TRIGGER IF EXISTS forum_posts_update_statistics_on_update");
        DB::unprepared("SET @disable_wiki_revisions_update_statistics_on_update = 1;");

        // Comments
        $this->process('comment', 'Comment', Comment::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Competitions
        $this->process('competition', 'Competition brief', Competition::where('brief_html', '=', '')->where('brief_text', '!=', ''), 'brief_text', 'brief_html');
        $this->process('competition', 'Competition results intro', Competition::where('results_intro_html', '=', '')->where('results_intro_text', '!=', ''), 'results_intro_text', 'results_intro_html');
        $this->process('competition', 'Competition results outro', Competition::where('results_outro_html', '=', '')->where('results_outro_text', '!=', ''), 'results_outro_text', 'results_outro_html');

        // Competition Entries
        $this->process('competition', 'Competition entry', CompetitionEntry::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Competition Restrictions
        $this->process('competition', 'Competition restriction', CompetitionRestriction::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Competition Results
        $this->process('competition', 'Competition result', CompetitionResult::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Forum Posts
        $this->process('post', 'Forum post', ForumPost::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Journals
        $this->process('journal', 'Journal', Journal::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Messages
        $this->process('message', 'Message', Message::where('content_html', '=', '')->where('content_text', '!=', ''));

        // News
        $this->process('news', 'News', News::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Polls
        $this->process('poll', 'Poll', Poll::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Users
        $this->process('user', 'User', User::where('info_biography_html', '=', '')->where('info_biography_text', '!=', ''), 'info_biography_text', 'info_biography_html');

        // Vault Items
        $this->process('vault', 'Vault item', VaultItem::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Vault Item Reviews
        $this->process('vault', 'Vault item review', VaultItemReview::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Wiki Revisions
        $this->process('wiki', 'Wiki revision', WikiRevision::query()->with(['wiki_object'])->where('content_text', '!=', '')->whereAny(['content_html', 'content_plain'], '=', ''), 'content_text', 'content_html', 'content_plain', function ($rev, $result) {
            DB::unprepared("delete from wiki_revision_metas where revision_id = {$rev->id}");
            $meta = [];
            foreach ($result->GetMetadata() as $md) {
                $c = $md['key'];
                $v = $md['value'];
                if ($c == 'WikiLink') {
                    $meta[] = new WikiRevisionMeta([ 'key' => WikiRevisionMeta::LINK, 'value' => $v ]);
                } else if ($c == 'WikiUpload') {
                    $meta[] = new WikiRevisionMeta([ 'key' => WikiRevisionMeta::LINK, 'value' => 'upload:' . $v ]);
                } else if ($c == 'WikiCategory') {
                    $meta[] = new WikiRevisionMeta([ 'key' => WikiRevisionMeta::CATEGORY, 'value' => str_replace(' ', '_', $v) ]);
                }
            }
            $object = $rev->wiki_object;
            if ($object->type_id == WikiType::UPLOAD) {
                $upload = WikiUpload::query()->where('object_id', '=', $object->id)->where('revision_id', '=', $rev->id)->first();
                if ($upload) {
                    $file_name = $upload->getServerFileName();
                    $info = getimagesize($file_name);
                    $size = filesize($file_name);
                    array_push(
                        $meta,
                        new WikiRevisionMeta(['key' => WikiRevisionMeta::UPLOAD_ID, 'value' => $upload->id]),
                        new WikiRevisionMeta(['key' => WikiRevisionMeta::FILE_SIZE, 'value' => $size]),
                        new WikiRevisionMeta(['key' => WikiRevisionMeta::IMAGE_WIDTH, 'value' => $info ? $info[0] : 0]),
                        new WikiRevisionMeta(['key' => WikiRevisionMeta::IMAGE_HEIGHT, 'value' => $info ? $info[1] : 0])
                    );
                }
            }
            $rev->wiki_revision_metas()->saveMany($meta);
        });

        // Let's get those triggers back in there
        DB::unprepared("SET @disable_wiki_revisions_update_statistics_on_update = NULL;");

        DB::unprepared("
            CREATE TRIGGER forum_posts_update_statistics_on_update AFTER UPDATE ON forum_posts
            FOR EACH ROW BEGIN
                CALL update_thread_statistics(NEW.thread_id);
                CALL update_forum_statistics(NEW.forum_id);
                CALL update_user_forum_statistics(NEW.user_id);

                IF NEW.thread_id != OLD.thread_id THEN
                    CALL update_thread_statistics(OLD.thread_id);
                END IF;

                IF NEW.forum_id != OLD.forum_id THEN
                    CALL update_forum_statistics(OLD.forum_id);
                END IF;

                IF NEW.user_id != OLD.user_id THEN
                    CALL update_user_forum_statistics(OLD.user_id);
                END IF;
            END;");
    }

    private function process(string $entity, string $type, Builder $query, string $source = 'content_text', string $target_html = 'content_html', null | string $target_plain = null, callable | null $callback = null)
    {
        $filteredEntity = $this->argument('entity');
        if ($filteredEntity != 'all' && $filteredEntity != $entity) return;

        $inc = 1000;
        $this->comment("Processing: {$type}");
        $grand_total = $query->count();

        for ($i = 0; $i < $grand_total; $i += $inc) {

            $cb = function($query_result) use ($type, $source, $target_html, $target_plain, $callback, $inc, $grand_total, $i) {

                $total = $query_result->count();
                $count = 1;
                $last_reported = 0;
                foreach ($query_result as $q) {
                    try {
                        $parse_result = bbcode_result($q->$source);
                        $q->$target_html = $parse_result->ToHtml();
                        if ($target_plain !== null) $q->$target_plain = $parse_result->ToPlainText();
                        $q->timestamps = false;
                        $q->save();
                        if ($callback && is_callable($callback)) {
                            call_user_func($callback, $q, $parse_result);
                        }
                    } catch (\Exception $ex) {
                        $this->comment("ERROR processing {$type}: {$q->id}");
                        throw $ex;
                    }
                    $count++;
                    $last_reported = $this->report($type . " group {$i} of {$grand_total}: ", $total, $count, $last_reported);
                }
            };

            $results = $query->limit($inc)->get();
            $cb($results);

            unset($results);
            unset($cb);
        }
    }

    private function report($type, $total, $count, $last_reported) {
        if ($count - $last_reported >= $total / 10) {
            $this->comment("{$type} {$count}/{$total}");
            return $count;
        }
        return $last_reported;
    }
}
