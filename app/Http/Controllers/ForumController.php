<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Forum;
use App\Models\Comment;
use App\Models\Impression;
use App\Models\UserImpression;
use App\Models\CommentImpression;
use App\Models\UserCommentImpression;
use App\Models\Report;
use App\Models\UserReport;
use App\Models\CommentReport;
use App\Models\UserCommentReport;
use App\Models\Tour;
use App\Models\User;
use App\Models\UserRating;
use App\Models\SavedForum;

class ForumController extends Controller
{
    //fórum kezdőoldal, témák kilistázása
    public function index()
    {
        $categories = Category::with(['forums' => function ($query) {
            $query->with(['user', 'impression'])
                ->leftJoin('impressions', 'forums.id', '=', 'impressions.forum_id')
                ->orderByDesc('impressions.likes')
                ->select('forums.*')
                ->take(5);
        }])->get();

        $hasPastTours = false;

        if (auth()->check()) {
            $hasPastTours = auth()->user()
                ->joinedTours()
                ->whereNotNull('date')
                ->where('date', '<', now())
                ->exists();
        }

        return view('forum.index', [
            'categories'   => $categories,
            'hasPastTours' => $hasPastTours,
        ]);
    }


    //témák oldala, fórumok kilistázása
    public function show(Category $category, Request $request)
    {
        $forums = $category->forums()->with(['user', 'impression', 'userReports', 'userImpressions']);

        if ($request->has('q') && $request->q !== '') {
            $q = $request->q;
            $forums->where('name', 'LIKE', "%$q%");
        }

        $filters = explode(',', $request->get('filters', ''));

        if (in_array('az', $filters)) {
            $forums->orderBy('name', 'asc');
        }

        if (in_array('za', $filters)) {
            $forums->orderBy('name', 'desc');
        }

        if (in_array('updated_oldest', $filters)) {
            $forums->orderBy('updated_at', 'asc');
        }

        if (in_array('updated_newest', $filters)) {
            $forums->orderBy('updated_at', 'desc');
        }

        if (in_array('most_likes', $filters)) {
            $forums->withCount('userImpressions as likes_count')
                ->orderBy('likes_count', 'desc');
        }

        if (auth()->check()) {

            if (in_array('hide_reported', $filters)) {
                $forums->whereDoesntHave('userReports', function($q) {
                    $q->where('user_id', auth()->id());
                });
            }

            if (in_array('hide_disliked', $filters)) {
                $forums->whereDoesntHave('userImpressions', function($q) {
                    $q->where('user_id', auth()->id())
                    ->where('dislike', true);
                });
            }
        }

        $forums = $forums->get();

        return view('forum.list', compact('category', 'forums', 'filters'));
    }

    //saját fórumok, kedvelt fórumok, mentett fórumok kilistázása
    public function myForums()
    {
        $user = auth()->user();

        // Saját fórumok
        $ownForumsByCategory = Forum::where('owner', $user->id)
            ->with(['user', 'category', 'impression'])
            ->latest()
            ->get()
            ->groupBy('category_id');

        // Mentett fórumok
        $savedForumsByCategory = Forum::whereIn('id', function ($q) use ($user) {
                $q->select('forum_id')
                ->from('saved_forums')
                ->where('user_id', $user->id);
            })
            ->with(['user', 'category', 'impression'])
            ->latest()
            ->get()
            ->groupBy('category_id');

        // Kedvelt fórumok
        $likedForumsByCategory = Forum::whereIn('id', function ($q) use ($user) {
                $q->select('forum_id')
                ->from('user_impressions')
                ->where('user_id', $user->id)
                ->where('like', true);
            })
            ->with(['user', 'category', 'impression'])
            ->latest()
            ->get()
            ->groupBy('category_id');

        return view('forum.myforums', [
            'ownForumsByCategory'   => $ownForumsByCategory,
            'savedForumsByCategory' => $savedForumsByCategory,
            'likedForumsByCategory' => $likedForumsByCategory,
        ]);
    }


    //irányítás a fórum létrehozása oldalra
    public function create(Category $category)
    {
        return view('forum.create', compact('category'));
    }

