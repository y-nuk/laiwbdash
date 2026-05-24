<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * /dashboard：ログイン後の振り分け。
     * admin / staff → /admin/dashboard
     * client       → /client/dashboard
     */
    public function index(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isInternal()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('client.dashboard');
    }
}
