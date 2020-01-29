const cacheName = 'hatchery::20190129::static';

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
        '/1.js',
        '/2.js',
        '/3.js',
        '/4.js',
        '/5.js',
        '/badge/sha2017',
        '/badge/disobey2019',
        '/badge/hackerhotel2019',
        '/badge/card10',
        '/badge/campzone2019',
        '/badge/disobey2020',
        '/badge/troopers2019',
        '/badge/fri3d2018',
        '/badge/ohs2018',
        '/badge/hacktivity2019',
        '/badge/fri3d2020',
        '/login',
        '/register',
        '/projects',
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