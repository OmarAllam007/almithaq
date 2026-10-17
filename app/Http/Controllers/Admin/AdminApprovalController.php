<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfileImage;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminApprovalController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Approvals/Index', [
            'images' => ProfileImage::query()->with('user:id,name,username')->where('is_approved', false)->latest()->paginate(20)->through(fn (ProfileImage $image) => [
                'id' => $image->id,
                'user' => $image->user,
                'is_main' => $image->is_main,
                'created_at' => $image->created_at,
                'thumbnail_url' => route('admin.approvals.thumbnail', $image),
            ]),
        ]);
    }

    public function approve(ProfileImage $image)
    {
        $image->approve();

        return back()->with('success', 'Image approved.');
    }

    public function reject(ProfileImage $image)
    {
        $user = $image->user;
        $wasMain = $image->is_main;
        $image->delete();
        if ($wasMain) {
            $user?->profileImages()->first()?->setAsMain();
        }

        return back()->with('success', 'Image rejected and removed.');
    }

    public function thumbnail(ProfileImage $image)
    {
        abort_unless(Storage::disk('local')->exists($image->thumbnail_path), 404);

        return Storage::disk('local')->response($image->thumbnail_path);
    }
}
