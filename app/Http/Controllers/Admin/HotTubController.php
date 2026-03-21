<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\HotTub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HotTubController extends Controller
{
    public function index()
    {
        $items = HotTub::orderBy('created_at', 'desc')->paginate(6);
        $brands = Schema::hasTable('brands') ? Brand::orderBy('name')->get() : collect();
        return view('admin.hot-tubs', compact('items', 'brands'));
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
        $base = $data['brand'] . '-' . $data['model'];
        $data['slug'] = $this->makeUniqueSlug($base);

        $item = HotTub::create($data);
        if ($request->wantsJson() || $request->ajax()) {
            $item->refresh();
            return response()->json(['ok' => true, 'item' => $item]);
        }
        return redirect()->route('admin.hot-tubs.index')->with('success', 'Hot tub created.');
    }

    public function edit(HotTub $hot_tub)
    {
        $items = HotTub::orderBy('created_at', 'desc')->paginate(6);
        $brands = Schema::hasTable('brands') ? Brand::orderBy('name')->get() : collect();
        return view('admin.hot-tubs-edit', ['item' => $hot_tub, 'items' => $items, 'brands' => $brands]);
    }

    public function update(Request $request, HotTub $hot_tub)
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
        $slug = $hot_tub->slug;
        if ($request->boolean('regen_slug') || empty($hot_tub->slug)) {
            $slug = $data['slug'] = $this->makeUniqueSlug($data['brand'] . '-' . $data['model'], $hot_tub->id);
        }

        $hot_tub->update($data);
        return redirect()->route('admin.hot-tubs.index')->with('success', 'Hot tub updated.');
    }

    public function uploadImages(Request $request, HotTub $hot_tub)
    {
        $request->validate([
            'images.*' => 'nullable|file|image|max:51200',
            'image' => 'nullable|file|image|max:51200',
        ]);

        $slug = $hot_tub->slug ?: $this->makeUniqueSlug(($hot_tub->brand ?? '') . '-' . ($hot_tub->model ?? ''), $hot_tub->id);
        $folder = 'hot-tubs/' . $slug;
        $stored = [];

        // Handle images[] array (fallback for traditional form submit if ever used)
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $idx => $file) {
                if ($file && $file->isValid()) {
                    $stored[] = $this->optimizeAndStore($file, $folder, $idx);
                }
            }
        }

        // Handle single image field (used by individual image upload JS)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file && $file->isValid()) {
                $stored[] = $this->optimizeAndStore($file, $folder, time());
            }
        }

        // Merge with existing images (keep up to 10)
        $existing = $hot_tub->images ?? [];
        $hot_tub->images = array_values(array_slice(array_merge($existing, array_filter($stored)), 0, 10));

        if (!$hot_tub->slug) {
            $hot_tub->slug = $slug;
        }

        $hot_tub->save();

        return response()->json(['ok' => true, 'images' => $hot_tub->images, 'item' => $hot_tub]);
    }

    public function deleteImage(Request $request, HotTub $hot_tub, int $index)
    {
        $images = $hot_tub->images ?? [];
        if (!isset($images[$index]))
            return response()->json(['ok' => false, 'msg' => 'Not found'], 404);
        $path = $images[$index];
        unset($images[$index]);
        $hot_tub->images = array_values($images);
        $hot_tub->save();
        
        // Only delete from storage if not used elsewhere (though usually they aren't)
        Storage::disk('public')->delete($path);
        
        return response()->json(['ok' => true, 'images' => $hot_tub->images]);
    }

    public function setMainImage(Request $request, HotTub $hot_tub)
    {
        $index = $request->input('index');
        $images = $hot_tub->images ?? [];
        if (!isset($images[$index]))
            return response()->json(['ok' => false, 'msg' => 'Not found'], 404);
        
        $main = $images[$index];
        unset($images[$index]);
        array_unshift($images, $main);
        
        $hot_tub->images = array_values($images);
        $hot_tub->save();
        
        return response()->json(['ok' => true, 'images' => $hot_tub->images]);
    }

    public function destroy(HotTub $hot_tub)
    {
        $hot_tub->delete();
        return back()->with('success', 'Hot tub deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'brand_id' => 'nullable|exists:brands,id|required_without:brand',
            'brand' => 'nullable|string|max:255|required_without:brand_id',
            'product_type' => 'required|string|max:50',
            'model' => 'required|string|max:255',
            'tier' => 'nullable|string|max:50',
            'seats' => 'nullable|integer|min:0|max:50',
            'jets' => 'nullable|integer|min:0|max:200',
            'dimensions' => 'nullable|string|max:255',
            'power_requirements' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'comfort' => 'nullable|numeric|min:0|max:5',
            'efficiency' => 'nullable|numeric|min:0|max:5',
            'features' => 'nullable|numeric|min:0|max:5',
            'quality' => 'nullable|numeric|min:0|max:5',
            'value' => 'nullable|numeric|min:0|max:5',
            'description' => 'nullable|string',
            'pros' => 'nullable|array',
            'cons' => 'nullable|array',
            'images.*' => 'nullable|file|image|max:51200',
        ]);
    }

    private function storeImages(Request $request, string $folder): array
    {
        $stored = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $idx => $file) {
                if (!$file)
                    continue;
                $stored[] = $this->optimizeAndStore($file, $folder, $idx);
            }
        }
        return array_values(array_filter($stored));
    }

    private function optimizeAndStore($file, string $folder, int $idx = 0): ?string
    {
        $mime = $file->getMimeType();
        $src = null;
        if ($mime === 'image/jpeg' || $mime === 'image/jpg')
            $src = \imagecreatefromjpeg($file->getRealPath());
        elseif ($mime === 'image/png')
            $src = \imagecreatefrompng($file->getRealPath());
        elseif (\function_exists('imagecreatefromwebp') && $mime === 'image/webp')
            $src = \imagecreatefromwebp($file->getRealPath());
        else
            $src = @\imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (!$src) {
            $name = time() . '_' . $idx . '_' . $file->getClientOriginalName();
            return $file->storeAs($folder, $name, 'public');
        }
        $w = \imagesx($src);
        $h = \imagesy($src);
        $max = 1600;
        if ($w > $max) {
            $nw = $max;
            $nh = (int) round($h * ($nw / $w));
            $dst = \imagecreatetruecolor($nw, $nh);
            \imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            \imagedestroy($src);
            $src = $dst;
            $w = $nw;
            $h = $nh;
        }
        $base = time() . '_' . $idx . '_' . Str::random(6);
        if (\function_exists('imagewebp')) {
            $name = $base . '.webp';
            \ob_start();
            \imagewebp($src, null, 80);
            $data = \ob_get_clean();
            \imagedestroy($src);
            Storage::disk('public')->put($folder . '/' . $name, $data);
            return $folder . '/' . $name;
        }
        if ($mime === 'image/png') {
            $name = $base . '.png';
            \ob_start();
            \imagepng($src, null, 7);
            $data = \ob_get_clean();
            \imagedestroy($src);
            Storage::disk('public')->put($folder . '/' . $name, $data);
            return $folder . '/' . $name;
        }
        $name = $base . '.jpg';
        \ob_start();
        \imagejpeg($src, null, 85);
        $data = \ob_get_clean();
        \imagedestroy($src);
        Storage::disk('public')->put($folder . '/' . $name, $data);
        return $folder . '/' . $name;
    }

    private function linesToArray(?string $text): array
    {
        if (!$text)
            return [];
        return array_values(array_filter(array_map(fn($s) => trim($s), preg_split('/\r\n|\r|\n/', $text))));
    }

    private function calcOverall(array $data): ?float
    {
        $parts = [];
        foreach (['comfort', 'efficiency', 'features', 'quality', 'value'] as $k) {
            if (isset($data[$k]) && $data[$k] !== null && $data[$k] !== '') {
                $parts[] = (float) $data[$k];
            }
        }
        return count($parts) ? round(array_sum($parts) / count($parts), 1) : null;
    }

    private function makeUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $try = $slug;
        $i = 1;
        while (HotTub::where('slug', $try)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $try = $slug . '-' . $i++;
        }
        return $try;
    }
}
