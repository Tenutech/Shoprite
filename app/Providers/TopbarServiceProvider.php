<?php

namespace App\Providers;

use App\Models\Message;
use App\Models\Notification;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class TopbarServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        view()->composer('layouts.topbar', function (View $view) {
            //User ID
            $userID = Auth::id();

            // Messages
            $messages = Message::with([
                'from',
                'to'
            ])
            ->where('to_id', $userID)
            ->where('read', 'No')
            ->orderBy('created_at', 'desc')
            ->get();

            // Notifications
            $notifications = Notification::with([
                'user',
                'causer',
                'subject',
                'type',
            ])
            ->where('user_id', $userID)
            ->where('type_id', '!=', 3)
            ->where('show', 'Yes')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($notification) {
                return $notification->subject_type . ':' . $notification->subject_id;
            })
            ->map(function ($groupedNotifications) {
                // Return the latest notification from the group
                return $groupedNotifications->first();
            });

            $view->with([
                'messages' => $messages,
                'notifications' => $notifications
            ]);
        });
    }
}
