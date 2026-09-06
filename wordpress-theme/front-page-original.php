<?php get_header(); ?>
<?php
$orh_latest = function_exists('orh_last_uploaded_song') ? orh_last_uploaded_song() : null;
$orh_current = orh_current_song();
if (!$orh_current) $orh_current = $orh_latest;

$orh_stream = '';
if ($orh_current) {
    $orh_stream = !empty($orh_current['audio']) ? $orh_current['audio'] : orh_song_audio($orh_current['id']);
    $orh_audio_id = (int)get_post_meta($orh_current['id'],'orh_audio_id',true);
    if ($orh_audio_id) {
        $orh_attachment_url = wp_get_attachment_url($orh_audio_id);
        if ($orh_attachment_url) $orh_stream = $orh_attachment_url;
    }
}

$orh_song_title = $orh_current ? $orh_current['title'] : 'Новый день';
$orh_artist_name = $orh_current && !empty($orh_current['artist_id']) ? get_the_title($orh_current['artist_id']) : (($orh_current && !empty($orh_current['artist'])) ? $orh_current['artist'] : 'Премьера эфира');
$orh_cover = $orh_current ? orh_media_url($orh_current['id'],'large') : '';
?>
<main>
<section class="hero hero-v13" id="radio"><div class="hero-copy">
<div class="eyebrow"><i></i> LIVE • 24/7 • ТОЛЬКО ХИТЫ</div>
<div class="station-name"><span>ОНЛАЙН РАДИО</span><strong>ХИТ</strong></div>
<h1>Популярные песни,<br><em>новые артисты.</em></h1>
<p>Музыка, которую хочется слушать снова. Онлайн Радио Хит — эфир 24/7, чарт, премьеры и новые возможности для артистов.</p>
<div class="actions"><button class="primary" onclick="toggleRadio()"><span class="orh-icon" data-play></span> Слушать эфир</button><audio id="orh-stream" data-orh-player="1" data-orh-source="<?php echo esc_attr($orh_current ? "on-air" : "fallback"); ?>" autoplay preload="auto" src="<?php echo esc_url($orh_stream); ?>"></audio><a class="secondary" href="#chart">Открыть чарт</a></div>
<div class="facts facts-v13"><div><b>24/7</b><small>онлайн</small></div><div><b>Только хиты</b><small>лучшие треки</small></div><div><b>Новые артисты</b><small>каждый день</small></div><div><b>Твой ритм</b><small>твоя музыка</small></div></div>
</div>
<div class="player player-v13">
<div class="player-head"><span>СЕЙЧАС ИГРАЕТ</span><span class="live-dot">● LIVE</span></div>
<div class="player-main">
<div class="cover cover-v13 square-cover" data-current-cover><?php if($orh_cover): ?><img src="<?php echo esc_url($orh_cover); ?>" alt="<?php echo esc_attr($orh_song_title); ?>" loading="eager"><?php else: ?><span>HIT</span><small>ONLINE RADIO</small><?php endif; ?></div>
<div class="player-info"><div class="kicker">ОНЛАЙН РАДИО ХИТ</div><h2 data-current-song><?php echo esc_html($orh_song_title);?></h2><p data-current-artist><?php echo esc_html($orh_artist_name);?></p><div class="equalizer" aria-hidden="true"><?php for($j=0;$j<42;$j++) echo '<i style="--h:'.(18+(($j*13)%45)).'px"></i>'; ?></div></div>
</div>
<?php
$orh_queue=[];
$orh_queue_posts=get_posts(['post_type'=>'orh_song','post_status'=>'publish','numberposts'=>12,'orderby'=>'date','order'=>'DESC']);
foreach($orh_queue_posts as $qp){
    $qa=orh_song_audio($qp->ID); if(!$qa) continue;
    $qaid=(int)get_post_meta($qp->ID,'orh_artist_id',true);
    $qan=$qaid?get_the_title($qaid):'Артист';
    $qcover=orh_media_url($qp->ID,'full');
    $orh_queue[]=['id'=>$qp->ID,'title'=>$qp->post_title,'artist'=>$qan,'url'=>$qa,'cover'=>$qcover];
}
?>
<div class="player-bottom player-bottom-v13">
  <div class="player-controls">
    <button type="button" aria-label="Повторить трек" data-repeat onclick="toggleRepeat()"><span class="orh-icon repeat"></span></button>
    <button type="button" aria-label="Предыдущий трек" onclick="radioPrevious()"><span class="orh-icon prev"></span></button>
    <button type="button" class="play" onclick="toggleRadio()" data-play><span class="orh-icon"></span></button>
    <button type="button" aria-label="Следующий трек" onclick="radioNext()"><span class="orh-icon next"></span></button>
    <button type="button" aria-label="Включить повтор" data-repeat-toggle onclick="toggleRepeat()"><span class="orh-icon repeat"></span></button>
  </div>
  <div class="radio-seek-wrap">
    <div class="radio-time"><span data-current-time>0:00</span><span data-duration>0:00</span></div>
    <input class="radio-progress" type="range" min="0" max="100" value="0" step="0.1" aria-label="Позиция трека" data-progress>
  </div>
  <div class="radio-volume-wrap">
    <span class="volume-label">Громкость</span>
    <input class="radio-volume" type="range" min="0" max="1" value="0.8" step="0.01" aria-label="Громкость" data-volume>
  </div>
