import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { navigationRef } from './routerBridge';

/**
 * Exposes React Router navigate to axios interceptors (e.g. 419 redirect).
 */
export default function NavigationSetter() {
  const navigate = useNavigate();

  useEffect(() => {
    navigationRef.current = navigate;
    return () => {
      navigationRef.current = null;
    };
  }, [navigate]);

  return null;
}
