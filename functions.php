<?php
if (!defined('ABSPATH')) exit;
define('ORH_VERSION','46.0.0');

function orh_setup(){
 add_theme_support('title-tag'); add_theme_support('post-thumbnails');
 add_theme_support('html5',['search-form','comment-form','comment-list','gallery','caption','style','script']);
 register_nav_menus(['primary'=>'Главное меню']);
}
add_action('after_setup_theme','orh_setup');

function orh_assets(){
 wp_enqueue_style('orh-style',get_stylesheet_uri(),[],ORH_VERSION);
 wp_enqueue_script('orh-app',get_template_directory_uri().'/assets/app.js',[],ORH_VERSION,true);
 wp_localize_script('orh-app','ORH',['ajax'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('orh_player')]);
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

/* ORH SEO 9.1 — safe lightweight SEO fallback */
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
 if(is_front_page())$canonical=home_url('/');
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
 $url=home_url('/');$graph=[['@type'=>'WebSite','url'=>$url,'name'=>get_bloginfo('name'),'description'=>orhseo_desc()]];
 if(is_front_page())$graph[]=['@type'=>'RadioStation','name'=>'Онлайн Радио Хит','url'=>$url,'description'=>'Онлайн Радио Хит — популярные песни, новые артисты и музыка 24/7.'];
 if(is_singular('orh_song')){
  $item=['@type'=>'MusicRecording','name'=>get_the_title(),'url'=>get_permalink()];
  $aid=(int)get_post_meta(get_the_ID(),'orh_artist_id',true);
  if($aid)$item['byArtist']=['@type'=>'MusicGroup','name'=>get_the_title($aid)];
  if(has_post_thumbnail())$item['image']=get_the_post_thumbnail_url(get_the_ID(),'large');
  $graph[]=$item;
 }
 if(is_singular('orh_service'))$graph[]=['@type'=>'Service','name'=>get_the_title(),'url'=>get_permalink(),'description'=>orhseo_desc(),'provider'=>['@type'=>'Organization','name'=>'Онлайн Радио Хит','url'=>$url]];
 echo '<script type="application/ld+json">'.wp_json_encode(['@context'=>'https://schema.org','@graph'=>$graph],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>'."\n";
}
add_action('wp_head','orhseo_schema',6);

/* Per-page SEO fields */
function orhseo_metaboxes(){
 foreach(['post','page','orh_song','orh_artist','orh_service'] as $type)add_meta_box('orhseo_box','SEO страницы','orhseo_box_html',$type,'normal','high');
}
add_action('add_meta_boxes','orhseo_metaboxes');
function orhseo_box_html($post){
 wp_nonce_field('orhseo_save','orhseo_nonce');
 $t=get_post_meta($post->ID,'_orhseo_title',true);$d=get_post_meta($post->ID,'_orhseo_description',true);
 echo '<p><label><b>SEO Title</b><br><input class="large-text" name="_orhseo_title" value="'.esc_attr($t).'" placeholder="'.esc_attr(orhseo_title()).'"></label></p>';
 echo '<p><label><b>Meta Description</b><br><textarea class="large-text" rows="3" name="_orhseo_description" placeholder="'.esc_attr(orhseo_desc()).'">'.esc_textarea($d).'</textarea></label></p>';
}
function orhseo_save($id){
 if(!isset($_POST['orhseo_nonce'])||!wp_verify_nonce($_POST['orhseo_nonce'],'orhseo_save'))return;
 if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)return;
 if(!current_user_can('edit_post',$id))return;
 update_post_meta($id,'_orhseo_title',sanitize_text_field($_POST['_orhseo_title']??''));
 update_post_meta($id,'_orhseo_description',sanitize_textarea_field($_POST['_orhseo_description']??''));
}
add_action('save_post','orhseo_save');
function orhseo_title_custom($parts){
 $id=get_queried_object_id();$custom=$id?get_post_meta($id,'_orhseo_title',true):'';
 if($custom&&!is_admin())$parts['title']=$custom;
 return $parts;
}
add_filter('document_title_parts','orhseo_title_custom',30);
function orhseo_desc_custom(){
 if(is_admin())return;
 $id=get_queried_object_id();$custom=$id?get_post_meta($id,'_orhseo_description',true):'';
 if($custom&&!defined('WPSEO_VERSION')&&!defined('RANK_MATH_VERSION')&&!defined('AIOSEO_VERSION')){
  echo '<meta name="description" content="'.esc_attr(orhseo_trim($custom)).'">'."\n";
 }
}
add_action('wp_head','orhseo_desc_custom',4);




/* V37 — real media resolver: featured image -> ORH ID/URL -> matching JPG in Media Library. */
if (!function_exists('orh_media_url')) {
function orh_media_url($post_id, $size='large') {
    $post_id=(int)$post_id;
    if(!$post_id) return '';

    $ids=[];
    $thumb=(int)get_post_thumbnail_id($post_id);
    if($thumb) $ids[]=$thumb;

    $saved=(int)get_post_meta($post_id,'orh_image_id',true);
    if($saved) $ids[]=$saved;

    foreach(array_unique($ids) as $id){
        $url=wp_get_attachment_image_url($id,$size);
        if(!$url) $url=wp_get_attachment_url($id);
        if($url) return esc_url_raw($url);
    }

    $saved_url=trim((string)get_post_meta($post_id,'orh_image_url',true));
    if($saved_url && filter_var($saved_url,FILTER_VALIDATE_URL)) return esc_url_raw($saved_url);

    /*
     * Last real-data fallback: find a JPG in Media Library whose filename
     * matches the post slug/title. This is intentionally restricted to JPG
     * and to the same record's semantic name, never an arbitrary image.
     */
    $slug=sanitize_title(get_post_field('post_name',$post_id));
    $title_slug=sanitize_title(get_the_title($post_id));
    $needles=array_values(array_unique(array_filter([$slug,$title_slug])));
    if($needles){
        $atts=get_posts([
            'post_type'=>'attachment',
            'post_status'=>'inherit',
            'post_mime_type'=>'image/jpeg',
            'posts_per_page'=>100,
            'orderby'=>'ID',
            'order'=>'DESC',
            'fields'=>'ids'
        ]);
        foreach($atts as $aid){
            $file=get_attached_file($aid);
            $name=$file?pathinfo($file,PATHINFO_FILENAME):'';
            $aname=sanitize_title($name);
            foreach($needles as $needle){
                if($needle && ($aname===$needle || preg_match('/^'.preg_quote($needle,'/').'(-\d+)?$/',$aname))){
                    $url=wp_get_attachment_image_url($aid,$size);
                    if(!$url) $url=wp_get_attachment_url($aid);
                    if($url) return esc_url_raw($url);
                }
            }
        }
    }

    return '';
}
}

