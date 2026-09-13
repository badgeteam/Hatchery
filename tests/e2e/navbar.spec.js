// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

import { expect, test } from '@playwright/test';

// The stylesheet is Bootstrap 3 and the JavaScript is Bootstrap 5, which
// only listens for data-bs-* attributes. A stray data-toggle left the
// hamburger dead on phones from 2022 until someone noticed, and nothing here
// would have failed: every other test runs at desktop width, where the
// button is not even rendered.
test.describe('navbar on a phone', () => {
	test.use({ viewport: { width: 400, height: 800 } });

	test('the hamburger opens and closes the menu', async ({ page }) => {
		// The front page is the splash layout, which has no navbar; the login
		// page is public and uses the full one.
		await page.goto('/login');

		const button = page.locator('button.navbar-toggle');
		const menu = page.locator('#app-navbar-collapse');

		await expect(button).toBeVisible();
		await expect(menu).toBeHidden();

		await button.click();
		await expect(menu).toBeVisible();
		await expect(menu.getByRole('link', { name: 'Eggs' })).toBeVisible();
		await expect(button).toHaveAttribute('aria-expanded', 'true');

		// Bootstrap ignores clicks while the slide is still running, and only
		// swaps .collapsing for .show once it has finished.
		await expect(menu).toHaveClass(/\bshow\b/);
		await button.click();
		await expect(menu).toBeHidden();
		await expect(button).toHaveAttribute('aria-expanded', 'false');
	});
});
