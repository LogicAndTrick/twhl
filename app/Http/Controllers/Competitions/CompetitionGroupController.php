<?php

namespace App\Http\Controllers\Competitions;

use App\Helpers\TypedRouting;
use App\Http\Controllers\Controller;
use App\Models\Competitions\CompetitionRestrictionGroup;
use Illuminate\Http\Request;

#[TypedRouting]
class CompetitionGroupController extends Controller
{

    public function __construct()
    {
        $this->permission(['index', 'create', 'edit', 'delete', 'restore'], 'CompetitionAdmin');
    }

    public function postCreate(Request $request)
    {
        $this->validate($request, [
            'title' => ['required', 'max:255']
        ]);
        CompetitionRestrictionGroup::Create([
            'title' => $request->input('title'),
            'is_multiple' => $request->boolean('is_multiple')
        ]);
        return redirect('competition-restriction/index');
    }

    public function getEdit(int $id)
    {
        $group = CompetitionRestrictionGroup::findOrFail($id);
        return view('competitions/group/edit', [
            'group' => $group
        ]);
    }

    public function postEdit(Request $request)
    {
        $this->validate($request, [
            'id' => ['required', 'integer'],
            'title' => ['required', 'max:255']
        ]);
        $id = $request->integer('id');
        $group = CompetitionRestrictionGroup::findOrFail($id);
        $group->update([
            'title' => $request->input('title'),
            'is_multiple' => $request->boolean('is_multiple')
        ]);
        return redirect('competition-restriction/index');
    }

    public function getDelete(int $id)
    {
        $group = CompetitionRestrictionGroup::findOrFail($id);
        return view('competitions/group/delete', [
            'group' => $group
        ]);
    }

    public function postDelete(Request $request)
    {
        $id = $request->integer('id');
        $group = CompetitionRestrictionGroup::findOrFail($id);
        $group->delete();
        return redirect('competition-restriction/index');
    }
}
