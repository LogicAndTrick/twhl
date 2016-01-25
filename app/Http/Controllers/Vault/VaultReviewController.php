<?php namespace App\Http\Controllers\Vault;

use App\Helpers\Image;
use App\Http\Controllers\Controller;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentMeta;
use App\Models\Vault\VaultInclude;
use App\Models\Vault\VaultItem;
use App\Models\Vault\VaultItemInclude;
use App\Models\Vault\VaultItemReview;
use App\Models\Vault\VaultScreenshot;
use Illuminate\Support\Facades\Validator;
use Request;
use Input;
use Auth;
use DB;

class VaultReviewController extends Controller {

	public function __construct() {
        $this->permission(['create', 'edit', 'delete'], 'VaultCreate');
        $this->permission(['restore'], 'VaultAdmin');
	}

    // Create / edit

    public function getCreate($id) {

        $existing_review = VaultItemReview::whereUserId(Auth::user()->id)->whereItemId($id)->first();
        if ($existing_review) return redirect('vault-review/edit/'.$existing_review->id);

        $item = VaultItem::with(['user', 'vault_screenshots'])->findOrFail($id);
        if ($item->user_id == Auth::user()->id) abort(404);

        return view('vault/review/create', [
            'item' => $item
        ]);
    }

    public function postCreate() {

        $existing_review = VaultItemReview::whereUserId(Auth::user()->id)->whereItemId(Request::input('item_id'))->first();
        if ($existing_review) return redirect('vault-review/edit/'.$existing_review->id);

        $id = Request::input('item_id');
        $item = VaultItem::findOrFail($id);
        if ($item->user_id == Auth::user()->id) abort(404);

        $this->validate(Request::instance(), [
            'item_id' => 'required',
            'content_text' => 'required|max:10000',
            'score_architecture' => 'required|numeric|between:0,10',
            'score_texturing' => 'required|numeric|between:0,10',
            'score_ambience' => 'required|numeric|between:0,10',
            'score_lighting' => 'required|numeric|between:0,10',
            'score_gameplay' => 'required|numeric|between:0,10'
        ]);

        $review = VaultItemReview::Create([
            'item_id' => Request::input('item_id'),
            'user_id' => Auth::user()->id,
            'comment_id' => null,

            'content_text' => Request::input('content_text'),
            'content_html' => app('bbcode')->Parse(Request::input('content_text')),

            'score_architecture' => Request::input('score_architecture'),
            'score_texturing' => Request::input('score_texturing'),
            'score_ambience' => Request::input('score_ambience'),
            'score_lighting' => Request::input('score_lighting'),
            'score_gameplay' => Request::input('score_gameplay')
        ]);

        // See if the user has a comment with a rating
        $existing_rating = DB::selectOne('SELECT COUNT(*) as c
            FROM comment_metas AS m
            LEFT JOIN comments AS c ON c.id = m.comment_id
            WHERE article_type = ? AND article_id = ? AND user_id = ?
            AND c.deleted_at IS NULL',
            [Comment::VAULT, $review->item_id, $review->user_id])->c > 0;

        // Save the placeholder comment
        $comment = Comment::Create([
            'article_type' => Comment::VAULT,
            'article_id' => $review->item_id,
            'user_id' => $review->user_id,
            'content_text' => 'Placeholder for vault item review #'.$review->id,
            'content_html' => 'Placeholder for vault item review #'.$review->id
        ]);

        // Add a star rating if there's no existing one
        $metas = [];
        if (!$existing_rating) $metas[] = new CommentMeta([ 'key' => CommentMeta::RATING, 'value' => $review->getStarRating() ]);
        $metas[] = new CommentMeta([ 'key' => CommentMeta::TEMPLATE, 'value' => 'vault-item-review' ]);
        $metas[] = new CommentMeta([ 'key' => CommentMeta::TEMPLATE_ARTICLE_TYPE, 'value' => 'VaultItemReview' ]);
        $metas[] = new CommentMeta([ 'key' => CommentMeta::TEMPLATE_ARTICLE_ID, 'value' => $review->id ]);
        $comment->comment_metas()->saveMany($metas);

        DB::statement('CALL update_comment_statistics(?, ?, ?);', [Comment::VAULT, $review->item_id, $comment->user_id]);

        // Update the review with the comment
        $review->update([
            'comment_id' => $comment->id
        ]);

        return redirect('vault/view/'.$review->item_id.'#comment-'.$review->comment_id);
    }

