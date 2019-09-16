<?php namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use App\Models\Messages\Message as Message;
use App\Models\Messages\MessageThread;
use App\Models\Messages\MessageThreadUser;
use App\Models\Messages\MessageUser;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller {

	public function __construct() {
        $this->permission(['index', 'view', 'send', 'delete'], true);
	}

    private static function GetUser($id) {
        if (!permission('Admin') || !$id) $id = Auth::user()->id;
        return User::findOrFail($id);
    }

	public function getIndex($id = 0) {
        $user = MessageController::GetUser($id);

        $threads = MessageThread::whereRaw('(select count(*) from message_users as mu where mu.thread_id = message_threads.id and mu.user_id = ?)', [ $user->id ])
            ->with(['last_message', 'last_message.user'])
            ->orderBy('updated_at', 'desc')
            ->paginate();

        $thread_ids = $threads->map(function($x) { return $x->id; })->toArray();
        $unread = MessageUser::whereIn('thread_id', $thread_ids)
            ->whereUserId($user->id)
            ->where('is_unread', '=', true)
            ->get()->map(function($x) { return $x->thread_id; })->toArray();

        return view('user/message/index', [
            'user' => $user,
            'threads' => $threads,
            'unread' => $unread
        ]);
	}

    public function getView($id) {
        $thread = MessageThread::with(['messages', 'messages.user', 'participants'])->findOrFail($id);
        if (!$thread->canView()) abort(404);

        $unread = MessageUser::whereThreadId($id)
            ->whereUserId(Auth::user()->id)
            ->where('is_unread', '=', true)
            ->get()->map(function($x) { return $x->message_id; })->toArray();

        // Mark all messages in this thread as read
        DB::statement('update message_users set is_unread = ? where thread_id = ? and user_id = ?', [ false, $id, Auth::user()->id ]);

        return view('user/message/view', [
            'thread' => $thread,
            'unread' => $unread
        ]);
    }

    public function getSend($to_user = 0) {
        $user = MessageController::GetUser(null);
        return view('user/message/send', [
            'user' => $user,
            'recipients' => ($to_user ? [$to_user] : [])
        ]);
    }

    public function postSend() {
        $this->validate(Request::instance(), [
            'id' => 'numeric',
            'users' => 'required_without:id|array|min:1',
            'subject' => 'required_without:id|max:255',
            'content_text' => 'required|max:10000'
        ]);

        $users = [];

        $id = Request::input('id');
        $u = Request::input('users');
        if ($id) {
            // Post to an existing thread
            $thread = MessageThread::findOrFail($id);
            if (!$thread->canView()) abort(404);

            // Get the list of users to send to
            $users = $thread->participants->map(function($x) { return $x->id; })->toArray();

            // Add any new users to the thread
            if (is_array($u)) {
                foreach ($u as $uid) {
                    $uid = intval($uid);
                    if (array_search($uid, $users) === false) {
                        // as long as they're not already in the thread
                        MessageThreadUser::Create([ 'thread_id' => $thread->id, 'user_id' => $uid ]);
                        $users[] = $uid;
                    }
                }
            }
        } else {
            // Start a new thread
            foreach ($u as $uid) $users[] = intval($uid);
            if (array_search(Auth::user()->id, $users) === false) $users[] = Auth::user()->id;

            // Make the thread
            $thread = MessageThread::Create([
                'user_id' => Auth::user()->id,
                'subject' => Request::input('subject')
            ]);

            // Assign all the users to the thread
            foreach ($users as $user) MessageThreadUser::Create([ 'thread_id' => $thread->id, 'user_id' => $user ]);
        }

        // Make the message
        $message = Message::Create([
            'user_id' => Auth::user()->id,
            'thread_id' => $thread->id,
            'content_text' => Request::input('content_text'),
            'content_html' => app('bbcode')->Parse(Request::input('content_text'))
        ]);

        // Send the message to all the users
        foreach ($users as $user) {
            MessageUser::Create([
                'thread_id' => $thread->id,
                'message_id' => $message->id,
                'user_id' => $user,
                'is_unread' => $user != Auth::user()->id
            ]);
        }

        $thread->update([ 'last_message_id' => $message->id ]);

        return redirect('message/view/'.$thread->id);
    }

    public function getDelete($id) {
        $thread = MessageThread::with(['participants'])->findOrFail($id);
        if (!$thread->canView()) abort(404);

        return view('user/message/delete', [
            'thread' => $thread
        ]);
    }

    public function postDelete() {
        $id = Request::input('id');
        MessageUser::where('thread_id', '=', $id)->where('user_id', '=', Auth::user()->id)->delete();
        return redirect('message/index');
    }
}
