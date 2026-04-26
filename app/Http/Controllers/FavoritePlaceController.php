<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\FavoritePlace;

class FavoritePlaceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $favorites = $user->favoritePlaces()
            ->withPivot('created_at')
            ->orderBy('name')
            ->get()
            ->map(function ($fav) use ($user) {
                return [
                    'id' => $fav->id,
                    'name' => $fav->name,
                    'lat' => $fav->lat,
                    'lng' => $fav->lng,
                    'description' => $fav->description,
                    'shared_by_id' => $fav->creator_id != $user->id ? $fav->creator_id : null,
                    'creator_id' => $fav->creator_id,
                    'is_creator' => $fav->creator_id == $user->id,
                    'created_at' => $fav->pivot->created_at,
                ];
            });
        
        return response()->json($favorites);
    }

    public function update(Request $request, FavoritePlace $favoritePlace)
    {
        $user = $request->user();
        
        // Only creator can update
        if ($user->id !== $favoritePlace->creator_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = [
            'name.required' => 'Adj meg egy rövid nevet a helynek.',
        ];

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
        ], $messages);

        $favoritePlace->update($data);

        return response()->json($favoritePlace);
    }

    public function destroy(Request $request, FavoritePlace $favoritePlace)
    {
        $user = $request->user();
        
        // Check if user has this favorite
        if (!$user->favoritePlaces()->where('favorite_places.id', $favoritePlace->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Remove the relationship (detach user from place)
        $user->favoritePlaces()->detach($favoritePlace->id);
        
        // If no one else has this place, delete it
        if ($favoritePlace->users()->count() == 0) {
            $favoritePlace->delete();
        }

        return response()->json(null, 204);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        
        $messages = [
            'name.required' => 'Adj meg egy rövid nevet a helynek.',
        ];

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'description' => 'nullable|string',
        ], $messages);

        // Check if user already has a place with this name
        $existingPlace = $user->favoritePlaces()->where('name', $data['name'])->first();
        if ($existingPlace) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => ['name' => ['Már van ilyen nevű kedvenc helyed. Kérlek válassz másik nevet.']]
            ], 422);
        }

        // Create the place
        $fav = FavoritePlace::create([
            'creator_id' => $user->id,
            'name' => $data['name'],
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'description' => $data['description'] ?? null,
        ]);

        // Attach to user
        $user->favoritePlaces()->attach($fav->id);

        return response()->json([
            'id' => $fav->id,
            'name' => $fav->name,
            'lat' => $fav->lat,
            'lng' => $fav->lng,
            'description' => $fav->description,
            'creator_id' => $fav->creator_id,
            'shared_by_id' => null,
        ], 201);
    }

    public function share(Request $request)
    {
        $request->validate([
            'fav_id' => 'required|integer|exists:favorite_places,id',
            'friends' => 'required|array',
            'friends.*' => 'integer|exists:users,id',
        ]);

        $fav = FavoritePlace::findOrFail($request->fav_id);
        $sharer = $request->user();

        // Ensure the user has this favorite
        if (!$sharer->favoritePlaces()->where('favorite_places.id', $fav->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $skipped = [];
        $shared = [];

        foreach ($request->friends as $friendId) {
            if ($friendId == $sharer->id) continue;
            
            $friend = \App\Models\User::find($friendId);
            if (!$friend) continue;
            
            // Check if friend already has this exact place
            if ($friend->favoritePlaces()->where('favorite_places.id', $fav->id)->exists()) {
                $skipped[] = [
                    'id' => $friendId,
                    'name' => $friend->name
                ];
                continue;
            }
            
            // Check if friend already has a place with this name
            if ($friend->favoritePlaces()->where('name', $fav->name)->exists()) {
                $skipped[] = [
                    'id' => $friendId,
                    'name' => $friend->name
                ];
                continue;
            }
            
            // Attach the place to the friend
            $friend->favoritePlaces()->attach($fav->id);
            
            $shared[] = [
                'id' => $friendId,
                'name' => $friend->name
            ];
        }

        return response()->json([
            'skipped' => $skipped,
            'shared' => $shared,
            'message' => count($shared) > 0 ? 'Sikeres megosztás!' : 'Nem sikerült megosztani a helyet.'
        ]);
    }
}