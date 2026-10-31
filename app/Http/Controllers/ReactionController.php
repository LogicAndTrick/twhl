<?php namespace App\Http\Controllers;

use App\Models\Reactions\Reaction;
use Illuminate\Validation\Rule;
use Auth;
use Request;

class ReactionController extends Controller {
    
	public function __construct()
	{
        $this->permission(['add', 'remove'], true);
	}

    public function postAdd()
    {
        $this->validate(Request::instance(), [
            'type' => Rule::in([ Reaction::NEWS, Reaction::JOURNAL, Reaction::FORUM_POST, Reaction::COMMENT, Reaction::VAULT_ITEM ]),
            'id' => Rule::numeric()->greaterThan(0),
            'react' => Rule::numeric()->greaterThan(0),
        ]);
        $reaction = new Reaction([
            'entity_type' => Request::input('type'),
            'entity_id' => Request::input('id'),
            'user_id' => Auth::id(),
            'reaction_type_id' => Request::input('react')
        ]);
        if (!permission($reaction->getPermissionToReact())) return abort(403);

        $ent = $reaction->getEntity();
        if (!$ent) return abort(422);
        
        $existing = Reaction::query()
            ->with(['user:id,name,avatar_file,avatar_custom'])
            ->where('entity_type', '=', $reaction->entity_type)
            ->where('entity_id', '=', $reaction->entity_id)
            ->where('user_id', '=', $reaction->user_id)
            ->first();

        if ($existing) {
            $existing->update([ 'reaction_type_id' => $reaction->reaction_type_id ]);
            return response()->json([ $existing ], 200);
        } else {
            $u = Auth::user();
            $reaction->save();
            $reaction->user = [ 'id' => $u->id, 'name' => $u->name, 'avatar_inline' => $u->avatar_inline ];
            return response()->json([ $reaction ], 201);
        }
    }

    public function postRemove()
    {
        $this->validate(Request::instance(), [
            'type' => Rule::in([ Reaction::NEWS, Reaction::JOURNAL, Reaction::FORUM_POST, Reaction::COMMENT, Reaction::VAULT_ITEM ]),
            'id' => Rule::numeric()->greaterThan(0),
            'react' => Rule::numeric()->greaterThan(0),
        ]);
        $reaction = new Reaction([
            'entity_type' => Request::input('type'),
            'entity_id' => Request::input('id'),
            'user_id' => Auth::id(),
            'reaction_type_id' => Request::input('react')
        ]);
        if (!permission($reaction->getPermissionToReact())) return abort(403);

        $ent = $reaction->getEntity();
        if (!$ent) return abort(422);
        
        $existing = Reaction::query()
            ->with(['user:id,name,avatar_file,avatar_custom'])
            ->where('entity_type', '=', $reaction->entity_type)
            ->where('entity_id', '=', $reaction->entity_id)
            ->where('user_id', '=', $reaction->user_id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([], 200);
        } else {
            return response()->json([], 204);
        }
    }
}
