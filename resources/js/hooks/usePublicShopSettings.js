import { useQuery } from '@tanstack/react-query';
import { api } from '../api';

const queryKey = ['shop', 'public-settings'];

export function usePublicShopSettings() {
  return useQuery({
    queryKey,
    queryFn: async () => {
      const { data } = await api.get('shop/public-settings');
      return data?.data ?? {};
    },
    staleTime: 60_000,
  });
}
