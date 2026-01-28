<?php

namespace App\Http\Controllers;

use App\Http\Requests\InitiateSubscriptionRequest;
use App\Models\Enums\SubscriptionPlanPrice;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $activeSubscription = $user->activeSubscription();
        $subscriptions = $user->subscriptions()->latest()->get();

        return Inertia::render('Subscription/Index', [
            'activeSubscription' => $activeSubscription ? [
                'id' => $activeSubscription->id,
                'plan_duration' => $activeSubscription->plan_duration,
                'price' => $activeSubscription->price,
                'status' => $activeSubscription->status->value,
                'starts_at' => $activeSubscription->starts_at?->toDateString(),
                'expires_at' => $activeSubscription->expires_at?->toDateString(),
                'days_remaining' => $activeSubscription->daysRemaining(),
            ] : null,
            'subscriptions' => $subscriptions->map(fn ($sub) => [
                'id' => $sub->id,
                'plan_duration' => $sub->plan_duration,
                'price' => $sub->price,
                'status' => $sub->status->value,
                'starts_at' => $sub->starts_at?->toDateString(),
                'expires_at' => $sub->expires_at?->toDateString(),
            ]),
        ]);
    }

    public function initiate(InitiateSubscriptionRequest $request)
    {
        $planPrices =  SubscriptionPlanPrice::getAllValues();

        $planDuration = (int) $request->plan_duration;
        $price = $planPrices[$planDuration] ?? 10;

        // Just redirect to payment page with plan info - no DB records yet
        return redirect()->route('payment.create', [
            'plan_duration' => $planDuration,
            'amount' => $price,
        ]);
    }

    public function status()
    {
        $user = auth()->user();
        $activeSubscription = $user->activeSubscription();

        return response()->json([
            'has_active_subscription' => $user->hasActiveSubscription(),
            'subscription' => $activeSubscription ? [
                'id' => $activeSubscription->id,
                'plan_duration' => $activeSubscription->plan_duration,
                'price' => $activeSubscription->price,
                'status' => $activeSubscription->status->value,
                'starts_at' => $activeSubscription->starts_at?->toDateString(),
                'expires_at' => $activeSubscription->expires_at?->toDateString(),
                'days_remaining' => $activeSubscription->daysRemaining(),
            ] : null,
        ]);
    }
}
