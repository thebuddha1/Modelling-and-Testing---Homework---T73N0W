<?php 

namespace App\Http\Controllers; 

use Illuminate\Http\Request; 
use Illuminate\Support\Carbon; 

class SubscriptionController extends Controller 
{
    public function index() 
    {
        return view('subscription.subscription');
    }

    public function buy(Request $request) 
    {
        $request->validate([
            'plan' => 'required|in:monthly,semiannual,yearly',
        ]);

        $user = auth()->user();
        $plan = $request->plan;
        $price = 2000;

        $start = $user->subscription_end_at && Carbon::parse($user->subscription_end_at)->isFuture()
            ? Carbon::parse($user->subscription_end_at)
            : Carbon::now();

        $end = match($plan) {
            'monthly' => $start->copy()->addMonth(),
            'semiannual' => $start->copy()->addMonths(6),
            'yearly' => $start->copy()->addYear(),
        };

        $user->subscription_start_at = $user->subscription_start_at ?? Carbon::now();
        $user->subscription_end_at = $end;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Sikeres előfizetés vásárlás!",
            'subscription_end_at' => $end->translatedFormat('Y.m.d.'),
        ]);
    }
}