</div>
</div></section>
<section class="ticker"><span>СЕЙЧАС В ЭФИРЕ</span><b><?php echo esc_html($orh_song_title);?></b><span>РАДИО ХИТ</span><b>Музыка 24/7</b></section>

<section class="overview-grid" id="overview">
<div class="overview-card popular-card"><div class="card-head"><div><div class="eyebrow">ПОПУЛЯРНЫЕ ПЕСНИ</div><h2>Сейчас слушают</h2></div><a class="more" href="<?php echo esc_url(get_post_type_archive_link('orh_song'));?>">Смотреть все</a></div>
<?php $pop=new WP_Query(['post_type'=>'orh_song','posts_per_page'=>3,'post_status'=>'publish','meta_key'=>'orh_plays','orderby'=>'meta_value_num','order'=>'DESC']);$pi=0;if($pop->have_posts()):while($pop->have_posts()):$pop->the_post();$aid=(int)get_post_meta(get_the_ID(),'orh_artist_id',true);$an=$aid?get_the_title($aid):'Артист';$au=orh_song_audio(get_the_ID());$thumb=orh_media_url(get_the_ID(),'full');?><div class="mini-track"><strong><?php echo ++$pi;?></strong><button class="mini-cover" onclick="playSong(<?php echo esc_attr(get_the_ID());?>,'<?php echo esc_js(get_the_title());?>','<?php echo esc_js($an);?>','<?php echo esc_url($au);?>','<?php echo esc_url($thumb);?>')"><?php if($thumb)echo '<img src="'.esc_url($thumb).'" alt="" loading="lazy">';else echo 'H';?></button><div><b><?php the_title();?></b><small><?php echo esc_html($an);?></small></div><button class="mini-play" onclick="playSong(<?php echo esc_attr(get_the_ID());?>,'<?php echo esc_js(get_the_title());?>','<?php echo esc_js($an);?>','<?php echo esc_url($au);?>','<?php echo esc_url($thumb);?>')"><span class="mini-play-icon" aria-hidden="true"></span></button><span class="mini-plays"><?php echo esc_html(number_format_i18n((int)get_post_meta(get_the_ID(),'orh_plays',true)));?></span></div><?php endwhile;wp_reset_postdata();else:?><div class="empty">Песни появятся после добавления редактором.</div><?php endif;?></div>

