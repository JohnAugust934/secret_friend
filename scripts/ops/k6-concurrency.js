import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';
const GROUP_ID = __ENV.GROUP_ID || '1';
const INVITE_TOKEN = __ENV.INVITE_TOKEN || 'ABC123';

export const options = {
  scenarios: {
    invite_burst: {
      executor: 'constant-vus',
      vus: 20,
      duration: '20s',
      exec: 'inviteFlow',
    },
    draw_burst: {
      executor: 'constant-vus',
      vus: 5,
      duration: '20s',
      exec: 'drawFlow',
      startTime: '2s',
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.05'],
    http_req_duration: ['p(95)<1200'],
  },
};

export function inviteFlow() {
  const res = http.get(`${BASE_URL}/invite/${INVITE_TOKEN}`);
  check(res, {
    'invite endpoint responds': (r) => [200, 302, 404].includes(r.status),
  });
  sleep(0.5);
}

export function drawFlow() {
  const res = http.post(`${BASE_URL}/groups/${GROUP_ID}/draw`, {}, {
    redirects: 0,
    headers: {
      // This script assumes authenticated context cookie when needed.
      // Provide COOKIE env if running against protected environment.
      Cookie: __ENV.COOKIE || '',
    },
  });

  check(res, {
    'draw endpoint responds': (r) => [200, 302, 403, 419].includes(r.status),
  });
  sleep(1);
}
