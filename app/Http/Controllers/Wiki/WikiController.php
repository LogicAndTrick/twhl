<?php namespace App\Http\Controllers\Wiki;

use App\Http\Controllers\Controller;

class WikiController extends Controller {

	public function __construct()
	{
        $this->permission(['create', 'createupload'], 'WikiCreate');
        $this->permission(['edit', 'editupload', 'revert', 'revertupload'], 'WikiEdit');
        $this->permission(['delete', 'deleteupload'], 'WikiDelete');
	}

	public function getIndex()
	{
        return 'asdf';
	}

    public function getPage($page, $revision = 0) {
        return $page;
    }

    public function getEmbed($id)
    {
        return response()->download(public_path('images/'.$id), $id, array(), 'inline');
    }

}