if (!function_exists('orh_song_audio')) {
function orh_song_audio($post_id) {
    $url = trim((string)get_post_meta((int)$post_id, 'orh_audio_url', true));
    if ($url && filter_var($url, FILTER_VALIDATE_URL)) return esc_url_raw($url);

    $children = get_children([
        'post_parent' => (int)$post_id,
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'numberposts' => 30,
        'orderby' => 'ID',
        'order' => 'DESC',
    ]);

    foreach ($children as $attachment) {
        if (strpos((string)$attachment->post_mime_type, 'audio/') === 0) {
            $url = wp_get_attachment_url($attachment->ID);
            if ($url) return esc_url_raw($url);
        }
    }
    return '';
}
}


/* V41 functional polish — no layout changes. */
add_filter('style_loader_src', function($src,$handle){
    if($handle==='orh-style') return add_query_arg('orh_v','113',$src);
    return $src;
},10,2);

add_filter('script_loader_src', function($src,$handle){
    if(strpos($handle,'orh')===0) return add_query_arg('orh_v','113',$src);
    return $src;
},10,2);

add_filter('wp_get_attachment_image_attributes', function($attr){
    if(empty($attr['loading'])) $attr['loading']='lazy';
    return $attr;
},10,1);


/* V42 — reliable header menu fallback. Keeps the approved design and works even
   when WordPress has no menu assigned to the Primary location. */
if (!function_exists('orh_primary_menu_fallback')) {
function orh_primary_menu_fallback(){
    $items=[
        ['Главная', home_url('/')],
        ['Сейчас слушают', home_url('/#overview')],
        ['Чарт', home_url('/#chart')],
        ['Артисты', home_url('/#artists')],
        ['Новости', home_url('/#news')],
        ['Услуги', home_url('/#promotion')],
    ];
    echo '<ul class="menu">';
    foreach($items as $item){
        echo '<li class="menu-item"><a href="'.esc_url($item[1]).'">'.esc_html($item[0]).'</a></li>';
    }
    echo '</ul>';
}
}

/* V48 — deterministic latest uploaded song. */
if (!function_exists('orh_mark_audio_upload')) {
function orh_mark_audio_upload($meta_id,$object_id,$meta_key,$meta_value){
    if($meta_key !== 'orh_audio_url' || get_post_type($object_id) !== 'orh_song') return;
    if(!$meta_value) return;
    update_post_meta((int)$object_id,'orh_audio_uploaded_at',current_time('timestamp'));
}
}
add_action('updated_post_meta','orh_mark_audio_upload',10,4);
add_action('added_post_meta','orh_mark_audio_upload',10,4);

if (!function_exists('orh_last_uploaded_song')) {
function orh_last_uploaded_song(){
    $ids=get_posts([
        'post_type'=>'orh_song',
        'post_status'=>'publish',
        'posts_per_page'=>100,
        'fields'=>'ids',
        'orderby'=>'ID',
        'order'=>'DESC',
        'ignore_sticky_posts'=>true,
    ]);
    if(!$ids) return null;

    $best=null; $best_ts=0;
    foreach($ids as $id){
        $audio=function_exists('orh_song_audio') ? orh_song_audio($id) : '';
        if(!$audio) continue;
        $ts=(int)get_post_meta($id,'orh_audio_uploaded_at',true);
        if(!$ts) $ts=(int)get_post_field('post_modified_time',$id,true);
        if(!$ts) $ts=(int)get_post_time('U',true,$id);
        if($best===null || $ts>$best_ts){
            $best=$id; $best_ts=$ts;
        }
    }
    if(!$best){
        $fallback=get_posts([
            'post_type'=>'orh_song',
            'post_status'=>'publish',
            'posts_per_page'=>50,
            'orderby'=>'date',
            'order'=>'DESC',
            'ignore_sticky_posts'=>true,
        ]);
        foreach($fallback as $candidate){
            $candidate_audio=orh_song_audio($candidate->ID);
            if($candidate_audio){
                $best=(int)$candidate->ID;
                break;
            }
        }
    }
    if(!$best) return null;

    $audio=orh_song_audio($best);
    $cover=function_exists('orh_media_url') ? orh_media_url($best,'large') : '';
    $aid=(int)get_post_meta($best,'orh_artist_id',true);
    $artist=(string)get_post_meta($best,'orh_artist_name',true);
    if(!$artist && $aid) $artist=(string)get_the_title($aid);

    return [
        'id'=>(int)$best,
        'title'=>get_the_title($best),
        'artist'=>$artist,
        'artist_id'=>$aid,
        'audio'=>$audio,
        'cover'=>$cover,
    ];
}
}
