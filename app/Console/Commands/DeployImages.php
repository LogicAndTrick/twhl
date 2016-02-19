<?php

namespace App\Console\Commands;

use App\Helpers\Image;
use App\Models\Accounts\User;
use Illuminate\Console\Command;

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

        $avatars_path = $path . DIRECTORY_SEPARATOR . 'avatars';
        $compodl_path = $path . DIRECTORY_SEPARATOR . 'compodl';
        $compopics_path = $path . DIRECTORY_SEPARATOR . 'compopics';
        $mapvault_path = $path . DIRECTORY_SEPARATOR . 'mapvault';
        $tutpics_path = $path . DIRECTORY_SEPARATOR . 'tutpics';
        $uploads_path = $path . DIRECTORY_SEPARATOR . 'uploads';

        if (!is_dir($avatars_path)) { $this->comment('Couldn\'t find the "avatars" folder.'); return; }
        if (!is_dir($compodl_path)) { $this->comment('Couldn\'t find the "compodl" folder.'); return; }
        if (!is_dir($compopics_path)) { $this->comment('Couldn\'t find the "compopics" folder.'); return; }
        if (!is_dir($mapvault_path)) { $this->comment('Couldn\'t find the "mapvault" folder.'); return; }
        if (!is_dir($tutpics_path)) { $this->comment('Couldn\'t find the "tutpics" folder.'); return; }
        if (!is_dir($uploads_path)) { $this->comment('Couldn\'t find the "uploads" folder.'); return; }

        // Process avatars
        $users = User::where('avatar_custom', '=', 1)->get();
        $count = 1;
        $total = $users->count();
        $last_reported = 0;

        $temp = public_path('uploads/avatars/temp');
        if (!is_dir($temp)) mkdir($temp);

        foreach ($users as $user) {

            if (is_file(public_path($user->avatar_full))) {
                // Skip if this user already has an avatar
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
    }

    private function report($type, $total, $count, $last_reported) {
        if ($count - $last_reported >= $total / 10) {
            $this->comment("{$type} {$count}/{$total}");
            return $count;
        }
        return $last_reported;
    }
}
