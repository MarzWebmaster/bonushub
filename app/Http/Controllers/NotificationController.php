<?php

namespace App\Http\Controllers;

use App\Models\InAppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** The logged-in user's in-app notifications (written by BonusNotifier; previously there was no way to read them). */
class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        return view('notifications', [
            'items' => InAppNotification::where('user_id', Auth::id())->orderByDesc('id')->paginate(30),
            'unread' => InAppNotification::where('user_id', Auth::id())->whereNull('read_at')->count(),
        ]);
    }

    public function read($id): RedirectResponse
    {
        InAppNotification::where('user_id', Auth::id())->findOrFail($id)->markAsRead();
        return back();
    }

    public function readAll(): RedirectResponse
    {
        InAppNotification::where('user_id', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('success', 'Semua notifikasi ditanda sebagai telah dibaca.');
    }
}