<div class="overview-card chart-card" id="chart"><div class="card-head"><div><div class="eyebrow">ЧАРТ НЕДЕЛИ</div><h2>ТОП ХИТ</h2></div><a class="more" href="<?php echo esc_url(get_post_type_archive_link('orh_song'));?>">Смотреть все</a></div>
<?php $songs=new WP_Query(['post_type'=>'orh_song','posts_per_page'=>5,'post_status'=>'publish','meta_key'=>'orh_plays','orderby'=>'meta_value_num','order'=>'DESC']);$i=0;if($songs->have_posts()):while($songs->have_posts()):$songs->the_post();$artist_id=(int)get_post_meta(get_the_ID(),'orh_artist_id',true);$artist=$artist_id?get_the_title($artist_id):'Артист';$plays=(int)get_post_meta(get_the_ID(),'orh_plays',true);$audio=orh_song_audio(get_the_ID());$thumb=orh_media_url(get_the_ID(),'full');?><div class="mini-chart-row" role="button" tabindex="0" onclick="playSong(<?php echo esc_attr(get_the_ID());?>,'<?php echo esc_js(get_the_title());?>','<?php echo esc_js($artist);?>','<?php echo esc_url($audio);?>','<?php echo esc_url($thumb);?>')" onkeydown="if(event.key==='Enter')playSong(<?php echo esc_attr(get_the_ID());?>,'<?php echo esc_js(get_the_title());?>','<?php echo esc_js($artist);?>','<?php echo esc_url($audio);?>','<?php echo esc_url($thumb);?>')"><strong><?php echo ++$i;?></strong><span class="mini-cover"><?php if($thumb)echo '<img src="'.esc_url($thumb).'" alt="" loading="lazy">';else echo 'H';?></span><div><b><?php the_title();?></b><small><?php echo esc_html($artist);?></small></div><span class="wave" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></span><em><?php echo esc_html(number_format_i18n($plays));?></em></div><?php endwhile;wp_reset_postdata();else:?><div class="empty">Чарт наполнится после первых прослушиваний.</div><?php endif;?></div>

<div class="overview-card artists-card" id="artists"><div class="card-head"><div><div class="eyebrow">АРТИСТЫ РАДИО ОНЛАЙН ХИТ</div><h2>Новые имена</h2></div><a class="more" href="<?php echo esc_url(get_post_type_archive_link('orh_artist'));?>">Смотреть все</a></div>
<div class="artist-mini-grid"><?php $artists=get_posts(['post_type'=>'orh_artist','numberposts'=>6,'orderby'=>'date','order'=>'DESC']);foreach($artists as $a){$thumb=orh_media_url($a->ID,'full');?><a href="<?php echo esc_url(get_permalink($a->ID));?>" class="artist-mini"><span class="artist-avatar"><?php if($thumb)echo '<img src="'.esc_url($thumb).'" alt="'.esc_attr($a->post_title).'" loading="lazy">';else echo esc_html(mb_strtoupper(mb_substr($a->post_title,0,1)));?></span><b><?php echo esc_html($a->post_title);?></b></a><?php } if(!$artists)echo '<div class="empty">Артисты появятся после публикации редактором.</div>';?></div>
<div class="artist-select"><button onclick="openArtists()">АРТИСТЫ С НЕСКОЛЬКИМИ ТРЕКАМИ <span>⌄</span></button><div id="artistsMenu"><?php foreach(get_posts(['post_type'=>'orh_artist','numberposts'=>-1,'orderby'=>'title','order'=>'ASC']) as $a){$c=orh_artist_count($a->ID);if($c>=2)echo '<a href="'.esc_url(get_permalink($a->ID)).'">'.esc_html($a->post_title).'<small>'.esc_html($c).' трека(ов)</small></a>'; }?></div></div>
</div>

<div class="overview-card news-card-overview" id="news"><div class="card-head"><div><div class="eyebrow">НОВОСТИ И НОВЫЕ УСЛУГИ ПРОДЮСЕРСКОГО ЦЕНТРА</div><h2>Что нового</h2></div><a class="more" href="<?php echo esc_url(get_permalink(get_option('page_for_posts')));?>">Смотреть все</a></div>
<?php $orh_news=new WP_Query(['post_type'=>'post','posts_per_page'=>3,'post_status'=>'publish','orderby'=>'date','order'=>'DESC']);if($orh_news->have_posts()):while($orh_news->have_posts()):$orh_news->the_post();$nthumb=orh_media_url(get_the_ID(),'full');?><a class="news-overview-item" href="<?php the_permalink();?>"><span class="news-thumb"><?php if($nthumb)echo '<img src="'.esc_url($nthumb).'" alt="" loading="lazy">';?></span><div><b><?php the_title();?></b><small><?php echo esc_html(get_the_excerpt());?></small></div></a><?php endwhile;wp_reset_postdata();else:?><div class="empty">Новости и новые услуги появятся здесь.</div><?php endif;?></div>
</section>

