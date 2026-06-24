<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWeeklyMenuRequest;
use App\Models\Material;
use App\Models\WeeklyMenu;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $user     = $request->user();
        $todayDow = strtolower(Carbon::today()->format('D'));

        $menusByDow = $user->weeklyMenus()
            ->with('material')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('dow');

        $materials = $user->materials()->orderBy('title')->get();

        return view('menu', [
            'todayDow'      => $todayDow,
            'menuPageProps' => $this->buildMenuPageProps($todayDow, $menusByDow, $materials),
        ]);
    }

    /**
     * フロントエンドに渡す週間メニューページの props を組み立てる
     *
     * @param string $todayDow
     * @param \Illuminate\Support\Collection $menusByDow
     * @param \Illuminate\Support\Collection $materials
     * @return array<string, mixed>
     */
    private function buildMenuPageProps(string $todayDow, $menusByDow, $materials): array
    {
        $initialMenus = array_fill_keys(WeeklyMenu::DOWS, []);
        foreach ($menusByDow as $dow => $menus) {
            $initialMenus[$dow] = $menus->map->toMenuArray()->values()->all();
        }

        $catalog = collect(Material::COVER_IMAGES)
            ->flatMap(fn (array $names, string $type) => collect($names)->map(fn (string $name) => [
                'name'       => $name,
                'type'       => $type,
                'cover_path' => asset('images/materials/'.$name.'.png'),
            ]))
            ->sortBy('name')
            ->values();

        return [
            'todayDow'      => $todayDow,
            'dayMeta'       => WeeklyMenu::DAY_META,
            'initialMenus'  => $initialMenus,
            'storeUrl'      => route('menu.store'),
            'updateBaseUrl' => route('menu.update', ['weeklyMenu' => '__id__']),
            'catalog'       => $catalog->all(),
            'materials'     => $materials->map(fn ($m) => [
                'id'         => $m->id,
                'title'      => $m->title,
                'type'       => $m->type,
                'cover_path' => $m->cover_path ? asset($m->cover_path) : null,
            ])->values()->all(),
            'csrfToken' => csrf_token(),
        ];
    }

    /**
     * 週間メニューに種目を追加する
     *
     * @param StoreWeeklyMenuRequest $request
     * @return JsonResponse
     */
    public function store(StoreWeeklyMenuRequest $request): JsonResponse
    {
        $menu = $request->user()->weeklyMenus()->make();
        $this->saveMenu($request, $menu);

        return response()->json([
            'message' => '種目を追加しました',
            'menu'    => $menu->toMenuArray(),
        ], 201);
    }

    /**
     * 週間メニューの種目を更新する
     *
     * @param StoreWeeklyMenuRequest $request
     * @param WeeklyMenu $weeklyMenu
     * @return JsonResponse
     */
    public function update(StoreWeeklyMenuRequest $request, WeeklyMenu $weeklyMenu): JsonResponse
    {
        abort_if($weeklyMenu->user_id !== $request->user()->id, 403);

        $this->saveMenu($request, $weeklyMenu);

        return response()->json([
            'message' => '更新しました',
            'menu'    => $weeklyMenu->toMenuArray(),
        ]);
    }

    /**
     * 週間メニューの種目を削除する
     *
     * @param WeeklyMenu $weeklyMenu
     * @return JsonResponse
     */
    public function destroy(WeeklyMenu $weeklyMenu): JsonResponse
    {
        abort_if($weeklyMenu->user_id !== auth()->id(), 403);

        $weeklyMenu->delete();

        return response()->json(['message' => '削除しました']);
    }

    /**
     * store / update 共通の保存処理
     *
     * @param StoreWeeklyMenuRequest $request
     * @param WeeklyMenu $menu
     * @return void
     */
    private function saveMenu(StoreWeeklyMenuRequest $request, WeeklyMenu $menu): void
    {
        $validated = $request->validated();
        $reps      = WeeklyMenu::parseReps($validated['reps']);

        $menu->fill([
            'dow'         => $validated['dow'],
            'sort_order'  => $validated['sort_order'],
            'material_id' => $this->resolveMaterialId($request, $validated),
            'sets'        => $validated['sets'],
            'rep_min'     => $reps['min'],
            'rep_max'     => $reps['max'],
            'memo'        => $validated['memo'] ?? null,
        ])->save();

        $menu->load('material');
    }

    /**
     * material_id またはマスタカタログ名から material_id を解決する。
     * カタログ選択時はユーザーの materials に存在しなければ自動作成する。
     *
     * @param Request $request
     * @param array<string, mixed> $validated
     * @return int
     */
    private function resolveMaterialId(Request $request, array $validated): int
    {
        if (! empty($validated['material_id'])) {
            return (int) $validated['material_id'];
        }

        $name = $validated['catalog_name'];
        $type = $validated['catalog_type'] ?? 'legs';

        $material = $request->user()->materials()->firstOrCreate(
            ['title' => $name],
            [
                'type'               => $type,
                'cover_path'         => 'images/materials/'.$name.'.png',
                'estimated_minutes'  => 60,
            ]
        );

        return $material->id;
    }
}
