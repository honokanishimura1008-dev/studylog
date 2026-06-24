import { createApp } from 'vue';
import App from './App.vue';
import type { MenuPageProps } from './types';

const el = document.getElementById('menu-app');

if (el) {
  const props = JSON.parse(el.dataset.props ?? '{}') as MenuPageProps;

  createApp(App, props).mount(el);
}
