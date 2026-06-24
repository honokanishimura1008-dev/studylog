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
  <tr v-if="isEditing" class="menu-row-item editing">
    <td colspan="5">
      <InlineEditForm
        :menu="menu"
        :dow="dow"
        :catalog="catalog"
        :materials="materials"
        @save="emit('save', $event)"
        @cancel="emit('cancelEdit')"
      />
    </td>
  </tr>
  <tr v-else class="menu-row-item">
    <td><span class="order">{{ menu.sort_order }}</span></td>
    <td>
      <div class="ex-cell">
        <img v-if="menu.cover_path" class="ex-thumb" :src="menu.cover_path" alt="">
        <span class="ex">{{ menu.material_title }}</span>
      </div>
    </td>
    <td class="num sets-rep">{{ menu.sets_rep_display }}</td>
    <td class="memo">{{ menu.memo }}</td>
    <td v-if="editMode" class="edit-only">
      <span class="row-actions">
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
    </td>
  </tr>
</template>
