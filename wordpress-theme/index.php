<?php
/* Direct embed endpoint: no WordPress page creation is required. */
$orh_path=trim(parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH),'/');
if($orh_path==='embed'){
    status_header(200);
    if(isset($wp_query)) $wp_query->is_404=false;
    require get_template_directory().'/page-embed.php';
    exit;
}
get_header();
?><main class="section"><div class="eyebrow">ОНЛАЙН РАДИО ХИТ</div><h1>Страница</h1><?php if(have_posts()):while(have_posts()):the_post();the_content();endwhile;endif;?></main><?php get_footer(); ?>