import React, { useCallback, useEffect, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

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
    ctx.fillStyle = '#e5e7eb';
    ctx.fillRect(0, 0, w, h);
    data.forEach((d, i) => {
      const barH = (d.total / maxTotal) * (h - 30);
      ctx.fillStyle = 'oklch(0.65 0.2 250)';
      ctx.fillRect(20 + i * (barW + 4), h - 20 - barH, barW, barH);
      ctx.fillStyle = '#374151';
      ctx.font = '10px sans-serif';
      ctx.fillText(d.month, 20 + i * (barW + 4), h - 5);
    });
  }, [data]);
  return <canvas ref={canvasRef} className="w-full h-64 border border-base-300 rounded" width={400} height={256} aria-label="Sales by month" />;
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
    ctx.fillStyle = '#e5e7eb';
    ctx.fillRect(0, 0, w, h);
    data.forEach((d, i) => {
      const barH = (d.quantity / maxQty) * (h - 40);
      ctx.fillStyle = 'oklch(0.6 0.25 140)';
      ctx.fillRect(100 + i * (barW + 4), h - 30 - barH, barW, barH);
      ctx.fillStyle = '#374151';
      ctx.font = '9px sans-serif';
      ctx.fillText(String(d.quantity), 100 + i * (barW + 4) + barW / 2 - 4, h - 35 - barH);
    });
  }, [data]);
  return <canvas ref={canvasRef} className="w-full h-64 border border-base-300 rounded" width={400} height={256} aria-label="Top products" />;
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
    ctx.fillStyle = '#e5e7eb';
    ctx.fillRect(0, 0, w, h);
    data.forEach((d, i) => {
      const barH = (d.stock / maxStock) * (h - 30);
      ctx.fillStyle = d.stock < 5 ? 'oklch(0.6 0.25 25)' : 'oklch(0.65 0.2 80)';
      ctx.fillRect(20 + i * (barW + 4), h - 20 - barH, barW, barH);
      ctx.fillStyle = '#374151';
      ctx.font = '9px sans-serif';
      ctx.fillText(String(d.stock), 20 + i * (barW + 4) + barW / 2 - 4, h - 25 - barH);
    });
  }, [data]);
  return <canvas ref={canvasRef} className="w-full h-64 border border-base-300 rounded" width={400} height={256} aria-label="Low stock" />;
}

export default function AdminDashboardPage() {
  const navigate = useNavigate();
  const [sales, setSales] = useState([]);
  const [topProducts, setTopProducts] = useState([]);
  const [lowStock, setLowStock] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchStats = useCallback(async () => {
    try {
      const [s, tP, lS] = await Promise.all([
        api.get('admin/stats/sales-by-period'),
        api.get('admin/stats/top-products'),
        api.get('admin/stats/low-stock'),
      ]);
      if (s.data.success) setSales(s.data.data || []);
      if (tP.data.success) setTopProducts(tP.data.data || []);
      if (lS.data.success) setLowStock(lS.data.data || []);
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setSales([]);
      setTopProducts([]);
      setLowStock([]);
    } finally {
      setLoading(false);
    }
  }, [navigate]);

  useEffect(() => {
    fetchStats();
  }, [fetchStats]);

  if (loading) return <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>;

  return (
    <div className="space-y-8">
      <PageTitle>Admin — Dashboard</PageTitle>
      <section>
        <h2 className="text-lg font-semibold mb-2">Vendes per període</h2>
        <SalesChart data={sales} />
      </section>
      <section>
        <h2 className="text-lg font-semibold mb-2">Productes més venuts (top 10)</h2>
        <TopProductsChart data={topProducts} />
        {topProducts.length > 0 && (
          <ul className="list-disc list-inside mt-2 text-sm">
            {topProducts.map((p) => (
              <li key={p.product_id}>{p.name}: {p.quantity} u.</li>
            ))}
          </ul>
        )}
      </section>
      <section>
        <h2 className="text-lg font-semibold mb-2">Stock més baix</h2>
        <LowStockChart data={lowStock} />
        {lowStock.length > 0 && (
          <ul className="list-disc list-inside mt-2 text-sm">
            {lowStock.map((p) => (
              <li key={p.id}>{p.name} ({p.code}): {p.stock} u.</li>
            ))}
          </ul>
        )}
      </section>
    </div>
  );
}
