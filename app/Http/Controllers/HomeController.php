<?php namespace App\Http\Controllers;

use App\Models\Vault\Motm;
use DB;
use App\Models\Vault\VaultItem;
use App\Models\Wiki\WikiObject;

class HomeController extends Controller {

	public function __construct()
	{

	}

	public function index()
	{
        $motm = Motm::with(['vault_item', 'vault_item.user', 'vault_item.vault_screenshots'])
            ->whereNotNull('item_id')
            ->orderByRaw('(year * 10) + month DESC')
            ->first();

        $new_maps = VaultItem::with(['user', 'vault_screenshots'])
            ->whereTypeId(1) // Maps
            ->orderBy('updated_at', 'desc')
            ->limit(6)
            ->get();

        $excluded = $new_maps->map(function($m) { return $m->id; });
        if ($motm) $excluded[] = $motm->item_id;

        $top_maps = VaultItem::with(['user', 'vault_screenshots'])
            ->whereTypeId(1) // Maps
            ->whereCategoryId(2) // Completed
            ->whereFlagRatings(true)
            ->where('stat_ratings', '>=', 5)
            ->whereRaw('(ceil(stat_average_rating * 2) / 2) >= 4.5')
            ->whereIn('id', $excluded, 'and', true) // NOT in
            ->orderByRaw('RAND()')
            ->limit(5)
            ->get();

        $wiki_edits = WikiObject::with(['current_revision', 'current_revision.user'])
            ->orderBy('updated_at', 'desc')
            ->limit(12)
            ->get();

		return view('home/index', [
            'motm' => $motm,
            'top_maps' => $top_maps,
            'new_maps' => $new_maps,
            'wiki_edits' => $wiki_edits
        ]);
	}

}
