import React from 'react';

/**
 * Catches uncaught JS errors in the component tree and renders a fallback.
 * Pass a `fallback` render prop: <ErrorBoundary fallback={(error, reset) => <ErrorPage error={error} resetError={reset} />}>
 */
export default class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null };
    this.reset = this.reset.bind(this);
  }

  static getDerivedStateFromError(error) {
    return { hasError: true, error };
  }

  reset() {
    this.setState({ hasError: false, error: null });
  }

  render() {
    if (this.state.hasError) {
      if (typeof this.props.fallback === 'function') {
        return this.props.fallback(this.state.error, this.reset);
      }
      return this.props.fallback ?? null;
    }
    return this.props.children;
  }
}
