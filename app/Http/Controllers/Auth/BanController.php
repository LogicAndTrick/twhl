<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth;
use Request;
use Carbon\Carbon;
use App\Models\Accounts\Ban;

class BanController extends Controller
{
    public function getIndex()
    {
        $id = !Auth::user() ? -1 : Auth::user()->id;
        $ip = Request::ip();
        $now = Carbon::now();

        $activeBan = Ban::where('created_at', '<=', $now)
            ->whereRaw('(ends_at IS NULL OR ends_at >= ?)', [$now])
            ->whereRaw('(user_id = ? OR ip = ?)', [$id, $ip])
            ->first();

        if (!$activeBan) return redirect('/');
        return view('auth/banned', [
            'ban' => $activeBan
        ]);
    }
}
