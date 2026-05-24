<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\ContactAutoReplyMail;
use App\Mail\ContactReceivedMail;
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

        // 1. 運営宛：受信通知（Reply-To に送信者メアド設定済）
        Mail::to(config('mail.from.address', 'contact@laiweb-dash.com'))
            ->send(new ContactReceivedMail($validated));

        // 2. 送信者宛：自動返信
        Mail::to($validated['email'])
            ->send(new ContactAutoReplyMail($validated));

        return back()->with('status',
            'お問い合わせを送信しました。受付確認のメールを ' . $validated['email'] . ' 宛にお送りしています。'
        );
    }
}
