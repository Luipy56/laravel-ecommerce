import { useEffect, useState } from 'react';
import { getApiPendingCount, subscribeApiPending } from '../api';

/**
 * Number of in-flight HTTP requests on the shared `api` axios instance (all storefront/admin calls).
 */
export default function useApiPendingCount() {
  const [count, setCount] = useState(() => getApiPendingCount());

  useEffect(() => subscribeApiPending(setCount), []);

  return count;
}