    public function getEdit($id) {
        $review = VaultItemReview::with(['user'])->findOrFail($id);
        if (!$review->isEditable()) abort(404);

        $item = VaultItem::with(['user', 'vault_screenshots'])->findOrFail($review->item_id);

        $content = Request::old('content_text');
        if (!$content) $content = $review->content_text;

        return view('vault/review/edit', [
            'item' => $item,
            'review' => $review,
            'content' => $content
        ]);
    }

    public function postEdit() {
        $review = VaultItemReview::with(['user'])->findOrFail(Request::input('id'));
        if (!$review->isEditable()) abort(404);

        $item = VaultItem::with(['user', 'vault_screenshots'])->findOrFail($review->item_id);

        $this->validate(Request::instance(), [
            'id' => 'required',
            'content_text' => 'required|max:10000',
            'score_architecture' => 'required|numeric|between:0,10',
            'score_texturing' => 'required|numeric|between:0,10',
            'score_ambience' => 'required|numeric|between:0,10',
            'score_lighting' => 'required|numeric|between:0,10',
            'score_gameplay' => 'required|numeric|between:0,10'
        ]);

        $review->update([
            'content_text' => Request::input('content_text'),
            'content_html' => app('bbcode')->Parse(Request::input('content_text')),

            'score_architecture' => Request::input('score_architecture'),
            'score_texturing' => Request::input('score_texturing'),
            'score_ambience' => Request::input('score_ambience'),
            'score_lighting' => Request::input('score_lighting'),
            'score_gameplay' => Request::input('score_gameplay')
        ]);

        // Update the comment if it has a rating
        $meta = CommentMeta::whereCommentId($review->comment_id)->whereKey(CommentMeta::RATING)->first();
        if ($meta) {
            $meta->update([ 'value' => $review->getStarRating() ]);
            DB::statement('CALL update_comment_statistics(?, ?, ?);', [Comment::VAULT, $review->item_id, $review->comment->user_id]);
        }

        return redirect('vault/view/'.$review->item_id.'#comment-'.$review->comment_id);
    }

    public function getDelete($id) {
        $review = VaultItemReview::findOrFail($id);
        if (!$review->isEditable()) abort(404);

        $item = VaultItem::with(['user', 'vault_screenshots'])->findOrFail($review->item_id);

        return view('vault/review/delete', [
            'item' => $item,
            'review' => $review
        ]);
    }

    public function postDelete() {
        $review = VaultItemReview::findOrFail(Request::input('id'));
        if (!$review->isEditable()) abort(404);

        $comment = $review->comment;
        if ($comment) {
            $type = $comment->article_type;
            $id = $comment->article_id;
            $user = $comment->user_id;

            $comment->delete();
            DB::statement('CALL update_comment_statistics(?, ?, ?);', [$type, $id, $user]);
        }

        $review->delete();
        return redirect('vault/view/'.$review->item_id);
    }

    // Administrative Tasks

    public function getRestore($id) {
        $rev = VaultItemReview::onlyTrashed()->findOrFail($id);
        return view('vault-review/restore', [
            'item' => $rev
        ]);
    }

    public function postRestore() {
        $rev = VaultItemReview::onlyTrashed()->findOrFail(Request::input('id'));
        if (!$rev->isEditable()) abort(404);
        $rev->restore();
        return redirect('vault-review/view/'.$rev->id);
    }
}
