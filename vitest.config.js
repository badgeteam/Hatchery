// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

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
		coverage: {
			provider: 'v8',
			reporter: ['text-summary', 'lcov'],
			reportsDirectory: 'coverage/js',
			// Report on all the source, not just what a test happened to
			// import, so untested files show up as untested.
			include: ['resources/assets/js/**/*.js'],
		},
	},
});
