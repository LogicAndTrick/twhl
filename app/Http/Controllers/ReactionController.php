<?php

namespace App\Http\Controllers;

use App\Helpers\TypedRouting;
use App\Models\Reactions\Reaction;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

#[TypedRouting]
class ReactionController extends Controller
{
    public function __construct()
    {
        $this->permission(['add', 'remove'], true);
    }

    public function postAdd(Request $request)
    {
        $this->validate($request, [
            'type' => Rule::in([Reaction::NEWS, Reaction::JOURNAL, Reaction::FORUM_POST, Reaction::COMMENT, Reaction::VAULT_ITEM]),
            'id' => Rule::numeric()->integer()->greaterThan(0),
            'react' => Rule::numeric()->integer()->greaterThan(0)
        ]);
        $reaction = new Reaction([
            'entity_type' => $request->input('type'),
            'entity_id' => $request->integer('id'),
            'user_id' => Auth::id(),
            'reaction_type_id' => $request->integer('react')
        ]);
        if (!permission($reaction->getPermissionToReact())) return abort(403);

        $ent = $reaction->getEntity();
        if (!$ent) return abort(422);

        $existing = Reaction::query()
            ->with(['user:id,name,avatar_file,avatar_custom'])
            ->where('entity_type', '=', $reaction->entity_type)
            ->where('entity_id', '=', $reaction->entity_id)
            ->where('reaction_type_id', '=', $reaction->reaction_type_id)
            ->get();

        $found = $existing->where('user_id', '=', $reaction->user_id)->first();
        if (!$found) {
            $reaction->save();
            $existing->add([
                ...$reaction->toArray(),
                'user' => Auth::user()
            ]);
        }

        return response()->json($existing, $found ? 202 : 200);
    }

    public function postRemove(Request $request)
    {
        $this->validate($request, [
            'type' => Rule::in([Reaction::NEWS, Reaction::JOURNAL, Reaction::FORUM_POST, Reaction::COMMENT, Reaction::VAULT_ITEM]),
            'id' => Rule::numeric()->integer()->greaterThan(0),
            'react' => Rule::numeric()->integer()->greaterThan(0),
        ]);
        $reaction = new Reaction([
            'entity_type' => $request->input('type'),
            'entity_id' => $request->integer('id'),
            'user_id' => Auth::id(),
            'reaction_type_id' => $request->integer('react')
        ]);
        if (!permission($reaction->getPermissionToReact())) return abort(403);

        $ent = $reaction->getEntity();
        if (!$ent) return abort(422);

        $existing = Reaction::query()
            ->with(['user:id,name,avatar_file,avatar_custom'])
            ->where('entity_type', '=', $reaction->entity_type)
            ->where('entity_id', '=', $reaction->entity_id)
            ->where('reaction_type_id', '=', $reaction->reaction_type_id)
            ->get();

        $found = $existing->where('user_id', '=', $reaction->user_id)->first();
        if ($found) {
            $existing = $existing->diff([$found]);
            $found->delete();
        }

        return response()->json($existing, !$found ? 202 : 200);
    }
}
