<script setup lang="ts">
import { reactive, watch } from 'vue';
import type { CatalogItem, Dow, MenuFormPayload } from '../types';

const props = defineProps<{
  show: boolean;
  dow: Dow | null;
  catalog: CatalogItem[];
  nextSortOrder: number;
}>();

const emit = defineEmits<{
  close: [];
  submit: [payload: MenuFormPayload];
}>();

const form = reactive({
  catalog_name: '',
  catalog_type: 'legs',
  sort_order: 1,
  sets: 3,
  reps: '',
  memo: '',
});

const error = reactive({ message: '' });

watch(
  () => props.show,
  (visible) => {
    if (visible && props.dow) {
      form.catalog_name = '';
      form.catalog_type = 'legs';
      form.sort_order = props.nextSortOrder;
      form.sets = 3;
      form.reps = '';
      form.memo = '';
      error.message = '';
    }
  },
);

function onExerciseChange() {
  const item = props.catalog.find((c) => c.name === form.catalog_name);
  if (item) {
    form.catalog_type = item.type;
  }
}

function submit() {
  if (!props.dow) return;

  if (!form.catalog_name) {
    error.message = '種目を選択してください';
    return;
  }

  emit('submit', {
    dow: props.dow,
    sort_order: form.sort_order,
    catalog_name: form.catalog_name,
    catalog_type: form.catalog_type,
    sets: form.sets,
    reps: form.reps.trim(),
    memo: form.memo.trim() || null,
  });
}

function setError(message: string) {
  error.message = message;
}

defineExpose({ setError });
</script>

<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.45);">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">種目を追加</h5>
          <button type="button" class="btn-close" @click="emit('close')" />
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small">種目（マスタ）</label>
            <select
              v-model="form.catalog_name"
              class="form-select form-select-sm"
              @change="onExerciseChange"
            >
              <option value="">選択してください</option>
              <option v-for="item in catalog" :key="item.name" :value="item.name">
                {{ item.name }}
              </option>
            </select>
          </div>
          <div class="row g-2">
            <div class="col-3">
              <label class="form-label small">順</label>
              <input
                v-model.number="form.sort_order"
                type="number"
                class="form-control form-control-sm"
                min="1"
                max="99"
              >
            </div>
            <div class="col-3">
              <label class="form-label small">セット</label>
              <input
                v-model.number="form.sets"
                type="number"
                class="form-control form-control-sm"
                min="1"
                max="99"
              >
            </div>
            <div class="col-6">
              <label class="form-label small">レップ</label>
              <input
                v-model="form.reps"
                type="text"
                class="form-control form-control-sm"
                placeholder="6-8"
              >
            </div>
            <div class="col-12">
              <label class="form-label small">メモ</label>
              <input
                v-model="form.memo"
                type="text"
                class="form-control form-control-sm"
                maxlength="500"
              >
            </div>
          </div>
          <div v-if="error.message" class="alert alert-danger py-2 small mt-2">
            {{ error.message }}
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" @click="emit('close')">取消</button>
          <button type="button" class="btn btn-accent" @click="submit">追加</button>
        </div>
      </div>
    </div>
  </div>
</template>
