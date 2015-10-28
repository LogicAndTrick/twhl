<?php namespace App\Console\Commands;

use App\Models\Wiki\WikiObject;
use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionMeta;
use App\Models\Wiki\WikiType;
use App\Models\Wiki\WikiUpload;
use Illuminate\Console\Command;
use DB;

class ProcessWikiuploads extends Command {

	protected $name = 'process:wiki_uploads';
	protected $description = 'Process any images in the uploads/wiki/process folder and create uploads from them.';

	public function fire()
	{
        $path = public_path('uploads/wiki/process');
        if (!is_dir($path)) return;

        $files = glob($path.'/*');
        $count = 1;
        $total = count($files);
        foreach ($files as $file) {
            $object = WikiObject::Create([ 'type_id' => WikiType::UPLOAD ]);
            $text = 'This image was automatically converted from the TWHL3 tutorials database.';
            $parse_result = app('bbcode')->ParseResult($text);
            $info = pathinfo($file);

            // The title can only change for standard pages
            $title = $info['basename'];
            $slug = 'upload:' . WikiRevision::CreateSlug($title);

            // Create the revision
            $revision = WikiRevision::Create([
                'object_id' => $object->id,
                'user_id' => 1983,
                'slug' => $slug,
                'title' => $title,
                'content_text' => $text,
                'content_html' => $parse_result->text,
                'message' => 'Automatic TWHL3 conversion'
            ]);

            // Parse meta from the content
            $meta = [];
            foreach ($parse_result->meta as $c => $v) {
                if ($c == 'WikiLink') {
                    foreach ($v as $val) $meta[] = new WikiRevisionMeta([ 'key' => WikiRevisionMeta::LINK, 'value' => $val ]);
                } else if ($c == 'WikiImage') {
                    foreach ($v as $val) $meta[] = new WikiRevisionMeta([ 'key' => WikiRevisionMeta::LINK, 'value' => 'upload:' . $val ]);
                } else if ($c == 'WikiCategory') {
                    foreach ($v as $val) $meta[] = new WikiRevisionMeta([ 'key' => WikiRevisionMeta::CATEGORY, 'value' => str_replace(' ', '_', $val) ]);
                }
            }

            $upload = WikiUpload::Create([
                'object_id' => $object->id,
                'revision_id' => $revision->id,
                'extension' => strtolower($info['extension'])
            ]);
            rename($file, $upload->getServerFileName());

            $file_name = $upload->getServerFileName();
            $info = getimagesize($file_name);
            $size = filesize($file_name);

            $revision->wiki_revision_metas()->saveMany([
                new WikiRevisionMeta(['key' => WikiRevisionMeta::UPLOAD_ID, 'value' => $upload->id]),
                new WikiRevisionMeta(['key' => WikiRevisionMeta::FILE_SIZE, 'value' => $size]),
                new WikiRevisionMeta(['key' => WikiRevisionMeta::IMAGE_WIDTH, 'value' => $info ? $info[0] : 0]),
                new WikiRevisionMeta(['key' => WikiRevisionMeta::IMAGE_HEIGHT, 'value' => $info ? $info[1] : 0]),
            ]);

            // Save meta & update the object
            $revision->wiki_revision_metas()->saveMany($meta);
            DB::statement('CALL update_wiki_object(?);', [$object->id]);

            $this->comment("Done ({$count}/{$total}): {$revision->title}");
            $count++;
        }
	}
}