<section class="promotion-preview" id="promotion-directions"><div class="section-top"><div><div class="eyebrow">КРУПНЫЕ НАПРАВЛЕНИЯ</div><h2>Направления продвижения</h2><p>Только крупные направления. Каждый раздел открывается на отдельной странице.</p></div><a class="more" href="<?php echo esc_url(get_post_type_archive_link('orh_service'));?>">Все услуги →</a></div>
<div class="promotion-preview-grid"><?php $orh_groups=orh_service_groups(); $orh_dir_i=0; if($orh_groups): foreach(array_slice($orh_groups,0,4) as $g): $orh_dir_i++; $url=get_term_link($g);?><a class="promotion-preview-card" href="<?php echo esc_url($url);?>"><div><small>НАПРАВЛЕНИЕ</small><h3><?php echo esc_html($g->name);?></h3><p><?php echo esc_html(wp_trim_words($g->description?:'Продвижение музыки и новые возможности для артиста.',14));?></p><strong>Подробнее →</strong></div><span><?php echo esc_html(str_pad((string)$orh_dir_i,2,'0',STR_PAD_LEFT));?></span></a><?php endforeach; else:?><div class="empty">Направления продвижения появятся после добавления редактором.</div><?php endif;?></div></section>

<div class="quick-links" aria-label="Быстрый переход">
  <a href="#radio"><span>01</span><b>Эфир</b><small>Слушать сейчас</small></a>
  <a href="#chart"><span>02</span><b>Чарт</b><small>Популярные треки</small></a>
  <a href="#artists"><span>03</span><b>Артисты</b><small>Новые имена</small></a>
  <a href="#promotion-directions"><span>04</span><b>Продвижение</b><small>Возможности</small></a>
</div>
<section class="section releases"><div class="heading"><div><div class="eyebrow">СВЕЖИЕ ПРЕМЬЕРЫ</div><h2>Новые треки</h2></div><a class="more" href="<?php echo esc_url(get_post_type_archive_link('orh_song'));?>">Все песни →</a></div><div class="release-grid"><?php $new=new WP_Query(['post_type'=>'orh_song','posts_per_page'=>6,'post_status'=>'publish','orderby'=>'date','order'=>'DESC']);if($new->have_posts()):while($new->have_posts()):$new->the_post();$aid=(int)get_post_meta(get_the_ID(),'orh_artist_id',true);$an=$aid?get_the_title($aid):'Артист';$au=orh_song_audio(get_the_ID());$thumb=orh_media_url(get_the_ID(),'full');?><article class="release-card"><button class="release-cover" onclick="playSong(<?php echo esc_attr(get_the_ID());?>,'<?php echo esc_js(get_the_title());?>','<?php echo esc_js($an);?>','<?php echo esc_url($au);?>','<?php echo esc_url($thumb);?>')"><?php if($thumb)echo '<img src="'.esc_url($thumb).'" alt="" loading="lazy">';else echo '<span>HIT</span>';?><i class="play-icon" aria-hidden="true"></i></button><div><small>ПРЕМЬЕРА</small><h3><?php the_title();?></h3><p><?php echo esc_html($an);?></p></div></article><?php endwhile;wp_reset_postdata();else:?><div class="empty">Новые песни появятся после публикации редакцией.</div><?php endif;?></div></section>

