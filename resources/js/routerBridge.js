/**
 * Set from a component inside BrowserRouter so axios interceptors can navigate (e.g. 419).
 * @type {{ current: import('react-router-dom').NavigateFunction | null }}
 */
export const navigationRef = { current: null };
