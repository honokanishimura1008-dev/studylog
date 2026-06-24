<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaterialRequest;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $materials = $user->materials()
            ->withSum('mussleLogs as minutes_sum', 'minutes')
            ->latest()
            ->get();

        return view('materials.index', compact('materials'));
    }

    public function create(): View
    {
        $types = Material::TYPES;
        $coverImages = Material::COVER_IMAGES;

        return view('materials.create', compact('types', 'coverImages'));
    }

    public function store(StoreMaterialRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->materials()->create($validated);

        return redirect()
            ->route('materials.index')
            ->with('status', 'メニューを追加しました');
    }

    public function edit(Material $material): View
    {
        $user = auth()->user();

        abort_if($material->user_id !== $user->id, 403);

        $types = Material::TYPES;
        $coverImages = Material::COVER_IMAGES;

        return view('materials.edit', compact('material', 'types', 'coverImages'));
    }

    public function update(StoreMaterialRequest $request, Material $material): RedirectResponse
    {
        $user = $request->user();

        abort_if($material->user_id !== $user->id, 403);

        $validated = $request->validated();

        // 種別を変更したのに画像を選び直さなかった場合、旧種別の画像が残らないようにする
        if (! array_key_exists('cover_path', $validated) && $validated['type'] !== $material->type) {
            $validated['cover_path'] = null;
        }

        $material->update($validated);

        return redirect()
            ->route('materials.index')
            ->with('status', 'メニューを更新しました');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $user = auth()->user();

        abort_if($material->user_id !== $user->id, 403);

        $material->delete();

        return back()->with('status', 'メニューを削除しました');
    }
}
