<script setup lang="ts">
import { computed, ref } from 'vue';
import type { Dow, MenuFormPayload, MenuItem, MenuPageProps } from './types';
import { useMenuApi } from './composables/useMenuApi';
import AddMenuModal from './components/AddMenuModal.vue';
import DayView from './components/DayView.vue';
import WeekView from './components/WeekView.vue';

const props = defineProps<MenuPageProps>();

const api = useMenuApi(props.csrfToken);

const editMode = ref(false);
const viewMode = ref<'day' | 'week'>('day');
const selectedDow = ref<Dow>(props.todayDow);
const editingId = ref<number | null>(null);
const addModalDow = ref<Dow | null>(null);
const showAddModal = ref(false);
const addModalRef = ref<InstanceType<typeof AddMenuModal> | null>(null);

const menusByDow = ref<Record<Dow, MenuItem[]>>(structuredClone(props.initialMenus));

function toggleEditMode() {
  editMode.value = !editMode.value;
  if (!editMode.value) {
    editingId.value = null;
  }
}

function menusFor(dow: Dow): MenuItem[] {
  return menusByDow.value[dow] ?? [];
}

function nextSortOrder(dow: Dow): number {
  const items = menusFor(dow);
  if (items.length === 0) return 1;
  return Math.max(...items.map((m) => m.sort_order)) + 1;
}

function upsertMenu(menu: MenuItem) {
  const list = [...menusFor(menu.dow)];
  const index = list.findIndex((m) => m.id === menu.id);

  if (index >= 0) {
    list[index] = menu;
  } else {
    list.push(menu);
  }

  list.sort((a, b) => a.sort_order - b.sort_order);
  menusByDow.value[menu.dow] = list;
}

function removeMenu(id: number) {
  for (const dow of Object.keys(menusByDow.value) as Dow[]) {
    menusByDow.value[dow] = menusFor(dow).filter((m) => m.id !== id);
  }
}

function startEdit(id: number) {
  editingId.value = id;
}

function cancelEdit() {
  editingId.value = null;
}

function openAddModal(dow: Dow) {
  addModalDow.value = dow;
  showAddModal.value = true;
}

function closeAddModal() {
  showAddModal.value = false;
  addModalDow.value = null;
}

async function handleSave(payload: MenuFormPayload, menuId?: number) {
  try {
    const url = menuId
      ? `${props.updateBaseUrl}/${menuId}`
      : props.storeUrl;

    const data = menuId
      ? await api.update(url, payload)
      : await api.store(url, payload);

    upsertMenu(data.menu);
    editingId.value = null;
    closeAddModal();
  } catch (err) {
    const message = err instanceof Error ? err.message : 'エラーが発生しました';
    if (menuId) {
      alert(message);
    } else {
      addModalRef.value?.setError(message);
    }
  }
}

async function handleDelete(id: number) {
  if (!confirm('この種目を削除しますか？')) return;

  try {
    await api.destroy(`${props.updateBaseUrl}/${id}`);
    removeMenu(id);
    if (editingId.value === id) {
      editingId.value = null;
    }
  } catch (err) {
    alert(err instanceof Error ? err.message : 'エラーが発生しました');
  }
}

const addModalSortOrder = computed(() =>
  addModalDow.value ? nextSortOrder(addModalDow.value) : 1,
);
</script>

<template>
  <main class="container py-4 menu-app" :class="{ 'edit-mode': editMode }">
    <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
      <h1 class="page-title fs-3 mb-0">週間メニュー</h1>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <button
          type="button"
          class="btn btn-sm btn-ghost btn-edit-mode"
          :class="{ active: editMode }"
          @click="toggleEditMode"
        >
          {{ editMode ? '編集モード ON' : '編集モード' }}
        </button>
        <div class="view-seg">
          <button
            type="button"
            :class="{ active: viewMode === 'day' }"
            @click="viewMode = 'day'"
          >
            日別表示
          </button>
          <button
            type="button"
            :class="{ active: viewMode === 'week' }"
            @click="viewMode = 'week'"
          >
            週間一覧
          </button>
        </div>
      </div>
    </div>
    <p class="note mb-4">毎週固定のトレーニングメニュー。臀部 週3 / 肩・腕 週2 を軸に構成。</p>

    <div v-show="viewMode === 'day'">
      <DayView
        :day-meta="dayMeta"
        :menus-by-dow="menusByDow"
        :selected-dow="selectedDow"
        :today-dow="todayDow"
        :edit-mode="editMode"
        :editing-id="editingId"
        :catalog="catalog"
        :materials="materials"
        @select-dow="selectedDow = $event"
        @edit="startEdit"
        @delete="handleDelete"
        @save="handleSave($event, editingId ?? undefined)"
        @cancel-edit="cancelEdit"
        @add="openAddModal"
      />
    </div>

    <div v-show="viewMode === 'week'">
      <WeekView
        :day-meta="dayMeta"
        :menus-by-dow="menusByDow"
        :edit-mode="editMode"
        :editing-id="editingId"
        :catalog="catalog"
        :materials="materials"
        @edit="startEdit"
        @delete="handleDelete"
        @save="handleSave($event, editingId ?? undefined)"
        @cancel-edit="cancelEdit"
        @add="openAddModal"
      />
    </div>

    <div class="panel p-4 mt-4">
      <div class="day-head mb-2">
        <span class="t" style="font-size: 1rem">プログラムの要点</span>
      </div>
      <ul class="point-list">
        <li><span class="pn">a</span><span>臀部直接刺激は週3（月・木・土）。ヒップスラストは週2で重量日とパンプ日に分ける</span></li>
        <li><span class="pn">b</span><span>肩・上腕は週2ずつ。サイドレイズは三角筋中部狙いで週8セット確保</span></li>
        <li><span class="pn">c</span><span>各種目の最終セットはRIR 1-2。ヒップスラスト重量日のみ全力近くまで</span></li>
        <li><span class="pn">d</span><span>4-6週ごとに重量かレップを更新。停滞したらデロード1週</span></li>
      </ul>
    </div>

    <AddMenuModal
      ref="addModalRef"
      :show="showAddModal"
      :dow="addModalDow"
      :catalog="catalog"
      :next-sort-order="addModalSortOrder"
      @close="closeAddModal"
      @submit="handleSave"
    />
  </main>
</template>
