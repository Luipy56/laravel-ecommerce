import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '../api';
import { useAuth } from '../contexts/AuthContext';
import StarRating from './StarRating';

function RatingBar({ star, count, total }) {
  const pct = total > 0 ? Math.round((count / total) * 100) : 0;
  return (
    <div className="flex items-center gap-2 text-sm">
      <span className="w-4 text-end tabular-nums text-base-content/70">{star}</span>
      <span className="text-warning text-xs">★</span>
      <div className="flex-1 bg-base-200 rounded-full h-2 overflow-hidden">
        <div className="bg-warning h-2 rounded-full transition-all" style={{ width: `${pct}%` }} />
      </div>
      <span className="w-6 text-end tabular-nums text-xs text-base-content/60">{count}</span>
    </div>
  );
}

function ReviewForm({ entityType, entityId, queryKeyPrefix, existing, onSuccess }) {
  const { t } = useTranslation();
  const [rating, setRating] = useState(existing?.rating ?? 0);
  const [hovered, setHovered] = useState(0);
  const [comment, setComment] = useState(existing?.comment ?? '');
  const [error, setError] = useState(null);
  const queryClient = useQueryClient();

  const mutation = useMutation({
    mutationFn: (payload) => api.post(`${entityType}/${entityId}/reviews`, payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [queryKeyPrefix, entityId] });
      queryClient.invalidateQueries({ queryKey: [`${queryKeyPrefix}-mine`, entityId] });
      setError(null);
      onSuccess?.();
    },
    onError: (err) => {
      setError(err?.response?.data?.message ?? t('common.error'));
    },
  });

  const handleSubmit = (e) => {
    e.preventDefault();
    if (rating < 1) {
      setError(t('shop.reviews.error_rating_required'));
      return;
    }
    mutation.mutate({ rating, comment: comment.trim() || null });
  };

  const displayRating = hovered || rating;

  return (
    <form onSubmit={handleSubmit} className="space-y-3">
      {error && (
        <div role="alert" className="alert alert-error alert-soft text-sm py-2">{error}</div>
      )}

      <fieldset>
        <legend className="text-sm font-medium text-base-content mb-1">{t('shop.reviews.your_rating')}</legend>
        <div
          className="rating rating-md"
          role="radiogroup"
          aria-label={t('shop.reviews.your_rating')}
          onMouseLeave={() => setHovered(0)}
        >
          {[1, 2, 3, 4, 5].map((star) => (
            <button
              key={star}
              type="button"
              role="radio"
              aria-checked={rating === star}
              aria-label={`${star} ${star === 1 ? t('shop.reviews.star') : t('shop.reviews.stars')}`}
              className={`mask mask-star-2 w-7 h-7 cursor-pointer transition-colors ${
                star <= displayRating ? 'bg-warning' : 'bg-base-300'
              }`}
              onMouseEnter={() => setHovered(star)}
              onClick={() => setRating(star)}
            />
          ))}
        </div>
      </fieldset>

      <div>
        <label htmlFor={`review-comment-${entityId}`} className="text-sm font-medium text-base-content block mb-1">
          {t('shop.reviews.comment')} <span className="text-base-content/50 font-normal">({t('common.optional')})</span>
        </label>
        <textarea
          id={`review-comment-${entityId}`}
          className="textarea textarea-bordered w-full text-sm"
          rows={3}
          maxLength={2000}
          value={comment}
          onChange={(e) => setComment(e.target.value)}
          placeholder={t('shop.reviews.comment_placeholder')}
        />
      </div>

      <div className="flex justify-end">
        <button
          type="submit"
          className="btn btn-primary btn-sm"
          disabled={mutation.isPending}
        >
          {mutation.isPending && <span className="loading loading-spinner loading-xs" />}
          {existing ? t('shop.reviews.update_review') : t('shop.reviews.submit_review')}
        </button>
      </div>
    </form>
  );
}

function ReviewCard({ review }) {
  const { t } = useTranslation();
  const date = review.created_at
    ? new Date(review.created_at).toLocaleDateString('ca-ES', { year: 'numeric', month: 'long', day: 'numeric' })
    : '';
  return (
    <div className="py-3 first:pt-0 last:pb-0">
      <div className="flex items-start gap-3">
        <div className="flex-none w-9 h-9 rounded-full bg-primary/10 text-primary text-sm font-bold flex items-center justify-center">
          {review.client_initials || '?'}
        </div>
        <div className="min-w-0 flex-1">
          <div className="flex flex-wrap items-center gap-2">
            <StarRating value={review.rating} size="xs" />
            {review.verified_purchase && (
              <span className="badge badge-success badge-soft badge-xs">{t('shop.reviews.verified_purchase')}</span>
            )}
            {date && <span className="text-xs text-base-content/50">{date}</span>}
          </div>
          {review.comment && (
            <p className="mt-1 text-sm text-base-content/80">{review.comment}</p>
          )}
        </div>
      </div>
    </div>
  );
}

/**
 * Generic reviews section. Pass either `productId` or `packId`.
 * Existing usage: <ReviewsSection productId={id} />
 * Pack usage:     <ReviewsSection packId={id} />
 */
