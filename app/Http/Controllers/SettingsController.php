<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
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
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!password_verify($request->current_password, $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $user->update([
            'password' => bcrypt($request->new_password),
        ]);

        return redirect()->route('settings')->with('success', 'Password updated.');
    }

    public function stripe(Request $request): RedirectResponse
    {
        $request->validate([
            'stripe_key' => 'nullable|string',
            'stripe_secret' => 'nullable|string',
            'stripe_webhook_secret' => 'nullable|string',
        ]);

        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        $updates = [
            'STRIPE_KEY' => $request->input('stripe_key', ''),
            'STRIPE_SECRET' => $request->input('stripe_secret', ''),
            'STRIPE_WEBHOOK_SECRET' => $request->input('stripe_webhook_secret', ''),
        ];

        foreach ($updates as $key => $value) {
            if (preg_match('/^' . $key . '=.*/m', $content)) {
                $content = preg_replace('/^' . $key . '=.*/m', $key . '=' . $value, $content);
            } else {
                $content .= "\n" . $key . '=' . $value;
            }
        }

        file_put_contents($envPath, $content);
        Artisan::call('config:clear');

        return redirect()->route('settings')->with('success', 'Stripe settings saved.');
    }
}