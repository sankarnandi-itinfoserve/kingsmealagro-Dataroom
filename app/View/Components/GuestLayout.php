<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class GuestLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): ?View
    {
        try {
            return view('layouts.guest');
        } catch (\Exception $e) {
            Log::error('GuestLayout::render failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
