<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\FeaturedContent;
use App\Models\HotTub;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FeaturedContentController extends Controller
{
    public function index()
    {
        $items = FeaturedContent::with(['hotTub', 'brand'])->orderBy('created_at','desc')->paginate(7);
        $brands = Brand::orderBy('name')->get();
        $products = HotTub::orderBy('brand')->orderBy('model')->get();
        return view('admin.featured', compact('items','brands','products'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if (empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['title'] ?: ($this->composeTitle($data)));
        }
        if ($path = $this->storeImage($request, 'featured/'.$data['slug'])) {
            $data['image_url'] = $path;
        }
        FeaturedContent::create($data);
        return redirect()->route('admin.featured')->with('success', 'Featured content created.');
    }

    public function edit(FeaturedContent $featured)
    {
        $items = FeaturedContent::with(['hotTub', 'brand'])->orderBy('created_at','desc')->paginate(7);
        $brands = Brand::orderBy('name')->get();
        $products = HotTub::orderBy('brand')->orderBy('model')->get();
        return view('admin.featured-edit', ['item'=>$featured,'items'=>$items,'brands'=>$brands,'products'=>$products]);
    }

    public function update(Request $request, FeaturedContent $featured)
    {
        $data = $this->validateData($request);
        if ($request->boolean('regen_slug') || empty($featured->slug)) {
            $data['slug'] = $this->uniqueSlug($data['title'] ?: $this->composeTitle($data), $featured->id);
        }
        $slug = $data['slug'] ?? $featured->slug;
        if ($path = $this->storeImage($request, 'featured/'.$slug)) {
            $data['image_url'] = $path;
        }
        $featured->update($data);
        return redirect()->route('admin.featured')->with('success', 'Featured content updated.');
    }

    public function destroy(FeaturedContent $featured)
    {
        $featured->delete();
        return back()->with('success', 'Featured content deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'content_type' => ['required','string','max:50'],
            'brand_id' => ['nullable','exists:brands,id'],
            'hot_tub_id' => ['nullable','exists:hot_tubs,id'],
            'title' => ['nullable','string','max:255'],
            'slug' => ['nullable','string','max:255'],
            'image_url' => ['nullable','string','max:1000'],
            'image' => ['nullable','file','image','max:5120'],
            'featured_from' => ['nullable','date'],
            'featured_until' => ['nullable','date','after_or_equal:featured_from'],
            'show_on_homepage' => ['nullable','boolean'],
            'status' => ['required','in:active,inactive'],
        ]);
    }

    private function composeTitle(array $data): string
    {
        return trim(($data['content_type'] ?? '').' '.($data['title'] ?? ''));
    }

    private function uniqueSlug(string $name, ?int $ignoreId=null): string
    {
        $slug = Str::slug($name ?: Str::random(6));
        $try = $slug; $i=1;
        while (FeaturedContent::where('slug',$try)->when($ignoreId, fn($q)=>$q->where('id','!=',$ignoreId))->exists()) {
            $try = $slug.'-'.$i++;
        }
        return $try;
    }

    private function storeImage(Request $request, string $folder): ?string
    {
        if (!$request->hasFile('image')) return null;
        $file = $request->file('image');
        if (!$file) return null;
        $name = time().'_'.$file->getClientOriginalName();
        $path = $file->storeAs($folder, $name, 'public');
        return Storage::disk('public')->url($path);
    }
}
