<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_medicines'  => Medicine::count(),
            'total_categories' => Category::count(),
            'total_suppliers'  => Supplier::count(),
            'low_stock'        => Medicine::lowStock()->count(),
            'out_of_stock'     => Medicine::where('stock', 0)->count(),
            'rx_required'      => Medicine::rxOnly()->count(),
        ];

        // Top 5 categories by medicine count (uses one-to-many)
        $topCategories = Category::withCount('medicines')
                                 ->orderByDesc('medicines_count')
                                 ->limit(5)
                                 ->get();

        // Low-stock alerts (stock < 10, eager-loaded with category)
        $lowStockItems = Medicine::with('category')
                                 ->lowStock()
                                 ->orderBy('stock')
                                 ->limit(8)
                                 ->get();

        // Recent medicines
        $recentMedicines = Medicine::with('category')
                                   ->latest()
                                   ->limit(6)
                                   ->get();

        return view('dashboard.index', compact(
            'stats', 'topCategories', 'lowStockItems', 'recentMedicines'
        ));
    }
}
