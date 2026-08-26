<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): ?View
    {
        try {
            return view('layouts.app');
        } catch (\Exception $e) {
            Log::error('AppLayout::render failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
