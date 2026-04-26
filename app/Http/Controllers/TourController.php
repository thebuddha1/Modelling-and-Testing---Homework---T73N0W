<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\Request;
use App\Models\AdView;
use Carbon\Carbon;

class TourController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $today = now()->toDateString();

        $toursQuery = Tour::withCount('participants')
            ->with('user:id,name')
            ->whereNotNull('date');

        if ($user) {
            $toursQuery->where(function ($query) use ($user, $today) {
                $query->where(function ($sub) use ($user, $today) {
                    $sub->whereDate('date', '>=', $today)
                        ->where(function ($inner) use ($user) {
                            $inner->where('is_public', true)
                                ->orWhere('user_id', $user->id)
                                ->orWhereHas('participants', function ($participantQuery) use ($user) {
                                    $participantQuery->where('users.id', $user->id);
                                });
                        });
                })
                ->orWhere(function ($sub) use ($user, $today) {
                    $sub->whereDate('date', '<', $today)
                        ->where(function ($inner) use ($user) {
                            $inner->where('user_id', $user->id)
                                ->orWhereHas('participants', function ($participantQuery) use ($user) {
                                    $participantQuery->where('users.id', $user->id);
                                });
                        });
                });
            });
        } else {
            $toursQuery->where('is_public', true)
                ->whereDate('date', '>=', $today);
        }

        $tours = $toursQuery->orderBy('date', 'desc')->get();

        $joinedTourIds = $user
            ? $user->joinedTours()->pluck('tour_id')->all()
            : [];

        $hasTourRoutes = $tours->contains(function (Tour $tour) {
            $coordinates = $tour->route_geometry['coordinates'] ?? null;

            return is_array($coordinates) && count($coordinates) >= 2;
        });
        // Advertisement handling
        if ($user && (!$user->subscription_end_at || $user->subscription_end_at->isPast())) {
            $today = Carbon::today()->toDateString();
            $alreadyViewed = AdView::where('user_id', $user->id)
                                   ->where('viewed_at', $today)
                                   ->exists();

            if (!$alreadyViewed) {
                AdView::create([
                    'user_id' => $user->id,
                    'viewed_at' => $today,
                ]);
            }
        }

        return view('main', [
            'tours' => $tours,
            'joinedTourIds' => $joinedTourIds,
            'hasTourRoutes' => $hasTourRoutes,
        ]);
    }

    public function create()
    {
        return view('tours.planner', [
            'minDate' => now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'description' => ['required', 'string'],
            'max_participants' => ['required', 'integer', 'min:1', 'max:20'],
            'location' => ['required', 'string', 'max:255'],
            'is_public' => ['required', 'boolean'],
            'route' => ['required', 'json'],
        ]);

        Tour::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'date' => $validated['date'],
            'description' => $validated['description'],
            'max_participants' => $validated['max_participants'],
            'location' => $validated['location'],
            'is_public' => (bool) $validated['is_public'],
            'route_geometry' => json_decode($validated['route'], true),
        ]);

        return redirect()->route('tours.create')->with('status', 'Túra létrehozva.');
    }

    public function join(Request $request, Tour $tour)
    {
        $user = $request->user();

        if ((! $tour->is_public && $tour->user_id !== $user->id) || ! $tour->date || $tour->date->isBefore(now()->startOfDay())) {
            return redirect()->route('home')->with('error', 'Ehhez a túrához már nem lehet csatlakozni.');
        }

        if ($tour->participants()->where('user_id', $user->id)->exists()) {
            return redirect()->route('home')->with('status', 'Már csatlakoztál ehhez a túrához.');
        }

        if ($tour->participants()->count() >= $tour->max_participants) {
            return redirect()->route('home')->with('error', 'Ez a túra már megtelt.');
        }

        $tour->participants()->syncWithoutDetaching([$user->id]);

        NotificationController::createTourJoinNotification($tour, $user);

        return redirect()->route('home')->with('status', 'Sikeresen csatlakoztál a túrához.');
    }

    public function leave(Request $request, Tour $tour)
    {
        if ($tour->date && $tour->date->isBefore(now()->startOfDay())) {
            return redirect()->route('home')->with('error', 'Lejárt túráról nem lehet lejelentkezni.');
        }

        $request->user()->joinedTours()->detach($tour->id);

        return redirect()->route('home')->with('status', 'Jelentkezésed sikeresen visszavontad.');
    }

    public function show(Tour $tour)
    {
        $tour->load(['user', 'participants']);
        $isOwner = auth()->check() && (int) auth()->id() === (int) $tour->user_id;
        $isParticipant = auth()->check() && $tour->participants->contains(auth()->user());
        $isPast = optional($tour->date)?->isBefore(now()->startOfDay());

        return view('tours.show', [
            'tour' => $tour,
            'isOwner' => $isOwner,
            'isParticipant' => $isParticipant,
            'isPast' => $isPast,
        ]);
    }

    public function edit(Tour $tour)
    {
        if (auth()->id() !== $tour->user_id) {
            abort(403);
        }

        return view('tours.planner', [
            'tour' => $tour,
            'minDate' => now()->toDateString(),
        ]);
    }

    public function update(Request $request, Tour $tour)
    {
        if (auth()->id() !== $tour->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'description' => ['required', 'string'],
            'max_participants' => ['required', 'integer', 'min:1', 'max:20'],
            'location' => ['required', 'string', 'max:255'],
            'is_public' => ['required', 'boolean'],
            'route' => ['required', 'json'],
        ]);

        $tour->update([
            'name' => $validated['name'],
            'date' => $validated['date'],
            'description' => $validated['description'],
            'max_participants' => $validated['max_participants'],
            'location' => $validated['location'],
            'is_public' => (bool) $validated['is_public'],
            'route_geometry' => json_decode($validated['route'], true),
        ]);

        NotificationController::createTourUpdateNotification($tour);

        return redirect()->route('tours.show', $tour)->with('status', 'Túra frissítve.');
    }

    public function invite(Request $request, Tour $tour)
    {
        if (auth()->id() !== $tour->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = \App\Models\User::find($validated['user_id']);

        if ($tour->participants->contains($user)) {
            return back()->with('error', 'A felhasználó már részt vesz a túrán.');
        }
        
        if ($tour->participants()->count() >= $tour->max_participants) {
             return back()->with('error', 'A túra betelt.');
        }

        $tour->participants()->attach($user);

        NotificationController::createTourInviteNotification(auth()->id(), $user->id, $tour->name);
        return back()->with('status', 'Felhasználó sikeresen hozzáadva a túrához.');
    }

    public function searchUsersForInvite(Request $request, Tour $tour)
    {
        if (auth()->id() !== $tour->user_id) {
            return response()->json([]);
        }

        $term = $request->get('q', '');
        $friendsOnly = $request->get('friends_only', 'false') === 'true';

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $participantIds = $tour->participants->pluck('id')->toArray();
        $participantIds[] = auth()->id(); // exclude self

        $query = \App\Models\User::query()
            ->where('name', 'like', '%' . $term . '%')
            ->whereNotIn('id', $participantIds);

        if ($friendsOnly) {
            $friendIds = auth()->user()->friends()->pluck('users.id')->toArray();
            $query->whereIn('id', $friendIds);
        }

        $users = $query
            ->orderByRaw(
                "CASE
                    WHEN name = ? THEN 0
                    WHEN name LIKE ? THEN 1
                    ELSE 2
                 END",
                [$term, $term . '%']
            )
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url,
                ];
            });

        return response()->json($users);
    }

    public function destroy(Tour $tour)
    {
        if (auth()->id() !== $tour->user_id) {
            abort(403);
        }

        $tour->delete();

        return redirect()->route('home')->with('status', 'Túra sikeresen törölve.');
    }
}