    // fórum létrehozása
    public function store(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('forums')->where(function ($query) use ($category) {
                    return $query->where('category_id', $category->id);
                })
            ],
            'description' => 'nullable|string|max:255',
            'content' => 'required|string',
        ], [
            'name.required' => 'A fórum neve kötelező mező.',
            'name.unique'   => 'Ezzel a névvel már létezik fórum ebben a kategóriában.',
            'content.required' => 'A fórum tartalma kötelező mező.',
        ]);

        $forum = Forum::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'content' => $validated['content'],
            'owner' => auth()->id(),
            'category_id' => $category->id,
        ]);

        $forum->impression()->create([
            'likes' => 0,
            'dislikes' => 0,
        ]);

        $forum->report()->create([
            'count' => 0,
        ]);

        return redirect()
            ->route('forum.post', $forum->id)
            ->with('success', 'A fórum sikeresen létrehozva!');
    }

    //irányítás adott fórum oldalára
    public function post($id)
    {
        $forum = Forum::find($id);

        if (!$forum) {
            return redirect()->route('forum.deleted');
        }

        $forum->load([
            'user',
            'impression',
            'userReports' => function ($q) {
                $q->where('user_id', auth()->id());
            },
            'savedByUsers' => function ($q) {
                $q->where('user_id', auth()->id());
            },
            'comments' => function ($q) {
                $q->latest();
            },
            'comments.user',
            'comments.impression',
            'comments.userReports' => function ($q) {
                $q->where('user_id', auth()->id());
            }
        ]);

        $userReport = $forum->userReports->first();
        $alreadySaved = $forum->savedByUsers->isNotEmpty();

        $userImpression = UserImpression::where('forum_id', $forum->id)
            ->where('user_id', auth()->id())
            ->first();

        $comments = $forum->comments->map(function ($comment) {
            $comment->current_user_report = $comment->userReports->first();

            $comment->current_user_impression = $comment->impression
                ? $comment->current_user_impression = UserCommentImpression::where('comment_id', $comment->id)
                    ->where('user_id', auth()->id())
                    ->first()
                : null;

            return $comment;
        });

        return view('forum.post', [
            'forum' => $forum,
            'userReport' => $userReport,
            'alreadySaved' => $alreadySaved,
            'userImpression' => $userImpression,
            'comments' => $comments
        ]);
    }

    //komment közzététele
    public function storeComment(Request $request, $forumId)
    {
        $forum = Forum::find($forumId);

        if (!$forum) {
            return redirect()->route('forum.deleted');
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ], [
            'content.required' => 'A komment nem lehet üres.',
        ]);

        $comment = Comment::create([
            'content' => $request->input('content'),
            'user_id' => auth()->id(),
            'forum_id' => $forum->id,
        ]);

        $comment->impression()->create([
        'likes' => 0,
        'dislikes' => 0,
        ]);

        $comment->report()->create([
        'count' => 0,
        ]);

        return redirect()->route('forum.post', $forum->id);
    }

    //fórum szerkesztés mentés
    public function update(Request $request, $id)
    {
        $forum = Forum::find($id);

        if (!$forum) {
            return redirect()->route('forum.deleted');
        }

        if (auth()->id() !== $forum->owner) {
            abort(403, 'Nincs jogosultságod szerkeszteni ezt a fórumot.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('forums')
                    ->where(function ($query) use ($forum) {
                        return $query->where('category_id', $forum->category_id);
                    })
                    ->ignore($forum->id)
            ],
            'description' => 'nullable|string|max:255',
            'content' => 'required|string',
        ], [
            'name.required' => 'A fórum neve kötelező.',
            'name.unique'   => 'Ezzel a névvel már létezik fórum ebben a kategóriában.',
            'content.required' => 'A tartalom kötelező.',
        ]);

        $changes = array_diff_assoc($validated, [
            'name' => $forum->name,
            'description' => $forum->description,
            'content' => $forum->content
        ]);

        if (empty($changes)) {
            return redirect()
                ->route('forum.post', $forum->id)
                ->with('nochange', 'Nem történt változtatás.');
        }

        $forum->update($validated);

        return redirect()
            ->route('forum.post', $forum->id)
            ->with('success', 'A fórum frissítve lett.');
    }


    //fórum törlése
    public function destroy($id)
    {
        $forum = Forum::find($id);

        if (!$forum) {
            return redirect()->route('forum.deleted');
        }

        if (auth()->id() !== $forum->owner) {
            abort(403, 'Nincs jogosultságod törölni ezt a fórumot.');
        }
        $forum->comments()->delete();
        $forum->delete();

        return redirect()
            ->route('forum.index')
            ->with('success', 'A fórum sikeresen törölve lett.');
    }

    //komment törlése
    public function destroyComment($id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return redirect()->back();
        }

        if (auth()->id() !== $comment->user_id) {
            abort(403, 'Nincs jogosultságod törölni ezt a kommentet.');
        }

        $forumId = $comment->forum_id;
        $comment->delete();

        return redirect()
            ->route('forum.post', $forumId)
            ->with('success', 'A komment törölve lett.');
    }

    //komment szerkesztése
    public function updateComment(Request $request, $id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return redirect()->back();
        }

        if (auth()->id() !== $comment->user_id) {
            abort(403, 'Nincs jogosultságod szerkeszteni ezt a kommentet.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:1000'
        ], [
            'content.required' => 'A komment nem lehet üres.'
        ]);

        if ($validated['content'] === $comment->content) {
            return back()->with('nochange', 'Nem történt változás a kommentben.');
        }

        $comment->update($validated);

        return back()->with('success', 'A komment frissítve lett.');
    }

    // Fórum like/dislike mentése
    public function saveImpressions(Request $request, $forumId)
    {
        $forum = Forum::find($forumId);

        if (!$forum) {
            return response()->json(['deleted' => true], 410);
        }

        if (!auth()->check()) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $payload = json_decode($request->getContent(), true);
        if (!$payload) {
            return response()->json(['error' => 'Invalid JSON'], 422);
        }

        $newState = $payload['newState'] ?? "none";

        $userImpression = $forum->userImpressionForCurrentUser()->first();

        if (!$userImpression) {
            $userImpression = new UserImpression();
            $userImpression->user_id = auth()->id();
            $userImpression->forum_id = $forum->id;
        }

        $userImpression->like    = $newState === "like";
        $userImpression->dislike = $newState === "dislike";
        $userImpression->save();

        $likes = $forum->userImpressions()->where('like', true)->count();
        $dislikes = $forum->userImpressions()->where('dislike', true)->count();

        if (!$forum->impression) {
            $forum->impression()->create([
                'likes' => $likes,
                'dislikes' => $dislikes
            ]);
        } else {
            $forum->impression->likes = $likes;
            $forum->impression->dislikes = $dislikes;
            $forum->impression->save();
        }

        return response()->json([
            'status' => 'ok',
            'likes' => $likes,
            'dislikes' => $dislikes,
            'state' => $newState
        ]);
    }

    //kommentek like/dislike mentése
    public function saveCommentImpressions(Request $request, $id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['deleted' => true], 410);
        }

        if (!auth()->check()) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $payload = json_decode($request->getContent(), true);
        if (!$payload) {
            return response()->json(['error' => 'Invalid JSON'], 422);
        }

        $newState = $payload['newState'] ?? "none";

        $userImpression = $comment->userImpressionForCurrentUser()->first();

        if (!$userImpression) {
            $userImpression = new UserCommentImpression();
            $userImpression->user_id = auth()->id();
            $userImpression->comment_id = $comment->id;
        }

        $userImpression->like    = $newState === "like";
        $userImpression->dislike = $newState === "dislike";
        $userImpression->save();

        $likes = $comment->userImpressions()->where('like', true)->count();
        $dislikes = $comment->userImpressions()->where('dislike', true)->count();

        if (!$comment->impression) {
            $comment->impression()->create([
                'likes' => $likes,
                'dislikes' => $dislikes
            ]);
        } else {
            $comment->impression->likes = $likes;
            $comment->impression->dislikes = $dislikes;
            $comment->impression->save();
        }

        return response()->json([
            'status' => 'ok',
            'likes' => $likes,
            'dislikes' => $dislikes,
            'state' => $newState
        ]);
    }

    //fórum jelentés leadása
    public function submitReport(Request $request, $forumId)
    {
        $forum = Forum::find($forumId);

        if (!$forum) {
            return redirect()->route('forum.deleted');
        }

        if (!auth()->check()) {
            return redirect()->back();
        }

        $existing = UserReport::where('user_id', auth()->id())
                            ->where('forum_id', $forum->id)
                            ->first();

        if ($existing) {
            return redirect()->back();
        }

        UserReport::create([
            'user_id' => auth()->id(),
            'forum_id' => $forum->id,
            'reported' => true,
        ]);

        if (!$forum->report) {
            $forum->report()->create(['count' => 1]);
        } else {
            $forum->report->count += 1;
            $forum->report->save();
        }

        if ($forum->report->count >= 3) {

            $categoryId = $forum->category_id;

            $forum->delete();

            return redirect()
                ->route('forum.index', $categoryId)
                ->with('success', 'A fórum automatikusan törlésre került több bejelentés miatt.');
        }

        return redirect()->back();
    }

    //komment jelentése
    public function submitCommentReport(Request $request, $commentId)
    {
        $comment = Comment::find($commentId);

        if (!$comment) {
            return redirect()->back();
        }

        if (!auth()->check()) {
            return redirect()->back();
        }

        $existing = UserCommentReport::where('user_id', auth()->id())
                                    ->where('comment_id', $comment->id)
                                    ->first();

        if ($existing) {
            return redirect()->back();
        }

        UserCommentReport::create([
            'user_id' => auth()->id(),
            'comment_id' => $comment->id,
            'reported' => true,
        ]);

        if (!$comment->report) {
            $comment->report()->create(['count' => 1]);
        } else {
            $comment->report->count += 1;
            $comment->report->save();
        }

        if ($comment->report->count >= 3) {

            $forumId = $comment->forum_id;

            $comment->delete();

            return redirect()
                ->route('forum.post', $forumId)
                ->with('success', 'A komment automatikusan törlésre került több bejelentés miatt.');
        }

        return redirect()->back();
    }

    //túra visszajelzések oldala
    public function tourRatings()
    {
        $user = auth()->user();

        $tours = $user->joinedTours()
            ->whereNotNull('date')
            ->where('date', '<', now())
            ->with('participants')
            ->orderBy('date', 'desc')
            ->get();

        $ratingsGiven = $user->ratingsGiven()->get();

        foreach ($tours as $tour) {

            $otherParticipants = $tour->participants->filter(fn($p) =>
                $p->id !== $user->id
            );

            $ratedIds = $ratingsGiven
                ->whereIn('rated_user_id', $otherParticipants->pluck('id'))
                ->pluck('rated_user_id')
                ->toArray();

            $tour->needs_rating = $otherParticipants
                ->whereNotIn('id', $ratedIds)
                ->count() > 0;
        }

        $tours = $tours->sortByDesc('needs_rating');

        return view('forum.ratings', compact('tours'));
    }

    //konkrét túra visszajelzés oldala
    public function tourRatingDetail(Tour $tour)
    {
        $currentUser = auth()->user();

        $participants = $tour->participants;

        $ratingsGiven = $currentUser->ratingsGiven()
            ->whereIn('rated_user_id', $participants->pluck('id'))
            ->get()
            ->keyBy('rated_user_id');

        $participantsWithRatings = $participants->map(function($participant) use ($ratingsGiven, $currentUser) {
            $participant->existing_rating = $ratingsGiven->get($participant->id) ?? null;
            $participant->is_self = $participant->id === $currentUser->id;
            return $participant;
        });

        return view('forum.tourrating', [
            'tour' => $tour,
            'participants' => $participantsWithRatings
        ]);
    }

    //értékelés mentése
    public function submitRating(Request $request, $tourId, User $user)
    {
        $tour = Tour::find($tourId);

        if (!$tour) {
            return redirect()->route('tour.deleted');
        }

        $ratingUserId = auth()->id();

        if ($ratingUserId === $user->id) {
            return redirect()->route('tour.deleted');
        }

        if (!$tour->participants->contains($ratingUserId) ||
            !$tour->participants->contains($user->id)) 
        {
            return redirect()->route('tour.deleted');
        }

        $exists = UserRating::where('rating_user_id', $ratingUserId)
            ->where('rated_user_id', $user->id)
            ->exists();

        if ($exists) {
            return back();
        }

        $validated = $request->validate([
            'precision' => 'required|integer|min:1|max:5',
            'driving'   => 'required|integer|min:1|max:5',
            'social'    => 'required|integer|min:1|max:5',
        ]);

        UserRating::create([
            'rating_user_id' => $ratingUserId,
            'rated_user_id'  => $user->id,
            'precision'      => $validated['precision'],
            'driving'        => $validated['driving'],
            'social'         => $validated['social'],
        ]);

        return redirect()->back();
    }

    //értékelés visszavonása
    public function deleteRating($tourId, User $user)
    {
        $tour = Tour::find($tourId);

        if (!$tour) {
            return redirect()->route('tour.deleted');
        }

        $ratingUserId = auth()->id();

        if (!$tour->participants->contains($ratingUserId)) {
            return redirect()->route('tour.deleted');
        }

        UserRating::where('rating_user_id', $ratingUserId)
            ->where('rated_user_id', $user->id)
            ->delete();

        return redirect()->back();
    }

    //fórum mentése
    public function saveForum(Forum $forum)
    {
        if (!auth()->check()) return back();
        if ($forum->owner == auth()->id()) return back();

        $exists = SavedForum::where('user_id', auth()->id())
                            ->where('forum_id', $forum->id)
                            ->first();

        if (!$exists) {
            SavedForum::create([
                'user_id' => auth()->id(),
                'forum_id' => $forum->id,
            ]);
        }

        return redirect()->back();
    }

    //fórum mentésének visszavonása
    public function unsaveForum(Forum $forum)
    {
        if (!auth()->check()) return back();

        SavedForum::where('user_id', auth()->id())
                ->where('forum_id', $forum->id)
                ->delete();

        return redirect()->back();
    }

}
