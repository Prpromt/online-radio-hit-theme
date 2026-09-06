<?php
if (!defined('ABSPATH')) exit;
define('ORH_VERSION','46.1.0');

function orh_setup(){
 add_theme_support('title-tag'); add_theme_support('post-thumbnails');
 add_theme_support('html5',['search-form','comment-form','comment-list','gallery','caption','style','script']);
 register_nav_menus(['primary'=>'Главное меню']);
}
add_action('after_setup_theme','orh_setup');

/* Public artist-placement levels page: available on the preview without requiring manual WP page creation. */
function orh_levels_route(){ add_rewrite_rule('^artist-levels/?$','index.php?orh_levels=1','top'); }
add_action('init','orh_levels_route');
function orh_levels_query_vars($vars){ $vars[]='orh_levels'; return $vars; }
add_filter('query_vars','orh_levels_query_vars');
function orh_levels_template($template){
 if((int)get_query_var('orh_levels')===1){
  global $wp_query;
  $wp_query->is_404=false;
  $wp_query->is_page=true;
  status_header(200);
  $file=get_theme_file_path('page-radio-levels.php');
  if(file_exists($file)) return $file;
 }
 return $template;
}
add_filter('template_include','orh_levels_template',99);
function orh_levels_title($title){ if((int)get_query_var('orh_levels')===1) return 'Возможности размещения на радио — Онлайн Радио Хит'; return $title; }
add_filter('pre_get_document_title','orh_levels_title',20);
function orh_levels_flush(){ flush_rewrite_rules(); }
add_action('after_switch_theme','orh_levels_flush');

function orh_assets(){
 wp_enqueue_style('orh-style',get_stylesheet_uri(),[],ORH_VERSION);
 wp_enqueue_script('orh-app',get_template_directory_uri().'/assets/app.js',[],ORH_VERSION,true);
 wp_localize_script('orh-app','ORH',['ajax'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('orh_player'),'api'=>rest_url('orh/v1/radio')]);
}
add_action('wp_enqueue_scripts','orh_assets');

function orh_count_play(){
 check_ajax_referer('orh_player','nonce');
 $id=absint($_POST['song_id']??0);
 if(!$id||get_post_type($id)!=='orh_song')wp_send_json_error();
 $plays=(int)get_post_meta($id,'orh_plays',true)+1; update_post_meta($id,'orh_plays',$plays);
 wp_send_json_success(['plays'=>$plays]);
}
add_action('wp_ajax_orh_count_play','orh_count_play'); add_action('wp_ajax_nopriv_orh_count_play','orh_count_play');

function orh_playlist_for_artist($artist_id){
 return new WP_Query([
  'post_type'=>'orh_song','posts_per_page'=>-1,'post_status'=>'publish',
  'meta_key'=>'orh_artist_id','meta_value'=>(int)$artist_id,
  'orderby'=>'date','order'=>'DESC'
 ]);
}
function orh_current_song(){
 $q=new WP_Query([
  'post_type'=>'orh_song','posts_per_page'=>1,'post_status'=>'publish',
  'meta_key'=>'orh_on_air','meta_value'=>'1','orderby'=>'date','order'=>'DESC'
 ]);
 if($q->have_posts()){ $q->the_post(); $id=get_the_ID(); $data=[
  'id'=>$id,'title'=>get_the_title($id),
  'artist_id'=>(int)get_post_meta($id,'orh_artist_id',true),
  'audio'=>esc_url_raw(get_post_meta($id,'orh_audio_url',true)),
  'cover'=>get_the_post_thumbnail_url($id,'large')
 ]; wp_reset_postdata(); return $data; }
 wp_reset_postdata(); return null;
}

function orh_artist_count($id){
 $q=new WP_Query(['post_type'=>'orh_song','posts_per_page'=>1,'fields'=>'ids','meta_key'=>'orh_artist_id','meta_value'=>(int)$id,'post_status'=>'publish']);
 return (int)$q->found_posts;
}

/* Simple central subscriber table. Later this can be moved to the shared mailing service. */
function orh_install_tables(){
 global $wpdb; $table=$wpdb->prefix.'orh_subscribers'; $charset=$wpdb->get_charset_collate();
 require_once ABSPATH.'wp-admin/includes/upgrade.php';
 dbDelta("CREATE TABLE $table (
 id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 email varchar(190) NOT NULL,
 source varchar(100) NOT NULL DEFAULT 'radio-hit',
 consent tinyint(1) NOT NULL DEFAULT 1,
 status varchar(20) NOT NULL DEFAULT 'active',
 created_at datetime NOT NULL,
 PRIMARY KEY(id), UNIQUE KEY email(email)
 ) $charset;");
}
add_action('after_switch_theme','orh_install_tables');

