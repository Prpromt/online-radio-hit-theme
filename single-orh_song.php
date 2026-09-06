<?php
get_header();
the_post();
$song_id=get_the_ID();
$artist_id=(int)get_post_meta($song_id,'orh_artist_id',true);
$artist_name=$artist_id?get_the_title($artist_id):'Артист';
$audio=orh_song_audio($song_id);
$thumb=orh_media_url($song_id,'large');
$plays=(int)get_post_meta($song_id,'orh_plays',true);
$on_air=(string)get_post_meta($song_id,'orh_on_air',true)==='1';
$artist_level=$artist_id && function_exists('orh_artist_level') ? orh_artist_level($artist_id) : 1;
$artist_public=$artist_id && function_exists('orh_artist_level_public') ? orh_artist_level_public($artist_id) : false;
$level_name=function_exists('orh_artist_level_name') ? orh_artist_level_name($artist_level) : 'Уровень 1 · базовый';
?>
<main class="song-single section">
<div class="orh-breadcrumbs"><a href="<?php echo esc_url(home_url('/'));?>">Радио Хит</a><span aria-hidden="true">→</span><a href="<?php echo esc_url(get_post_type_archive_link('orh_song'));?>">Все песни</a><span aria-hidden="true">→</span><b><?php the_title();?></b></div>
<section class="song-single-head">
<div class="song-single-cover" aria-hidden="true" <?php if($thumb)echo 'style="background-image:url(\''.esc_url($thumb).'\')"';?>><?php if(!$thumb)echo '♪';?></div>
<div class="song-single-info">
<div class="eyebrow"><?php echo $on_air?'СЕЙЧАС В ЭФИРЕ':'ПЕСНЯ РАДИО ОНЛАЙН ХИТ';?></div>
<h1><?php the_title();?></h1>
<?php if($artist_id && $artist_public):?><p class="song-single-artist"><a href="<?php echo esc_url(get_permalink($artist_id));?>"><?php echo esc_html($artist_name);?></a></p><?php else:?><p class="song-single-artist"><?php echo esc_html($artist_name);?></p><?php endif;?>
<div class="eyebrow" style="margin-top:10px;"><?php echo esc_html($level_name);?></div>
<?php if($artist_level===1):?><p class="song-single-placement">✓ Песня размещена на Радио Хит</p><?php endif;?>
<div class="artist-stats"><span><b><?php echo esc_html(number_format_i18n($plays));?></b> прослушиваний</span><span><b><?php echo $on_air?'24/7':'ХИТ';?></b> радио</span></div>
<?php if($audio):?><button type="button" class="primary" onclick="playSong(<?php echo esc_attr($song_id);?>,'<?php echo esc_js(get_the_title());?>','<?php echo esc_js($artist_name);?>','<?php echo esc_url($audio);?>','<?php echo esc_url($thumb);?>')">▶ Слушать песню</button><?php else:?><p class="empty">Аудиофайл этой песни пока не подключён.</p><?php endif;?>
</div>
</section>
<section class="song-single-content"><div class="eyebrow">О ПЕСНЕ</div><?php the_content();?></section>
</main>
<?php get_footer();?>