<section class="artist-block" id="artist"><div><div class="eyebrow">ДЛЯ АРТИСТОВ</div><h2>Выставить Песню на Радио<br><em>Онлайн Радио Хит</em></h2><p>Заявки рассматривает редакция. Публикация песен выполняется только администратором или редактором радио.</p></div><div class="requirements"><div><b>01</b><span>Продюсер · Telegram</span><small>Подписка обязательна</small></div><div><b>02</b><span>Продюсер · MAX</span><small>Подписка обязательна</small></div><div><b>03</b><span>Радио · Telegram</span><small>Подписка обязательна</small></div><a class="primary wide" href="<?php echo esc_url(home_url('/artist-application/'));?>">Перейти к заявке →</a></div></section>
<?php $orh_groups=orh_service_groups(); if(!empty($orh_groups)){ ?>
<section class="promotion-hub" id="promotion-services"><div class="section-top"><div><div class="eyebrow">УСЛУГИ ПО НАПРАВЛЕНИЯМ</div><h2>Услуги продвижения</h2><p>Ниже — конкретные услуги и предложения внутри каждого направления.</p></div><a class="more" href="<?php echo esc_url(get_post_type_archive_link('orh_service'));?>">Все услуги →</a></div>
<div class="promotion-groups">
<?php $orh_group_i=0; foreach($orh_groups as $g){ $orh_group_i++; $url=get_term_link($g); ?>
<a class="promotion-group" href="<?php echo esc_url($url);?>"><span class="group-number"><?php echo esc_html(str_pad((string)$orh_group_i,2,'0',STR_PAD_LEFT));?></span><div><small>НАПРАВЛЕНИЕ ПРОДВИЖЕНИЯ</small><h3><?php echo esc_html($g->name);?></h3><p><?php echo esc_html(wp_trim_words($g->description?:'Продвижение музыки и новые возможности для артиста.',12));?></p></div><b>→</b></a>
<?php } ?>
</div>
<?php foreach($orh_groups as $g){ $q=orh_services_for_group($g->term_id,3); if(!$q->have_posts()) continue; ?>
<div class="service-line"><div class="service-line-title"><span><?php echo esc_html($g->name);?></span><a href="<?php echo esc_url(get_term_link($g));?>">Смотреть направление →</a></div><div class="service-cards"><?php while($q->have_posts()){ $q->the_post(); $ac=get_post_meta(get_the_ID(),'orh_service_accent',true)?:'acid'; $lb=get_post_meta(get_the_ID(),'orh_service_label',true); $thumb=orh_media_url(get_the_ID(),'large'); ?><a class="service-card <?php echo esc_attr($ac);?>" href="<?php the_permalink();?>"><div class="service-visual" <?php if($thumb) echo 'style="background-image:url(\''.esc_url($thumb).'\')"';?>><span><?php echo $lb?esc_html($lb):'УСЛУГА';?></span><i>↗</i></div><div class="service-card-copy"><h3><?php the_title();?></h3><p><?php echo esc_html(get_the_excerpt());?></p><strong>Подробнее →</strong></div></a><?php } wp_reset_postdata();?></div></div>
<?php } ?>
</section>
<?php } ?>
<section class="mailing"><div><div class="eyebrow">ЕДИНАЯ РАССЫЛКА</div><h2>Новости и новые услуги<br>Продюсерского центра</h2></div><form onsubmit="subscribeForm(event)"><p>Одна рассылка для подписчиков всех музыкальных сайтов.</p><input id="mailEmail" type="email" required placeholder="Ваш email"><button class="primary" type="submit">Подписаться</button><small id="mailMsg"></small></form></section>

<div class="sticky-mini-player" id="stickyPlayer" aria-live="polite"><div class="sticky-mini-cover<?php echo $orh_cover ? " has-cover" : ""; ?>" data-sticky-cover<?php if($orh_cover) echo " style=\"background-image:url('".esc_url($orh_cover)."')\""; ?>></div><div class="sticky-mini-info"><small>СЕЙЧАС ИГРАЕТ</small><b data-sticky-song><?php echo esc_html($orh_song_title);?></b><span data-sticky-artist><?php echo esc_html($orh_artist_name);?></span><small class="sticky-time" data-sticky-time>0:00</small></div><button class="sticky-play" onclick="toggleActiveAudio()" data-play><span class="mini-play-icon" aria-hidden="true"></span></button><a href="#radio">Плеер ↑</a></div>

<section class="premium-service-rail-v24">
<div class="premium-rail-head"><div><span class="eyebrow">ВОЗМОЖНОСТИ ДЛЯ АРТИСТОВ</span><h2>Продвигайте музыку<br><em>на новом уровне.</em></h2></div><a href="<?php echo esc_url(home_url('/uslugi/')); ?>">Все услуги →</a></div>
<div class="premium-rail-grid">
<a class="premium-rail-card" href="<?php echo esc_url(home_url('/uslugi/')); ?>"><span>Я</span><b>Продвижение в Яндексе</b><small>Рекомендации • плейлисты • аудитория</small></a>
<a class="premium-rail-card" href="<?php echo esc_url(home_url('/uslugi/')); ?>"><span>VK</span><b>Продвижение ВКонтакте</b><small>Музыка • рекомендации • охват</small></a>
<a class="premium-rail-card" href="<?php echo esc_url(home_url('/uslugi/')); ?>"><span>ТГ</span><b>Продвижение в Telegram</b><small>Каналы • посевы • новые слушатели</small></a>
</div></section>
<script>window.ORH_QUEUE=<?php echo wp_json_encode($orh_queue,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);?>;</script></main><?php get_footer(); ?>
