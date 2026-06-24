export type Dow = 'mon' | 'tue' | 'wed' | 'thu' | 'fri' | 'sat' | 'sun';

export type ViewMode = 'day' | 'week';

export interface DayMeta {
  label: string;
  short: string;
  title: string;
  chips: [string, string][];
  off: boolean;
}

export interface CatalogItem {
  name: string;
  type: string;
  cover_path: string;
}

export interface MaterialItem {
  id: number;
  title: string;
  type: string;
  cover_path: string | null;
}

export interface MenuItem {
  id: number;
  dow: Dow;
  sort_order: number;
  material_id: number;
  material_title: string;
  cover_path: string | null;
  sets: number;
  rep_min: number;
  rep_max: number;
  rep_display: string;
  sets_rep_display: string;
  memo: string;
}

export interface MenuPageProps {
  todayDow: Dow;
  dayMeta: Record<Dow, DayMeta>;
  initialMenus: Record<Dow, MenuItem[]>;
  storeUrl: string;
  updateBaseUrl: string;
  catalog: CatalogItem[];
  materials: MaterialItem[];
  csrfToken: string;
}

export interface MenuFormPayload {
  dow: Dow;
  sort_order: number;
  catalog_name: string;
  catalog_type: string;
  sets: number;
  reps: string;
  memo: string | null;
}

export interface MenuApiResponse {
  message: string;
  menu: MenuItem;
}
