<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\FeaturedContent;
use App\Models\HotTub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FeaturedContentController extends Controller
{
    public function index(Request $request)
    {
        $items = FeaturedContent::query()
            ->with(['hotTub', 'brand'])
            ->when($request->search, function ($q, $search) {
                $q->where('title', 'like', "%{$search}%");
            })
            ->when($request->content_type, function ($q, $type) {
                $q->where('content_type', $type);
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(7)
            ->withQueryString();
        $brands = Brand::orderBy('name')->get();
        $products = HotTub::orderBy('brand')->orderBy('model')->get();
        return view('admin.featured', compact('items', 'brands', 'products'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if (empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['title'] ?: ($this->composeTitle($data)));
        }
        if ($request->hasFile('image')) {
            $data['image_url'] = $request->file('image')->store('featured', 'public');
        }
        FeaturedContent::create($data);
        return redirect()->route('admin.featured')->with('success', 'Featured content created.');
    }

    public function edit(FeaturedContent $featured)
    {
        $items = FeaturedContent::with(['hotTub', 'brand'])->orderBy('created_at', 'desc')->paginate(7);
        $brands = Brand::orderBy('name')->get();
        $products = HotTub::orderBy('brand')->orderBy('model')->get();
        return view('admin.featured-edit', ['item' => $featured, 'items' => $items, 'brands' => $brands, 'products' => $products]);
    }

    public function update(Request $request, FeaturedContent $featured)
    {
        $data = $this->validateData($request);
        if ($request->boolean('regen_slug') || empty($featured->slug)) {
            $data['slug'] = $this->uniqueSlug($data['title'] ?: $this->composeTitle($data), $featured->id);
        }
        if ($request->hasFile('image')) {
            if ($featured->image_url && ! str_starts_with($featured->image_url, 'http')) {
                Storage::disk('public')->delete($featured->image_url);
            }
            $data['image_url'] = $request->file('image')->store('featured', 'public');
        }
        $featured->update($data);
        return redirect()->route('admin.featured')->with('success', 'Featured content updated.');
    }

    public function destroy(FeaturedContent $featured)
    {
        if ($featured->image_url && ! str_starts_with($featured->image_url, 'http')) {
            Storage::disk('public')->delete($featured->image_url);
        }
        $featured->delete();
        return back()->with('success', 'Featured content deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'content_type' => ['required', 'string', 'max:50'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'hot_tub_id' => ['nullable', 'exists:hot_tubs,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'file', 'image', 'max:5120'],
            'featured_from' => ['nullable', 'date'],
            'featured_until' => ['nullable', 'date', 'after_or_equal:featured_from'],
            'show_on_homepage' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    private function composeTitle(array $data): string
    {
        return trim(($data['content_type'] ?? '') . ' ' . ($data['title'] ?? ''));
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name ?: Str::random(6));
        $try = $slug;
        $i = 1;
        while (FeaturedContent::where('slug', $try)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $try = $slug . '-' . $i++;
        }
        return $try;
    }
}
