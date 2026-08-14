import { defineConfig } from 'vitest/config';

/**
 * Kept separate from vite.config.js on purpose: that config loads
 * laravel-vite-plugin, which refuses to start under CI, and the unit tests
 * have no use for it.
 */
export default defineConfig({
	test: {
		include: ['tests/js/**/*.test.js'],
		exclude: ['tests/e2e/**', 'node_modules/**'],
		environment: 'node',
	},
});
