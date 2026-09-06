<?php get_header(); ?>
<main class="artist-archive">
<div class="orh-breadcrumbs"><a href="<?php echo esc_url(home_url('/'));?>">Радио Хит</a><span aria-hidden="true">→</span><b>Артисты Радио Онлайн ХИТ</b></div>
<section class="artist-archive-head"><div class="eyebrow">АРТИСТЫ РАДИО ОНЛАЙН ХИТ</div><h1>Артисты Радио<br><em>Онлайн ХИТ</em></h1><p>Музыканты, чьи песни звучат в эфире. Открывайте новые имена и слушайте их каталоги.</p></section>
<div class="artist-profile-grid" role="list">
<?php if(have_posts()): while(have_posts()): the_post(); $id=get_the_ID(); $thumb=orh_media_url($id,'medium'); $count=orh_artist_count($id); $level=orh_artist_level($id); $label=$count==1?'трек':($count<5?'трека':'треков'); ?>
<a class="artist-profile-card" role="listitem" href="<?php the_permalink();?>" aria-label="Открыть артиста <?php echo esc_attr(get_the_title());?>, <?php echo esc_attr($count.' '.$label);?> в каталоге">
<span class="artist-profile-card-img" aria-hidden="true" <?php if($thumb)echo 'style="background-image:url(\''.esc_url($thumb).'\')"';?>><?php if(!$thumb)echo esc_html(function_exists('mb_strtoupper')?mb_strtoupper(mb_substr(get_the_title(),0,1)):'H');?></span>
<div><small><?php echo esc_html(orh_artist_level_name($level));?></small><h2><?php the_title();?></h2><p><?php echo esc_html($count.' '.$label);?> · <?php echo esc_html(orh_artist_level_range($level));?></p></div><span class="artist-arrow" aria-hidden="true">↗</span>
</a>
<?php endwhile; else:?><div class="empty">Артисты пока не добавлены.</div><?php endif;?>
</div></main><?php get_footer();?>