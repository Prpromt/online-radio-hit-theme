export default {
  async fetch(request, env) {
    const url = new URL(request.url);

    if (url.pathname === '/health') {
      return new Response('ok', {
        headers: {
          'content-type': 'text/plain; charset=utf-8',
          'cache-control': 'no-store'
        }
      });
    }

    // Public artist levels page. Use a directory index asset so Cloudflare
    // does not normalize /artist-levels.html back to /artist-levels/.
    if (url.pathname === '/artist-levels' || url.pathname === '/artist-levels/') {
      const assetUrl = new URL('/artist-levels/index.html', request.url);
      const assetRequest = new Request(assetUrl.toString(), request);
      const assetResponse = await env.ASSETS.fetch(assetRequest);
      const headers = new Headers(assetResponse.headers);
      headers.set('Cache-Control', 'no-store, max-age=0');
      headers.set('X-Content-Type-Options', 'nosniff');
      headers.set('Referrer-Policy', 'strict-origin-when-cross-origin');
      headers.set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
      headers.set('Content-Security-Policy', "default-src 'self'; img-src 'self' data:; media-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; font-src 'self' data:; object-src 'none'; base-uri 'none'; frame-ancestors *");
      return new Response(assetResponse.body, {
        status: assetResponse.status,
        statusText: assetResponse.statusText,
        headers
      });
    }

    const response = await env.ASSETS.fetch(request);
    const headers = new Headers(response.headers);
    headers.set('X-Content-Type-Options', 'nosniff');
    headers.set('Referrer-Policy', 'strict-origin-when-cross-origin');
    headers.set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    headers.set('Content-Security-Policy', "default-src 'self'; img-src 'self' data:; media-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; font-src 'self' data:; object-src 'none'; base-uri 'none'; frame-ancestors *");

    if (url.pathname === '/' || url.pathname.startsWith('/embed/') || url.pathname.startsWith('/embed-code/')) {
      headers.set('Cache-Control', 'no-store, max-age=0');
    }

    return new Response(response.body, {
      status: response.status,
      statusText: response.statusText,
      headers
    });
  }
};
