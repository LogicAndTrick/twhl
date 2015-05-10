@extends('app')

@section('content')
    <h2>Create New Competition</h2>

    @form(competition-admin/create upload=true)
        @text(name:competition_name) = Competition Name
        {? $status = \App\Models\Competitions\CompetitionStatus::DRAFT; ?}
        @autocomplete(status_id api/competition-statuses $status) = Initial Status
        @autocomplete(type_id api/competition-types) = Competition Type
        @autocomplete(judge_type_id api/competition-judge-types) = Judging Method
        @autocomplete(engines[] api/engines multiple=true) = Allowed Engines
        @autocomplete(judges[] api/users multiple=true) = Judges (if applicable)
        @textarea(brief_text) = Competition Brief
        @text(open_date) = Date Open (dd/mm/yyyy)
        @text(close_date) = Date Closed (dd/mm/yyyy)
        @text(voting_close_date) = Date Voting Closed (if applicable) (dd/mm/yyyy)
        @file(brief_attachment) = (Optional) Attachment (16mb limit)
        @submit = Create Competition
    @endform
@endsection
