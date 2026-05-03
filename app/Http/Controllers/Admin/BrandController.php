<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brands = Brand::query()
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->when($request->type, function ($q, $type) {
                $q->where('type', $type);
            })
            ->orderBy('name')
            ->paginate(7)
            ->withQueryString();

        return view('admin.brands', compact('brands'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:brands,name'],
            'type' => ['nullable', 'string', 'max:50'],
            'types' => ['nullable', 'array'],
            'types.*' => ['in:hot_tub,swim_spa,both,outdoor_kitchen,sauna,other'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string'],
            'featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'country_of_origin' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('brands/logos', 'public');
        }

        $data['featured'] = (bool) ($data['featured'] ?? false);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['types'] = array_values($data['types'] ?? []);
        $data['slug'] = $this->uniqueSlug($data['name']);
        Brand::create($data);
        return redirect()->route('admin.brands.index')->with('success', 'Brand created.');
    }

    public function edit(Brand $brand)
    {
        $brands = Brand::orderBy('name')->paginate(7);
        return view('admin.brands-edit', ['item' => $brand, 'brands' => $brands]);
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:brands,name,' . $brand->id],
            'type' => ['nullable', 'string', 'max:50'],
            'types' => ['nullable', 'array'],
            'types.*' => ['in:hot_tub,swim_spa,both,outdoor_kitchen,sauna,other'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string'],
            'featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'country_of_origin' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            if ($brand->logo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($brand->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('brands/logos', 'public');
        }

        $data['featured'] = (bool) ($data['featured'] ?? false);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['types'] = array_values($data['types'] ?? []);
        if ($request->boolean('regen_slug') || empty($brand->slug)) {
            $data['slug'] = $this->uniqueSlug($data['name'], $brand->id);
        }
        $brand->update($data);
        return redirect()->route('admin.brands.index')->with('success', 'Brand updated.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        return back()->with('success', 'Brand deleted.');
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $try = $slug;
        $i = 1;
        while (Brand::where('slug', $try)->exists()) {
            $try = $slug . '-' . $i++;
        }
        return $try;
    }
}
