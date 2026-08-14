import { defineConfig, devices } from '@playwright/test';

/**
 * Browser tests for the CodeMirror editor.
 *
 * These need a running Hatchery, so they are not part of `npm test`. Point
 * APP_BASE_URL at a server that has been seeded with `php artisan db:seed
 * --class=E2eSeeder`, or let the webServer block below start one.
 */
export default defineConfig({
	testDir: './tests/e2e',
	fullyParallel: false,
	workers: 1,
	forbidOnly: Boolean(process.env.CI),
	retries: process.env.CI ? 1 : 0,
	reporter: process.env.CI ? [['github'], ['list']] : 'list',
	use: {
		baseURL: process.env.APP_BASE_URL || 'http://127.0.0.1:8000',
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
	},
	projects: [
		{ name: 'chromium', use: { ...devices['Desktop Chrome'] } },
	],
	webServer: process.env.APP_BASE_URL ? undefined : {
		command: 'php artisan serve --host=127.0.0.1 --port=8000',
		url: 'http://127.0.0.1:8000/up',
		reuseExistingServer: !process.env.CI,
		timeout: 60_000,
	},
});
