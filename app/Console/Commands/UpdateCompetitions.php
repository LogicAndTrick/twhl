<?php namespace App\Console\Commands;

use App\Models\Competitions\Competition;
use App\Models\Competitions\CompetitionStatus;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UpdateCompetitions extends Command {

	protected $name = 'update:competitions';
	protected $description = 'Update competition statuses if required.';

	public function handle()
	{
        // Competition statuses:
        // if (DRAFT && open_date <= now) ACTIVE
        // if (ACTIVE && close_date <= now) isVoted ? VOTING : JUDGING
        // if (VOTING && voting_close_date <= now) JUDGING

        $now = Carbon::now();
        $comps = Competition::whereIn('status_id', [ CompetitionStatus::DRAFT, CompetitionStatus::ACTIVE, CompetitionStatus::VOTING ])->get();
        foreach ($comps as $comp)
        {
            /** @var $comp Competition */
            $changed = false;
            if ($comp->status_id == CompetitionStatus::DRAFT && $comp->getOpenTime() <= $now)
            {
                $this->comment("{$comp->name} is DRAFT will change to ACTIVE");
                $comp->status_id = CompetitionStatus::ACTIVE;
                $changed = true;
            }
            // Don't use else on the rare chance a comp goes straight from DRAFT to VOTING/JUDGING (pretty dumb...)
            if ($comp->status_id == CompetitionStatus::ACTIVE && $comp->getCloseTime() <= $now)
            {
                $this->comment("{$comp->name} is ACTIVE and will change to " . ($comp->isVoted() ? 'VOTING' : 'JUDGING'));
                $comp->status_id = $comp->isVoted() ? CompetitionStatus::VOTING : CompetitionStatus::JUDGING;
                $changed = true;
            }
            // Same deal here
            if ($comp->status_id == CompetitionStatus::VOTING && $comp->getVotingCloseTime() <= $now)
            {
                $this->comment("{$comp->name} is VOTING and will change to JUDGING");
                $comp->status_id = CompetitionStatus::JUDGING;
                $changed = true;
            }
            if ($changed) $comp->save();
        }
	}
}
