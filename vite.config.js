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
				// bootstrap-sass and codemirror are still on the legacy @import API.
				loadPaths: ['node_modules'],
				silenceDeprecations: ['import', 'global-builtin', 'legacy-js-api'],
			},
		},
	},
});
