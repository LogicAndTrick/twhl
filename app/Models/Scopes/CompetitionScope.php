<?php

namespace App\Models\Scopes;
use App\Models\Competitions\CompetitionStatus;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompetitionScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!permission('CompetitionAdmin')) {
            $builder->where('status_id', '!=', CompetitionStatus::DRAFT);
        }
    }
}
