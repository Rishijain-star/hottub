<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DealerAcademyContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DealerAcademyController extends Controller
{
    public function index(Request $request)
    {
        $items = DealerAcademyContent::query()
            ->when($request->search, function ($q, $search) {
                $q->where('title', 'like', "%{$search}%");
            })
            ->when($request->content_type, function ($q, $type) {
                $q->where('content_type', $type);
            })
            ->when($request->category, function ($q, $category) {
                $q->where('category', $category);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(7)
            ->withQueryString();
        return view('admin.dealer-academy.index', compact('items'));
    }

    public function create()
    {
        return view('admin.dealer-academy.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_type' => 'required|in:video,pdf,article,link',
            'category' => 'required|string|max:255',
            'file' => 'nullable|file|max:51200', // 50MB max
            'external_link' => 'nullable|url',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('academy', 'public');
            $data['file_path'] = $path;
        }

        DealerAcademyContent::create($data);

        return redirect()->route('admin.dealer-academy.index')->with('success', 'Academy content created.');
    }

    public function edit(DealerAcademyContent $dealer_academy)
    {
        return view('admin.dealer-academy.edit', ['item' => $dealer_academy]);
    }

    public function update(Request $request, DealerAcademyContent $dealer_academy)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_type' => 'required|in:video,pdf,article,link',
            'category' => 'required|string|max:255',
            'file' => 'nullable|file|max:51200',
            'external_link' => 'nullable|url',
        ]);

        if ($request->hasFile('file')) {
            if ($dealer_academy->file_path) {
                Storage::disk('public')->delete($dealer_academy->file_path);
            }
            $path = $request->file('file')->store('academy', 'public');
            $data['file_path'] = $path;
        }

        $dealer_academy->update($data);

        return redirect()->route('admin.dealer-academy.index')->with('success', 'Academy content updated.');
    }

    public function destroy(DealerAcademyContent $dealer_academy)
    {
        if ($dealer_academy->file_path) {
            Storage::disk('public')->delete($dealer_academy->file_path);
        }
        $dealer_academy->delete();

        return redirect()->route('admin.dealer-academy.index')->with('success', 'Academy content deleted.');
    }
}
