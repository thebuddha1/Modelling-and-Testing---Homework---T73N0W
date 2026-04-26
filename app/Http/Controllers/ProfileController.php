<?php


namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $authUser = Auth::user();
        $notifications = $authUser->unreadNotifications()
            ->orderByDesc('created_at')
            ->get();

        return view('profile.show', [
            'user' => $user,
            'notifications' => $notifications,
            'is_private' => $user->is_private,
            'isFriend' => $authUser->friends->contains($user->id),
            'ratings' => $this->summarizeRatings($user->ratingsReceived()->get()),
        ]);
    }

    public function edit(Request $request, User $user)
    {
        if ($request->user()->id !== $user->id) {
            abort(403, 'Nincs jogosultságod más profilját szerkeszteni.');
        }

        $experience = [
            'beginner' => 'Kezdő',
            'intermediate' => 'Középhaladó',
            'advanced' => 'Haladó',
            'expert' => 'Expert',
        ];

        $categories = [
            'naked' => 'Naked',
            'sport' => 'Sport',
            'touring' => 'Túra',
            'adventure' => 'Adventure',
            'cruiser' => 'Cruiser',
            'enduro' => 'Enduro',
        ];

        return view('profile.edit', [
            'user' => $user,
            'experience' => $experience,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, User $user)
    {
        if ($request->user()->id !== $user->id) {
            abort(403, 'Nincs jogosultságod más profilját szerkeszteni.');
        }

        $formFields = $request->validate([
            'bio' => ['nullable', 'string', 'max:1000'],
            'experience_level' => ['nullable', 'string', 'in:beginner,intermediate,advanced,expert'],
            'bike_type' => ['nullable', 'string', 'max:100'],
            'bike_category' => ['nullable', 'string', 'in:naked,sport,touring,adventure,cruiser,enduro'],
            'bike_year' => ['nullable', 'integer', 'between:1900,' . date('Y')],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'is_private' => ['nullable', 'boolean'],
        ]);


        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $formFields['avatar'] = $path;
        }

        $formFields['is_private'] = $request->boolean('is_private');

        $user->update($formFields);

        return back()->with('status', 'Profil frissítve!');
    }

    public function search(Request $request)
    {
        $term = $request->get('q', '');

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $users = User::query()
            ->where('name', 'like', '%' . $term . '%')
            ->orderByRaw(
                "CASE
                    WHEN name = ? THEN 0        -- pontos egyezés
                    WHEN name LIKE ? THEN 1     -- kezdeti egyezés
                    ELSE 2                      -- bárhol benne
                 END",
                [$term, $term . '%']
            )
            ->limit(3)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url,
                    'profile_url' => url('/profile/' . $user->id),
                ];
            });

        return response()->json($users);
    }

    private function summarizeRatings($ratings)
    {
        if ($ratings->isEmpty()) return null;
        $prec_count = 0; $prec_value = 0;
        $driv_count = 0; $driv_value = 0;
        $soc_count = 0;  $soc_value = 0;

        foreach ($ratings as $rating) {
            if (!is_null($rating->precision)) {
                $prec_value += $rating->precision;
                $prec_count++;
            }
            if (!is_null($rating->driving)) {
                $driv_value += $rating->driving;
                $driv_count++;
            }
            if (!is_null($rating->social)) {
                $soc_value += $rating->social;
                $soc_count++;
            }
        }
        
        $summary = [
            'precision' => $prec_count 
                           ? number_format($prec_value / $prec_count, 1, '.', '')
                           : null,
            'driving' => $driv_count 
                         ? number_format($driv_value / $driv_count, 1, '.', '')
                         : null,
            'social' => $soc_count 
                        ? number_format($soc_value / $soc_count, 1, '.', '') 
                        : null,
        ]; 

        return $summary;
    }
}
