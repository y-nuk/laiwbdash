<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class StaticPageController extends Controller
{
    public function privacy(): View
    {
        return view('public.privacy');
    }

    public function terms(): View
    {
        return view('public.terms');
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'category' => ['nullable', 'in:general,trial,bug,other'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // 簡易：log driver でもメール内容が storage/logs/laravel.log に記録される
        // 本番では Xserver SMTP 経由で運営宛 + 自動返信を送る
        Mail::raw(
            "新しいお問い合わせがあります。\n\n" .
            "種別：{$validated['category']}\n" .
            "お名前：{$validated['name']}\n" .
            "会社名：" . ($validated['company'] ?? '—') . "\n" .
            "メール：{$validated['email']}\n" .
            "電話：" . ($validated['phone'] ?? '—') . "\n" .
            "内容：\n{$validated['message']}\n",
            function ($mail) use ($validated) {
                $mail->to(config('mail.from.address', 'contact@laiweb-dash.com'))
                    ->subject('【laiweb-dash お問い合わせ】' . $validated['name']);
            }
        );

        return back()->with('status', 'お問い合わせを送信しました。通常 2 営業日以内にご返信いたします。');
    }
}
