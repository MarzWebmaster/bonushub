<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $user->update($request->only('name'));

        return redirect()->route('settings')->with('success', 'Settings updated.');
    }

    public function notifications(Request $request): RedirectResponse
    {
        return redirect()->route('settings')->with('success', 'Notification preferences saved.');
    }

    public function password(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ]);

        $user = auth()->user();
        $user->update(['password' => bcrypt($request->new_password)]);

        return redirect()->route('settings')->with('success', 'Password updated.');
    }
}