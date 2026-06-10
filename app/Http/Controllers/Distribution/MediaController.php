<?php

namespace App\Http\Controllers\Distribution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Distribution\MediaAssetRequest;
use App\Models\MediaAsset;
use Illuminate\Http\RedirectResponse;
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

    public function store(MediaAssetRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store('media/'.auth()->id(), 'public');

        MediaAsset::create([
            'user_id' => auth()->id(),
            'title' => $request->validated('title'),
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => $request->validated('status'),
            'notes' => $request->validated('notes'),
        ]);

        return redirect()->route('distribution.media.index')->with('status', 'Media uploaded.');
    }

    public function edit(MediaAsset $medium): View
    {
        $this->authorize('view', $medium);

        return view('distribution.media.edit', [
            'asset' => $medium,
            'statuses' => MediaAsset::statuses(),
        ]);
    }

    public function update(MediaAssetRequest $request, MediaAsset $medium): RedirectResponse
    {
        $this->authorize('update', $medium);

        $medium->update($request->validated());

        return redirect()->route('distribution.media.index')->with('status', 'Media updated.');
    }

    public function destroy(MediaAsset $medium): RedirectResponse
    {
        $this->authorize('delete', $medium);
        Storage::disk('public')->delete($medium->path);
        $medium->delete();

        return redirect()->route('distribution.media.index')->with('status', 'Media deleted.');
    }
}
