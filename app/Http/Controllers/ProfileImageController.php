<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileImageRequest;
use App\Models\ProfileImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Intervention\Image\Laravel\Facades\Image;

class ProfileImageController extends Controller
{
    public function index(): InertiaResponse
    {
        $images = auth()->user()
            ->profileImages()
            ->ordered()
            ->get()
            ->map(function (ProfileImage $image) {
                return [
                    'id' => $image->id,
                    'thumbnail_url' => $image->thumbnail_url,
                    'is_main' => $image->is_main,
                    'is_approved' => $image->is_approved,
                    'order' => $image->order,
                    'created_at' => $image->created_at,
                ];
            });

        return Inertia::render('Profile/Gallery', [
            'images' => $images,
            'canUploadMore' => $images->count() < 5,
        ]);
    }

    public function store(StoreProfileImageRequest $request): RedirectResponse
    {
        $user = $request->user();
        $uploadedImages = [];

        foreach ($request->file('images') as $index => $image) {
            // Generate unique filename
            $filename = uniqid('profile_', true).'.'.$image->getClientOriginalExtension();

            // Create paths
            $imagePath = 'profile-images/'.$filename;
            $thumbnailPath = 'profile-images/thumbnails/'.$filename;

            // Store original image with max dimensions
            $originalImage = Image::read($image);
            $originalImage->scaleDown(width: 1920, height: 1920);
            Storage::disk('local')->put($imagePath, (string) $originalImage->encode());

            // Create and store thumbnail
            $thumbnail = Image::read($image);
            $thumbnail->cover(200, 200);
            Storage::disk('local')->put($thumbnailPath, (string) $thumbnail->encode());

            // Get the highest order number for this user
            $maxOrder = ProfileImage::where('user_id', $user->id)->max('order') ?? -1;

            // Create database record
            $profileImage = ProfileImage::create([
                'user_id' => $user->id,
                'image_path' => $imagePath,
                'thumbnail_path' => $thumbnailPath,
                'is_main' => false,
                'is_approved' => false,
                'order' => $maxOrder + 1,
            ]);

            $uploadedImages[] = $profileImage;

            // If this is the first image, set it as main
            if ($user->profileImages()->count() === 1) {
                $profileImage->setAsMain();
            }
        }

        return redirect()
            ->route('profile.gallery')
            ->with('success', count($uploadedImages).' image(s) uploaded successfully. Waiting for admin approval.');
    }

    public function destroy(ProfileImage $image): RedirectResponse
    {
        // Ensure user can only delete their own images
        if ($image->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $wasMain = $image->is_main;

        $image->delete();

        // If deleted image was main, set another image as main
        if ($wasMain) {
            $nextMainImage = auth()->user()
                ->profileImages()
                ->ordered()
                ->first();

            if ($nextMainImage) {
                $nextMainImage->setAsMain();
            }
        }

        return redirect()
            ->route('profile.gallery')
            ->with('success', 'Image deleted successfully.');
    }

    public function setMain(ProfileImage $image): RedirectResponse
    {
        // Ensure user can only modify their own images
        if ($image->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $image->setAsMain();

        return redirect()
            ->route('profile.gallery')
            ->with('success', 'Main image updated successfully.');
    }

    public function serveThumbnail(ProfileImage $image): Response
    {
        // Ensure user can only view their own images
        //        if ($image->user_id !== auth()->id()) {
        //            abort(403, 'Unauthorized action.');
        //        }

        $path = Storage::disk('local')->path($image->thumbnail_path);

        if (! file_exists($path)) {
            abort(404);
        }

        $file = Storage::disk('local')->get($image->thumbnail_path);
        $mimeType = Storage::disk('local')->mimeType($image->thumbnail_path);

        return response($file, 200)->header('Content-Type', $mimeType);
    }
}
