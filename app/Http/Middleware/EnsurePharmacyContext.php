<?php

namespace App\Http\Middleware;

use App\Services\BranchContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePharmacyContext
{
    public function __construct(private BranchContext $branchContext) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($user->tenant_id === null) {
            abort(403, 'Your account is not linked to a pharmacy tenant.');
        }

        if ($user->is_active === false) {
            abort(403, 'Your account is inactive.');
        }

        if ($this->branchContext->activeTenantId() === null) {
            $this->branchContext->initialize($user);
        }

        if ($this->branchContext->activeBranchId() === null) {
            abort(403, 'No active branch is available for your account.');
        }

        return $next($request);
    }
}
