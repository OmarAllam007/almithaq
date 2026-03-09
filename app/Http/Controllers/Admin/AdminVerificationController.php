<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminVerificationController extends Controller
{
    /**
     * List all users who have uploaded verification videos and are pending approval.
     */
    public function index()
    {
        $pendingUsers = User::query()
            ->whereNotNull('verification_video_path')
            ->where('is_verified', false)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'video_url' => $user->verification_video_path
                        ? route('admin.verifications.video', $user->id)
                        : null,
                    'created_at' => $user->created_at?->format('Y-m-d H:i'),
                    'updated_at' => $user->updated_at?->format('Y-m-d H:i'),
                ];
            });

        return Inertia::render('Admin/Verifications/Index', [
            'pendingUsers' => $pendingUsers,
        ]);
    }

    /**
     * Serve the verification video securely (only for admins).
     */
    public function serveVideo(User $user)
    {
        $admin = auth()->user();

        if (!$admin->isAdmin()) {
            abort(403);
        }

        if (!$user->verification_video_path || !Storage::disk('local')->exists($user->verification_video_path)) {
            abort(404, 'Video not found.');
        }

        return Storage::disk('local')->response($user->verification_video_path);
    }

    /**
     * Approve a user's verification.
     */
    public function approve(User $user)
    {
        $user->update([
            'is_verified' => true,
        ]);

        return redirect()->back()->with('success', "{$user->name} has been verified successfully.");
    }

    /**
     * Reject a user's verification — delete the video and reset so they can re-upload.
     */
    public function reject(User $user)
    {
        // Delete the video file
        if ($user->verification_video_path && Storage::disk('local')->exists($user->verification_video_path)) {
            Storage::disk('local')->delete($user->verification_video_path);
        }

        // Reset the user's video path and keep is_verified as false
        $user->update([
            'verification_video_path' => null,
            'is_verified' => false,
        ]);

        return redirect()->back()->with('success', "{$user->name}'s verification has been rejected. They can re-upload.");
    }
}