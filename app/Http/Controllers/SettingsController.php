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
        // SECURITY: this rewrites .env, so only the superadmin may call it.
        abort_unless(auth()->user()?->hasRole('superadmin'), 403);

        // Single-token values only: no whitespace/newlines/quotes/'#', so a value can
        // never smuggle extra KEY=VALUE lines (APP_KEY, DB_*, APP_DEBUG...) into .env.
        $request->validate([
            'stripe_key' => ['nullable', 'string', 'max:255', 'regex:/^pk_(live|test)_[A-Za-z0-9]+$/'],
            'stripe_secret' => ['nullable', 'string', 'max:255', 'regex:/^(sk|rk)_(live|test)_[A-Za-z0-9]+$/'],
            'stripe_webhook_secret' => ['nullable', 'string', 'max:255', 'regex:/^whsec_[A-Za-z0-9]+$/'],
        ]);

        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        $updates = [
            'STRIPE_KEY' => $request->input('stripe_key', ''),
            'STRIPE_SECRET' => $request->input('stripe_secret', ''),
            'STRIPE_WEBHOOK_SECRET' => $request->input('stripe_webhook_secret', ''),
        ];

        foreach ($updates as $key => $value) {
            $line = $key . '=' . $value;
            if (preg_match('/^' . $key . '=.*/m', $content)) {
                // callback form: $value is never interpreted as a $1 / \0 backreference
                $content = preg_replace_callback('/^' . $key . '=.*/m', fn () => $line, $content);
            } else {
                $content .= "\n" . $line;
            }
        }

        file_put_contents($envPath, $content);
        Artisan::call('config:clear');

        return redirect()->route('settings')->with('success', 'Stripe settings saved.');
    }
}
