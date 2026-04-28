import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import {
  AreaChart,
  Area,
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  Legend,
} from 'recharts';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

function getThemeColor(variable) {
  if (typeof document === 'undefined') return '#888';
  const value = getComputedStyle(document.documentElement).getPropertyValue(variable).trim();
  return value || '#888';
}

function formatMonthLabel(monthStr, locale) {
  if (!monthStr || monthStr.length < 7) return monthStr;
  const date = new Date(monthStr + '-01');
  return date.toLocaleDateString(locale || 'es', { month: 'short', year: 'numeric' });
}

/** Short month name from "01".."12" for axis labels. */
function formatMonthShort(monthKey, locale) {
  if (!monthKey || monthKey.length < 2) return monthKey;
  const monthIndex = parseInt(monthKey, 10) - 1;
  const date = new Date(2000, monthIndex, 1);
  return date.toLocaleDateString(locale || 'es', { month: 'short' });
}

export default function AdminDashboardPage() {
  const { t, i18n } = useTranslation();
  const navigate = useNavigate();
  const [sales, setSales] = useState({
    data: [],
    current_year: new Date().getFullYear(),
    previous_year: new Date().getFullYear() - 1,
  });
  const [topProducts, setTopProducts] = useState([]);
  const [lowStock, setLowStock] = useState([]);
  const [postalCodes, setPostalCodes] = useState([]);
  const [postalCode, setPostalCode] = useState('');
  const [filterYear, setFilterYear] = useState('');
  const [filterMonth, setFilterMonth] = useState('');
  const [loading, setLoading] = useState(true);

  const chartLocale = i18n.language === 'es' ? 'es-ES' : i18n.language === 'en' ? 'en-GB' : 'ca-ES';
  const locale = chartLocale;

  const fetchPostalCodes = useCallback(async (signal) => {
    try {
      const res = await api.get('admin/stats/postal-codes', signal ? { signal } : {});
      if (res.data.success && Array.isArray(res.data.data)) setPostalCodes(res.data.data);
    } catch (_) {}
  }, []);

  const fetchStats = useCallback(
    async (signal) => {
      const params = {};
      if (postalCode) params.postal_code = postalCode;
      if (filterYear) params.year = filterYear;
      if (filterMonth) params.month = filterMonth;
      const opts = Object.keys(params).length ? (signal ? { signal, params } : { params }) : signal ? { signal } : {};
      try {
        const [s, tP, lS] = await Promise.all([
          api.get('admin/stats/sales-by-period', opts),
          api.get('admin/stats/top-products', opts),
          api.get('admin/stats/low-stock', signal ? { signal } : {}),
        ]);
      if (s.data.success) {
        setSales({
          data: s.data.data || [],
          current_year: s.data.current_year ?? new Date().getFullYear(),
          previous_year: s.data.previous_year ?? new Date().getFullYear() - 1,
        });
      }
      if (tP.data.success) setTopProducts(tP.data.data || []);
      if (lS.data.success) setLowStock(lS.data.data || []);
    } catch (err) {
      if (err.name !== 'AbortError') {
        if (err.response?.status === 401) navigate('/admin/login');
        setSales({ data: [], current_year: new Date().getFullYear(), previous_year: new Date().getFullYear() - 1 });
        setTopProducts([]);
        setLowStock([]);
      }
    } finally {
        setLoading(false);
      }
    },
    [navigate, postalCode, filterYear, filterMonth]
  );

  const yearSelectOptions = useMemo(() => {
    const y0 = new Date().getFullYear();
    const out = [];
    for (let y = y0; y >= y0 - 14; y -= 1) out.push(y);
    return out;
  }, []);

  const monthOptions = useMemo(
    () =>
      Array.from({ length: 12 }, (_, i) => {
        const value = String(i + 1);
        const label = new Date(2000, i, 1).toLocaleDateString(locale, { month: 'long' });
        return { value, label };
      }),
    [locale]
  );

  useEffect(() => {
    const ac = new AbortController();
    fetchPostalCodes(ac.signal);
    return () => ac.abort();
  }, [fetchPostalCodes]);

  useEffect(() => {
    const ac = new AbortController();
    fetchStats(ac.signal);
    return () => ac.abort();
  }, [fetchStats]);

  const totalSales = (sales.data || []).reduce((acc, d) => acc + (d.total_current || 0), 0);
  const lowStockCount = lowStock.length;
  const topCount = topProducts.length;

  const primaryColor = getThemeColor('--color-primary');
  const tooltipBg = getThemeColor('--color-base-100');
  const tooltipBorder = getThemeColor('--color-base-300');
  const textColor = getThemeColor('--color-base-content');
  const gridColor = getThemeColor('--color-base-300');

  const salesChartData = useMemo(
    () =>
      (sales.data || []).map((d) => ({
        name: formatMonthShort(d.month, locale),
        total_current: Number(d.total_current) || 0,
        total_previous: Number(d.total_previous) || 0,
        count_current: d.count_current ?? 0,
        count_previous: d.count_previous ?? 0,
      })),
    [sales.data, locale]
  );

  const currentYear = sales.current_year ?? new Date().getFullYear();
  const previousYear = sales.previous_year ?? new Date().getFullYear() - 1;
  const secondaryColor = getThemeColor('--color-secondary');

  const tooltipStyle = useMemo(
    () => ({
      backgroundColor: tooltipBg,
      border: `1px solid ${tooltipBorder}`,
      borderRadius: 'var(--radius-box, 0.5rem)',
      padding: '0.5rem 0.75rem',
      fontSize: '0.875rem',
      color: textColor,
    }),
    [tooltipBg, tooltipBorder, textColor]
  );

  if (loading) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center gap-2 sm:gap-4 justify-between">
        <PageTitle>{t('admin.dashboard.title')}</PageTitle>
        <div className="flex flex-wrap items-center gap-2 sm:gap-3 justify-end">
          <div className="flex items-center gap-2 shrink-0">
            <label htmlFor="dashboard-postal-code" className="text-sm text-base-content/80 whitespace-nowrap">
              {t('admin.dashboard.filter_postal_code')}
            </label>
            <select
              id="dashboard-postal-code"
              className="select select-bordered select-sm sm:select-md w-full min-w-0 max-w-[12rem]"
              value={postalCode}
              onChange={(e) => setPostalCode(e.target.value)}
              aria-label={t('admin.dashboard.filter_postal_code')}
            >
              <option value="">{t('admin.dashboard.filter_all')}</option>
              {postalCodes.map((code) => (
                <option key={code} value={code}>
                  {code}
                </option>
              ))}
            </select>
          </div>
          <div className="flex items-center gap-2 shrink-0">
            <label htmlFor="dashboard-filter-year" className="text-sm text-base-content/80 whitespace-nowrap">
              {t('admin.dashboard.filter_year')}
            </label>
            <select
              id="dashboard-filter-year"
              className="select select-bordered select-sm sm:select-md w-full min-w-0 max-w-[7.5rem]"
              value={filterYear}
              onChange={(e) => setFilterYear(e.target.value)}
              aria-label={t('admin.dashboard.filter_year')}
            >
              <option value="">{t('admin.dashboard.filter_all')}</option>
              {yearSelectOptions.map((y) => (
                <option key={y} value={String(y)}>
                  {y}
                </option>
              ))}
            </select>
          </div>
          <div className="flex items-center gap-2 shrink-0">
            <label htmlFor="dashboard-filter-month" className="text-sm text-base-content/80 whitespace-nowrap">
              {t('admin.dashboard.filter_month')}
            </label>
            <select
              id="dashboard-filter-month"
              className="select select-bordered select-sm sm:select-md w-full min-w-0 max-w-[10rem]"
              value={filterMonth}
              onChange={(e) => setFilterMonth(e.target.value)}
              aria-label={t('admin.dashboard.filter_month')}
            >
              <option value="">{t('admin.dashboard.filter_all')}</option>
              {monthOptions.map((m) => (
                <option key={m.value} value={m.value}>
                  {m.label}
                </option>
              ))}
            </select>
          </div>
        </div>
      </div>

      <div className="stats stats-vertical w-full shadow sm:stats-horizontal bg-base-100 rounded-box overflow-hidden">
        <div className="stat">
          <div className="stat-title">{t('admin.dashboard.kpi_total_sales')}</div>
          <div className="stat-value text-primary">{totalSales.toFixed(2)} €</div>
        </div>
        <div className="stat">
          <div className="stat-title">{t('admin.dashboard.kpi_low_stock_count')}</div>
          <div className="stat-value text-warning">{lowStockCount}</div>
        </div>
        <div className="stat">
          <div className="stat-title">{t('admin.dashboard.kpi_top_count')}</div>
          <div className="stat-value text-success">{topCount}</div>
        </div>
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <h2 className="card-title text-lg" id="sales-chart-title">
            {t('admin.dashboard.sales_by_period')}
          </h2>
          {salesChartData.length === 0 ? (
            <p className="text-base-content/70 py-8 text-center" aria-live="polite">
              {t('admin.dashboard.no_data')}
            </p>
          ) : (
            <div className="w-full h-[300px]" role="img" aria-labelledby="sales-chart-title" aria-label={t('admin.dashboard.sales_by_period')}>
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={salesChartData} margin={{ top: 8, right: 8, left: 8, bottom: 24 }}>
                  <defs>
                    <linearGradient id="salesAreaGradientCurrent" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stopColor={primaryColor} stopOpacity={0.85} />
                      <stop offset="95%" stopColor={primaryColor} stopOpacity={0} />
                    </linearGradient>
                    <linearGradient id="salesAreaGradientPrevious" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stopColor={secondaryColor} stopOpacity={0.6} />
                      <stop offset="95%" stopColor={secondaryColor} stopOpacity={0} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" stroke={gridColor} />
                  <XAxis dataKey="name" tick={{ fill: textColor, fontSize: 12 }} />
                  <YAxis tick={{ fill: textColor, fontSize: 12 }} tickFormatter={(v) => `${v} €`} />
                  <Tooltip
                    content={({ active, payload }) => {
                      if (!active || !payload?.length) return null;
                      const d = payload[0].payload;
                      return (
                        <div style={tooltipStyle}>
                          <p className="font-medium">{d.name}</p>
                          <p>{currentYear}: {Number(d.total_current).toFixed(2)} € ({d.count_current} {t('admin.dashboard.tooltip_orders')})</p>
                          <p>{previousYear}: {Number(d.total_previous).toFixed(2)} € ({d.count_previous} {t('admin.dashboard.tooltip_orders')})</p>
                        </div>
                      );
                    }}
                  />
                  <Legend
                    formatter={() => []}
                    wrapperStyle={{ paddingTop: 8 }}
                    content={() => (
                      <div className="flex flex-wrap justify-center gap-4 text-sm" style={{ color: textColor }}>
                        <span className="flex items-center gap-2">
                          <span className="w-3 h-3 rounded-sm" style={{ backgroundColor: primaryColor }} aria-hidden />
                          {currentYear}
                        </span>
                        <span className="flex items-center gap-2">
                          <span className="w-3 h-3 rounded-sm" style={{ backgroundColor: secondaryColor }} aria-hidden />
                          {previousYear}
                        </span>
                      </div>
                    )}
                  />
                  <Area type="natural" dataKey="total_current" name={String(currentYear)} stroke={primaryColor} strokeWidth={2} fill="url(#salesAreaGradientCurrent)" />
                  <Area type="natural" dataKey="total_previous" name={String(previousYear)} stroke={secondaryColor} strokeWidth={2} fill="url(#salesAreaGradientPrevious)" />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          )}
        </div>
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <h2 className="card-title text-lg" id="sales-chart-linear-title">
            {t('admin.dashboard.sales_by_period_linear')}
          </h2>
          {salesChartData.length === 0 ? (
            <p className="text-base-content/70 py-8 text-center" aria-live="polite">
              {t('admin.dashboard.no_data')}
            </p>
          ) : (
            <div className="w-full h-[300px]" role="img" aria-labelledby="sales-chart-linear-title" aria-label={t('admin.dashboard.sales_by_period_linear')}>
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={salesChartData} margin={{ top: 8, right: 8, left: 8, bottom: 24 }}>
                  <defs>
                    <linearGradient id="salesAreaLinearGradientCurrent" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stopColor={primaryColor} stopOpacity={0.85} />
                      <stop offset="95%" stopColor={primaryColor} stopOpacity={0} />
                    </linearGradient>
                    <linearGradient id="salesAreaLinearGradientPrevious" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stopColor={secondaryColor} stopOpacity={0.6} />
                      <stop offset="95%" stopColor={secondaryColor} stopOpacity={0} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" stroke={gridColor} />
                  <XAxis dataKey="name" tick={{ fill: textColor, fontSize: 12 }} />
                  <YAxis tick={{ fill: textColor, fontSize: 12 }} tickFormatter={(v) => `${v} €`} />
                  <Tooltip
                    content={({ active, payload }) => {
                      if (!active || !payload?.length) return null;
                      const d = payload[0].payload;
                      return (
                        <div style={tooltipStyle}>
                          <p className="font-medium">{d.name}</p>
                          <p>{currentYear}: {Number(d.total_current).toFixed(2)} € ({d.count_current} {t('admin.dashboard.tooltip_orders')})</p>
                          <p>{previousYear}: {Number(d.total_previous).toFixed(2)} € ({d.count_previous} {t('admin.dashboard.tooltip_orders')})</p>
                        </div>
                      );
                    }}
                  />
                  <Legend
                    formatter={() => []}
                    wrapperStyle={{ paddingTop: 8 }}
                    content={() => (
                      <div className="flex flex-wrap justify-center gap-4 text-sm" style={{ color: textColor }}>
                        <span className="flex items-center gap-2">
                          <span className="w-3 h-3 rounded-sm" style={{ backgroundColor: primaryColor }} aria-hidden />
                          {currentYear}
                        </span>
                        <span className="flex items-center gap-2">
                          <span className="w-3 h-3 rounded-sm" style={{ backgroundColor: secondaryColor }} aria-hidden />
                          {previousYear}
                        </span>
                      </div>
                    )}
                  />
                  <Area type="linear" dataKey="total_current" name={String(currentYear)} stroke={primaryColor} strokeWidth={2} fill="url(#salesAreaLinearGradientCurrent)" />
                  <Area type="linear" dataKey="total_previous" name={String(previousYear)} stroke={secondaryColor} strokeWidth={2} fill="url(#salesAreaLinearGradientPrevious)" />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          )}
        </div>
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <h2 className="card-title text-lg" id="orders-chart-title">
            {t('admin.dashboard.orders_by_period')}
          </h2>
          {salesChartData.length === 0 ? (
            <p className="text-base-content/70 py-8 text-center" aria-live="polite">
              {t('admin.dashboard.no_data')}
            </p>
          ) : (
            <div className="w-full h-[280px]" role="img" aria-labelledby="orders-chart-title" aria-label={t('admin.dashboard.orders_by_period')}>
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={salesChartData} margin={{ top: 8, right: 8, left: 8, bottom: 24 }}>
                  <defs>
                    <linearGradient id="ordersBarGradientCurrent" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stopColor="#fb5412" />
                      <stop offset="100%" stopColor="#8B2400" />
                    </linearGradient>
                    <linearGradient id="ordersBarGradientPrevious" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stopColor={secondaryColor} />
                      <stop offset="100%" stopColor={secondaryColor} stopOpacity={0.7} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" stroke={gridColor} />
                  <XAxis dataKey="name" tick={{ fill: textColor, fontSize: 12 }} />
                  <YAxis tick={{ fill: textColor, fontSize: 12 }} allowDecimals={false} />
                  <Tooltip
                    content={({ active, payload }) => {
                      if (!active || !payload?.length) return null;
                      const d = payload[0].payload;
                      return (
                        <div style={tooltipStyle}>
                          <p className="font-medium">{d.name}</p>
                          <p>{currentYear}: {d.count_current} {t('admin.dashboard.tooltip_orders')}</p>
                          <p>{previousYear}: {d.count_previous} {t('admin.dashboard.tooltip_orders')}</p>
                        </div>
                      );
                    }}
                  />
                  <Legend
                    wrapperStyle={{ paddingTop: 8 }}
                    content={() => (
                      <div className="flex flex-wrap justify-center gap-4 text-sm" style={{ color: textColor }}>
                        <span className="flex items-center gap-2">
                          <span className="w-3 h-3 rounded-sm" style={{ backgroundColor: primaryColor }} aria-hidden />
                          {currentYear}
                        </span>
                        <span className="flex items-center gap-2">
                          <span className="w-3 h-3 rounded-sm" style={{ backgroundColor: secondaryColor }} aria-hidden />
                          {previousYear}
                        </span>
                      </div>
                    )}
                  />
                  <Bar dataKey="count_current" name={String(currentYear)} fill="url(#ordersBarGradientCurrent)" radius={[4, 4, 0, 0]} />
                  <Bar dataKey="count_previous" name={String(previousYear)} fill="url(#ordersBarGradientPrevious)" radius={[4, 4, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          )}
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="card bg-base-100 shadow border border-base-200">
          <div className="card-body">
            <h2 className="card-title text-lg">
              {t('admin.dashboard.top_products')}
            </h2>
            {topProducts.length === 0 ? (
              <p className="text-base-content/70 py-6 text-center" aria-live="polite">
                {t('admin.dashboard.no_data')}
              </p>
            ) : (
              <ul className="flex flex-col gap-0 text-sm text-base-content/80 mt-2 w-full">
                {topProducts.map((p) => (
                  <li key={p.product_id} className="w-full min-w-0">
                    <Link
                      to={`/admin/products/${p.product_id}`}
                      className="flex items-center justify-between w-full min-w-0 gap-2 py-1.5 px-2 rounded hover:bg-base-200/50 focus:outline-none focus:ring-2 focus:ring-primary/50"
                    >
                      <span className="min-w-0 flex-1">{p.name}</span>
                      <span className="shrink-0 whitespace-nowrap">{p.quantity} {t('admin.dashboard.units')}</span>
                    </Link>
                  </li>
                ))}
              </ul>
            )}
          </div>
        </div>
        <div className="card bg-base-100 shadow border border-base-200">
          <div className="card-body">
            <h2 className="card-title text-lg">
              {t('admin.dashboard.low_stock')}
            </h2>
            {lowStock.length === 0 ? (
              <p className="text-base-content/70 py-6 text-center" aria-live="polite">
                {t('admin.dashboard.no_data')}
              </p>
            ) : (
              <ul className="flex flex-col gap-0 text-sm text-base-content/80 mt-2 w-full">
                {lowStock.map((p) => (
                  <li key={p.id} className="w-full min-w-0">
                    <Link
                      to={`/admin/products/${p.id}`}
                      className="flex items-center justify-between w-full min-w-0 gap-2 py-1.5 px-2 rounded hover:bg-base-200/50 focus:outline-none focus:ring-2 focus:ring-primary/50"
                    >
                      <span className="min-w-0 flex-1">{p.name} ({p.code})</span>
                      <span className="shrink-0 whitespace-nowrap">{p.stock} {t('admin.dashboard.units')}</span>
                    </Link>
                  </li>
                ))}
              </ul>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