function orh_subscribe(){
 if(!isset($_POST['nonce'])||!wp_verify_nonce($_POST['nonce'],'orh_subscribe'))wp_send_json_error(['message'=>'Ошибка безопасности.']);
 global $wpdb; $email=sanitize_email($_POST['email']??'');
 if(!is_email($email))wp_send_json_error(['message'=>'Укажите корректный email.']);
 $table=$wpdb->prefix.'orh_subscribers';
 $wpdb->replace($table,['email'=>$email,'source'=>'radio-hit','consent'=>1,'status'=>'active','created_at'=>current_time('mysql')],['%s','%s','%d','%s','%s']);
 wp_send_json_success(['message'=>'Вы подписаны на новости и новые услуги продюсерского центра.']);
}
add_action('wp_ajax_orh_subscribe','orh_subscribe');add_action('wp_ajax_nopriv_orh_subscribe','orh_subscribe');

function orh_application_shortcode(){
 if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['orh_apply_nonce'])&&wp_verify_nonce($_POST['orh_apply_nonce'],'orh_apply')){
  $name=sanitize_text_field($_POST['artist_name']??'');$email=sanitize_email($_POST['email']??'');
  $desc=sanitize_textarea_field($_POST['description']??'');$contacts=sanitize_text_field($_POST['contacts']??'');
  $links=sanitize_textarea_field($_POST['links']??'');$comment=sanitize_textarea_field($_POST['comment']??'');
  $subs=!empty($_POST['subscriptions_confirmed']);$news=!empty($_POST['news_consent']);
  if($name&&is_email($email)&&$subs){
   $id=wp_insert_post(['post_type'=>'orh_application','post_status'=>'private','post_title'=>$name,'post_content'=>$desc."\n\nКомментарий:\n".$comment]);
   if($id){
    foreach(['email'=>$email,'contacts'=>$contacts,'links'=>$links,'news_consent'=>$news?'yes':'no'] as $k=>$v)update_post_meta($id,$k,$v);
    if($news)orh_add_subscriber($email);
    wp_mail($email,'Заявка — Онлайн Радио Хит',"Спасибо за заявку в Онлайн Радио Хит.\n\nРедакция получила ваши данные. После рассмотрения с вами свяжутся и предоставят информацию об услугах по размещению песен в эфире.");
    return '<div class="success">Заявка принята. На указанный email отправлено ответное письмо.</div>';
   }
  }
  return '<div class="error">Проверьте данные и подтвердите подписку на три канала.</div>';
 }
 ob_start();?>
 <form class="form" method="post"><?php wp_nonce_field('orh_apply','orh_apply_nonce');?>
 <input name="artist_name" required placeholder="Имя / псевдоним артиста"><textarea name="description" required placeholder="Описание артиста"></textarea>
 <input type="email" name="email" required placeholder="Email"><input name="contacts" placeholder="Телефон / контакт менеджера">
 <textarea name="links" placeholder="Ссылки на стриминги и соцсети"></textarea><textarea name="comment" placeholder="Дополнительная информация"></textarea>
 <label><input type="checkbox" name="subscriptions_confirmed" required> Я подписался на «Продюсер» в Telegram, «Продюсер» в MAX и «Радио» в Telegram.</label>
 <label class="consent"><input type="checkbox" name="news_consent"> Согласен получать новости и информацию о новых услугах продюсерского центра.</label>
 <button class="primary wide">Отправить заявку →</button></form><?php return ob_get_clean();
}
add_shortcode('orh_artist_application','orh_application_shortcode');

function orh_add_subscriber($email){
 global $wpdb;$table=$wpdb->prefix.'orh_subscribers';$wpdb->replace($table,['email'=>sanitize_email($email),'source'=>'artist-application','consent'=>1,'status'=>'active','created_at'=>current_time('mysql')],['%s','%s','%d','%s','%s']);
}

function orh_service_groups(){
 $terms=get_terms(['taxonomy'=>'orh_service_group','hide_empty'=>true,'number'=>8]);
 return is_wp_error($terms)?[]:$terms;
}
function orh_services_for_group($term_id,$limit=3){
 return new WP_Query(['post_type'=>'orh_service','posts_per_page'=>$limit,'post_status'=>'publish','tax_query'=>[['taxonomy'=>'orh_service_group','field'=>'term_id','terms'=>$term_id]],'orderby'=>'date','order'=>'DESC']);
}

