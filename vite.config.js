// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
	resolve: {
		alias: {
			// bootstrap-sass still points $icon-font-path at a webpack style "~" path.
			'~bootstrap-sass': fileURLToPath(new URL('./node_modules/bootstrap-sass', import.meta.url)),
		},
	},
	plugins: [
		laravel({
			input: [
				'resources/assets/sass/app.scss',
				'resources/assets/js/app.js',
			],
			refresh: true,
		}),
	],
	css: {
		preprocessorOptions: {
			scss: {
				loadPaths: ['node_modules'],
				// bootstrap-sass is Bootstrap 3 and has not been touched since
				// 2019: it uses lighten(), darken(), if() and the global builtins
				// throughout, and no release is coming to change that. Its
				// deprecations are noise we cannot act on, so only report the ones
				// from our own files.
				quietDeps: true,
				// app.scss still reaches bootstrap-sass with @import, because it
				// was written for that and does not work as a module.
				silenceDeprecations: ['import'],
			},
		},
	},
	build: {
		// The editor chunk is CodeMirror with five languages and the Vim and
		// Emacs keymaps, about 800 kB minified. It is loaded on demand, only on
		// pages with an editor, and splitting it further would not change what
		// those pages download. Warn if it grows past a round number instead.
		chunkSizeWarningLimit: 1000,
	},
});
