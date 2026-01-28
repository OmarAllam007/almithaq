<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use App\Models\AccountDeletion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteAccountController extends Controller
{
    public function __invoke(DeleteAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $request) {
            AccountDeletion::create([
                'user_id' => $user->id,
                'reason' => $request->validated('reason'),
                'message' => $request->validated('message'),
            ]);

            $user->update(['is_active' => false]);
            $user->delete();
        });

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Your account has been deleted.');
    }
}
