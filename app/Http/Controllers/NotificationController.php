<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications()->paginate(15);
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('status', 'Notification marked as read.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();

        return back()->with('status', 'Notification deleted.');
    }

    public function clearAll(): RedirectResponse
    {
        auth()->user()->notifications()->delete();

        return back()->with('status', 'All notifications cleared.');
    }
}