/* Admin dashboard */

/* ORH SEO 9.2 — safe lightweight SEO fallback */
if(!function_exists('orhseo_trim')){
function orhseo_trim($text,$max=155){
 $text=wp_strip_all_tags(wp_specialchars_decode((string)$text));
 $text=preg_replace('/\s+/u',' ',trim($text));
 $len=function_exists('mb_strlen')?mb_strlen($text,'UTF-8'):strlen($text);
 if($len>$max)$text=(function_exists('mb_substr')?mb_substr($text,0,$max-1,'UTF-8'):substr($text,0,$max-1)).'…';
 return $text;
}}
function orhseo_title(){
 $site=get_bloginfo('name');
 if((int)get_query_var('orh_levels')===1)return 'Возможности размещения на радио — Онлайн Радио Хит';
 if(is_singular('orh_song'))return get_the_title().' — слушать онлайн | '.$site;
 if(is_singular('orh_artist'))return get_the_title().' — песни и артист | '.$site;
 if(is_singular('orh_service'))return get_the_title().' — продвижение музыки | '.$site;
 if(is_tax('orh_service_group'))return single_term_title('',false).' — услуги для артистов | '.$site;
 if(is_post_type_archive('orh_song'))return 'Все песни — Онлайн Радио Хит';
 if(is_post_type_archive('orh_service'))return 'Продвижение музыки — услуги для артистов';
 if(is_front_page())return 'Онлайн Радио Хит — слушать радио онлайн 24/7';
 if(is_singular('post')||is_page())return get_the_title().' | '.$site;
 return $site.' — онлайн радио и музыка';
}
function orhseo_desc(){
 if((int)get_query_var('orh_levels')===1)return 'Уровни размещения на Онлайн Радио Хит: возможности артиста при размещении 1, 2–5 и 6+ песен, условия, статистика и развитие присутствия на радио.';
 if(is_singular('orh_song')){
  $aid=(int)get_post_meta(get_the_ID(),'orh_artist_id',true);
  $artist=$aid?get_the_title($aid):'артист';
  return orhseo_trim(get_the_title().' — слушать онлайн на Онлайн Радио Хит. '.$artist.'. Новая музыка и эфир 24/7.');
 }
 if(is_singular('orh_artist'))return orhseo_trim(get_the_title().' — песни и музыка артиста на Онлайн Радио Хит. Слушайте треки онлайн и открывайте новую музыку.');
 if(is_singular('orh_service'))return orhseo_trim(get_the_title().' — услуга по продвижению музыки и песен. Описание, условия и заявка.');
 if(is_tax('orh_service_group'))return orhseo_trim(single_term_title('',false).' — услуги и возможности для продвижения песен, артистов и музыкальных релизов.');
 if(is_front_page())return 'Онлайн Радио Хит — слушайте популярные песни, открывайте новых артистов, следите за чартом и музыкальными премьерами. Эфир 24/7.';
 if(is_post_type_archive('orh_song'))return 'Каталог песен Онлайн Радио Хит: популярные треки, новые релизы и музыка артистов. Слушайте онлайн.';
 if(is_post_type_archive('orh_service'))return 'Услуги по продвижению песен и артистов: направления, конкретные услуги и возможности для музыкального продвижения.';
 if(is_singular('post'))return orhseo_trim(get_the_excerpt()?:get_the_content());
 if(is_page())return orhseo_trim(get_the_excerpt()?:get_bloginfo('description'));
 return orhseo_trim(get_bloginfo('description')?:'Онлайн Радио Хит — популярные песни, новые артисты и музыка 24/7.');
}
function orhseo_title_filter($parts){
 if(!is_admin())$parts['title']=orhseo_title();
 return $parts;
}
add_filter('document_title_parts','orhseo_title_filter',20);

