<?php

namespace App\Console\Commands;

use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionMeta;
use App\Models\Wiki\WikiType;
use Illuminate\Console\Command;

class FormatWiki extends Command
{
    protected $signature = 'format:wiki {scope=unformatted} {--slugs-only}';
    protected $description = 'Format wiki revisions. Scope can be one of: unformatted (default), active, all';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $scope = $this->argument('scope');
        $slugs_only = $this->option('slugs-only') === true;
        $query = WikiRevision::query()->with(['wiki_revision_metas', 'wiki_object']);
        switch ($scope) {
            case 'unformatted':
                $query->where('content_html', '=', '');
                break;
            case 'active':
                $query->where('is_active', '=', 1);
                break;
            case 'all':
                break;
            default:
                $this->error("Unknown scope: $scope.");
                return;
                break;
        }

        $chunk_size = 100;
        $total = $query->count();
        $this->info("Formatting $total wiki article(s). Scope: $scope" . ($slugs_only ? ' (slugs only)' : ''));
        $current = 0;

        $query->chunk($chunk_size, function ($revisions) use (&$current, $total, $slugs_only) {
            /** @var WikiRevision $rev */
            foreach ($revisions as $rev ) {

                $trim_title = substr($rev->title, 0, 50);
                if (strlen($rev->title) > strlen($trim_title)) $trim_title = substr($trim_title, 0, 47) . '...';
                $this->getOutput()->write("\r[$current/$total] Revision #{$rev->id} ($trim_title)");
                $current++;

                $rev->slug = WikiRevision::CreateSlug($rev->title);
                if ($rev->wiki_object && $rev->wiki_object->type_id == WikiType::UPLOAD) $rev->slug = 'upload:'.$rev->slug;

                if (!$slugs_only) {
                    $result = app('bbcode')->ParseResult($rev->content_text);
                    $rev->content_html = $result->text;
                    $rev->timestamps = false;

                    $meta = [];
                    foreach ($result->meta as $c => $v) {
                        if ($c == 'WikiLink') {
                            foreach ($v as $val) $meta[] = new WikiRevisionMeta(['key' => WikiRevisionMeta::LINK, 'value' => $val]);
                        } else if ($c == 'WikiUpload') {
                            foreach ($v as $val) $meta[] = new WikiRevisionMeta(['key' => WikiRevisionMeta::LINK, 'value' => 'upload:' . $val]);
                        } else if ($c == 'WikiCategory') {
                            foreach ($v as $val) $meta[] = new WikiRevisionMeta(['key' => WikiRevisionMeta::CATEGORY, 'value' => str_replace(' ', '_', $val)]);
                        }
                    }
                    $rev->wiki_revision_metas()->whereIn('key', [WikiRevisionMeta::LINK, WikiRevisionMeta::CATEGORY])->delete();
                    $rev->wiki_revision_metas()->saveMany($meta);
                }

                $rev->save();
                $this->getOutput()->write("\r" . str_repeat(' ', 80));
            }
        });
        $this->getOutput()->write("\r");
        $this->info("Done.");
    }
}
