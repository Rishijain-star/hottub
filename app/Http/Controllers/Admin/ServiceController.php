<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $items = Service::query()
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at','desc')
            ->paginate(7)
            ->withQueryString();
        return view('admin.services', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['includes'] = $request->input('includes', []);
        $data['slug'] = $data['slug'] ?: $this->uniqueSlug($data['name']);
        if ($path = $this->storeImage($request, 'services/'.$data['slug'])) {
            $data['image_url'] = $path;
        }
        Service::create($data);
        return redirect()->route('admin.services.index')->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        $items = Service::orderBy('created_at','desc')->paginate(7);
        return view('admin.services-edit', ['item' => $service, 'items' => $items]);
    }

    public function update(Request $request, Service $service)
    {
        $data = $this->validateData($request);
        $data['includes'] = $request->input('includes', []);
        
        // Handle slug logic: only change if requested or if regen_slug is checked
        if ($request->boolean('regen_slug')) {
            $data['slug'] = $this->uniqueSlug($data['name'], $service->id);
        } elseif (empty($data['slug'])) {
            // If user left slug field blank and didn't check regen_slug, 
            // remove 'slug' from $data so Eloquent doesn't try to set it to NULL
            unset($data['slug']);
        }

        // Use current slug for image folder unless we just generated a new one
        $slugForFolder = $data['slug'] ?? $service->slug;
        
        if ($path = $this->storeImage($request, 'services/'.$slugForFolder)) {
            $data['image_url'] = $path;
        }

        $service->update($data);
        return redirect()->route('admin.services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return back()->with('success', 'Service deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255'],
            'price' => ['nullable','numeric','min:0'],
            'image_url' => ['nullable','string','max:1000'],
            'description' => ['nullable','string'],
            'includes' => ['nullable','array'],
            'status' => ['required','in:active,inactive'],
            'image' => ['nullable','file','image','max:51200'],
        ]);
    }

    private function storeImage(Request $request, string $folder): ?string
    {
        if (!$request->hasFile('image')) return null;
        $file = $request->file('image');
        if (!$file) return null;
        $name = time().'_'.$file->getClientOriginalName();
        $path = $file->storeAs($folder, $name, 'public');
        // Store only relative path in DB (e.g. services/slug/file.jpg)
        return $path;
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $try = $slug;
        $i = 1;
        while (Service::where('slug', $try)->when($ignoreId, fn($q)=>$q->where('id','!=',$ignoreId))->exists()) {
            $try = $slug.'-'.$i++;
        }
        return $try;
    }
}