function orhseo_head(){
 if(defined('WPSEO_VERSION')||defined('RANK_MATH_VERSION')||defined('AIOSEO_VERSION'))return;
 $title=orhseo_title();$desc=orhseo_desc();$canonical='';
 if((int)get_query_var('orh_levels')===1)$canonical=home_url('/artist-levels/');
 elseif(is_front_page())$canonical=home_url('/');
 elseif(is_singular()||is_post_type_archive()||is_tax()||is_category()||is_tag())$canonical=get_permalink()?:home_url('/');
 else $canonical=home_url('/');
 $image='';
 if(is_singular()&&has_post_thumbnail())$image=get_the_post_thumbnail_url(get_the_ID(),'large');
 if(!$image)$image=get_theme_file_uri('screenshot.png');
 echo '<meta name="description" content="'.esc_attr($desc).'">'."\n";
 echo '<link rel="canonical" href="'.esc_url($canonical).'">'."\n";
 echo '<meta property="og:type" content="'.(is_singular()?'article':'website').'">'."\n";
 echo '<meta property="og:title" content="'.esc_attr($title).'">'."\n";
 echo '<meta property="og:description" content="'.esc_attr($desc).'">'."\n";
 echo '<meta property="og:url" content="'.esc_url($canonical).'">'."\n";
 echo '<meta property="og:site_name" content="'.esc_attr(get_bloginfo('name')).'">'."\n";
 if($image)echo '<meta property="og:image" content="'.esc_url($image).'">'."\n";
 echo '<meta name="twitter:card" content="summary_large_image">'."\n";
 echo '<meta name="twitter:title" content="'.esc_attr($title).'">'."\n";
 echo '<meta name="twitter:description" content="'.esc_attr($desc).'">'."\n";
 if($image)echo '<meta name="twitter:image" content="'.esc_url($image).'">'."\n";
}
add_action('wp_head','orhseo_head',5);

function orhseo_schema(){
 if(is_admin()||defined('WPSEO_VERSION')||defined('RANK_MATH_VERSION')||defined('AIOSEO_VERSION'))return;
 $is_levels=(int)get_query_var('orh_levels')===1;
 $url=$is_levels?home_url('/artist-levels/'):home_url('/');
 $graph=[['@type'=>'WebSite','url'=>$url,'name'=>get_bloginfo('name'),'description'=>orhseo_desc()]];
 if(is_front_page())$graph[]=['@type'=>'RadioStation','name'=>'Онлайн Радио Хит','url'=>$url,'description'=>'Онлайн Радио Хит — популярные песни, новые артисты и музыка 24/7.'];
 if($is_levels)$graph[]=['@type'=>'WebPage','name'=>'Возможности размещения на радио — Онлайн Радио Хит','url'=>$url,'description'=>orhseo_desc(),'isPartOf'=>['@type'=>'WebSite','url'=>home_url('/')]];
 if(is_singular('orh_song')){
  $item=['@type'=>'MusicRecording','name'=>get_the_title(),'url'=>get_permalink()];
  $aid=(int)get_post_meta(get_the_ID(),'orh_artist_id',true);
  if($aid)$item['byArtist']=['@type'=>'MusicGroup','name'=>get_the_title($aid)];
  if(has_post_thumbnail())$item['image']=get_the_post_thumbnail_url(get_the_ID(),'large');
  $graph[]=$item;
 }
 if(is_singular('orh_service'))$graph[]=['@type'=>'Service','name'=>get_the_title(),'url'=>get_permalink(),'description'=>orhseo_desc(),'provider'=>['@type'=>'Organization','name'=>'Онлайн Радио Хит']];
 echo '<script type="application/ld+json">'.wp_json_encode(['@context'=>'https://schema.org','@graph'=>$graph],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>';
}
add_action('wp_head','orhseo_schema',6);

/* Radio API: the Preview keeps its local demo fallback, while WordPress exposes the real station state. */
function orh_radio_api(){
 register_rest_route('orh/v1','/radio',[
  'methods'=>WP_REST_Server::READABLE,
  'permission_callback'=>'__return_true',
  'callback'=>function(){
   $song=orh_current_song();
   $stream=apply_filters('orh_radio_stream_url',get_option('orh_radio_stream_url',''));
   $artist=$song&&$song['artist_id']?get_the_title($song['artist_id']):'';
   $response=[
    'station'=>'Онлайн Радио Хит',
    'stream'=>esc_url_raw($stream),
    'song'=>$song?[
     'id'=>$song['id'],
     'title'=>$song['title'],
     'artist'=>$artist,
     'audio'=>$song['audio'],
     'cover'=>$song['cover']
    ]:null,
    'updated_at'=>current_time('mysql',true)
   ];
   $result=rest_ensure_response($response);
   $result->header('Cache-Control','no-store, no-cache, must-revalidate, max-age=0');
   return $result;
  }
 ]);
}
add_action('rest_api_init','orh_radio_api');
