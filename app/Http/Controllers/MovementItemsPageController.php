<?php

namespace App\Http\Controllers;

use App\Models\MovementItem;
use Illuminate\Contracts\View\View;

class MovementItemsPageController extends Controller
{
    public function __invoke(): View
    {
        return view('ui.movement-items', [
            'items' => MovementItem::with(['movement', 'product'])->latest('id')->paginate(15),
        ]);
    }
}
