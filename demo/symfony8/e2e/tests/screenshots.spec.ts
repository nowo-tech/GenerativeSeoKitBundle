import { test, expect } from '@playwright/test';
import { mkdirSync } from 'node:fs';
import { resolve } from 'node:path';

const outDir = process.env.SCREENSHOT_DIR
  ? resolve(process.env.SCREENSHOT_DIR)
  : resolve(__dirname, '../../../../docs/images/demo');

test.beforeAll(() => {
  mkdirSync(outDir, { recursive: true });
});

test.describe('feature screenshots', () => {
  test('overview — use-case panel', async ({ page }) => {
    await page.goto('/');
    const panel = page.locator('[data-screenshot-target]').first();
    await expect(panel).toBeVisible();
    await panel.screenshot({ path: resolve(outDir, 'overview.png') });
  });

  test('llms — generated /llms.txt (citations, docs, optional, related)', async ({ page }) => {
    await page.setViewportSize({ width: 960, height: 600 });
    const response = await page.goto('/llms.txt');
    expect(response?.status()).toBe(200);
    await expect(page.locator('body')).toContainText('## Citations');
    await page.screenshot({ path: resolve(outDir, 'llms.png'), fullPage: true });
  });
});
