import { useQuery } from '@tanstack/react-query';
import { api } from '../api';

export const FAVORITES_QUERY_PREFIX = ['favorites'];

/**
 * Lightweight favorite id sets for the logged-in verified client.
 * @param {boolean} enabled — typically user?.email_verified
 */
export function useFavoriteIdsQuery(enabled) {
  return useQuery({
    queryKey: [...FAVORITES_QUERY_PREFIX, 'ids'],
    queryFn: async ({ signal }) => {
      const r = await api.get('favorites/ids', { signal });
      if (!r.data?.success) {
        throw new Error('favorites ids');
      }
      return r.data.data;
    },
    enabled: Boolean(enabled),
    staleTime: 30_000,
  });
}
