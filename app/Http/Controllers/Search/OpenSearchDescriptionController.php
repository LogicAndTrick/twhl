<?php

namespace App\Http\Controllers\Search;

use App\Helpers\TypedRouting;
use App\Http\Controllers\Controller;

#[TypedRouting]
class OpenSearchDescriptionController extends Controller
{

	public function __construct()
	{
		$this->anonymous(['index']);
	}

	public function getIndex()
	{
		return response()
			->view('search/opensearchdescription', [])
			->header('Content-Type', 'application/opensearchdescription+xml');
	}
}
