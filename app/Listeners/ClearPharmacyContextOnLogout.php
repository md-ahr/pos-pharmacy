<?php

namespace App\Listeners;

use App\Services\BranchContext;
use Illuminate\Auth\Events\Logout;

class ClearPharmacyContextOnLogout
{
    public function __construct(private BranchContext $branchContext) {}

    public function handle(Logout $event): void
    {
        $this->branchContext->clear();
    }
}
