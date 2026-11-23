<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Contracts\View\View;

class InventoryWebController extends Controller
{
    public function __invoke(): View
    {
        return view('ui.home', [
            'productsCount' => Product::count(),
            'movementsCount' => StockMovement::count(),
            'stockTotal' => Stock::sum('quantity'),
            'lowStocksCount' => Stock::with('product')->get()
                ->filter(fn (Stock $s) => $s->product && $s->quantity < $s->product->minimum_stock)
                ->count(),
        ]);
    }
}
