const cacheName = 'hatchery::22061217';

self.addEventListener('install', e => {
	e.waitUntil(
		caches.open(cacheName).then(cache => {
			return cache.addAll([
				'/svg/500.svg',
				'/svg/503.svg',
				'/svg/403.svg',
				'/svg/404.svg',
				'/css/app.css',
				'/js/app.js',
				'/img/isok.gif',
				'/img/rulez.gif',
				'/img/sucks.gif',
				'/img/alert.gif',
				'/img/bs.png',
				'/fonts/vendor/bootstrap-sass/bootstrap/glyphicons-halflings-regular.woff',
				'/fonts/vendor/bootstrap-sass/bootstrap/glyphicons-halflings-regular.eot',
				'/fonts/vendor/bootstrap-sass/bootstrap/glyphicons-halflings-regular.woff2',
				'/fonts/vendor/bootstrap-sass/bootstrap/glyphicons-halflings-regular.ttf',
				'/fonts/vendor/bootstrap-sass/bootstrap/glyphicons-halflings-regular.svg',
				'/mix-manifest.json',
				'/js/806.js',
				'/js/867.js',
				'/vendor/webauthn/webauthn.js',
				'/vendor/horizon/app.css',
				'/vendor/horizon/img/horizon.svg',
				'/vendor/horizon/img/favicon.png',
				'/vendor/horizon/img/sprite.svg',
				'/vendor/horizon/app-dark.css',
				'/vendor/horizon/mix-manifest.json',
				'/vendor/horizon/app.js',
				'/img/git.svg',
				'/img/collab.svg',
				'/vendor/livewire/livewire.js'
			]).then(() => self.skipWaiting());
		})
	);
});

self.addEventListener('fetch', event => {
	event.respondWith(
		caches.open(cacheName).then(cache => {
			return cache.match(event.request).then(res => {
				return res || fetch(event.request);
			});
		})
	);
});
