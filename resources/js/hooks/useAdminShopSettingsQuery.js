import { useCallback } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useLocation } from 'react-router-dom';
import { api } from '../api';
import {
  ADMIN_INDEX_COLUMN_REGISTRY,
  ADMIN_INDEX_TABLE_ORDER,
} from '../config/adminIndexColumnsRegistry';

export const adminShopSettingsQueryKey = ['admin', 'shop-settings'];

export function useAdminShopSettingsQuery() {
  const location = useLocation();
  const enabled =
    location.pathname.startsWith('/admin') &&
    !/^\/admin\/login\/?$/i.test(location.pathname);

  return useQuery({
    queryKey: adminShopSettingsQueryKey,
    queryFn: async () => {
      const { data } = await api.get('admin/settings');
      if (!data?.success || !data.data) {
        throw new Error('admin_settings');
      }
      return data.data;
    },
    enabled,
    staleTime: 30_000,
  });
}

/**
 * @param {string} tableId key in ADMIN_INDEX_COLUMN_REGISTRY
 */
export function useAdminIndexColumnVisibility(tableId) {
  const { data, isLoading, isFetching } = useAdminShopSettingsQuery();

  const isVisible = useCallback(
    (columnId) => {
      const allowed = ADMIN_INDEX_COLUMN_REGISTRY[tableId]?.map((c) => c.id) ?? [];
      if (allowed.length === 0) {
        return true;
      }
      const prefs = data?.admin_index_columns?.[tableId];
      const visible = Array.isArray(prefs) && prefs.length > 0
        ? prefs.filter((id) => allowed.includes(id))
        : allowed;
      const effective = visible.length > 0 ? visible : allowed;
      return effective.includes(columnId);
    },
    [data, tableId],
  );

  return { isVisible, isLoading: isLoading || isFetching };
}

/** Build payload key `admin_index_columns` from checkbox state for PUT /admin/settings. */
export function buildAdminIndexColumnsPayload(columnPrefs) {
  const out = {};
  for (const tableId of ADMIN_INDEX_TABLE_ORDER) {
    const allowed = ADMIN_INDEX_COLUMN_REGISTRY[tableId]?.map((c) => c.id) ?? [];
    const row = columnPrefs[tableId];
    if (!row || typeof row !== 'object') {
      out[tableId] = allowed;
      continue;
    }
    const visible = allowed.filter((id) => row[id] !== false);
    out[tableId] = visible.length > 0 ? visible : allowed;
  }
  return out;
}

/**
 * @param {Record<string, string[]>|undefined|null} serverCols normalized from API
 * @returns {Record<string, Record<string, boolean>>}
 */
export function columnPrefsFromServer(serverCols) {
  const out = {};
  for (const tableId of ADMIN_INDEX_TABLE_ORDER) {
    const allowed = ADMIN_INDEX_COLUMN_REGISTRY[tableId]?.map((c) => c.id) ?? [];
    const visible = Array.isArray(serverCols?.[tableId])
      ? serverCols[tableId].filter((id) => allowed.includes(id))
      : allowed;
    const visSet = new Set(visible.length > 0 ? visible : allowed);
    out[tableId] = {};
    for (const id of allowed) {
      out[tableId][id] = visSet.has(id);
    }
  }
  return out;
}
