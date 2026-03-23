import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.notify = (message, type = 'info') => {
  window.dispatchEvent(new CustomEvent('notify', { detail: { message, type } }));
};

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const sendTelemetry = async (payload) => {
  try {
    await fetch('/telemetry/frontend', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify(payload),
      keepalive: true,
    });
  } catch {
    // best effort only
  }
};

window.addEventListener('error', (event) => {
  sendTelemetry({
    type: 'js_error',
    message: event.message || 'unknown_error',
    url: event.filename || window.location.href,
    line: event.lineno || null,
    column: event.colno || null,
    stack: event.error?.stack || null,
  });
});

window.addEventListener('unhandledrejection', (event) => {
  const reason = event.reason;
  sendTelemetry({
    type: 'promise_rejection',
    message: typeof reason === 'string' ? reason : (reason?.message || 'unhandled_rejection'),
    url: window.location.href,
    stack: reason?.stack || null,
  });
});

window.addEventListener('load', () => {
  const nav = performance.getEntriesByType('navigation')[0];

  if (!nav) return;

  sendTelemetry({
    type: 'web_perf',
    message: 'navigation_timing',
    url: window.location.href,
    metrics: {
      domContentLoadedMs: Math.round(nav.domContentLoadedEventEnd),
      loadMs: Math.round(nav.loadEventEnd),
      transferSize: nav.transferSize || null,
    },
  });
});

Alpine.start();
