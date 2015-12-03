<?php namespace App\Http\Controllers;

use App\Models\Vault\VaultItem;
use App\Models\Wiki\WikiObject;

class HomeController extends Controller {

	public function __construct()
	{

	}

	public function index()
	{
        $motm_id = 0;
        $top_maps = VaultItem::with(['user', 'vault_screenshots'])
            ->whereTypeId(1) // Maps
            ->whereCategoryId(2) // Completed
            ->whereFlagRatings(true)
            ->where('stat_ratings', '>=', 5)
            ->whereRaw('(ceil(stat_average_rating * 2) / 2) >= 4.5')
            ->whereIn('id', [$motm_id], 'and', true) // NOT in
            ->orderByRaw('RAND()')
            ->limit(5)
            ->get();

        $new_maps = VaultItem::with(['user', 'vault_screenshots'])
            ->whereTypeId(1) // Maps
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        $wiki_edits = WikiObject::with(['current_revision', 'current_revision.user'])
            ->orderBy('updated_at', 'desc')
            ->limit(8)
            ->get();

		return view('home/index', [
            'top_maps' => $top_maps,
            'new_maps' => $new_maps,
            'wiki_edits' => $wiki_edits
        ]);
	}

}
