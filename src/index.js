const securityHeaders = () => ({
  'X-Content-Type-Options': 'nosniff',
  'Referrer-Policy': 'strict-origin-when-cross-origin',
  'Permissions-Policy': 'camera=(), microphone=(), geolocation=()',
  'Content-Security-Policy': "default-src 'self'; img-src 'self' data:; media-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; font-src 'self' data:; object-src 'none'; base-uri 'none'; frame-ancestors *"
});

function previewHome(response) {
  return new HTMLRewriter()
    .on('head', { element(el) {
      el.append('<link rel="stylesheet" href="/assets/mobile-premium.css?v=3">', { html: true });
      el.append('<link rel="stylesheet" href="/assets/mobile-rebuild.css?v=3">', { html: true });
      el.append('<link rel="stylesheet" href="/assets/final-polish.css?v=2">', { html: true });
      el.append('<link rel="stylesheet" href="/assets/web-polish.css?v=5">', { html: true });
      el.append('<link rel="stylesheet" href="/assets/player-layout-v2.css?v=2">', { html: true });
      el.append('<link rel="stylesheet" href="/assets/homepage-polish.css?v=1.0">', { html: true });
      el.append('<link rel="stylesheet" href="/assets/homepage-polish-v2.css?v=1.0">', { html: true });
      el.append('<link rel="stylesheet" href="/assets/hero-player-v2.css?v=1.0">', { html: true });
      el.append('<link rel="stylesheet" href="/assets/player-final.css?v=1.0">', { html: true });
      el.append('<link rel="stylesheet" href="/assets/internal-pages-polish.css?v=1.0">', { html: true });
      el.append('<script src="/assets/app.js?v=16" defer></script>', { html: true });
    }})
    .on('nav', { element(el) { if (el.getAttribute('class') === 'site-nav') el.setAttribute('id', 'siteNav'); }})
    .on('.site-nav a[href="#promotion-directions"]', { element(el) {
      el.setInnerContent('Возможности артистов'); el.setAttribute('href', '/artist-levels/'); el.setAttribute('class', 'artist-levels-nav');
    }})
    .on('audio', { element(el) { el.removeAttribute('src'); }})
    .transform(response);
}

export default { async fetch(request, env) {
  const url = new URL(request.url);
  if (url.pathname === '/health') {
    const headers = new Headers(securityHeaders()); headers.set('content-type', 'text/plain; charset=utf-8'); headers.set('cache-control', 'no-store');
    return new Response('ok', { headers });
  }
  if (url.pathname === '/artist-levels' || url.pathname === '/artist-levels/') {
    const levelsResponse = await env.ASSETS.fetch(new Request(new URL('/artist-levels-preview.html', request.url), { method: 'GET', headers: request.headers }));
    const headers = new Headers(levelsResponse.headers); Object.entries(securityHeaders()).forEach(([key,value]) => headers.set(key,value)); headers.set('Cache-Control','no-store', 'max-age=0');
    return new Response(levelsResponse.body, { status: levelsResponse.status, statusText: levelsResponse.statusText, headers });
  }
  const response = await env.ASSETS.fetch(request); const headers = new Headers(response.headers); Object.entries(securityHeaders()).forEach(([key,value]) => headers.set(key,value));
  if (url.pathname === '/' || url.pathname.startsWith('/embed/') || url.pathname.startsWith('/embed-code/') || url.pathname.startsWith('/assets/app.js') || url.pathname.startsWith('/assets/player-hotfix.css') || url.pathname.startsWith('/assets/mobile-fixes.css') || url.pathname.startsWith('/theme-preview.css') || url.pathname.startsWith('/assets/mobile-premium.css') || url.pathname.startsWith('/assets/mobile-rebuild.css') || url.pathname.startsWith('/assets/final-polish.css') || url.pathname.startsWith('/assets/web-polish.css') || url.pathname.startsWith('/assets/player-layout-v2.css') || url.pathname.startsWith('/assets/homepage-polish.css') || url.pathname.startsWith('/assets/homepage-polish-v2.css') || url.pathname.startsWith('/assets/hero-player-v2.css') || url.pathname.startsWith('/assets/player-final.css') || url.pathname.startsWith('/assets/internal-pages-polish.css')) headers.set('Cache-Control','no-store, max-age=0');
  const output = url.pathname === '/' ? previewHome(new Response(response.body, { status: response.status, statusText: response.statusText, headers })) : new Response(response.body, { status: response.status, statusText: response.statusText, headers });
  return output;
}};