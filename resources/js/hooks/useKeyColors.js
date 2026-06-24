import { useQuery } from '@tanstack/react-query';
import { api } from '../api';

export function useKeyColors(enabled = true) {
  return useQuery({
    queryKey: ['key-colors'],
    queryFn: async ({ signal }) => {
      const { data } = await api.get('key-colors', { signal });
      if (!data?.success) return [];
      return data.data || [];
    },
    enabled,
    staleTime: 5 * 60_000,
  });
}
