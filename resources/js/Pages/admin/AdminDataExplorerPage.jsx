import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

function formatCell(value) {
  if (value === null || value === undefined) return '';
  if (typeof value === 'boolean') return value ? '1' : '0';
  if (typeof value === 'object') return '';
  return String(value);
}

export default function AdminDataExplorerPage() {
  const { t, i18n } = useTranslation();
  const numberLocale =
    i18n.language === 'es' ? 'es-ES' : i18n.language === 'en' ? 'en-GB' : 'ca-ES';
  const navigate = useNavigate();

  const [schema, setSchema] = useState(null);
  const [table, setTable] = useState('');
  const [q, setQ] = useState('');
  const [dateColumn, setDateColumn] = useState('');
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');
  const [rows, setRows] = useState([]);
  const [columns, setColumns] = useState([]);
  const [hasMore, setHasMore] = useState(false);
  const [loadingMore, setLoadingMore] = useState(false);
  const [loading, setLoading] = useState(false);
  const pageRef = useRef(1);
  const sentinelRef = useRef(null);
  const [schemaLoading, setSchemaLoading] = useState(true);
  const [error, setError] = useState('');

  const [metric, setMetric] = useState('count');
  const [groupBy, setGroupBy] = useState('');
  const [valueColumn, setValueColumn] = useState('');
  const [aggregateRows, setAggregateRows] = useState([]);
  const [aggregateLoading, setAggregateLoading] = useState(false);
  const [refreshNonce, setRefreshNonce] = useState(0);
  const perPage = 25;

  const selectedMeta = useMemo(
    () => schema?.tables?.find((x) => x.name === table),
    [schema, table],
  );

  const sortColumnDefault = useMemo(() => {
    const cols = selectedMeta?.columns || [];
    if (cols.includes('id')) return 'id';
    return cols[0] ?? '';
  }, [selectedMeta]);

  const fetchSchema = useCallback(async () => {
    setSchemaLoading(true);
    try {
      const { data } = await api.get('admin/data-explorer/schema');
      if (data.success && data.data?.tables?.length) {
        setSchema(data.data);
        setTable((prev) => prev || data.data.tables[0].name);
      } else {
        setSchema(data.data || { tables: [], limits: {} });
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setSchema({ tables: [], limits: {} });
    } finally {
      setSchemaLoading(false);
    }
  }, [navigate]);

  useEffect(() => {
    fetchSchema();
  }, [fetchSchema]);

  useEffect(() => {
    if (!selectedMeta?.columns?.length) return;
    setGroupBy((g) => (g && selectedMeta.columns.includes(g) ? g : selectedMeta.columns[0]));
    setValueColumn((v) => (v && selectedMeta.columns.includes(v) ? v : selectedMeta.columns[0]));
  }, [selectedMeta]);

  const buildQueryPayload = useCallback(
    (pageNum) => ({
      table,
      q: q.trim() || undefined,
      date_column: dateColumn || undefined,
      date_from: dateFrom || undefined,
      date_to: dateTo || undefined,
      sort_column: sortColumnDefault || undefined,
      sort_direction: 'desc',
      page: pageNum,
      per_page: perPage,
    }),
    [table, q, dateColumn, dateFrom, dateTo, sortColumnDefault, perPage],
  );

  const loadQuery = useCallback(
    async (pageNum, reset = false) => {
      if (!table) return;
      if (reset) setLoading(true);
      else setLoadingMore(true);
      setError('');
      try {
        const { data } = await api.post('admin/data-explorer/query', buildQueryPayload(pageNum));
        if (data.success) {
          const list = data.data || [];
          if (reset) setRows(list);
          else setRows((prev) => [...prev, ...list]);
          const cols =
            selectedMeta?.columns?.length ? selectedMeta.columns : Object.keys(list[0] || {});
          setColumns(cols);
          const meta = data.meta || {};
          setHasMore((meta.current_page ?? pageNum) < (meta.last_page ?? 1));
          pageRef.current = pageNum;
        }
      } catch (err) {
        if (err.response?.status === 401) navigate('/admin/login');
        setError(err.response?.data?.message || t('common.error'));
        if (reset) setRows([]);
      } finally {
        if (reset) setLoading(false);
        else setLoadingMore(false);
      }
    },
    [table, buildQueryPayload, selectedMeta, navigate, t],
  );

  useEffect(() => {
    if (!table || schemaLoading) return;
    pageRef.current = 1;
    loadQuery(1, true);
  }, [table, schemaLoading, refreshNonce, loadQuery]);

  useEffect(() => {
    if (!hasMore) return;
    const el = sentinelRef.current;
    if (!el) return;
    const obs = new IntersectionObserver(
      (entries) => {
        if (!entries.some((e) => e.isIntersecting)) return;
        if (!hasMore || loadingMore || loading) return;
        const next = pageRef.current + 1;
        pageRef.current = next;
        loadQuery(next, false);
      },
      { rootMargin: '120px' },
    );
    obs.observe(el);
    return () => obs.disconnect();
  }, [hasMore, loadingMore, loading, loadQuery]);

  const handleApply = () => {
    setRefreshNonce((n) => n + 1);
  };

  const handleExport = async () => {
    if (!table) return;
    try {
      const payload = {
        table,
        q: q.trim() || undefined,
        date_column: dateColumn || undefined,
        date_from: dateFrom || undefined,
        date_to: dateTo || undefined,
        sort_column: sortColumnDefault || undefined,
        sort_direction: 'desc',
      };
      const response = await api.post('admin/data-explorer/export', payload, {
        responseType: 'blob',
      });
      const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `data-explorer-${table}.csv`;
      a.click();
      window.URL.revokeObjectURL(url);
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setError(t('common.error'));
    }
  };

  const runAggregate = async () => {
    if (!table || !groupBy) return;
    setAggregateLoading(true);
    setError('');
    try {
      const body = {
        table,
        metric,
        group_by: groupBy,
        q: q.trim() || undefined,
        date_column: dateColumn || undefined,
        date_from: dateFrom || undefined,
        date_to: dateTo || undefined,
      };
      if (metric === 'sum' || metric === 'avg') {
        body.value_column = valueColumn;
      }
      const { data } = await api.post('admin/data-explorer/aggregate', body);
      if (data.success) setAggregateRows(data.data || []);
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setError(err.response?.data?.message || t('common.error'));
      setAggregateRows([]);
    } finally {
      setAggregateLoading(false);
    }
  };

  const limits = schema?.limits || {};

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.data_explorer.title')}</PageTitle>

      {limits.query_timeout_seconds != null && (
        <p className="text-sm text-base-content/70">
          {t('admin.data_explorer.limits_label')}: {limits.max_per_page ?? ''} ·{' '}
          {limits.max_export_rows ?? ''} · {limits.query_timeout_seconds}s
        </p>
      )}

      <div className="flex flex-wrap items-center gap-2 sm:gap-4">
        <label className="flex flex-col gap-1 min-w-0">
          <span className="text-sm text-base-content/70">{t('admin.data_explorer.table')}</span>
          <select
            className="select select-bordered select-sm sm:select-md min-w-[12rem]"
            value={table}
            onChange={(e) => {
              setTable(e.target.value);
            }}
            disabled={schemaLoading || !schema?.tables?.length}
          >
            {(schema?.tables || []).map((tb) => (
              <option key={tb.name} value={tb.name}>
                {t(tb.label_key)}
              </option>
            ))}
          </select>
        </label>
        <input
          type="search"
          className="input input-bordered input-sm sm:input-md w-full min-w-0 max-w-md"
          placeholder={t('admin.data_explorer.search_placeholder')}
          value={q}
          onChange={(e) => setQ(e.target.value)}
          aria-label={t('admin.data_explorer.search_placeholder')}
        />
        <label className="flex flex-col gap-1 min-w-0">
          <span className="text-sm text-base-content/70">{t('admin.data_explorer.date_column')}</span>
          <select
            className="select select-bordered select-sm sm:select-md max-w-xs"
            value={dateColumn}
            onChange={(e) => setDateColumn(e.target.value)}
          >
            <option value="">{t('admin.data_explorer.date_all')}</option>
            {(selectedMeta?.date_columns || []).map((c) => (
              <option key={c} value={c}>
                {c}
              </option>
            ))}
          </select>
        </label>
        <label className="flex flex-col gap-1">
          <span className="text-sm text-base-content/70">{t('admin.data_explorer.date_from')}</span>
          <input
            type="date"
            className="input input-bordered input-sm sm:input-md"
            value={dateFrom}
            onChange={(e) => setDateFrom(e.target.value)}
          />
        </label>
        <label className="flex flex-col gap-1">
          <span className="text-sm text-base-content/70">{t('admin.data_explorer.date_to')}</span>
          <input
            type="date"
            className="input input-bordered input-sm sm:input-md"
            value={dateTo}
            onChange={(e) => setDateTo(e.target.value)}
          />
        </label>
        <div className="flex flex-wrap items-end gap-2 ml-auto justify-end">
          <button type="button" className="btn btn-primary btn-sm sm:btn-md shrink-0" onClick={handleApply}>
            {t('admin.data_explorer.apply')}
          </button>
          <button type="button" className="btn btn-outline btn-sm sm:btn-md shrink-0" onClick={handleExport}>
            {t('admin.data_explorer.export_csv')}
          </button>
        </div>
      </div>

      {error ? (
        <div className="alert alert-warning" role="alert">
          {error}
        </div>
      ) : null}

      <div className="card bg-base-100 shadow border border-base-200 overflow-hidden">
        {loading ? (
          <div className="flex justify-center py-12">
            <span className="loading loading-spinner loading-lg" aria-hidden="true" />
          </div>
        ) : rows.length === 0 ? (
          <div className="p-8 text-center text-base-content/70">{t('admin.data_explorer.no_results')}</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="table table-zebra table-sm [&_th]:whitespace-nowrap [&_td]:whitespace-nowrap [&_thead_th]:border-b-2 [&_thead_th]:border-base-300 [&_thead_th]:font-semibold [&_thead_th]:bg-transparent">
              <thead>
                <tr>
                  {columns.map((col) => (
                    <th key={col}>{col}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {rows.map((row, idx) => (
                  <tr key={idx}>
                    {columns.map((col) => (
                      <td key={col} className="max-w-xs truncate" title={formatCell(row[col])}>
                        {formatCell(row[col])}
                      </td>
                    ))}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      <div ref={sentinelRef} className="py-2 flex justify-center" aria-hidden="true">
        {loadingMore && <span className="loading loading-spinner loading-md" />}
      </div>

      <div className="card bg-base-100 shadow border border-base-200 p-4 space-y-4">
        <h2 className="font-semibold text-lg">{t('admin.data_explorer.aggregate_panel')}</h2>
        <div className="flex flex-wrap items-end gap-2 sm:gap-4">
          <label className="flex flex-col gap-1">
            <span className="text-sm text-base-content/70">{t('admin.data_explorer.metric')}</span>
            <select
              className="select select-bordered select-sm sm:select-md"
              value={metric}
              onChange={(e) => setMetric(e.target.value)}
            >
              <option value="count">{t('admin.data_explorer.metric_count')}</option>
              <option value="sum">{t('admin.data_explorer.metric_sum')}</option>
              <option value="avg">{t('admin.data_explorer.metric_avg')}</option>
            </select>
          </label>
          <label className="flex flex-col gap-1">
            <span className="text-sm text-base-content/70">{t('admin.data_explorer.group_by')}</span>
            <select
              className="select select-bordered select-sm sm:select-md min-w-[10rem]"
              value={groupBy}
              onChange={(e) => setGroupBy(e.target.value)}
            >
              {(selectedMeta?.columns || []).map((c) => (
                <option key={c} value={c}>
                  {c}
                </option>
              ))}
            </select>
          </label>
          {(metric === 'sum' || metric === 'avg') && (
            <label className="flex flex-col gap-1">
              <span className="text-sm text-base-content/70">{t('admin.data_explorer.value_column')}</span>
              <select
                className="select select-bordered select-sm sm:select-md min-w-[10rem]"
                value={valueColumn}
                onChange={(e) => setValueColumn(e.target.value)}
              >
                {(selectedMeta?.columns || []).map((c) => (
                  <option key={c} value={c}>
                    {c}
                  </option>
                ))}
              </select>
            </label>
          )}
          <button
            type="button"
            className="btn btn-secondary btn-sm sm:btn-md shrink-0 ml-auto"
            onClick={runAggregate}
            disabled={aggregateLoading || !groupBy}
          >
            {aggregateLoading ? t('admin.data_explorer.loading') : t('admin.data_explorer.run_aggregate')}
          </button>
        </div>

        {aggregateRows.length > 0 && (
          <div className="overflow-x-auto">
            <table className="table table-zebra table-sm">
              <thead>
                <tr>
                  <th>{t('admin.data_explorer.col_group_value')}</th>
                  <th className="text-end">{t('admin.data_explorer.col_aggregate')}</th>
                </tr>
              </thead>
              <tbody>
                {aggregateRows.map((r, i) => (
                  <tr key={i}>
                    <td>{formatCell(r.group_value)}</td>
                    <td className="text-end tabular-nums">
                      {r.aggregate_value != null
                        ? Number(r.aggregate_value).toLocaleString(numberLocale)
                        : ''}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
