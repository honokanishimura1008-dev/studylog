<script setup lang="ts">
import { computed } from 'vue';
import type { CatalogItem, Dow, MaterialItem, MenuFormPayload, MenuItem } from '../types';
import InlineEditForm from './InlineEditForm.vue';

const props = defineProps<{
  menu: MenuItem;
  dow: Dow;
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
}>();

const isEditing = computed(() => props.editingId === props.menu.id);
</script>

<template>
  <li v-if="isEditing" class="menu-row-item editing">
    <InlineEditForm
      :menu="menu"
      :dow="dow"
      :catalog="catalog"
      :materials="materials"
      @save="emit('save', $event)"
      @cancel="emit('cancelEdit')"
    />
  </li>
  <li v-else class="menu-row-item">
    <div class="row-main">
      <span class="row-order">{{ menu.sort_order }}</span>
      <span class="row-name">{{ menu.material_title }}</span>
      <span class="row-sets">{{ menu.sets }}×{{ menu.rep_display }}</span>
    </div>
    <div v-if="menu.memo" class="row-memo">{{ menu.memo }}</div>
    <span v-if="editMode" class="row-actions">
      <button type="button" class="btn btn-sm btn-ghost py-0 px-2" @click="emit('edit', menu.id)">
        編集
      </button>
      <button
        type="button"
        class="btn btn-sm btn-ghost py-0 px-2 text-danger"
        @click="emit('delete', menu.id)"
      >
        削除
      </button>
    </span>
  </li>
</template>
