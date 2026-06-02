import { useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { navigationRef } from './routerBridge';

/**
 * Exposes React Router navigate to axios interceptors (e.g. 419 redirect).
 * Also scrolls to top on every route change so individual links don't need
 * manual window.scrollTo calls (which cause visible flicker).
 */
export default function NavigationSetter() {
  const navigate = useNavigate();
  const { pathname } = useLocation();

  useEffect(() => {
    navigationRef.current = navigate;
    return () => {
      navigationRef.current = null;
    };
  }, [navigate]);

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [pathname]);

  return null;
}
