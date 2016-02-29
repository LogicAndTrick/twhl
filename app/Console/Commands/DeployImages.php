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

class DeployImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:images {path : The path to the "main" folder from TWHL3.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy all the images';

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

        $avatars_path = $path . DIRECTORY_SEPARATOR . 'avatars'; // avatars - done
        $compodl_path = $path . DIRECTORY_SEPARATOR . 'compodl'; // comp entries - done
        $compopics_path = $path . DIRECTORY_SEPARATOR . 'compopics'; // comp images + comp entry screenshots - done
        $mapvault_path = $path . DIRECTORY_SEPARATOR . 'mapvault'; // vault screenshots + vault items - done
        $tutorialdl_path = $path . DIRECTORY_SEPARATOR . 'tutorialdl'; // tutorial attachments
        $tutpics_path = $path . DIRECTORY_SEPARATOR . 'tutpics'; // tutorial images
        $uploads_path = $path . DIRECTORY_SEPARATOR . 'uploads'; // competition entries - done

        if (!is_dir($avatars_path)) { $this->comment('Couldn\'t find the "avatars" folder.'); return; }
        if (!is_dir($compodl_path)) { $this->comment('Couldn\'t find the "compodl" folder.'); return; }
        if (!is_dir($compopics_path)) { $this->comment('Couldn\'t find the "compopics" folder.'); return; }
        if (!is_dir($mapvault_path)) { $this->comment('Couldn\'t find the "mapvault" folder.'); return; }
        if (!is_dir($tutpics_path)) { $this->comment('Couldn\'t find the "tutpics" folder.'); return; }
        if (!is_dir($uploads_path)) { $this->comment('Couldn\'t find the "uploads" folder.'); return; }


        /**
         * --------------------------------
         * PROCESS AVATARS
         * --------------------------------
         */


        $users = User::where('avatar_custom', '=', 1)->get();
        $count = 1;
        $total = $users->count();
        $last_reported = 0;

        $temp = public_path('uploads/avatars/temp');
        if (!is_dir($temp)) mkdir($temp);

        foreach ($users as $user) {

            if (is_file(public_path('uploads/avatars/full/'.$user->avatar_file))) {
                // Skip if this user already has an avatar
                $count++;
                continue;
            }

            $av = $avatars_path . DIRECTORY_SEPARATOR . $user->avatar_file;

            if (is_file($av)) {
                $name = $user->avatar_file;

                $temp_name = $temp . DIRECTORY_SEPARATOR . $user->id . '_temp' . $name;
                copy($av, $temp_name);
                Image::MakeThumbnails($temp_name, Image::$avatar_image_sizes, public_path('uploads/avatars/'), $name, true);
                unlink($temp_name);
            } else {
                $user->avatar_custom = false;
                $user->avatar_file = 'user_noavatar1.png';
                $user->save();
            }
            $count++;
            $last_reported = $this->report("Avatars: ", $total, $count, $last_reported);
        }



        /**
         * --------------------------------
         * PROCESS COMPETITION ENTRIES
         * --------------------------------
         */


        $entries = CompetitionEntry::where('is_hosted_externally', '=', 0)->get();
        $count = 1;
        $total = $entries->count();
        $last_reported = 0;

        foreach ($entries as $entry) {

            $proper_location = public_path('uploads/competition/entries/' . $entry->file_location);

            if (is_file($proper_location) || !$entry->file_location) {
                // File is already in the right spot or we don't know where it is
                $count++;
                continue;
            }

            // Try and find the file and put it in the right spot
            $location = $uploads_path . DIRECTORY_SEPARATOR . $entry->file_location;
            if (!is_file($location)) $location = $compodl_path . DIRECTORY_SEPARATOR . $entry->file_location;
            if (!is_file($location)) {
                $this->comment('Warning: Entry file not found - ' . $entry->file_location);
                $entry->file_location = '';
                $entry->save();
            } else {
                copy($location, $proper_location);
            }

            $count++;
            $last_reported = $this->report("Entries: ", $total, $count, $last_reported);
        }


        /**
         * --------------------------------
         * PROCESS COMPETITION ENTRY SCREENSHOTS
         * --------------------------------
         */


        $shots = CompetitionEntryScreenshot::where('image_full', 'LIKE', 'legacy/%')->get();
        $count = 1;
        $total = $shots->count();
        $last_reported = 0;

        $temp = public_path('uploads/competition/temp');
        if (!is_dir($temp)) mkdir($temp);

        $c30_sub = $compopics_path . DIRECTORY_SEPARATOR . 'compo_030';

        foreach ($shots as $shot) {
            $file_name = str_ireplace('legacy/', '', $shot->image_full);

            $path = $compopics_path . DIRECTORY_SEPARATOR . $file_name;
            if (!is_file($path)) $path = $c30_sub . DIRECTORY_SEPARATOR . $file_name;
            if (!is_file($path)) {
                $shot->delete();
            } else {
                $info = pathinfo($file_name);
                $temp_path = $temp . DIRECTORY_SEPARATOR . $shot->id . '_temp.' . $info['extension'];
                copy($path, $temp_path);
                $thumbs = Image::MakeThumbnails(
                    $temp_path, Image::$comp_image_sizes,
                    public_path('uploads/competition/'), $shot->id . '.' . $info['extension'], true
                );
                unlink($temp_path);

                // Update the shot object
                $shot->update([
                    'image_thumb' => $thumbs[0] ? $thumbs[0] : $thumbs[1],
                    'image_full' => $thumbs[1]
                ]);
            }

            $count++;
            $last_reported = $this->report("Entry Screenshots: ", $total, $count, $last_reported);
        }


