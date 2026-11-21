import { test, expect } from '@playwright/test';

test.describe('demo happy path', () => {
  test('home responds and shows the main UI', async ({ page }) => {
    const response = await page.goto('/');
    expect(response?.ok()).toBeTruthy();
    await expect(page.getByTestId('demo-platform')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Generative SEO Kit', level: 1 })).toBeVisible();
  });

  test('llms.txt is plain text', async ({ request }) => {
    const response = await request.get('/llms.txt');
    expect(response.ok()).toBeTruthy();
    expect(response.headers()['content-type']).toContain('text/plain');
    expect(await response.text()).toContain('Generative SEO Kit Demo');
  });
});
