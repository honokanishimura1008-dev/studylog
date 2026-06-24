<script setup lang="ts">
import { computed } from 'vue';
import type { CatalogItem, DayMeta, Dow, MaterialItem, MenuFormPayload, MenuItem } from '../types';
import MenuRowList from './MenuRowList.vue';

const props = defineProps<{
  dayMeta: Record<Dow, DayMeta>;
  menusByDow: Record<Dow, MenuItem[]>;
  editMode: boolean;
  editingId: number | null;
  catalog: CatalogItem[];
  materials: MaterialItem[];
}>();

const emit = defineEmits<{
  edit: [id: number];
  delete: [id: number];
  save: [payload: MenuFormPayload];
  cancelEdit: [];
  add: [dow: Dow];
}>();

const trainingDays = computed(() =>
  (Object.keys(props.dayMeta) as Dow[]).filter((dow) => !props.dayMeta[dow].off),
);

function mainChip(meta: DayMeta): [string, string] | null {
  return meta.chips.find(([cls]) => cls !== 'off') ?? null;
}
</script>

<template>
  <div class="row g-3">
    <div v-for="dow in trainingDays" :key="dow" class="col-6 col-lg-3">
      <div class="week-col">
        <div class="dw">
          {{ dayMeta[dow].label }}
          <span
            v-if="mainChip(dayMeta[dow])"
            class="chip ms-1"
            :class="mainChip(dayMeta[dow])![0]"
          >
            {{ dayMeta[dow].short }}
          </span>
        </div>
        <ol class="menu-list">
          <MenuRowList
            v-for="menu in menusByDow[dow]"
            :key="menu.id"
            :menu="menu"
            :dow="dow"
            :edit-mode="editMode"
            :editing-id="editingId"
            :catalog="catalog"
            :materials="materials"
            @edit="emit('edit', $event)"
            @delete="emit('delete', $event)"
            @save="emit('save', $event)"
            @cancel-edit="emit('cancelEdit')"
          />
          <li v-if="menusByDow[dow].length === 0" class="text-secondary small">種目なし</li>
        </ol>
        <button
          v-if="editMode"
          type="button"
          class="btn btn-sm btn-ghost btn-add-exercise"
          @click="emit('add', dow)"
        >
          ＋ 種目を追加
        </button>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="week-col" style="opacity: 0.55">
        <div class="dw">水・日 <span class="chip off ms-1">オフ</span></div>
        <div class="note mt-2">完全休養 × 2日</div>
      </div>
    </div>
  </div>
</template>
