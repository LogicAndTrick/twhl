<?php

namespace App\Console\Commands;

use App\Helpers\Image;
use App\Models\Accounts\Permission;
use App\Models\Accounts\User;
use App\Models\Competitions\Competition;
use App\Models\Competitions\CompetitionEntry;
use App\Models\Competitions\CompetitionEntryScreenshot;
use App\Models\Vault\VaultItem;
use App\Models\Vault\VaultScreenshot;
use App\Models\Wiki\WikiObject;
use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionMeta;
use App\Models\Wiki\WikiType;
use App\Models\Wiki\WikiUpload;
use Illuminate\Console\Command;
use DB;

class DeployTutorialImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:tutimages {path : The path to the "main" folder from TWHL3.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy the tutorial images only';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = $this->argument('path');
        if (!is_dir($path)) $path = realpath($this->laravel->basePath() . DIRECTORY_SEPARATOR . $path);
        if (!is_dir($path)) {
            $this->comment('Unable to locate path: ' . $path);
            return;
        }

        $this->comment('Located: ' . $path);

        $temp = public_path('uploads/wiki');
        if (!is_dir($temp)) mkdir($temp);

        $tutpics_path = $path . DIRECTORY_SEPARATOR . 'tutpics'; // tutorial images - done

        if (!is_dir($tutpics_path)) { $this->comment('Couldn\'t find the "tutpics" folder.'); return; }


        /**
         * -----------------------------------------------------
         * PROCESS TUTORIALS AND OTHER WIKI IMAGES / ATTACHMENTS
         * -----------------------------------------------------
         */


        $revisions = WikiRevision::where('is_active', '=', 1)->get();
        $count = 1;
        $total = $revisions->count();
        $last_reported = 0;

        foreach ($revisions as $revis) {
            $text = $revis->content_text;

            preg_match_all('/\[img:(.*?)(\]|\|)/', $text, $result, PREG_SET_ORDER);
            for ($i = 0; $i < count($result); $i++) {
                $name = $result[$i][1];
                $file_name = $tutpics_path . DIRECTORY_SEPARATOR . $result[$i][1];

                try {
                    $info = getimagesize($file_name);
                } catch (\ErrorException $ex) {
                    $info = null;
                }

                // Find an entry in the wiki for this
                $rev = WikiRevision::where('slug', '=', 'upload:'.$name)->where('is_active', '=', 1)->first();
                if (!$rev && is_file($file_name) && !!$info) {
                    // No wiki entry found, create a new one
                    $rev_desc = "[cat:Tutorial Images]\nThis is an image for: [[{$revis->title}]]";
                    $info = pathinfo($file_name);

                    $obj = WikiObject::Create([
                        'type_id' => WikiType::UPLOAD
                    ]);

                    $parsed_description = bbcode_result($rev_desc);
                    $rev = WikiRevision::Create([
                        'object_id' => $obj->id,
                        'user_id' => $revis->user_id,
                        'is_active' => true,
                        'slug' => 'upload:'.$name,
                        'title' => $name,
                        'content_text' => $rev_desc,
                        'content_html' => $parsed_description->ToHtml(),
                        'message' => 'Automatically migrated from TWHL3',
                        'summary' => WikiRevision::summaryFromParseResult($parsed_description)
                    ]);

                    $upload = WikiUpload::Create([
                        'object_id' => $obj->id,
                        'revision_id' => $rev->id,
                        'extension' => $info['extension']
                    ]);

                    $dir = public_path($upload->getRelativeDirectoryName());
                    if (!is_dir($dir)) mkdir($dir);
                    $path = $upload->getServerFileName();
                    copy($file_name, $path);

                    try {
                        $info = getimagesize($path);
                    } catch (\ErrorException $ex) {
                        $info = null;
                    }
                    $size = filesize($path);

                    $rev->wiki_revision_metas()->saveMany([
                        new WikiRevisionMeta(['key' => WikiRevisionMeta::CATEGORY, 'value' => 'Tutorial_Images' ]),
                        new WikiRevisionMeta(['key' => WikiRevisionMeta::UPLOAD_ID, 'value' => $upload->id]),
                        new WikiRevisionMeta(['key' => WikiRevisionMeta::FILE_SIZE, 'value' => $size]),
                        new WikiRevisionMeta(['key' => WikiRevisionMeta::IMAGE_WIDTH, 'value' => $info ? $info[0] : 0]),
                        new WikiRevisionMeta(['key' => WikiRevisionMeta::IMAGE_HEIGHT, 'value' => $info ? $info[1] : 0]),
                    ]);

                    DB::statement('CALL update_wiki_object(?);', [$obj->id]);
                }
            }

            $count++;
            $last_reported = $this->report("Wiki Revisions: ", $total, $count, $last_reported);
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
