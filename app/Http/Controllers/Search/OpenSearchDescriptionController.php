<?php namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
class OpenSearchDescriptionController extends Controller {

	public function __construct()
	{

	}

    public function getIndex()
   	{
   		return response()
		    ->view('search/opensearchdescription', [])
		    ->header('Content-Type', 'application/opensearchdescription+xml');
   	}
}
