<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $items = Part::query()
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('part_number', 'like', "%{$search}%");
                });
            })
            ->when($request->category, function ($q, $category) {
                $q->where('category', $category);
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at','desc')
            ->paginate(7)
            ->withQueryString();
        $brands = Brand::orderBy('name')->get();
        return view('admin.parts', compact('items','brands'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['compatible_brand_ids'] = $request->input('compatible_brand_ids', []);
        $data['slug'] = $data['slug'] ?: $this->uniqueSlug($data['name']);
        $images = $this->storeImages($request, 'parts/'.$data['slug']);
        if ($images) {
            $data['images'] = $images;
        }
        Part::create($data);
        return redirect()->route('admin.parts')->with('success', 'Part created.');
    }

    public function edit(Part $part)
    {
        $items = Part::orderBy('created_at','desc')->paginate(7);
        $brands = Brand::orderBy('name')->get();
        return view('admin.parts-edit', ['item'=>$part,'items'=>$items,'brands'=>$brands]);
    }

    public function update(Request $request, Part $part)
    {
        $data = $this->validateData($request);
        $data['compatible_brand_ids'] = $request->input('compatible_brand_ids', []);
        $slug = $part->slug;
        if ($request->boolean('regen_slug') || empty($part->slug) || (!empty($data['slug']) && $data['slug'] !== $part->slug)) {
            $slug = $data['slug'] = $this->uniqueSlug($data['name'], $part->id);
        }
        $existing = $part->images ?? [];
        $new = $this->storeImages($request, 'parts/'.$slug);
        if ($new) {
            $data['images'] = array_values(array_slice(array_merge($existing, $new), 0, 4));
        }
        $part->update($data);
        return redirect()->route('admin.parts')->with('success', 'Part updated.');
    }

    public function destroy(Part $part)
    {
        $part->delete();
        return back()->with('success', 'Part deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255'],
            'part_number' => ['nullable','string','max:255'],
            'category' => ['nullable','string','max:100'],
            'price' => ['nullable','numeric','min:0'],
            'description' => ['nullable','string'],
            'images.*' => ['nullable','file','image','max:5120'],
            'compatible_brand_ids' => ['nullable','array'],
            'status' => ['required','in:active,inactive'],
        ]);
    }

    private function storeImages(Request $request, string $folder): array
    {
        $stored = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $idx => $file) {
                if (!$file) continue;
                $name = time().'_'.$idx.'_'.$file->getClientOriginalName();
                $path = $file->storeAs($folder, $name, 'public');
                $stored[] = Storage::disk('public')->url($path);
            }
        }
        return array_slice($stored, 0, 4);
    }

    private function uniqueSlug(string $name, ?int $ignoreId=null): string
    {
        $slug = Str::slug($name);
        $try = $slug; $i=1;
        while (Part::where('slug',$try)->when($ignoreId, fn($q)=>$q->where('id','!=',$ignoreId))->exists()) {
            $try = $slug.'-'.$i++;
        }
        return $try;
    }
}

