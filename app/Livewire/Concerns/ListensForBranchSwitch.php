<?php

namespace App\Livewire\Concerns;

use Livewire\Attributes\On;

trait ListensForBranchSwitch
{
    #[On('branch-switched')]
    public function handleBranchSwitched(): void
    {
        $this->refreshAfterBranchSwitch();
    }

    protected function refreshAfterBranchSwitch(): void
    {
        //
    }
}
