<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\OutdoorProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OutdoorProductController extends Controller
{
    public function index()
    {
        $items = OutdoorProduct::orderBy('created_at', 'desc')->paginate(7);
        $brands = Schema::hasTable('brands') ? Brand::orderBy('name')->get() : collect();
        return view('admin.outdoor-products', compact('items', 'brands'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if (!empty($data['brand_id'])) {
            $brand = Brand::find($data['brand_id']);
            if ($brand) {
                $data['brand'] = $brand->name;
            }
        }
        $data['pros'] = $request->input('pros', []);
        $data['cons'] = $request->input('cons', []);
        $data['overall'] = $this->calcOverall($data);
        $base = ($data['brand'] ?? 'outdoor') . '-' . $data['model'];
        $data['slug'] = $this->makeUniqueSlug($base);

        $item = OutdoorProduct::create($data);
        if ($request->wantsJson() || $request->ajax()) {
            $item->refresh();
            return response()->json(['ok' => true, 'item' => $item]);
        }
        return redirect()->route('admin.outdoor-products.index')->with('success', 'Outdoor product created.');
    }

    public function edit(OutdoorProduct $outdoor_product)
    {
        $items = OutdoorProduct::orderBy('created_at', 'desc')->paginate(6);
        $brands = Schema::hasTable('brands') ? Brand::orderBy('name')->get() : collect();
        return view('admin.outdoor-products-edit', ['item' => $outdoor_product, 'items' => $items, 'brands' => $brands]);
    }

    public function update(Request $request, OutdoorProduct $outdoor_product)
    {
        $data = $this->validateData($request);
        if (!empty($data['brand_id'])) {
            $brand = Brand::find($data['brand_id']);
            if ($brand) {
                $data['brand'] = $brand->name;
            }
        }
        $data['pros'] = $request->input('pros', []);
        $data['cons'] = $request->input('cons', []);
        $data['overall'] = $this->calcOverall($data);
        if ($request->boolean('regen_slug') || empty($outdoor_product->slug)) {
            $data['slug'] = $this->makeUniqueSlug(($data['brand'] ?? 'outdoor') . '-' . $data['model'], $outdoor_product->id);
        }

        $outdoor_product->update($data);
        return redirect()->route('admin.outdoor-products.index')->with('success', 'Outdoor product updated.');
    }

    public function destroy(OutdoorProduct $outdoor_product)
    {
        $outdoor_product->delete();
        return back()->with('success', 'Outdoor product deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'brand_id' => 'nullable|integer',
            'brand' => 'nullable|string|max:255',
            'model' => 'required|string|max:255',
            'product_type' => 'required|string|max:255',
            'tier' => 'nullable|string|max:255',
            'dimensions' => 'nullable|string|max:255',
            'quality' => 'nullable|numeric|min:0|max:5',
            'durability' => 'nullable|numeric|min:0|max:5',
            'features' => 'nullable|numeric|min:0|max:5',
            'value' => 'nullable|numeric|min:0|max:5',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);
    }

    private function calcOverall(array $data): float
    {
        $scores = [
            (float)($data['quality'] ?? 0),
            (float)($data['durability'] ?? 0),
            (float)($data['features'] ?? 0),
            (float)($data['value'] ?? 0),
        ];
        $filtered = array_filter($scores, fn($v) => $v > 0);
        if (!count($filtered)) return 0.0;
        return round(array_sum($filtered) / count($filtered), 1);
    }

    private function makeUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $try = $slug;
        $i = 1;
        while (OutdoorProduct::where('slug', $try)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $try = $slug . '-' . $i++;
        }
        return $try;
    }

    public function uploadImages(Request $request, OutdoorProduct $outdoor_product)
    {
        $request->validate([
            'image' => 'required|file|image|max:51200',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $folder = 'outdoor-products/' . $outdoor_product->slug;
            $name = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs($folder, $name, 'public');
            $url = $path; // Store relative path

            $imgs = $outdoor_product->images ?? [];
            $imgs[] = $url;
            $outdoor_product->images = $imgs;
            $outdoor_product->save();

            return response()->json(['ok' => true, 'url' => asset('storage/' . $url)]);
        }
        return response()->json(['ok' => false], 400);
    }
}
