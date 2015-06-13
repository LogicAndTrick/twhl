<?php namespace App\Http\Controllers;

class HomeController extends Controller {

	public function __construct()
	{

	}

	public function index()
	{
		return view('home/index', [

        ]);
	}

}
