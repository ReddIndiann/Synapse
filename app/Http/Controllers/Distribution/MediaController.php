<?php

namespace App\Http\Controllers\Distribution;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View
    {
        $assets = MediaAsset::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(12);

        return view('distribution.media.index', compact('assets'));
    }

    public function create(): View
    {
        return view('distribution.media.create', [
            'statuses' => MediaAsset::statuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:10240'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:'.implode(',', MediaAsset::statuses())],
        ]);

        $file = $request->file('file');
        $path = $file->store('media/'.auth()->id(), 'public');

        MediaAsset::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('distribution.media.index')->with('status', 'Media uploaded.');
    }

    public function edit(MediaAsset $medium): View
    {
        $this->authorizeAsset($medium);

        return view('distribution.media.edit', [
            'asset' => $medium,
            'statuses' => MediaAsset::statuses(),
        ]);
    }

    public function update(Request $request, MediaAsset $medium): RedirectResponse
    {
        $this->authorizeAsset($medium);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:'.implode(',', MediaAsset::statuses())],
        ]);

        $medium->update($validated);

        return redirect()->route('distribution.media.index')->with('status', 'Media updated.');
    }

    public function destroy(MediaAsset $medium): RedirectResponse
    {
        $this->authorizeAsset($medium);
        Storage::disk('public')->delete($medium->path);
        $medium->delete();

        return redirect()->route('distribution.media.index')->with('status', 'Media deleted.');
    }

    private function authorizeAsset(MediaAsset $asset): void
    {
        abort_unless($asset->user_id === auth()->id(), 403);
    }
}