export default function ReviewsSection({ productId, packId }) {
  const { t } = useTranslation();
  const { user, loading: authLoading } = useAuth();
  const queryClient = useQueryClient();
  const [page, setPage] = useState(1);
  const [showForm, setShowForm] = useState(false);

  const entityType = packId != null ? 'packs' : 'products';
  const entityId = packId ?? productId;
  const queryKeyPrefix = packId != null ? 'pack-reviews' : 'product-reviews';

  const reviewsQuery = useQuery({
    queryKey: [queryKeyPrefix, entityId, page],
    queryFn: async ({ signal }) => {
      const r = await api.get(`${entityType}/${entityId}/reviews`, { params: { page, per_page: 5 }, signal });
      return r.data;
    },
    enabled: !!entityId,
    staleTime: 30_000,
  });

  const mineQuery = useQuery({
    queryKey: [`${queryKeyPrefix}-mine`, entityId],
    queryFn: async ({ signal }) => {
      const r = await api.get(`${entityType}/${entityId}/reviews/mine`, { signal });
      return r.data;
    },
    enabled: !!entityId && !!user,
    staleTime: 30_000,
  });

  const agg = reviewsQuery.data?.aggregate;
  const reviews = reviewsQuery.data?.data ?? [];
  const meta = reviewsQuery.data?.meta;
  const myReview = mineQuery.data?.data ?? null;
  const canReview = mineQuery.data?.can_review ?? false;

  return (
    <section className="mt-8 border-t border-base-200 pt-6" aria-labelledby="reviews-heading">
      <h2 id="reviews-heading" className="text-lg font-semibold mb-4">{t('shop.reviews.title')}</h2>

      {/* Aggregate */}
      {agg && agg.reviews_count > 0 && (
        <div className="card bg-base-100 shadow border border-base-200 mb-6">
          <div className="card-body p-4 flex flex-col sm:flex-row gap-4">
            <div className="flex flex-col items-center justify-center gap-1 sm:w-28 shrink-0">
              <span className="text-4xl font-bold tabular-nums text-base-content">
                {Number(agg.avg_rating).toFixed(1)}
              </span>
              <StarRating value={agg.avg_rating} size="sm" />
              <span className="text-xs text-base-content/60">
                {t('shop.reviews.total_count', { count: agg.reviews_count })}
              </span>
            </div>
            <div className="flex-1 flex flex-col justify-center gap-1">
              {[5, 4, 3, 2, 1].map((s) => (
                <RatingBar key={s} star={s} count={agg.distribution?.[s] ?? 0} total={agg.reviews_count} />
              ))}
            </div>
          </div>
        </div>
      )}

      {/* Write a review section */}
      {!authLoading && (
        <div className="mb-6">
          {!user ? (
            <p className="text-sm text-base-content/70">
              <Link to="/login" className="link link-primary">{t('auth.login')}</Link>
              {' '}{t('shop.reviews.login_to_review')}
            </p>
          ) : canReview || myReview ? (
            <div className="card card-border bg-base-100">
              <div className="card-body p-4">
                {myReview ? (
                  <>
                    <div className="flex items-center justify-between gap-2 mb-2">
                      <h3 className="font-medium text-sm">{t('shop.reviews.your_review')}</h3>
                      <button
                        type="button"
                        className="btn btn-ghost btn-xs"
                        onClick={() => setShowForm((v) => !v)}
                      >
                        {showForm ? t('common.cancel') : t('common.edit')}
                      </button>
                    </div>
                    {showForm ? (
                      <ReviewForm
                        entityType={entityType}
                        entityId={entityId}
                        queryKeyPrefix={queryKeyPrefix}
                        existing={myReview}
                        onSuccess={() => setShowForm(false)}
                      />
                    ) : (
                      <div>
                        <div className="flex items-center gap-2 mb-1">
                          <StarRating value={myReview.rating} size="xs" />
                        </div>
                        {myReview.comment && (
                          <p className="text-sm text-base-content/80">{myReview.comment}</p>
                        )}
                      </div>
                    )}
                  </>
                ) : (
                  <>
                    <h3 className="font-medium text-sm mb-2">{t('shop.reviews.write_review')}</h3>
                    <ReviewForm
                      entityType={entityType}
                      entityId={entityId}
                      queryKeyPrefix={queryKeyPrefix}
                      onSuccess={() => {
                        queryClient.invalidateQueries({ queryKey: [queryKeyPrefix, entityId] });
                      }}
                    />
                  </>
                )}
              </div>
            </div>
          ) : (
            <p className="text-sm text-base-content/60 italic">{t('shop.reviews.must_purchase')}</p>
          )}
        </div>
      )}

      {/* List */}
      {reviewsQuery.isPending && (
        <div className="flex justify-center py-4" aria-live="polite" aria-busy="true">
          <span className="loading loading-spinner loading-md" />
        </div>
      )}

      {!reviewsQuery.isPending && reviews.length === 0 && (
        <p className="text-sm text-base-content/60 italic">{t('shop.reviews.no_reviews')}</p>
      )}

      {reviews.length > 0 && (
        <div className="card bg-base-100 shadow border border-base-200">
          <div className="card-body p-4 divide-y divide-base-200">
            {reviews.map((r) => <ReviewCard key={r.id} review={r} />)}
          </div>
        </div>
      )}

      {/* Pagination */}
      {meta && meta.last_page > 1 && (
        <div className="join flex justify-center mt-6">
          <button
            type="button"
            className="btn join-item btn-sm bg-base-100 border-base-300"
            disabled={page <= 1}
            onClick={() => setPage((p) => Math.max(1, p - 1))}
          >
            {t('shop.pagination.prev')}
          </button>
          <span className="join-item flex items-center justify-center px-4 py-2 h-8 text-sm text-base-content bg-base-100 border border-base-300">
            {t('shop.pagination.page')} {meta.current_page} {t('shop.pagination.of')} {meta.last_page}
          </span>
          <button
            type="button"
            className="btn join-item btn-sm bg-base-100 border-base-300"
            disabled={page >= meta.last_page}
            onClick={() => setPage((p) => Math.min(meta.last_page, p + 1))}
          >
            {t('shop.pagination.next')}
          </button>
        </div>
      )}
    </section>
  );
}
