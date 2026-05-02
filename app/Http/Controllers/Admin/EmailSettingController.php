<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\MailketingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailSettingController extends Controller
{
    private array $keys = [
        'email_notifications_enabled',
        'mailketing_api_token',
        'mailketing_from_name',
        'mailketing_from_email',
        'email_notify_paid',
        'email_notify_session_complete',
    ];

    public function edit(): View
    {
        $settings = Setting::getMany($this->keys);

        return view('settings.email', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email_notifications_enabled' => ['nullable', 'boolean'],
            'mailketing_api_token' => ['nullable', 'string', 'max:255'],
            'mailketing_from_name' => ['nullable', 'string', 'max:100'],
            'mailketing_from_email' => ['nullable', 'email', 'max:150'],
            'email_notify_paid' => ['nullable', 'boolean'],
            'email_notify_session_complete' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('email_notifications_enabled')) {
            $request->validate([
                'mailketing_api_token' => ['required', 'string', 'max:255'],
                'mailketing_from_email' => ['required', 'email', 'max:150'],
                'mailketing_from_name' => ['required', 'string', 'max:100'],
            ]);
        }

        Setting::set('email_notifications_enabled', $request->has('email_notifications_enabled') ? '1' : '0');
        Setting::set('mailketing_api_token', $validated['mailketing_api_token'] ?? null);
        Setting::set('mailketing_from_name', $validated['mailketing_from_name'] ?? null);
        Setting::set('mailketing_from_email', $validated['mailketing_from_email'] ?? null);
        Setting::set('email_notify_paid', $request->has('email_notify_paid') ? '1' : '0');
        Setting::set('email_notify_session_complete', $request->has('email_notify_session_complete') ? '1' : '0');

        return redirect()->route('settings.email')->with('success', 'Pengaturan email berhasil disimpan.');
    }

    public function test(Request $request, MailketingService $mail): JsonResponse
    {
        $validated = $request->validate([
            'recipient' => ['required', 'email'],
        ]);

        $siteName = (string) Setting::get('site_name', 'Philo Photobooth');
        $subject = 'Uji Coba Notifikasi Email — '.$siteName;

        $html = view('emails.test', [
            'subject' => $subject,
            'recipient' => $validated['recipient'],
            'sentAt' => now()->format('d F Y, H:i'),
        ])->render();

        $result = $mail->send(
            to: $validated['recipient'],
            subject: $subject,
            content: $html,
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['success'] ? 200 : 422);
    }
}
