<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AppLayout extends Component
{
    public $hideNavigation;

    public function __construct($hideNavigation = false)
    {
        // Convierte en booleano aunque llegue string
        $this->hideNavigation = filter_var($hideNavigation, FILTER_VALIDATE_BOOLEAN);
    }

    public function render()
    {
        return view('layouts.app');
    }
}
