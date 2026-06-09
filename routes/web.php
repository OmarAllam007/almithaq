<?php

use App\Http\Controllers\Api\UsernameCheckController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CompleteProfileController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DeleteAccountController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IgnoreController;
use App\Http\Controllers\ImageRequestController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NewMembersController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnlineMembersController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileImageController;
use App\Http\Controllers\QuickSearchController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\SmartSearchController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserInteractionsController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

// Auth::loginUsingId(1);
Route::group(['middleware' => 'guest'], function () {
    Route::get('signup', [RegisterController::class, 'showSignupForm'])->name('signup');
    Route::post('signup', [RegisterController::class, 'store'])->name('signup.store')->middleware('throttle:10,1');
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store')->middleware('throttle:10,1');

});

Route::get('/language/{locale}', [LanguageController::class, 'switchLanguage'])->name('language.switch');
Route::get('/language', [LanguageController::class, 'getCurrentLanguage'])->name('language.current');

Route::group(['middleware' => 'auth'], function () {
    // Routes accessible without profile completion
    Route::get('complete-profile', [CompleteProfileController::class, 'show'])->name('complete-profile');
    Route::post('complete-profile', [CompleteProfileController::class, 'store'])->name('complete-profile.store');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Verification Routes (accessible without profile completion or verification)
    Route::get('verification', [VerificationController::class, 'show'])->name('verification.show');
    Route::post('verification', [VerificationController::class, 'store'])->name('verification.store');

    // Routes requiring completed profile
    Route::group(['middleware' => [\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureIsVerified::class]], function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        // Debug routes (remove in production)
        if (app()->environment('local')) {
            Route::get('/test-broadcast', [\App\Http\Controllers\TestBroadcastController::class, 'testBroadcast']);
            Route::get('/echo-test', [\App\Http\Controllers\TestBroadcastController::class, 'echoTest']);
        }

        Route::get('profile', [ProfileController::class, 'index'])->name('profile');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('profile/delete-account', DeleteAccountController::class)->name('profile.delete-account');
        Route::post('profile/reset-password', ResetPasswordController::class)->name('profile.reset-password');
        Route::get('users/{user}', [ProfileController::class, 'show'])->name('users.show');
        Route::get('users/{user}/chat-info', [ProfileController::class, 'chatInfo'])->name('users.chat-info');
        Route::get('users/{user}/login-location', [ProfileController::class, 'loginLocation'])->name('users.login-location');

        // Profile Gallery
        Route::get('profile/gallery', [ProfileImageController::class, 'index'])->name('profile.gallery');
        Route::post('profile/gallery', [ProfileImageController::class, 'store'])->name('profile.gallery.store');
        Route::delete('profile/gallery/{image}', [ProfileImageController::class, 'destroy'])->name('profile.gallery.destroy');
        Route::patch('profile/gallery/{image}/set-main', [ProfileImageController::class, 'setMain'])->name('profile.gallery.set-main');
        Route::get('profile/images/{image}/thumbnail', [ProfileImageController::class, 'serveThumbnail'])->name('profile.images.thumbnail');
        Route::get('profile/images/{image}/original', [ProfileImageController::class, 'serveOriginal'])->name('profile.images.original');

        // New Members
        Route::get('new-members', [NewMembersController::class, 'index'])->name('new-members');
        Route::get('online-members', [OnlineMembersController::class, 'index'])->name('members-online');

        // Conversations & Messages
        Route::get('conversations', [ConversationController::class, 'index'])->name('conversations.index');
        Route::post('conversations', [ConversationController::class, 'store'])->name('conversations.store');
        Route::delete('conversations/bulk-delete', [ConversationController::class, 'bulkDestroy'])->name('conversations.bulk-destroy');
        Route::get('conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
        Route::delete('conversations/{conversation}', [ConversationController::class, 'destroy'])->name('conversations.destroy');
        Route::post('conversations/{conversation}/messages', [MessageController::class, 'store'])->name('messages.store');
        Route::post('conversations/{conversation}/mark-as-read', [MessageController::class, 'markAsRead'])->name('messages.mark-as-read');
        Route::post('conversations/{conversation}/typing', [MessageController::class, 'typing'])->name('messages.typing');
        Route::post('messages/mark-all-as-read', [MessageController::class, 'markAllAsRead'])->name('messages.mark-all-as-read');

        //    Route::post('/online')
        //    Route::post('/offline')
        // Favorites
        Route::post('users/{user}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

        // Ignore
        Route::post('users/{user}/ignore', [IgnoreController::class, 'toggle'])->name('ignores.toggle');

        // Report
        Route::post('users/{user}/report', [ReportController::class, 'store'])->name('reports.store');

        // Image Requests
        Route::post('users/{user}/image-request', [ImageRequestController::class, 'store'])->name('image-requests.store');
        Route::get('image-requests', [ImageRequestController::class, 'index'])->name('image-requests.index');
        Route::patch('image-requests/{imageRequest}/approve', [ImageRequestController::class, 'approve'])->name('image-requests.approve');
        Route::patch('image-requests/{imageRequest}/reject', [ImageRequestController::class, 'reject'])->name('image-requests.reject');
        Route::delete('image-requests/{imageRequest}', [ImageRequestController::class, 'destroy'])->name('image-requests.destroy');

        // Inbox
        Route::get('inbox', [InboxController::class, 'index'])->name('inbox.index');
        Route::get('who-liked-me', [UserInteractionsController::class, 'whoLikedMe'])->name('who-liked-me');
        Route::get('who-visited-me', [UserInteractionsController::class, 'whoVisitedMe'])->name('who-visited-me');
        Route::get('my-interactions', [UserInteractionsController::class, 'myInteractions'])->name('my-interactions');

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

        // Quick Search (from home page filters)
        Route::get('quick-search', [QuickSearchController::class, 'index'])->name('quick-search.index');

        // Smart Search
        Route::get('smart-search', [SmartSearchController::class, 'index'])->name('smart-search.index');
        Route::match(['get', 'post'], 'smart-search/search', [SmartSearchController::class, 'search'])->name('smart-search.search');
        Route::post('smart-search/clear', [SmartSearchController::class, 'clearFilters'])->name('smart-search.clear');

        // Subscription
        Route::get('subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
        Route::post('subscription/initiate', [SubscriptionController::class, 'initiate'])->name('subscription.initiate');
        Route::get('subscription/status', [SubscriptionController::class, 'status'])->name('subscription.status');

        // Payment
        Route::get('payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
        Route::get('payment/create', [PaymentController::class, 'create'])->name('payment.create');
        Route::get('payment/{payment}', [PaymentController::class, 'show'])->name('payment.show');
        Route::post('payment/process', [PaymentController::class, 'process'])->name('payment.process');
        Route::post('payment/{payment}/process', [PaymentController::class, 'process'])->name('payment.process.existing');
    }
    );

    require __DIR__.'/admin.php';
});

// Payment webhook (no auth required)
Route::post('payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

// API routes for validation
Route::post('api/check-username', [UsernameCheckController::class, 'check'])->name('api.check-username');
