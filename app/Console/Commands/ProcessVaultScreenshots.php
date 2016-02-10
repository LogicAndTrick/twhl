<?php namespace App\Console\Commands;

use App\Helpers\Image;
use App\Models\Comments\Comment;
use App\Models\Vault\VaultItem;
use App\Models\Vault\VaultScreenshot;
use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionMeta;
use Illuminate\Console\Command;
use DB;

class ProcessVaultScreenshots extends Command {

	protected $name = 'process:vault_screenshots';
	protected $description = 'Process images in the unprocessed vault screenshots folder.';

	public function fire()
	{
        $path = public_path('uploads/vault/process_screens');
        if (!is_dir($path)) return;
        $files = glob($path.'/*');
        $count = 1;
        $total = count($files);
        foreach ($files as $file) {
            $info = pathinfo($file);
            $title = $info['basename'];    // 1234.jpg
            $filename = $info['filename']; // 1234
            $ext = $info['extension'];     // jpg
            if (preg_match('/^(\d*)\.(png|jpg)$/s', $title))
            {
                $item = VaultItem::with(['vault_screenshots'])->find($filename);
                if ($item != null && count($item->vault_screenshots) == 0) {

                    // We need the id to save the files, so create the db object first
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

                    // Save the screenshot at various sizes
                    $temp_dir = public_path('uploads/vault/temp');
                    $temp_name = $shot->id . '_temp.' . $ext;
                    copy($file, $temp_dir . '/' . $temp_name);
                    $thumbs = Image::MakeThumbnails(
                        $temp_dir . '/' . $temp_name, Image::$vault_image_sizes,
                        public_path('uploads/vault/'), $shot->id . '.' . $ext, true
                    );
                    unlink($temp_dir . '/' . $temp_name);

                    // Update the shot object
                    $shot->update([
                        'image_thumb' => $thumbs[0] ? $thumbs[0] : $thumbs[4],
                        'image_small' => $thumbs[1] ? $thumbs[1] : $thumbs[4],
                        'image_medium' => $thumbs[2] ? $thumbs[2] : $thumbs[4],
                        'image_large' => $thumbs[3] ? $thumbs[3] : $thumbs[4],
                        'image_full' => $thumbs[4],
                        'image_size' => filesize(public_path('uploads/vault/'.$thumbs[4]))
                    ]);
                    $this->comment("Done ({$count}/{$total}): {$title}");
                }
            }

            $count++;
        }
	}
}
