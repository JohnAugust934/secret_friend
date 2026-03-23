import { test, expect } from 'playwright/test';

test('landing page loads and exposes auth actions', async ({ page }) => {
  await page.goto('/');
  await expect(page.getByRole('link', { name: /Entrar/i })).toBeVisible();
});

test('invite page for guests shows intermediate invite screen', async ({ page }) => {
  // Requires token existing in DB; keep as smoke contract test for route rendering behavior.
  // Replace ABC123 with a valid token in CI seeded data if needed.
  const response = await page.goto('/invite/ABC123');
  if (response && response.status() === 404) {
    test.skip(true, 'Token not present in this environment');
  }

  await expect(page.getByText(/voce recebeu um convite/i)).toBeVisible();
});
