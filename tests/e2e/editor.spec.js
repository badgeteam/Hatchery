import { expect, test } from '@playwright/test';

const EMAIL = 'e2e@badge.team';
const PASSWORD = 'e2e-password';
const PROJECT = 'e2e_editor';

/** Log in and land on the project list. */
async function login(page) {
	await page.goto('/login');
	await page.fill('input[name=email]', EMAIL);
	await page.fill('input[name=password]', PASSWORD);
	await Promise.all([
		page.waitForURL((url) => !url.pathname.startsWith('/login')),
		page.click('button[type=submit]'),
	]);
}

/** Find the README the seeder created, and return its edit URL. */
async function readmeEditUrl(page) {
	await page.goto(`/projects/${PROJECT}/edit`);
	const link = page.locator('a[href*="/files/"][href$="/edit"]').first();
	await expect(link).toBeVisible();

	return await link.getAttribute('href');
}

test.describe('file editor', () => {
	test.beforeEach(async ({ page }) => {
		await login(page);
	});

	test('replaces the textarea with a CodeMirror editor', async ({ page }) => {
		await page.goto(await readmeEditUrl(page));
		await expect(page.locator('.cm-editor')).toHaveCount(1);

		const textarea = page.locator('textarea#content');
		await expect(textarea).toHaveCSS('display', 'none');
		// The textarea has to stay inside the form so posts still carry it.
		expect(await textarea.evaluate((t) => Boolean(t.form))).toBe(true);
	});

	test('seeds the editor from the file and highlights it as markdown', async ({ page }) => {
		await page.goto(await readmeEditUrl(page));
		await expect(page.locator('.cm-content')).toContainText('Hello');
		await expect(page.locator('.cm-content')).toHaveAttribute('data-language', 'markdown');
		await expect(page.locator('.cm-gutters')).toHaveCount(1);
	});

	test('writes edits back into the textarea', async ({ page }) => {
		await page.goto(await readmeEditUrl(page));
		await page.click('.cm-content');
		await page.keyboard.type('EDITED');

		await expect(page.locator('textarea#content')).toHaveValue(/EDITED/);
	});

	test('saves what was typed into the editor', async ({ page }) => {
		const url = await readmeEditUrl(page);
		const marker = 'saved-by-e2e';

		await page.goto(url);
		await page.click('.cm-content');
		await page.keyboard.type(marker);
		await page.click('button[type=submit]');

		await page.goto(url);
		await expect(page.locator('.cm-content')).toContainText(marker);
	});
});

test.describe('public file view', () => {
	test('renders a read only editor for anonymous visitors', async ({ page, browser }) => {
		const owner = await browser.newPage();
		await login(owner);
		const editUrl = await readmeEditUrl(owner);
		await owner.close();

		await page.goto(editUrl.replace('/edit', ''));
		await expect(page.locator('.cm-editor')).toHaveCount(1);
		await expect(page.locator('.cm-content')).toHaveAttribute('contenteditable', 'false');
		await expect(page.locator('.cm-content')).toHaveAttribute('aria-readonly', 'true');
	});
});

test.describe('asset loading', () => {
	test('does not pull the editor bundle into pages without an editor', async ({ page }) => {
		const editorChunks = [];
		page.on('request', (request) => {
			if (/\/editor-[^/]+\.js$/.test(request.url())) {
				editorChunks.push(request.url());
			}
		});

		await page.goto('/');
		await page.waitForLoadState('networkidle');

		expect(editorChunks).toEqual([]);
	});

	test('gives inline blade scripts a working jQuery', async ({ page }) => {
		const errors = [];
		page.on('pageerror', (error) => errors.push(error.message));

		await login(page);
		await page.goto(`/projects/${PROJECT}/edit`);
		await page.waitForLoadState('networkidle');

		expect(await page.evaluate(() => typeof window.$)).toBe('function');
		expect(errors.filter((e) => e.includes('$ is not defined'))).toEqual([]);
	});

	test('initialises Dropzone even though app.js also handles load', async ({ page }) => {
		const errors = [];
		page.on('pageerror', (error) => errors.push(error.message));

		await login(page);
		await page.goto(`/projects/${PROJECT}/edit`);
		await page.waitForLoadState('networkidle');

		// The blade used to assign window.onload, which replaced the handler
		// app.js installs (and vice versa). Both listen now, so both run.
		await expect(page.locator('#uploader.dropzone')).toHaveCount(1);
		expect(errors).toEqual([]);
	});
});

test.describe('unsaved changes warning', () => {
	// The listener that clears window.onbeforeunload used to be attached to a
	// hardcoded #content_form, which the new project form does not have, so
	// saving a new egg always warned about losing changes.
	test('clears the warning when the new project form is submitted', async ({ page }) => {
		await login(page);
		await page.goto('/projects/create');
		await page.waitForSelector('.cm-editor');

		expect(await page.evaluate(() => typeof window.onbeforeunload)).toBe('function');

		await page.evaluate(() => {
			const form = document.querySelector('textarea#content').form;
			form.addEventListener('submit', (e) => e.preventDefault(), { once: true });
			form.requestSubmit();
		});

		expect(await page.evaluate(() => window.onbeforeunload)).toBeNull();
	});

	test('clears the warning when a file is saved', async ({ page }) => {
		await login(page);
		await page.goto(await readmeEditUrl(page));
		await page.waitForSelector('.cm-editor');

		await page.evaluate(() => {
			const form = document.querySelector('textarea#content').form;
			form.addEventListener('submit', (e) => e.preventDefault(), { once: true });
			form.requestSubmit();
		});

		expect(await page.evaluate(() => window.onbeforeunload)).toBeNull();
	});
});
