<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePrivilege
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$privileges): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        foreach ($privileges as $privilege) {
            if (method_exists($user, 'hasPrivilege') && $user->hasPrivilege($privilege)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this area.');
    }
}
