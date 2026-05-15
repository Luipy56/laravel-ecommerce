import '../scss/main_shop.scss'
import React, { useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '../api';
import { useAuth } from '../contexts/AuthContext';
import PageTitle from '../components/PageTitle';
import ProductCard from '../components/ProductCard';
import { Product } from '../lib/Product';
import { FAVORITES_QUERY_PREFIX } from '../hooks/useFavoriteIdsQuery';

export default function FavoritesPage() {
  const { t } = useTranslation();
  const { user, loading: authLoading } = useAuth();
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  useEffect(() => {
    if (authLoading) return;
    if (!user) {
      navigate(`/login?next=${encodeURIComponent('/favorites')}`, { replace: true });
    }
  }, [authLoading, user, navigate]);

  const favoritesQuery = useQuery({
    queryKey: [...FAVORITES_QUERY_PREFIX, 'list'],
    queryFn: async ({ signal }) => {
      const r = await api.get('favorites', { signal });
      if (!r.data?.success) {
        throw new Error('favorites');
      }
      return r.data.data.items ?? [];
    },
    enabled: Boolean(user?.email_verified),
  });

  const removeLineMutation = useMutation({
    mutationFn: async (lineId) => {
      await api.delete(`favorites/lines/${lineId}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: FAVORITES_QUERY_PREFIX });
    },
  });

  if (authLoading || !user) {
    return (
      <div className="flex justify-center py-12" aria-busy="true" aria-label={t('common.loading')}>
        <span className="loading loading-spinner loading-lg" />
      </div>
    );
  }

  if (!user.email_verified) {
    return (
      <div className="mx-auto w-full min-w-0 max-w-lg">
        <PageTitle>{t('shop.favorites.title')}</PageTitle>
        <div role="alert" className="alert alert-warning flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
          <span>{t('shop.favorites.verify_required')}</span>
          <Link
            to={`/verify-email?next=${encodeURIComponent('/favorites')}`}
            className="btn btn-sm btn-primary shrink-0 sm:ml-auto"
          >
            {t('auth.verify_email_page_title')}
          </Link>
        </div>
      </div>
    );
  }

  if (favoritesQuery.isPending) {
    return (
      <div className="flex justify-center py-12" aria-busy="true" aria-label={t('common.loading')}>
        <span className="loading loading-spinner loading-lg" />
      </div>
    );
  }

  if (favoritesQuery.isError) {
    return (
      <div>
        <PageTitle>{t('shop.favorites.title')}</PageTitle>
        <p className="text-error" role="alert">{t('common.error')}</p>
      </div>
    );
  }

  const items = favoritesQuery.data ?? [];

  return (
    <div>
      <PageTitle>{t('shop.favorites.title')}</PageTitle>
      {items.length === 0 ? (
        <p className="text-base-content/70 py-4">{t('shop.favorites.empty')}</p>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {items.map((item) => {
            const hasCardPayload = Boolean(item.product || item.pack);
            if (item.unavailable && !hasCardPayload) {
              return (
                <div
                  key={item.line_id}
                  className="card card-border bg-base-100 shadow p-4 flex flex-col gap-3 justify-between min-h-[10rem]"
                >
                  <p className="text-sm text-base-content/80">{t('shop.favorites.unavailable')}</p>
                  <div className="flex justify-end">
                    <button
                      type="button"
                      className="btn btn-outline btn-sm"
                      disabled={removeLineMutation.isPending}
                      onClick={() => removeLineMutation.mutate(item.line_id)}
                    >
                      {t('shop.favorites.remove_from_list')}
                    </button>
                  </div>
                </div>
              );
            }
            if (item.product) {
              return (
                <div key={item.line_id} className="flex flex-col gap-1 min-w-0">
                  {item.unavailable ? (
                    <p className="text-sm text-warning shrink-0">{t('shop.favorites.unavailable')}</p>
                  ) : null}
                  <ProductCard product={Product.fromApi(item.product)} />
                </div>
              );
            }
            if (item.pack) {
              return (
                <div key={item.line_id} className="flex flex-col gap-1 min-w-0">
                  {item.unavailable ? (
                    <p className="text-sm text-warning shrink-0">{t('shop.favorites.unavailable')}</p>
                  ) : null}
                  <ProductCard pack={item.pack} />
                </div>
              );
            }
            return (
              <div
                key={item.line_id}
                className="card card-border bg-base-100 shadow p-4 flex flex-col gap-3 justify-between min-h-[10rem]"
              >
                <p className="text-sm text-base-content/80">{t('shop.favorites.unavailable')}</p>
                <div className="flex justify-end">
                  <button
                    type="button"
                    className="btn btn-outline btn-sm"
                    disabled={removeLineMutation.isPending}
                    onClick={() => removeLineMutation.mutate(item.line_id)}
                  >
                    {t('shop.favorites.remove_from_list')}
                  </button>
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}
