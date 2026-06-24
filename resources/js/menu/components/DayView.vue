<script setup lang="ts">
import type { CatalogItem, DayMeta, Dow, MaterialItem, MenuFormPayload, MenuItem } from '../types';
import MenuRowTable from './MenuRowTable.vue';

defineProps<{
  dayMeta: Record<Dow, DayMeta>;
  menusByDow: Record<Dow, MenuItem[]>;
  selectedDow: Dow;
  todayDow: Dow;
  editMode: boolean;
  editingId: number | null;
  catalog: CatalogItem[];
  materials: MaterialItem[];
}>();

const emit = defineEmits<{
  selectDow: [dow: Dow];
  edit: [id: number];
  delete: [id: number];
  save: [payload: MenuFormPayload];
  cancelEdit: [];
  add: [dow: Dow];
}>();
</script>

<template>
  <div>
    <div class="day-tabs mb-4">
      <div
        v-for="(meta, dow) in dayMeta"
        :key="dow"
        class="day-tab"
        :class="{
          active: selectedDow === dow,
          'today-mark': todayDow === dow,
        }"
        @click="emit('selectDow', dow as Dow)"
      >
        <div class="dw">{{ meta.label }}</div>
        <div class="dn">{{ meta.short }}</div>
      </div>
    </div>

    <div class="panel p-4">
      <div
        v-for="(meta, dow) in dayMeta"
        :key="dow"
        class="day-pane"
        :class="{ hidden: selectedDow !== dow }"
      >
        <div class="day-head">
          <span class="t">{{ meta.title }}</span>
          <span v-for="([chipClass, chipLabel], i) in meta.chips" :key="i" class="chip" :class="chipClass">
            {{ chipLabel }}
          </span>
        </div>

        <div
          v-if="meta.off && menusByDow[dow as Dow].length === 0 && !editMode"
          class="off-box view-only-off"
        >
          <div class="big">🛌</div>
          {{ dow === 'wed' ? '完全休養日。回復もトレーニングの一部。' : '完全休養日。' }}
        </div>

        <div
          v-if="!meta.off || menusByDow[dow as Dow].length > 0 || editMode"
          class="table-responsive"
        >
          <table class="table-menu table">
            <thead>
              <tr>
                <th />
                <th>種目</th>
                <th>セット×レップ</th>
                <th>メモ</th>
                <th v-if="editMode" />
              </tr>
            </thead>
            <tbody>
              <MenuRowTable
                v-for="menu in menusByDow[dow as Dow]"
                :key="menu.id"
                :menu="menu"
                :dow="dow as Dow"
                :edit-mode="editMode"
                :editing-id="editingId"
                :catalog="catalog"
                :materials="materials"
                @edit="emit('edit', $event)"
                @delete="emit('delete', $event)"
                @save="emit('save', $event)"
                @cancel-edit="emit('cancelEdit')"
              />
              <tr v-if="menusByDow[dow as Dow].length === 0">
                <td colspan="5" class="text-secondary small">種目がまだありません。</td>
              </tr>
            </tbody>
          </table>
        </div>

        <button
          v-if="editMode"
          type="button"
          class="btn btn-sm btn-ghost btn-add-exercise"
          @click="emit('add', dow as Dow)"
        >
          ＋ 種目を追加
        </button>
      </div>
    </div>
  </div>
</template>
