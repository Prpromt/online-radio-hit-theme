export default {
  async fetch(request, env) {
    const url = new URL(request.url);
    if (url.pathname === '/health') return new Response('ok', {headers:{'content-type':'text/plain; charset=utf-8'}});
    return env.ASSETS.fetch(request);
  }
};
