import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  vus: 20,
  duration: '30s',
  thresholds: {
    http_req_duration: ['p(95)<500'],
    http_req_failed: ['rate<0.01'],
  },
};

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export default function () {
  const health = http.get(`${BASE_URL}/healthz`);
  check(health, {
    'health status is 200 or 503': (r) => r.status === 200 || r.status === 503,
  });

  const home = http.get(`${BASE_URL}/`);
  check(home, {
    'home is 200': (r) => r.status === 200,
  });

  sleep(1);
}
