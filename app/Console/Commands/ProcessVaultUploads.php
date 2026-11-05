<?php namespace App\Console\Commands;

use App\Helpers\Image;
use App\Models\Comments\Comment;
use App\Models\Vault\VaultItem;
use App\Models\Vault\VaultScreenshot;
use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionMeta;
use Illuminate\Console\Command;
use DB;

class ProcessVaultUploads extends Command {

	protected $name = 'process:vault_uploads';
	protected $description = 'Process uploads in the unprocessed vault uploads folder.';

	public function handle()
	{
        $path = storage_path('app/uploads/vault/process_uploads');
        if (!is_dir($path)) return;

        $files = glob($path.'/*');
        $count = 1;
        $total = count($files);
        foreach ($files as $file) {
            $info = pathinfo($file);
            $title = $info['basename'];    // 1234.zip
            $filename = $info['filename']; // 1234
            $ext = $info['extension'];     // zip
            if (preg_match('/^(\d*)\.(zip|rar|7z)$/s', $title))
            {
                $item = VaultItem::find($filename);
                $file_path = $item->getServerFilePath();
                if ($item != null && !file_exists($file_path)) {
                    copy($file, $file_path);
                    $item->file_size = filesize($file_path);
                    $item->save();
                    $this->comment("Done ({$count}/{$total}): {$title}");
                }
            }

            $count++;
        }
	}
}
