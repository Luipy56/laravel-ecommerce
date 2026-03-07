import React, { useCallback, useEffect, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

function getThemeColor(variable) {
  if (typeof document === 'undefined') return '#888';
  const value = getComputedStyle(document.documentElement).getPropertyValue(variable).trim();
  return value || '#888';
}

function SalesChart({ data }) {
  const canvasRef = useRef(null);
  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas || !data?.length) return;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);
    const w = rect.width;
    const h = rect.height;
    const maxTotal = Math.max(...data.map((d) => d.total), 1);
    const barW = Math.max(10, (w - 40) / data.length - 4);
    ctx.fillStyle = getThemeColor('--color-base-200');
    ctx.fillRect(0, 0, w, h);
    const barColor = getThemeColor('--color-primary');
    const textColor = getThemeColor('--color-base-content');
    data.forEach((d, i) => {
      const barH = (d.total / maxTotal) * (h - 30);
      ctx.fillStyle = barColor;
      ctx.fillRect(20 + i * (barW + 4), h - 20 - barH, barW, barH);
      ctx.fillStyle = textColor;
      ctx.font = '10px sans-serif';
      ctx.fillText(d.month, 20 + i * (barW + 4), h - 5);
    });
  }, [data]);
  return <canvas ref={canvasRef} className="w-full h-64 border border-base-300 rounded-box" width={400} height={256} aria-label="Sales by month" />;
}

function TopProductsChart({ data }) {
  const canvasRef = useRef(null);
  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas || !data?.length) return;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);
    const w = rect.width;
    const h = rect.height;
    const maxQty = Math.max(...data.map((d) => d.quantity), 1);
    const barW = Math.max(20, (w - 120) / data.length - 4);
    ctx.fillStyle = getThemeColor('--color-base-200');
    ctx.fillRect(0, 0, w, h);
    const barColor = getThemeColor('--color-success');
    const textColor = getThemeColor('--color-base-content');
    data.forEach((d, i) => {
      const barH = (d.quantity / maxQty) * (h - 40);
      ctx.fillStyle = barColor;
      ctx.fillRect(100 + i * (barW + 4), h - 30 - barH, barW, barH);
      ctx.fillStyle = textColor;
      ctx.font = '9px sans-serif';
      ctx.fillText(String(d.quantity), 100 + i * (barW + 4) + barW / 2 - 4, h - 35 - barH);
    });
  }, [data]);
  return <canvas ref={canvasRef} className="w-full h-64 border border-base-300 rounded-box" width={400} height={256} aria-label="Top products" />;
}

function LowStockChart({ data }) {
  const canvasRef = useRef(null);
  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas || !data?.length) return;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);
    const w = rect.width;
    const h = rect.height;
    const maxStock = Math.max(...data.map((d) => d.stock), 1);
    const barW = Math.max(15, (w - 40) / data.length - 4);
    ctx.fillStyle = getThemeColor('--color-base-200');
    ctx.fillRect(0, 0, w, h);
    const warningColor = getThemeColor('--color-warning');
    const errorColor = getThemeColor('--color-error');
    const textColor = getThemeColor('--color-base-content');
    data.forEach((d, i) => {
      const barH = (d.stock / maxStock) * (h - 30);
      ctx.fillStyle = d.stock < 5 ? errorColor : warningColor;
      ctx.fillRect(20 + i * (barW + 4), h - 20 - barH, barW, barH);
      ctx.fillStyle = textColor;
      ctx.font = '9px sans-serif';
      ctx.fillText(String(d.stock), 20 + i * (barW + 4) + barW / 2 - 4, h - 25 - barH);
    });
  }, [data]);
  return <canvas ref={canvasRef} className="w-full h-64 border border-base-300 rounded-box" width={400} height={256} aria-label="Low stock" />;
}

export default function AdminDashboardPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [sales, setSales] = useState([]);
  const [topProducts, setTopProducts] = useState([]);
  const [lowStock, setLowStock] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchStats = useCallback(async (signal) => {
    try {
      const [s, tP, lS] = await Promise.all([
        api.get('admin/stats/sales-by-period', signal ? { signal } : {}),
        api.get('admin/stats/top-products', signal ? { signal } : {}),
        api.get('admin/stats/low-stock', signal ? { signal } : {}),
      ]);
      if (s.data.success) setSales(s.data.data || []);
      if (tP.data.success) setTopProducts(tP.data.data || []);
      if (lS.data.success) setLowStock(lS.data.data || []);
    } catch (err) {
      if (err.name !== 'AbortError') {
        if (err.response?.status === 401) navigate('/admin/login');
        setSales([]);
        setTopProducts([]);
        setLowStock([]);
      }
    } finally {
      setLoading(false);
    }
  }, [navigate]);

  useEffect(() => {
    const ac = new AbortController();
    fetchStats(ac.signal);
    return () => ac.abort();
  }, [fetchStats]);

  const totalSales = sales.reduce((acc, d) => acc + (d.total || 0), 0);
  const lowStockCount = lowStock.length;
  const topCount = topProducts.length;

  if (loading) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.dashboard.title')}</PageTitle>

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
          <h2 className="card-title text-lg">{t('admin.dashboard.sales_by_period')}</h2>
          <SalesChart data={sales} />
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="card bg-base-100 shadow border border-base-200">
          <div className="card-body">
            <h2 className="card-title text-lg">{t('admin.dashboard.top_products')}</h2>
            <TopProductsChart data={topProducts} />
            {topProducts.length > 0 && (
              <ul className="list text-sm text-base-content/80 mt-2">
                {topProducts.map((p) => (
                  <li key={p.product_id} className="list-row">
                    <span>{p.name}</span>
                    <span>{p.quantity} {t('admin.dashboard.units')}</span>
                  </li>
                ))}
              </ul>
            )}
          </div>
        </div>
        <div className="card bg-base-100 shadow border border-base-200">
          <div className="card-body">
            <h2 className="card-title text-lg">{t('admin.dashboard.low_stock')}</h2>
            <LowStockChart data={lowStock} />
            {lowStock.length > 0 && (
              <ul className="list text-sm text-base-content/80 mt-2">
                {lowStock.map((p) => (
                  <li key={p.id} className="list-row">
                    <span>{p.name} ({p.code})</span>
                    <span>{p.stock} {t('admin.dashboard.units')}</span>
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
