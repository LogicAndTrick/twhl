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
use Illuminate\Console\Command;
use DB;
use Illuminate\Foundation\Auth\User;

class DeployFormat extends Command
{
    protected $signature = 'deploy:format';
    protected $description = 'Post deployment, process any records that need to be parsed with bbcode.';

    public function handle()
    {
        // Stop some triggers because they're pretty slow
        DB::unprepared("DROP TRIGGER IF EXISTS forum_posts_update_statistics_on_update");
        DB::unprepared("DROP TRIGGER IF EXISTS wiki_revisions_update_statistics_on_update");

        // Comments
        $this->process('Comment', Comment::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Competitions
        $this->process('Competition brief', Competition::where('brief_html', '=', '')->where('brief_text', '!=', ''), 'brief_text', 'brief_html');
        $this->process('Competition results intro', Competition::where('results_intro_html', '=', '')->where('results_intro_text', '!=', ''), 'results_intro_text', 'results_intro_html');
        $this->process('Competition results outro', Competition::where('results_outro_html', '=', '')->where('results_outro_text', '!=', ''), 'results_outro_text', 'results_outro_html');

        // Competition Entries
        $this->process('Competition entry', CompetitionEntry::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Competition Restrictions
        $this->process('Competition restriction', CompetitionRestriction::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Competition Results
        $this->process('Competition result', CompetitionResult::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Forum Posts
        $this->process('Forum post', ForumPost::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Journals
        $this->process('Journal', Journal::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Messages
        $this->process('Message', Message::where('content_html', '=', '')->where('content_text', '!=', ''));

        // News
        $this->process('News', News::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Polls
        $this->process('Poll', Poll::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Users
        $this->process('User', User::where('info_biography_html', '=', '')->where('info_biography_text', '!=', ''), 'info_biography_text', 'info_biography_html');

        // Vault Items
        $this->process('Vault item', VaultItem::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Vault Item Reviews
        $this->process('Vault item review', VaultItemReview::where('content_html', '=', '')->where('content_text', '!=', ''));

        // Wiki Revisions
        $this->process('Wiki revision', WikiRevision::where('content_html', '=', '')->where('content_text', '!=', ''), 'content_text', 'content_html', function ($rev, $result) {
            $meta = [];
            foreach ($result->meta as $c => $v) {
                if ($c == 'WikiLink') {
                    foreach ($v as $val) $meta[] = new WikiRevisionMeta([ 'key' => WikiRevisionMeta::LINK, 'value' => $val ]);
                } else if ($c == 'WikiUpload') {
                    foreach ($v as $val) $meta[] = new WikiRevisionMeta([ 'key' => WikiRevisionMeta::LINK, 'value' => 'upload:' . $val ]);
                } else if ($c == 'WikiCategory') {
                    foreach ($v as $val) $meta[] = new WikiRevisionMeta([ 'key' => WikiRevisionMeta::CATEGORY, 'value' => str_replace(' ', '_', $val) ]);
                }
            }
            $rev->wiki_revision_metas()->saveMany($meta);
            //DB::statement('CALL update_wiki_object(?);', [$rev->object_id]);
        });

        // Let's get those triggers back in there
        DB::unprepared("
            CREATE TRIGGER wiki_revisions_update_statistics_on_update AFTER UPDATE ON wiki_revisions
            FOR EACH ROW BEGIN
                CALL update_user_wiki_statistics(NEW.user_id);

                IF NEW.user_id != OLD.user_id THEN
                    CALL update_user_wiki_statistics(OLD.user_id);
                END IF;
            END;");

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

    private function process($type, $query, $source = 'content_text', $target = 'content_html', $callback = null)
    {
        $this->comment("Processing: {$type}");
        $grand_total = $query->count();
        $i = 0;

        $query->chunk(1000, function ($query_result) use (&$i, $grand_total, $type, $target, $source, $callback) {
            $last_reported = 0;
            $total = $query_result->count();
            $count = 1;
            foreach ($query_result as $q) {
                try {
                    $parse_result = app('bbcode')->ParseResult($q->$source);
                    $q->$target = $parse_result->text;
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
            $i += 1000;
        });
    }

    private function report($type, $total, $count, $last_reported) {
        if ($count - $last_reported >= $total / 10) {
            $this->comment("{$type} {$count}/{$total}");
            return $count;
        }
        return $last_reported;
    }
}
