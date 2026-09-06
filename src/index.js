const securityHeaders = () => ({
  'X-Content-Type-Options': 'nosniff',
  'Referrer-Policy': 'strict-origin-when-cross-origin',
  'Permissions-Policy': 'camera=(), microphone=(), geolocation=()',
  'Content-Security-Policy': "default-src 'self'; img-src 'self' data:; media-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; font-src 'self' data:; object-src 'none'; base-uri 'none'; frame-ancestors *"
});

const artistLevelsHtml = `<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Возможности размещения на радио — Онлайн Радио Хит</title><style>body{margin:0;background:#f5f6f8;color:#111216;font-family:Arial,sans-serif}main{min-height:100vh;padding:60px 5vw}section{max-width:1180px;margin:auto}.eyebrow{font-size:11px;font-weight:900;letter-spacing:.15em;color:#ff2875;text-transform:uppercase}h1{font-size:clamp(48px,8vw,96px);line-height:.92;letter-spacing:-.06em;margin:18px 0}h1 span{color:#ff2875}.lead{max-width:720px;font-size:18px;line-height:1.65;color:#68707c}.levels{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:50px}.level{background:#fff;border:1px solid #e4e6eb;border-radius:24px;padding:28px;min-height:330px}.featured{border:2px solid #ff2875;box-shadow:0 18px 50px #11121614}.level h2{font-size:28px;margin:14px 0}.level strong{color:#ff2875}.level p,.level small{color:#68707c;line-height:1.6}.how{margin-top:60px;background:#111216;color:#fff;border-radius:24px;padding:40px}.how-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}.how b{color:#dfff18}.how p{color:#aeb4bf;line-height:1.6}@media(max-width:800px){.levels,.how-grid{grid-template-columns:1fr}main{padding:40px 20px}.how{padding:28px}}</style></head><body><main><section><div class="eyebrow">ОНЛАЙН РАДИО ХИТ</div><h1>Больше музыки.<br><span>Больше возможностей.</span></h1><p class="lead">Редакция радио размещает и форматирует песни. Артист не загружает контент самостоятельно — команда радио отвечает за размещение и развитие присутствия артиста.</p><div class="levels"><article class="level"><b>УРОВЕНЬ 1</b><h2>Песня в эфире</h2><strong>1 песня</strong><p>Размещение песни в эфире Онлайн Радио Хит и участие в разделе новых песен.</p><hr><b>Размещение в эфире</b><p>Новые песни</p><small>Цена: уточняется</small></article><article class="level featured"><b>УРОВЕНЬ 2 · ПЕРВЫЙ ПЛАТНЫЙ</b><h2>Страница артиста</h2><strong>2–5 песен</strong><p>Все возможности первого уровня плюс публичная страница артиста, чарт и расширенная статистика.</p><hr><b>Публичная страница</b><p>Участие в чарте</p><p>Расширенная статистика</p><small>Цена: уточняется</small></article><article class="level"><b>УРОВЕНЬ 3</b><h2>Больше возможностей</h2><strong>6+ песен</strong><p>Все возможности второго уровня плюс будущие инструменты продвижения, аналитики и расширенного присутствия.</p><hr><b>Всё из уровня 2</b><p>Дополнительные возможности</p><p>Расширенное присутствие</p><small>Цена: будет определена</small></article></div><div class="how"><h2>Как это работает</h2><div class="how-grid"><div><b>01</b><h3>Вы передаёте песню</h3><p>Редакция получает материал и рассматривает его для размещения.</p></div><div><b>02</b><h3>Мы размещаем</h3><p>Команда радио добавляет песню в эфир и необходимые разделы.</p></div><div><b>03</b><h3>Растёт присутствие</h3><p>С увеличением количества песен открываются новые уровни возможностей.</p></div></div></div></section></main></body></html>`;

export default {
  async fetch(request, env) {
    const url = new URL(request.url);

    if (url.pathname === '/health') {
      return new Response('ok', { headers: { 'content-type': 'text/plain; charset=utf-8', 'cache-control': 'no-store', ...securityHeaders() } });
    }

    if (url.pathname === '/artist-levels' || url.pathname === '/artist-levels/') {
      return new Response(artistLevelsHtml, {
        status: 200,
        headers: { 'content-type': 'text/html; charset=utf-8', 'cache-control': 'no-store, max-age=0', ...securityHeaders() }
      });
    }

    const response = await env.ASSETS.fetch(request);
    const headers = new Headers(response.headers);
    Object.entries(securityHeaders()).forEach(([key, value]) => headers.set(key, value));

    if (url.pathname === '/' || url.pathname.startsWith('/embed/') || url.pathname.startsWith('/embed-code/')) {
      headers.set('Cache-Control', 'no-store, max-age=0');
    }

    return new Response(response.body, { status: response.status, statusText: response.statusText, headers });
  }
};
