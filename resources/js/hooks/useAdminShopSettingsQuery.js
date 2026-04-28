import { useCallback, useMemo } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useLocation } from 'react-router-dom';
import { api } from '../api';
import {
  ADMIN_INDEX_COLUMN_REGISTRY,
  ADMIN_INDEX_TABLE_ORDER,
} from '../config/adminIndexColumnsRegistry';

export const adminShopSettingsQueryKey = ['admin', 'shop-settings'];

/** Full ordered column ids for a table (allowed ids only), appending any missing registry ids at the end. */
function fullColumnSequence(tableId, columnOrder) {
  const allowed = ADMIN_INDEX_COLUMN_REGISTRY[tableId]?.map((c) => c.id) ?? [];
  const order = columnOrder?.[tableId];
  const seq = [];
  const seen = new Set();
  if (Array.isArray(order)) {
    for (const id of order) {
      if (allowed.includes(id) && !seen.has(id)) {
        seen.add(id);
        seq.push(id);
      }
    }
  }
  for (const id of allowed) {
    if (!seen.has(id)) {
      seq.push(id);
    }
  }
  return seq;
}

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

  const orderedVisibleColumnIds = useMemo(() => {
    const allowed = ADMIN_INDEX_COLUMN_REGISTRY[tableId]?.map((c) => c.id) ?? [];
    if (allowed.length === 0) {
      return [];
    }
    const prefs = data?.admin_index_columns?.[tableId];
    const list =
      Array.isArray(prefs) && prefs.length > 0 ? prefs.filter((id) => allowed.includes(id)) : allowed;
    return list.length > 0 ? list : allowed;
  }, [data, tableId]);

  const isVisible = useCallback(
    (columnId) => orderedVisibleColumnIds.includes(columnId),
    [orderedVisibleColumnIds],
  );

  return {
    isVisible,
    orderedVisibleColumnIds,
    isLoading: isLoading || isFetching,
  };
}

/**
 * @param {Record<string, Record<string, boolean>>} columnPrefs
 * @param {Record<string, string[]>} columnOrder
 */
export function buildAdminIndexColumnsPayload(columnPrefs, columnOrder) {
  const out = {};
  for (const tableId of ADMIN_INDEX_TABLE_ORDER) {
    const allowed = ADMIN_INDEX_COLUMN_REGISTRY[tableId]?.map((c) => c.id) ?? [];
    const seq = fullColumnSequence(tableId, columnOrder);
    const visible = seq.filter((id) => allowed.includes(id) && columnPrefs?.[tableId]?.[id] !== false);
    out[tableId] = visible.length > 0 ? visible : allowed;
  }
  return out;
}

/**
 * @param {Record<string, string[]>|undefined|null} serverCols normalized from API
 * @returns {{ columnPrefs: Record<string, Record<string, boolean>>, columnOrder: Record<string, string[]> }}
 */
export function columnOrderAndPrefsFromServer(serverCols) {
  const columnPrefs = {};
  const columnOrder = {};
  for (const tableId of ADMIN_INDEX_TABLE_ORDER) {
    const allowed = ADMIN_INDEX_COLUMN_REGISTRY[tableId]?.map((c) => c.id) ?? [];
    if (allowed.length === 0) {
      columnPrefs[tableId] = {};
      columnOrder[tableId] = [];
      continue;
    }
    const stored = Array.isArray(serverCols?.[tableId])
      ? serverCols[tableId].filter((id) => allowed.includes(id))
      : [];
    const visibleIds = stored.length > 0 ? stored : allowed;
    const visSet = new Set(visibleIds);
    columnPrefs[tableId] = {};
    for (const id of allowed) {
      columnPrefs[tableId][id] = visSet.has(id);
    }
    const hiddenOrdered = allowed.filter((id) => !visSet.has(id));
    columnOrder[tableId] = [...visibleIds, ...hiddenOrdered];
  }
  return { columnPrefs, columnOrder };
}

/**
 * @param {Record<string, string[]>|undefined|null} serverCols normalized from API
 * @returns {Record<string, Record<string, boolean>>}
 */
export function columnPrefsFromServer(serverCols) {
  return columnOrderAndPrefsFromServer(serverCols).columnPrefs;
}
