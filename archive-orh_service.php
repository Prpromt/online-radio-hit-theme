<?php get_header(); ?>
<main class="section service-archive">
<div class="orh-breadcrumbs"><a href="<?php echo esc_url(home_url('/'));?>">Радио Хит</a><span aria-hidden="true">→</span><b>Продвижение</b></div>
<div class="eyebrow">ПРОДВИЖЕНИЕ</div><h1>Возможности<br><em>для артиста</em></h1><p class="lead">Отдельные направления и конкретные услуги для продвижения песен.</p>
<div class="service-grid" role="list">
<?php if(have_posts()): while(have_posts()): the_post(); $id=get_the_ID(); $thumb=orh_media_url($id,'large'); ?>
<a class="service-card acid" role="listitem" href="<?php the_permalink();?>" aria-label="Подробнее об услуге: <?php echo esc_attr(get_the_title());?>">
<div class="service-visual" <?php if($thumb)echo 'style="background-image:url(\''.esc_url($thumb).'\')"';?>><span>УСЛУГА</span><i aria-hidden="true">↗</i></div>
<div class="service-card-copy"><h2><?php the_title();?></h2><p><?php echo esc_html(get_the_excerpt());?></p><strong>Подробнее →</strong></div>
</a>
<?php endwhile; else:?><div class="empty">Услуги пока не опубликованы.</div><?php endif;?>
</div></main><?php get_footer();?>