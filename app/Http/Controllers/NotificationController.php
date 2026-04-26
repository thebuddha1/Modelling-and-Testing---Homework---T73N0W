<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'category' => $notification->category,
                    'message' => $notification->message,
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            });

        return response()->json(['notifications' => $notifications]);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function createMock(Request $request)
    {
        $user = $request->user();

        Notification::create([
            'user_id'  => $user->id,
            'category' => 'Mock értesítés',
            'message'  => 'Ez egy teszt értesítés a fejlesztéshez.',
        ]);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', 'Mock értesítés létrehozva!');
    }

    public static function createFriendNotification($userId, $friendId)
    {
        $user = User::find($userId);
        $userName = $user->name;
        $friend = User::find($friendId);
        $friendName = $friend->name; 

        Notification::create([
            'user_id'  => $userId,
            'category' => 'Új barát',
            'message'  => "Sikeresen hozzáadtad $friendName-t barátodként!",
        ]);
        Notification::create([
            'user_id'  => $friendId,
            'category' => 'Új barát',
            'message'  => "$userName hozzáadott téged barátként!",
        ]);
    }

    public static function createTourInviteNotification($userId, $friendId, $tourName)
    {
        $user = User::find($userId);
        $userName = $user->name;

        $friend = User::find($friendId);
        $friendName = $friend->name;

        Notification::create([
            'user_id'  => $friendId,
            'category' => 'Túra',
            'message'  => "$userName hozzáadott téged a(z) \"$tourName\" túrához!",
        ]);
    }

    public static function createTourUpdateNotification(Tour $tour)
    {
        $tourName = $tour->name;
        $ownerName = $tour->user->name;

        foreach ($tour->participants as $participant) {
            if ($participant->id === $tour->user_id) {
                continue;
            }

            Notification::create([
                'user_id'  => $participant->id,
                'category' => 'Túra',
                'message'  => "$ownerName módosította a(z) \"$tourName\" túra adatait.",
            ]);
        }
    }

    public static function createTourJoinNotification(Tour $tour, User $joiningUser)
    {
        $tourName = $tour->name;
        $joiningUserName = $joiningUser->name;

        Notification::create([
            'user_id'  => $tour->user_id,
            'category' => 'Túra',
            'message'  => "$joiningUserName csatlakozott a(z) \"$tourName\" túrához.",
        ]);
    }
}