<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\FavoritePlaceController;

Route::get('/', [TourController::class, 'index'])->name('home');

// USER CONTROLLER
// Show registration form
Route::get('/register', [UserController::class, 'create'])->middleware('guest');

// Create new user
Route::post('/users', [UserController::class, 'store']);

// Show login form
Route::get('/login', [UserController::class, 'login'])->name('login')->middleware('guest');

// Log in user
Route::post('/users/authenticate', [UserController::class, 'authenticate']);

// Log out user
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth');

// PROFILE CONTROLLER
Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/profile/{user}/edit', [ProfileController::class, 'edit'])->middleware('auth');
Route::patch('/profile/{user}', [ProfileController::class, 'update'])->middleware('auth');
Route::get('/profile-search', [ProfileController::class, 'search']);

// NOTIFICATION CONTROLLER
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index')->middleware('auth');
Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read')->middleware('auth');
Route::post('/notifications/notifications/readall', [NotificationController::class, 'markAllAsRead'])->name('notifications.read_all')->middleware('auth');
Route::post('/notifications/mock', [NotificationController::class, 'createMock'])->name('notifications.mock')->middleware('auth');

// SUBSCRIPTION CONTROLLER
// Display the subscription page
Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index')->middleware('auth');

// Purchase a subscription
Route::post('/subscription/buy', [SubscriptionController::class, 'buy'])->name('subscription.buy')->middleware('auth');


// TOUR CONTROLLER
Route::get('/tours/{tour}', [TourController::class, 'show'])->name('tours.show');

Route::middleware('auth')->group(function () {
	Route::get('/tours/{tour}/edit', [TourController::class, 'edit'])->name('tours.edit');
	Route::put('/tours/{tour}', [TourController::class, 'update'])->name('tours.update');
	Route::post('/tours/{tour}/invite', [TourController::class, 'invite'])->name('tours.invite');
	Route::get('/tours/{tour}/search-users', [TourController::class, 'searchUsersForInvite'])->name('tours.search-users');

	Route::get('/tour_planning', [TourController::class, 'create'])->name('tours.create');
	Route::post('/tour_planning', [TourController::class, 'store'])->name('tours.store');
	Route::post('/tours/{tour}/join', [TourController::class, 'join'])->name('tours.join');
	Route::delete('/tours/{tour}/leave', [TourController::class, 'leave'])->name('tours.leave');
    Route::delete('/tours/{tour}', [TourController::class, 'destroy'])->name('tours.destroy');
});

// FORUM CONTROLLER
Route::middleware('auth')->group(function () {

    //Fórum főoldal
    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');

    //Saját fórumaim
    Route::get('/forum/my-forums', [ForumController::class, 'myForums'])->name('forum.myforums');

    //Fórumok listázása kategória szerint
    Route::get('/forum/category/{category}', [ForumController::class, 'show'])->name('forum.show');

    //Új fórum létrehozása
    Route::get('/forum/{category}/create', [ForumController::class, 'create'])->name('forum.create');

    //Új fórum mentése
    Route::post('/forum/{category}', [ForumController::class, 'store'])->name('forum.store');

    //FÓRUM OLDAL

    //Fórum részletes nézet
    Route::get('/forum/post/{id}', [ForumController::class, 'post'])->name('forum.post');

    //Fórum szerkesztés mentése
    Route::put('/forum/post/{id}', [ForumController::class, 'update'])->name('forum.update');

    //Fórum törlése
    Route::delete('/forum/post/{id}', [ForumController::class, 'destroy'])->name('forum.destroy');

    //Fórum jelentés
    Route::post('/forum/{id}/report', [ForumController::class, 'submitReport'])->name('forum.report');

    //Fórum like/dislike mentése
    Route::post('/forum/{id}/impressions/save', [ForumController::class, 'saveImpressions'])->name('impressions.save');

    //KOMMENTEK

    //Komment mentése
    Route::post('/forum/{id}/comments', [ForumController::class, 'storeComment'])->name('forum.comment.store');

    //Komment törlése
    Route::delete('/forum/comment/{id}', [ForumController::class, 'destroyComment'])->name('comment.destroy');

    //Komment frissítése
    Route::put('/forum/comment/{id}', [ForumController::class, 'updateComment'])->name('comment.update');

    //Komment jelentése
    Route::post('/comment/{id}/report', [ForumController::class, 'submitCommentReport'])->name('comment.report');

    //Komment like/dislike mentése
    Route::post('/comment/{id}/impressions/save', [ForumController::class, 'saveCommentImpressions'])->name('comment.impressions.save');

    //404-et helyettesítő oldal fórumokhoz
    Route::get('/forum/deleted', function () {return view('forum.deleted');})->name('forum.deleted');

    //404-et helyettesítő oldal értékelésekhez
    Route::get('/tour/deleted', function () {return view('tour.deleted');})->name('tour.deleted');

    //Túravisszajelzések oldal
    Route::get('/tour/ratings', [ForumController::class, 'tourRatings'])->name('tour.ratings');

    //Túra részletes értékelés oldala
    Route::get('/tour/ratings/{tour}', [ForumController::class, 'tourRatingDetail'])->name('tour.rating.detail');

    //Értékelés mentése
    Route::post('/tour/{tour}/rate/{user}', [ForumController::class, 'submitRating'])->name('tour.rate');

    //Értékelés törlése
    Route::delete('/tour/{tour}/rate/{user}', [ForumController::class, 'deleteRating'])->name('tour.rating.delete');

    //Fórum mentése
    Route::post('/forum/{forum}/save', [ForumController::class, 'saveForum'])->name('forum.save');

    //Mentés visszavonása
    Route::post('/forum/{forum}/unsave', [ForumController::class, 'unsaveForum'])->name('forum.unsave');

});

// FRIEND CONTROLLER
Route::middleware('auth')->group(function () {
    Route::post('/friends/add', [FriendController::class, 'add'])->name('friends.add');
    Route::post('/friends/remove', [FriendController::class, 'remove'])->name('friends.remove');
    Route::get('/friends/list', [FriendController::class, 'list'])->name('friends.list');
});

// INTERACTIVE MAP CONTROLLER
Route::middleware('auth')->group(function () {
    Route::get('/map', function () { return view('interactiveMap.map');})->name('tours.map');

    Route::get('/favorites', [FavoritePlaceController::class, 'index'])->name('favorites.index');
    Route::post('/favorites', [FavoritePlaceController::class, 'store'])->name('favorites.store');
    Route::patch('/favorites/{favoritePlace}', [FavoritePlaceController::class, 'update'])->name('favorites.update');
    Route::delete('/favorites/{favoritePlace}', [FavoritePlaceController::class, 'destroy'])->name('favorites.destroy');
    Route::post('/favorites/share', [FavoritePlaceController::class, 'share'])->name('favorites.share');
});
