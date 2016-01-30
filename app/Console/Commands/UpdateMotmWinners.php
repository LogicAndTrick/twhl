<?php namespace App\Console\Commands;

use App\Models\Vault\Motm;
use App\Models\Vault\VaultItem;
use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;

class UpdateMotmWinners extends Command {

	protected $name = 'update:motm';
	protected $description = 'Update motm winners if required.';

	public function fire()
	{
        /**
         * If we're in December, we want to look at the MOTM for October.
         * This gives each map at least a month to be voted on before MOTM
         * is processed.
         */
        $now = Carbon::now()->subMonths(0);

        // Only look at the last 3 months
        for ($i = 0; $i < 3; $i++) {
            $ym = $now->copy()->subMonths($i);
            $year = $ym->year;
            $month = $ym->month;
            $motm = Motm::where('year', '=', $year)->where('month', '=', $month)->first();
            if (!$motm) {
                $this->comment("The MOTM for {$ym->format('F Y')} needs to be created.");
                $winner = VaultItem::with(['user'])
                            ->whereIn('type_id', [1,4]) // Maps and mods
                            ->whereCategoryId(2) // Completed
                            ->whereFlagRatings(true)
                            ->whereRaw('(MONTH(created_at) = ? AND YEAR(created_at) = ?)', [$month, $year])
                            ->where('stat_ratings', '>=', 5)
                            ->orderBy('stat_average_rating', true)
                            ->limit(1)
                            ->first();
                $winner_id = null;
                if ($winner) {
                    $this->comment("The winner is: {$winner->name}");
                    $winner_id = $winner->id;
                } else {
                    $this->comment("No winner found. :(");
                }
                Motm::Create([
                    'item_id' => $winner_id,
                    'year' => $year,
                    'month' => $month
                ]);
            }
        }
	}
}
