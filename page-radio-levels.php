<?php
/*
Template Name: Уровни размещения на радио
*/
if (!defined('ABSPATH')) exit;
get_header();
?>
<style>
.orh-levels-page{--lp-bg:#f5f6f8;--lp-card:#fff;--lp-ink:#111216;--lp-muted:#68707c;--lp-line:#e3e6eb;--lp-pink:#ff2875;--lp-acid:#dfff18;--lp-dark:#15171d;max-width:1400px;margin:0 auto;background:var(--lp-bg);color:var(--lp-ink);font-family:Arial,Helvetica,sans-serif;overflow:hidden}
.orh-levels-hero{position:relative;padding:92px 6vw 82px;background:radial-gradient(circle at 78% 28%,#fff 0,#f8f9fa 35%,#eef0f3 100%);border-bottom:1px solid var(--lp-line)}
.orh-levels-hero:after{content:"";position:absolute;width:380px;height:380px;border-radius:50%;right:-120px;top:-150px;background:var(--lp-acid);opacity:.55;filter:blur(2px)}
.orh-levels-eyebrow{position:relative;z-index:1;font-size:11px;font-weight:900;letter-spacing:.18em;text-transform:uppercase}.orh-levels-eyebrow i{display:inline-block;width:8px;height:8px;background:var(--lp-pink);border-radius:50%;margin-right:9px}
.orh-levels-hero h1{position:relative;z-index:1;max-width:950px;margin:20px 0 25px;font-size:clamp(52px,7vw,94px);line-height:.9;letter-spacing:-.065em}.orh-levels-hero h1 em{font-style:normal;color:var(--lp-pink)}
.orh-levels-lead{position:relative;z-index:1;max-width:690px;color:var(--lp-muted);font-size:18px;line-height:1.6;margin:0}.orh-levels-note{position:relative;z-index:1;margin-top:27px;font-size:11px;font-weight:800;color:#30343c}
.orh-levels-wrap{padding:76px 5vw}.orh-levels-intro{display:flex;justify-content:space-between;gap:40px;align-items:end;margin-bottom:30px}.orh-levels-intro h2{font-size:48px;line-height:.95;letter-spacing:-.06em;margin:0;max-width:600px}.orh-levels-intro p{max-width:470px;color:var(--lp-muted);font-size:13px;line-height:1.65;margin:0}
.orh-levels-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.orh-level-card{position:relative;background:var(--lp-card);border:1px solid var(--lp-line);border-radius:25px;padding:30px;min-height:520px;box-shadow:0 18px 50px rgba(25,28,35,.07);display:flex;flex-direction:column}.orh-level-card.featured{border:2px solid var(--lp-pink);transform:translateY(-8px);box-shadow:0 25px 65px rgba(255,40,117,.13)}
.orh-level-card .badge{display:inline-flex;align-self:flex-start;padding:8px 11px;border-radius:999px;background:#f1f2f4;color:#515863;font-size:9px;font-weight:900;letter-spacing:.12em;text-transform:uppercase}.orh-level-card.featured .badge{background:var(--lp-pink);color:#fff}.orh-level-card.future .badge{background:var(--lp-acid);color:#111}
.orh-level-card h3{font-size:34px;letter-spacing:-.045em;margin:24px 0 7px}.orh-level-card .count{color:var(--lp-pink);font-size:12px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.orh-level-card>p{color:var(--lp-muted);font-size:13px;line-height:1.6;min-height:63px}.orh-level-card ul{list-style:none;padding:0;margin:12px 0 28px}.orh-level-card li{padding:12px 0;border-bottom:1px solid var(--lp-line);font-size:12px;font-weight:800;display:flex;gap:10px;align-items:flex-start}.orh-level-card li:before{content:"✓";color:var(--lp-pink);font-weight:900}.orh-level-card .coming{color:#747b86;font-weight:700}.orh-level-card .coming:before{content:"+"}.orh-level-price{margin-top:auto;padding-top:18px;border-top:1px solid var(--lp-line);font-size:10px;color:var(--lp-muted);letter-spacing:.08em;text-transform:uppercase}.orh-level-price strong{display:block;color:var(--lp-ink);font-size:21px;letter-spacing:-.03em;margin-top:6px}
.orh-levels-how{padding:80px 5vw;background:var(--lp-dark);color:#fff}.orh-levels-how h2{font-size:52px;letter-spacing:-.06em;margin:0 0 35px}.orh-how-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:15px}.orh-how-item{border:1px solid #30343d;border-radius:18px;padding:25px;background:#1b1e25}.orh-how-item b{display:block;color:var(--lp-acid);font-size:11px;letter-spacing:.15em}.orh-how-item h3{font-size:20px;margin:15px 0 8px}.orh-how-item p{margin:0;color:#aeb4bf;font-size:12px;line-height:1.65}
.orh-levels-terms{padding:76px 5vw;background:#fff}.orh-levels-terms-inner{max-width:850px}.orh-levels-terms h2{font-size:42px;letter-spacing:-.055em;margin:0 0 20px}.orh-levels-terms p{color:var(--lp-muted);font-size:13px;line-height:1.75}.orh-levels-terms strong{color:#111216}.orh-levels-cta{margin-top:30px;display:inline-block;background:var(--lp-acid);border:1px solid #cddd00;border-radius:12px;padding:14px 19px;text-decoration:none;color:#111216;font-size:11px;font-weight:900}
@media(max-width:900px){.orh-levels-grid,.orh-how-grid{grid-template-columns:1fr}.orh-level-card.featured{transform:none}.orh-levels-intro{display:block}.orh-levels-intro p{margin-top:20px}.orh-levels-wrap,.orh-levels-how,.orh-levels-terms{padding-left:25px;padding-right:25px}.orh-levels-hero{padding:65px 25px}.orh-levels-card{min-height:auto}}
</style>
<main class="orh-levels-page">
<section class="orh-levels-hero">
  <div class="orh-levels-eyebrow"><i></i> Онлайн Радио Хит · размещение артистов</div>
  <h1>Больше музыки.<br><em>Больше возможностей.</em></h1>
  <p class="orh-levels-lead">Размещаем песни артистов в эфире и постепенно открываем дополнительные возможности по мере роста каталога. Все песни добавляет и оформляет редакция радио — артисту не нужно самостоятельно загружать контент.</p>
  <div class="orh-levels-note">Количество размещённых песен определяет уровень возможностей.</div>
</section>
<section class="orh-levels-wrap">
  <div class="orh-levels-intro"><h2>Три уровня<br>присутствия.</h2><p>Начните с одной песни. Когда на радио появляется вторая, для артиста открывается следующий уровень — публичная страница, чарт и более глубокая статистика.</p></div>
  <div class="orh-levels-grid">
    <article class="orh-level-card">
      <span class="badge">Уровень 01 · базовый</span>
      <h3>Песня в эфире</h3><div class="count">1 размещённая песня</div>
      <p>Первый шаг — песня появляется на Онлайн Радио Хит и становится частью музыкального эфира.</p>
      <ul><li>Размещение песни в эфире радио</li><li>Раздел «Новые песни»</li><li>Бейдж размещения песни на радио</li></ul>
      <div class="orh-level-price">Стоимость <strong>уточняется</strong></div>
    </article>
    <article class="orh-level-card featured">
      <span class="badge">Уровень 02 · первый платный</span>
      <h3>Страница артиста</h3><div class="count">2–5 размещённых песен</div>
      <p>Полноценное публичное присутствие артиста на радио с участием песен в чарте и расширенной статистикой.</p>
      <ul><li>Всё из Уровня 1</li><li>Публичная страница артиста</li><li>Участие песен в чарте</li><li>Расширенная статистика на странице артиста</li><li>Бейдж страницы артиста</li><li>Участие в чарте артистов</li></ul>
      <div class="orh-level-price">Стоимость <strong>уточняется</strong></div>
    </article>
    <article class="orh-level-card future">
      <span class="badge">Уровень 03 · расширенный</span>
      <h3>Больше возможностей</h3><div class="count">6 и более размещённых песен</div>
      <p>Расширенный уровень для артистов с большим каталогом. Дополнительные возможности будут добавляться по мере развития радио.</p>
      <ul><li>Всё из Уровня 2</li><li class="coming">Новые инструменты продвижения</li><li class="coming">Дополнительная аналитика</li><li class="coming">Расширенные возможности присутствия на радио</li></ul>
      <div class="orh-level-price">Стоимость <strong>будет определена</strong></div>
    </article>
  </div>
</section>
<section class="orh-levels-how">
  <h2>Как это работает.</h2>
  <div class="orh-how-grid">
    <div class="orh-how-item"><b>01 · ВЫ ПЕРЕДАЁТЕ МУЗЫКУ</b><h3>Песни добавляет редакция</h3><p>Артист не занимается загрузкой и оформлением песен на сайте. Контент добавляется и проверяется нашей командой.</p></div>
    <div class="orh-how-item"><b>02 · РАДИО РАЗМЕЩАЕТ</b><h3>Песня выходит в эфир</h3><p>При одном размещённом треке артист получает базовое присутствие: эфир, «Новые песни» и бейдж размещения.</p></div>
    <div class="orh-how-item"><b>03 · КАТАЛОГ РАСТЁТ</b><h3>Открывается новый уровень</h3><p>При достижении следующего количества песен система автоматически определяет новый уровень возможностей.</p></div>
  </div>
</section>
<section class="orh-levels-terms">
  <div class="orh-levels-terms-inner"><h2>Условия будут развиваться.</h2><p>Эта страница — базовая версия системы уровней. <strong>Стоимость, сроки размещения, дополнительные условия и новые возможности</strong> будут уточняться и добавляться отдельно, без изменения самой логики уровней.</p><p>Количество песен является основным показателем уровня. При изменении количества размещённых песен уровень пересчитывается автоматически, а накопленные данные страницы артиста не должны теряться.</p><a class="orh-levels-cta" href="<?php echo esc_url(home_url('/')); ?>">Вернуться на радио</a></div>
</section>
</main>
<?php get_footer(); ?>