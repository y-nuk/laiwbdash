<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GbpConnectController extends Controller
{
    public function index(): View
    {
        return view('admin.gbp.connect', [
            'user' => Auth::user(),
            'configured' => config('services.google.client_id') && config('services.google.client_secret'),
        ]);
    }
}
