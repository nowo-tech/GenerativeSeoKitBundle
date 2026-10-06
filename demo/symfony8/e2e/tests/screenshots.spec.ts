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
});
