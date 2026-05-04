import React, { useMemo } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '../api';
import { useAuth } from '../contexts/AuthContext';
import { emitAppToast } from '../toastEvents';
import { IconHeart } from './icons';
import { FAVORITES_QUERY_PREFIX, useFavoriteIdsQuery } from '../hooks/useFavoriteIdsQuery';

/**
 * Toggle favorite (product or pack). Guests go to login with ?next=.
 * Logged-in unverified clients see a verification hint (API matches client.verified).
 */
export default function FavoriteToggle({ productId, packId, className = '' }) {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const location = useLocation();
  const { user } = useAuth();
  const queryClient = useQueryClient();

  const verified = Boolean(user?.email_verified);
  const { data: idsData } = useFavoriteIdsQuery(verified && (productId != null || packId != null));
  const liked = useMemo(() => {
    if (!verified || !idsData) return false;
    if (productId != null) {
      return (idsData.product_ids ?? []).map(Number).includes(Number(productId));
    }
    return (idsData.pack_ids ?? []).map(Number).includes(Number(packId));
  }, [verified, idsData, productId, packId]);

  const mutation = useMutation({
    mutationFn: async () => {
      const body =
        productId != null ? { product_id: Number(productId) } : { pack_id: Number(packId) };
      const r = await api.post('favorites/toggle', body);
      if (!r.data?.success) {
        const msg = r.data?.message ?? t('common.error');
        throw new Error(msg);
      }
      return r.data.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: FAVORITES_QUERY_PREFIX });
    },
    onError: (err) => {
      const ax = err?.response?.data;
      const msg = (typeof ax?.message === 'string' && ax.message) || err?.message || t('common.error');
      emitAppToast(msg, 'error');
    },
  });

  const handleClick = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (!user) {
      const next = encodeURIComponent(`${location.pathname}${location.search}`);
      navigate(`/login?next=${next}`);
      return;
    }
    if (!user.email_verified) {
      emitAppToast(t('shop.favorites.verify_required'), 'warning');
      navigate(`/verify-email?next=${encodeURIComponent(location.pathname + location.search)}`);
      return;
    }
    mutation.mutate();
  };

  const ariaLabel = liked ? t('shop.favorites.remove_aria') : t('shop.favorites.add_aria');

  return (
    <button
      type="button"
      className={`btn btn-circle btn-ghost btn-sm bg-base-100/90 hover:bg-base-100 shadow border border-base-300/80 ${className}`.trim()}
      onClick={handleClick}
      disabled={mutation.isPending}
      aria-label={ariaLabel}
      aria-pressed={liked}
    >
      {mutation.isPending ? (
        <span className="loading loading-spinner loading-xs text-primary" />
      ) : (
        <IconHeart className="h-5 w-5 shrink-0 text-error" filled={liked} aria-hidden="true" />
      )}
    </button>
  );
}
