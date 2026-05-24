<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * 指定したロール（複数可）のいずれかを持つユーザーのみ通過。
     *
     * usage: ->middleware('role:admin')
     *        ->middleware('role:admin,staff')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403, 'このページへのアクセス権限がありません。');
        }

        return $next($request);
    }
}
