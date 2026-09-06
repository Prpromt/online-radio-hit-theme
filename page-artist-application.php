<?php get_header(); ?>
<main class="application">
<div>
<div class="breadcrumbs" aria-label="Хлебные крошки"><a href="<?php echo esc_url(home_url('/')); ?>">Главная</a><span aria-hidden="true">/</span><span>Заявка</span></div>
<div class="eyebrow">ЗАЯВКА</div>
<h1>Выставить Песню на Радио<br><em>Онлайн Радио Хит</em></h1>
<p>Перед отправкой песни необходимо подписаться на три наших канала.</p>
<div class="subscription" aria-labelledby="subscription-title">
<h2 id="subscription-title">Обязательные подписки</h2>
<div><b>1. «Продюсер» в Telegram</b><small>Новости музыки, продвижения артистов и полезная информация.</small></div>
<div><b>2. «Продюсер» в MAX</b><small>Новости, возможности продвижения и информация для артистов.</small></div>
<div><b>3. «Радио» в Telegram</b><small>Анонсы новых песен, выхода треков в эфир, радиочартов и событий наших радиостанций.</small></div>
</div>
</div>
<div>
<?php echo do_shortcode('[orh_artist_application]'); ?>
</div>
</main>
<?php get_footer(); ?>