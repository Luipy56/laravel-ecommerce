import React, { useEffect, useState } from 'react';
import { IS_DEMO_ENABLED } from '../config/demoMode';

export default function DemoPhaseModalLoader() {
  const [Modal, setModal] = useState(null);

  useEffect(() => {
    if (!IS_DEMO_ENABLED) return;
    import('./DemoPhaseModal').then((module) => {
      setModal(() => module.default);
    });
  }, []);

  if (!Modal) {
    return null;
  }

  return <Modal />;
}
