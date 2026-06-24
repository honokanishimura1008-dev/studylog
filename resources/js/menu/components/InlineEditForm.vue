<script setup lang="ts">
import { computed, reactive } from 'vue';
import type { CatalogItem, Dow, MaterialItem, MenuFormPayload, MenuItem } from '../types';

const props = defineProps<{
  menu: MenuItem;
  dow: Dow;
  catalog: CatalogItem[];
  materials: MaterialItem[];
}>();

const emit = defineEmits<{
  save: [payload: MenuFormPayload];
  cancel: [];
}>();

const selectedTitle = computed(
  () => props.materials.find((m) => m.id === props.menu.material_id)?.title ?? props.menu.material_title,
);

const form = reactive({
  sort_order: props.menu.sort_order,
  catalog_name: selectedTitle.value,
  catalog_type:
    props.catalog.find((c) => c.name === selectedTitle.value)?.type ?? 'legs',
  sets: props.menu.sets,
  reps: props.menu.rep_display,
  memo: props.menu.memo,
});

function onExerciseChange() {
  const item = props.catalog.find((c) => c.name === form.catalog_name);
  if (item) {
    form.catalog_type = item.type;
  }
}

function submit() {
  if (!form.catalog_name) {
    alert('種目を選択してください');
    return;
  }

  emit('save', {
    dow: props.dow,
    sort_order: form.sort_order,
    catalog_name: form.catalog_name,
    catalog_type: form.catalog_type,
    sets: form.sets,
    reps: form.reps.trim(),
    memo: form.memo.trim() || null,
  });
}
</script>

<template>
  <div class="inline-edit">
    <div class="row g-2 align-items-end">
      <div class="col-2">
        <label class="form-label">順</label>
        <input
          v-model.number="form.sort_order"
          type="number"
          class="form-control form-control-sm"
          min="1"
          max="99"
        >
      </div>
      <div class="col-5">
        <label class="form-label">種目</label>
        <select
          v-model="form.catalog_name"
          class="form-select form-select-sm"
          @change="onExerciseChange"
        >
          <option value="">選択</option>
          <option v-for="item in catalog" :key="item.name" :value="item.name">
            {{ item.name }}
          </option>
        </select>
      </div>
      <div class="col-2">
        <label class="form-label">セット</label>
        <input
          v-model.number="form.sets"
          type="number"
          class="form-control form-control-sm"
          min="1"
          max="99"
        >
      </div>
      <div class="col-3">
        <label class="form-label">レップ</label>
        <input v-model="form.reps" type="text" class="form-control form-control-sm">
      </div>
      <div class="col-12">
        <label class="form-label">メモ</label>
        <input
          v-model="form.memo"
          type="text"
          class="form-control form-control-sm"
          maxlength="500"
        >
      </div>
      <div class="col-12 d-flex gap-2 mt-1">
        <button type="button" class="btn btn-sm btn-accent" @click="submit">保存</button>
        <button type="button" class="btn btn-sm btn-ghost" @click="emit('cancel')">取消</button>
      </div>
    </div>
  </div>
</template>
