<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use App\Models\DealerAcademyContent;
use Illuminate\Http\Request;

class DealerAcademyController extends Controller
{
    public function index(Request $request)
    {
        $query = DealerAcademyContent::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(12);
        $categories = DealerAcademyContent::select('category')->distinct()->pluck('category');

        return view('dealer.academy.index', compact('items', 'categories'));
    }

    public function show(DealerAcademyContent $dealer_academy)
    {
        return view('dealer.academy.show', ['item' => $dealer_academy]);
    }
}
