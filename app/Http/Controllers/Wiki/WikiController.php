<?php namespace App\Http\Controllers\Wiki;

use App\Http\Controllers\Controller;

class WikiController extends Controller {

	public function __construct()
	{
        // $this->middleware('auth', ['only' => ['getCreate', 'postCreate', 'getEdit', 'postEdit', 'getDelete', 'postDelete']]);
	}

	public function getIndex()
	{
        return 'asdf';
	}

    public function getEmbed($id)
    {
        return response()->download(public_path('images/'.$id), $id, array(), 'inline');
    }

}
