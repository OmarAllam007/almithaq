<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class VerificationController extends Controller
{
    /**
     * Show the verification upload page.
     */
    public function show()
    {
        $user = auth()->user();
        if($user->isVerified()){
            return redirect('/');
        }

        return Inertia::render('Verification/Upload', [
            'hasVideo' => !empty($user->verification_video_path),
            'isVerified' => (bool)$user->is_verified,
        ]);
    }

    /**
     * Handle the verification video upload.
     */
    public function store(Request $request)
    {
        $request->validate([
            'video' => [
                'required',
                'file',
                'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm,video/x-matroska',
                'max:10240', // 10MB
            ],
        ], [
            'video.required' => 'Please upload a verification video.',
            'video.mimetypes' => 'The file must be a video (MP4, MOV, AVI, WebM, or MKV).',
            'video.max' => 'The video must not exceed 10MB.',
        ]);

        $user = auth()->user();

        // Delete old video if exists
        if ($user->verification_video_path && Storage::disk('local')->exists($user->verification_video_path)) {
            Storage::disk('local')->delete($user->verification_video_path);
        }

        // Store the video in the private storage
        $path = $request->file('video')->store(
            "verification_videos/{$user->id}",
            'local'
        );

        // Update user record
        $user->update([
            'verification_video_path' => $path,
            'is_verified' => false, // Reset verification status on new upload
        ]);

        return redirect()->route('verification.show')
            ->with('success', 'Your verification video has been uploaded successfully. Please wait for admin approval.');
    }
}
