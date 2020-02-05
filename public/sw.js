const cacheName = 'hatchery::20200206';

self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(cacheName).then(cache => {
      return cache.addAll([
        '/',
        '/eggs/',
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
        '/0.js',
        '/2.js',
        '/icons/blank.gif',
        '/icons/back.gif',
        '/icons/tar.gif',
        '/icons/compressed.gif'
      ]).then(() => self.skipWaiting());
    })
  );
});
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.open(cacheName).then(cache => {
      return cache.match(event.request).then(res => {
        return res || fetch(event.request)
      });
    })
  );
});
