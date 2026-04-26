<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\NotificationController;

class FriendController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'friend_name' => 'required|string'
        ]);

        $currentUser = Auth::user();

        if ($request->friend_name === $currentUser->name) {
            return back()->with('friendError', 'Nem adhatod hozzá saját magadat!');
        }

        $friend = User::where('name', $request->friend_name)->first();

        if (!$friend) {
            return back()->with('friendError', 'A felhasználó nem található.');
        }

        $alreadyFriends = DB::table('friends')
            ->where('user_id', $currentUser->id)
            ->where('friend_id', $friend->id)
            ->exists();

        if ($alreadyFriends) {
            return back()->with('friendError', 'Már barátok vagytok.');
        }

        DB::table('friends')->insert([
            [
                'user_id' => $currentUser->id,
                'friend_id' => $friend->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => $friend->id,
                'friend_id' => $currentUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        NotificationController::CreateFriendNotification($currentUser->id, $friend->id);
        return back()->with('success', 'Barát sikeresen hozzáadva!');
    }

    public function remove(Request $request)
    {
         $request->validate([
            'friend_name' => 'required|string'
        ]);

        $currentUser = Auth::user();

        $friend = User::where('name', $request->friend_name)->first();

        if (!$friend) {
            return back()->with('friendError', 'A felhasználó nem található.');
        }
        
        DB::table('friends')
            ->where(function ($query) use ($currentUser, $friend) {
                $query->where('user_id', $currentUser->id)
                      ->where('friend_id', $friend->id);
            })
            ->orWhere(function ($query) use ($currentUser, $friend) {
                $query->where('user_id', $friend->id)
                      ->where('friend_id', $currentUser->id);
            })
            ->delete();

        return back()->with('success', 'Barát törölve.');
    }

    public function list(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }
            $friends = $user->friends()->select('users.id', 'users.name', 'users.avatar')->get();
            return response()->json($friends);
        } catch (\Exception $e) {
            \Log::error('Friend list error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
