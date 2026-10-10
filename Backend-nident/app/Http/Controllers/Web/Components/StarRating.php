<?php

namespace App\Http\Controllers\Web\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StarRating extends Component
{
    public $rating;
    public $label;

    /**
     * @param float $rating
     * @param string|null $label
     */
    public function __construct($rating = 0, $label = null)
    {
        $this->rating = $rating;
        $this->label = $label;
    }

    public function render()
    {
          return view('web.components.star-rating');
    }
}