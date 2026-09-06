document.addEventListener('DOMContentLoaded',function(){
 const b=document.getElementById('orh_pick_audio'),f=document.getElementById('orh_audio_url'),idField=document.getElementById('orh_audio_id');
 if(!b||!f||typeof wp==='undefined'||!wp.media)return;
 b.addEventListener('click',function(e){
  e.preventDefault();
  const frame=wp.media({title:'Выберите аудиофайл',button:{text:'Использовать файл'},library:{type:'audio'},multiple:false});
  frame.on('select',function(){
   const a=frame.state().get('selection').first().toJSON();
   f.value=a.url||'';
   if(idField)idField.value=a.id||'';
  });
  frame.open();
 });
});
/* V43 — autoplay last uploaded song. */
(function(){
  function initLastUploadedSong(){
    var body=document.body;
    if(!body) return;
    var raw=body.getAttribute('data-orh-last-song');
    if(!raw) return;
    var song;
    try{ song=JSON.parse(raw); }catch(e){ return; }
    if(!song || !song.audio) return;

    var audios=document.querySelectorAll('audio');
    var audio=audios.length ? audios[0] : null;
    if(!audio) return;

    // Do not override a user-selected/current track.
    if(audio.getAttribute('data-orh-initialized')==='1') return;
    audio.setAttribute('data-orh-initialized','1');

    audio.src=song.audio;
    audio.preload='auto';

    var coverUrl=song.cover || '';
    if(coverUrl && typeof window.setCover==='function'){
      window.setCover(coverUrl);
    }

    // Try audible autoplay. Modern browsers may block this.
    var p=audio.play();
    if(p && typeof p.catch==='function'){
      p.catch(function(){
        var start=function(){
          audio.play().catch(function(){});
          document.removeEventListener('click',start);
          document.removeEventListener('touchstart',start);
          document.removeEventListener('keydown',start);
        };
        document.addEventListener('click',start,{once:true});
        document.addEventListener('touchstart',start,{once:true});
        document.addEventListener('keydown',start,{once:true});
      });
    }
  }
  if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',initLastUploadedSong);
  }else{
    initLastUploadedSong();
  }
})();