        /**
         * --------------------------------
         * PROCESS COMPETITION BRIEF IMAGES
         * --------------------------------
         */


        $competitions = Competition::get();
        $count = 1;
        $total = $competitions->count();
        $last_reported = 0;

        $comp_admin_permission = Permission::where('name', '=', 'CompetitionAdmin')->first();

        foreach ($competitions as $comp) {
            $text = $comp->brief_text;

            continue;

            preg_match_all('/\[img:(.*?)(\]|\|)/', $text, $result, PREG_SET_ORDER);
            for ($i = 0; $i < count($result); $i++) {
                $name = $result[$i][1];
            	$file_name = $compopics_path . DIRECTORY_SEPARATOR . str_ireplace('competition', 'compo', $result[$i][1]);

                // Find an entry in the wiki for this
                $rev = WikiRevision::where('slug', '=', 'upload:'.$name)->where('is_active', '=', 1)->first();
                if (!$rev && is_file($file_name)) {
                    // No wiki entry found, create a new one
                    $rev_desc = "[cat:Competition Images]\nThis is the image for competition #{$comp->id}: {$comp->name}";
                    $info = pathinfo($file_name);

                    $obj = WikiObject::Create([
                        'type_id' => WikiType::UPLOAD,
                        'permission_id' => $comp_admin_permission->id
                    ]);

                    $rev = WikiRevision::Create([
                        'object_id' => $obj->id,
                        'user_id' => 1983,
                        'is_active' => true,
                        'slug' => 'upload:'.$name,
                        'title' => $name,
                        'content_text' => $rev_desc,
                        'content_html' => app('bbcode')->Parse($rev_desc),
                        'message' => 'Automatically migrated from TWHL3'
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

                    $info = getimagesize($path);
                    $size = filesize($path);

                    $rev->wiki_revision_metas()->saveMany([
                        new WikiRevisionMeta(['key' => WikiRevisionMeta::CATEGORY, 'value' => 'Competition_Images' ]),
                        new WikiRevisionMeta(['key' => WikiRevisionMeta::UPLOAD_ID, 'value' => $upload->id]),
                        new WikiRevisionMeta(['key' => WikiRevisionMeta::FILE_SIZE, 'value' => $size]),
                        new WikiRevisionMeta(['key' => WikiRevisionMeta::IMAGE_WIDTH, 'value' => $info ? $info[0] : 0]),
                        new WikiRevisionMeta(['key' => WikiRevisionMeta::IMAGE_HEIGHT, 'value' => $info ? $info[1] : 0]),
                    ]);

                    DB::statement('CALL update_wiki_object(?);', [$obj->id]);
                }
            }

            $count++;
            $last_reported = $this->report("Competitions: ", $total, $count, $last_reported);
        }


        /**
         * --------------------------------
         * PROCESS VAULT ITEMS
         * --------------------------------
         */

        $items = VaultItem::with(['vault_screenshots'])->get();
        $count = 1;
        $total = $items->count();
        $last_reported = 0;

        $temp = public_path('uploads/vault/temp');
        if (!is_dir($temp)) mkdir($temp);

        foreach ($items as $item) {
            $server_path = $item->getServerFilePath();

            if (!$item->is_hosted_externally && !file_exists($server_path)) {
                $path = $mapvault_path . DIRECTORY_SEPARATOR . $item->id . substr($item->file_location, -4);
                if (is_file($path)) {
                    copy($path, $server_path);
                }
            }

            if (count($item->vault_sceeenshots) == 0) {
                $path = $mapvault_path . DIRECTORY_SEPARATOR . $item->id . '.jpg';
                if (!is_file($path)) $path = $mapvault_path . DIRECTORY_SEPARATOR . $item->id . '.png';
                if (is_file($path)) {

                    $shot = VaultScreenshot::Create([
                        'item_id' => $item->id,
                        'is_primary' => true,
                        'image_thumb' => '',
                        'image_small' => '',
                        'image_medium' => '',
                        'image_large' => '',
                        'image_full' => '',
                        'image_size' => 0,
                        'order_index' => 0
                    ]);

                    $info = pathinfo($path);
                    $temp_path = $temp . DIRECTORY_SEPARATOR . $shot->id . '_temp.' . $info['extension'];
                    copy($path, $temp_path);
                    $thumbs = Image::MakeThumbnails(
                        $temp_path, Image::$vault_image_sizes,
                        public_path('uploads/vault/'), $shot->id . '.' . $info['extension'], true
                    );
                    unlink($temp_path);

                    // Update the shot object
                    $shot->update([
                        'image_thumb' => $thumbs[0] ? $thumbs[0] : $thumbs[4],
                        'image_small' => $thumbs[1] ? $thumbs[1] : $thumbs[4],
                        'image_medium' => $thumbs[2] ? $thumbs[2] : $thumbs[4],
                        'image_large' => $thumbs[3] ? $thumbs[3] : $thumbs[4],
                        'image_full' => $thumbs[4],
                        'image_size' => filesize(public_path('uploads/vault/'.$thumbs[4]))
                    ]);
                } else {
                    $this->comment('not found: '.$path);
                }
            }

            $count++;
            $last_reported = $this->report("Vault Items: ", $total, $count, $last_reported);
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
