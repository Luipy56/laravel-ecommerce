import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { useAuth } from '../contexts/AuthContext';
import PageTitle from '../components/PageTitle';

function rmaStatusBadgeClass(status) {
  switch (status) {
    case 'pending_review': return 'badge-warning';
    case 'approved': return 'badge-info';
    case 'refunded': return 'badge-success';
    case 'rejected': return 'badge-error';
    case 'cancelled': return 'badge-neutral';
    default: return 'badge-neutral';
  }
}

export default function ReturnRequestsPage() {
  const { t, i18n } = useTranslation();
  const { user, loading: authLoading } = useAuth();
  const [rmas, setRmas] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) return;
    setLoading(true);
    api.get('return-requests')
      .then((r) => { if (r.data.success) setRmas(r.data.data || []); })
      .catch(() => setRmas([]))
      .finally(() => setLoading(false));
  }, [user]);

  if (authLoading) {
    return <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>;
  }
  if (!user) return <Link to="/login" className="btn btn-primary">{t('auth.login')}</Link>;

  return (
    <div className="mx-auto w-full min-w-0 max-w-3xl">
      <div className="flex flex-wrap items-center justify-between gap-2 mb-3">
        <PageTitle className="mb-0">{t('shop.returns.title')}</PageTitle>
        <Link to="/orders" className="btn btn-ghost btn-sm shrink-0">{t('shop.orders')}</Link>
      </div>

      {loading ? (
        <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>
      ) : rmas.length === 0 ? (
        <div className="text-center py-12 text-base-content/60">
          <p>{t('shop.returns.empty')}</p>
        </div>
      ) : (
        <div className="space-y-3">
          {rmas.map((rma) => (
            <div key={rma.id} className="card bg-base-100 border border-base-200 shadow-sm rounded-2xl">
              <div className="card-body py-4 px-4 sm:px-5">
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div className="min-w-0">
                    <p className="font-semibold text-sm">
                      {t('shop.order')} #
                      <Link to={`/orders/${rma.order_id}`} className="link link-primary">
                        {rma.order_id}
                      </Link>
                    </p>
                    {rma.order?.order_date && (
                      <p className="text-xs text-base-content/60 mt-0.5">
                        {new Date(rma.order.order_date).toLocaleDateString(i18n.language)}
                      </p>
                    )}
                    <p className="text-sm text-base-content/70 mt-2 line-clamp-2">{rma.reason}</p>
                    {rma.refund_amount != null && (
                      <p className="text-sm font-medium mt-1">
                        {t('shop.returns.refund_amount')}: {Number(rma.refund_amount).toFixed(2)} €
                      </p>
                    )}
                  </div>
                  <div className="flex flex-col items-end gap-1 shrink-0">
                    <span className={`badge ${rmaStatusBadgeClass(rma.status)}`}>
                      {t(`shop.returns.status_${rma.status}`) || rma.status}
                    </span>
                    <span className="text-xs text-base-content/50 tabular-nums">
                      {rma.created_at ? new Date(rma.created_at).toLocaleDateString(i18n.language) : ''}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
