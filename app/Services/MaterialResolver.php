<?php

namespace App\Services;

use App\Models\User;

class MaterialResolver
{
    /**
     * material_id またはマスタカタログ名から material_id を解決する。
     * カタログ選択時はユーザーの materials に存在しなければ自動作成する。
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @return int
     */
    public function resolve(User $user, array $data): int
    {
        if (! empty($data['material_id'])) {
            return (int) $data['material_id'];
        }

        $name = $data['catalog_name'];
        $type = $data['catalog_type'] ?? 'legs';

        $material = $user->materials()->firstOrCreate(
            ['title' => $name],
            [
                'type'              => $type,
                'cover_path'        => 'images/materials/'.$name.'.png',
                'estimated_minutes' => 60,
            ]
        );

        return $material->id;
    }
}
