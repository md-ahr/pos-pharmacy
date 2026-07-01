<?php

namespace App\Listeners;

use App\Services\BranchContext;
use Illuminate\Auth\Events\Login;

class SetPharmacyContextOnLogin
{
    public function __construct(private BranchContext $branchContext) {}

    public function handle(Login $event): void
    {
        if ($event->user->tenant_id !== null) {
            $this->branchContext->initialize($event->user);
        }
    }
}
