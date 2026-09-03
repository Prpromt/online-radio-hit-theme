(function(){
'use strict';

var playing=false;
var radioAudio=null;
var activeAudio=null;

function player(){
  return document.getElementById('orh-stream');
}

function setPlayState(on){
  playing=!!on;
  document.querySelectorAll('[data-play]').forEach(function(e){
    e.classList.toggle('is-playing',playing);
    var icon=e.classList.contains('orh-icon') ? e : e.querySelector('.orh-icon');
    if(icon) icon.classList.toggle('pause',playing);
  });
  var eq=document.querySelector('.equalizer');
  if(eq) eq.classList.toggle('is-playing',playing);
}

function setCover(url){
  if(!url)return;
  document.querySelectorAll('[data-current-cover],[data-sticky-cover]').forEach(function(box){
    var img=box.querySelector('img');
    if(!img){
      img=document.createElement('img');
      box.insertBefore(img,box.firstChild);
    }
    // IMPORTANT: reuse the existing server-rendered image.
    // Never append a second image into the square cover.
    img.src=url;
    img.alt='Обложка текущего трека';
    img.loading='eager';
    img.style.width='100%';
    img.style.height='100%';
    img.style.objectFit='cover';
    img.style.objectPosition='center';
    img.style.display='block';
    box.classList.add('has-cover');
    box.querySelectorAll('span,small').forEach(function(e){e.style.display='none';});
  });
}

function showSticky(force){
  var s=document.getElementById('stickyPlayer');
  if(!s)return;
  if(force || window.scrollY>520)s.classList.add('visible');
  else s.classList.remove('visible');
}


function syncInitialPlayer(){
  var a=player();
  if(!a)return false;
  activeAudio=a;
  a.preload='auto';

  var src=a.getAttribute('src') || a.currentSrc;
  if(src){
    return true;
  }
  return false;
}

window.toggleRadio=function(){
  var a=document.getElementById('orh-stream');
  if(!a)return;
  activeAudio=a;

  var src=a.getAttribute('src') || a.currentSrc;
  if(!src)return;

  // A play request can remain pending while the browser loads the MP3.
  // Do not turn that first click into a pause on the next event.
  if(a._orhPlayPending){
    return;
  }

  if(!a.paused){
    a.pause();
    setPlayState(false);
    return;
  }

  a._orhPlayPending=true;
  try{
    if(a.readyState===0) a.load();
  }catch(e){}

  var p;
  try{ p=a.play(); }
  catch(e){
    a._orhPlayPending=false;
    setPlayState(false);
    return;
  }

  if(p&&p.then){
    p.then(function(){
      a._orhPlayPending=false;
      setPlayState(true);
      showSticky(true);
    }).catch(function(){
      a._orhPlayPending=false;
      setPlayState(false);
      showSticky(true);
    });
  }else{
    a._orhPlayPending=false;
    setPlayState(true);
    showSticky(true);
  }
};

window.toggleActiveAudio=function(){
  var a=player() || activeAudio;
  if(!a)return;
  activeAudio=a;
  var src=a.getAttribute('src') || a.currentSrc;
  if(!src)return;
  if(a.paused){
    if(a.readyState===0){try{a.load();}catch(e){}}
    var p=a.play();
    if(p&&p.then)p.then(function(){setPlayState(true);showSticky(true);}).catch(function(){setPlayState(false);});
  }else{
    a.pause();
    setPlayState(false);
  }
};

window.choose=function(title,artist,cover){
  try{
    sessionStorage.setItem('orh_last_ui_track',JSON.stringify({title:title||'',artist:artist||'',cover:cover||''}));
  }catch(e){}
  document.querySelectorAll('[data-current-song],[data-sticky-song]').forEach(function(e){e.textContent=title||'';});
  document.querySelectorAll('[data-current-artist],[data-sticky-artist]').forEach(function(e){e.textContent=artist||'';});
  if(cover)setCover(cover);
  window.scrollTo({top:0,behavior:'smooth'});
};

window.playSong=function(id,title,artist,url,cover){
  window.choose(title,artist,cover);
  if(!url)return;

  var a=player();
  if(!a){
    a=document.createElement('audio');
    a.id='orh-stream';
    a.preload='auto';
    document.body.appendChild(a);
  }

  a.src=url;
  a.preload='auto';
  activeAudio=a;
  var vol=document.querySelector('[data-volume]'); if(vol){a.volume=Number(vol.value);a.muted=(a.volume===0);}
  try{a.load();}catch(e){}

  a.play().then(function(){
    setPlayState(true);
    showSticky(true);
    countPlay(id);
  }).catch(function(){
    setPlayState(false);
    showSticky(true);
  });
};

function countPlay(id){
  if(!window.ORH)return;
  fetch(ORH.ajax,{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:new URLSearchParams({
      action:'orh_count_play',
      nonce:ORH.nonce,
      song_id:id
    })
  }).catch(function(){});
}

window.openArtists=function(){
  var x=document.getElementById('artistsMenu');
  if(x)x.classList.toggle('open');
};

window.subscribeForm=function(e){
  e.preventDefault();
  var input=document.getElementById('mailEmail');
  var msg=document.getElementById('mailMsg');
  if(!input||!msg)return;
  fetch(ORH.ajax,{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:new URLSearchParams({
      action:'orh_subscribe',
      nonce:ORH.nonce,
      email:input.value
    })
  }).then(function(r){return r.json();}).then(function(d){
    msg.textContent=(d.data&&d.data.message)||'Готово.';
    if(d.success)e.target.reset();
  }).catch(function(){msg.textContent='Не удалось отправить заявку.';});
};


var radioQueueIndex=0;
var radioRepeat=false;

function findCurrentQueueIndex(){
  var q=window.ORH_QUEUE||[];
  var a=player(); if(!q.length||!a)return 0;
  var src=a.getAttribute('src')||a.currentSrc||'';
  for(var i=0;i<q.length;i++) if(q[i].url===src)return i;
  return 0;
}
function radioLoadIndex(i,playNow){
  var q=window.ORH_QUEUE||[];
  if(!q.length)return;
  if(i<0)i=q.length-1;
  if(i>=q.length)i=0;
  radioQueueIndex=i;
  var x=q[i], a=player();
  if(!a)return;
  choose(x.title,x.artist,x.cover);
  a.src=x.url;
  a.preload='auto';
  var pr=document.querySelector('[data-progress]');
  var ct=document.querySelector('[data-current-time]');
  var du=document.querySelector('[data-duration]');
  if(pr) pr.value=0;
  if(ct) ct.textContent='0:00';
  if(du) du.textContent='0:00';
  activeAudio=a;
  try{a.load();}catch(e){}
  if(playNow){
    var p=a.play();
    if(p&&p.then)p.then(function(){setPlayState(true);showSticky(true);countPlay(x.id);}).catch(function(){setPlayState(false);});
  }
}
window.radioPrevious=function(){
  radioQueueIndex=findCurrentQueueIndex()-1;
  radioLoadIndex(radioQueueIndex,true);
};
window.radioNext=function(){
  radioQueueIndex=findCurrentQueueIndex()+1;
  radioLoadIndex(radioQueueIndex,true);
};
window.toggleRepeat=function(){
  radioRepeat=!radioRepeat;
  var b=document.querySelector('[data-repeat-toggle]');
  if(b)b.classList.toggle('is-active',radioRepeat);
  var a=player();
  if(a)a.loop=radioRepeat;
};

function tryAutoplayRadio(){
  var a=document.getElementById('orh-stream');
  if(!a)return false;
  activeAudio=a;
  var src=a.getAttribute('src')||a.currentSrc;
  if(!src)return false;

  a.autoplay=true;
  a.preload='auto';

  // First try the real radio autoplay with sound.
  function audiblePlay(){
    if(!a.paused)return true;
    try{
      a.muted=false;
      var p=a.play();
      if(p&&p.then){
        p.then(function(){
          setPlayState(true);
          showSticky(true);
        }).catch(function(){});
      }
      return true;
    }catch(e){ return false; }
  }

  // If the browser has not yet allowed audible autoplay, keep the exact
  // current track ready and retry when the media becomes playable.
  if(a.readyState>=2 && !a._orhPlayPending) audiblePlay();
  else {
    a.addEventListener('canplay',audiblePlay,{once:true});
    a.addEventListener('loadeddata',audiblePlay,{once:true});
  }
  return true;
}

document.addEventListener('DOMContentLoaded',function(){
  var playerBox=document.querySelector('.player-bottom-v13');
  if(playerBox){
    playerBox.querySelectorAll('input[type="range"]').forEach(function(el){
      if(!el.matches('[data-progress],[data-volume]')) el.remove();
    });
  }

  var a=player();
  if(a){
    activeAudio=a;
    var progress=document.querySelector('[data-progress]');
    var volume=document.querySelector('[data-volume]');
    if(volume){
      volume.value=isFinite(a.volume)?a.volume:0.8;
      volume.addEventListener('input',function(){
        var v=Math.max(0,Math.min(1,Number(volume.value)));
        a.volume=v;
        a.muted=(v===0);
      });
    }
    if(progress){
      var currentTimeEl=document.querySelector('[data-current-time]');
      var durationEl=document.querySelector('[data-duration]');
      var fmtTime=function(sec){
        sec=Math.max(0,Math.floor(Number(sec)||0));
        var m=Math.floor(sec/60), ss=String(sec%60).padStart(2,'0');
        return m+':'+ss;
      };
      var syncProgress=function(){
        if(isFinite(a.duration)&&a.duration>0){
          progress.value=(a.currentTime/a.duration)*100;
          if(durationEl) durationEl.textContent=fmtTime(a.duration);
        }else{
          progress.value=0;
          if(durationEl) durationEl.textContent='0:00';
        }
        if(currentTimeEl) currentTimeEl.textContent=fmtTime(a.currentTime);
        var stickyTime=document.querySelector('[data-sticky-time]'); if(stickyTime) stickyTime.textContent=fmtTime(a.currentTime);
      };
      a.addEventListener('timeupdate',syncProgress);
      a.addEventListener('loadedmetadata',syncProgress);
      a.addEventListener('durationchange',syncProgress);
      progress.addEventListener('input',function(){
        if(isFinite(a.duration)&&a.duration>0){
          a.currentTime=(Number(progress.value)/100)*a.duration;
        }
      });
    }
    radioQueueIndex=findCurrentQueueIndex();
    a.addEventListener('play',function(){setPlayState(true);});
    a.addEventListener('pause',function(){setPlayState(false);});
    a.addEventListener('ended',function(){if(radioRepeat)return;setPlayState(false);radioNext();});

    syncInitialPlayer();

  }

  tryAutoplayRadio();
  setTimeout(function(){ if(!a || !a._orhPlayPending) tryAutoplayRadio(); },1200);
  window.addEventListener('scroll',function(){showSticky(false);},{passive:true});
  showSticky(false);
});
})();

/* V86 — prevent accidental double submit on front-end forms */
document.addEventListener('submit',function(e){
  var form=e.target;
  if(!form || form.dataset.orhSubmitLock==='1') return;
  var btn=form.querySelector('button[type="submit"],input[type="submit"]');
  if(!btn) return;
  form.dataset.orhSubmitLock='1';
  setTimeout(function(){form.dataset.orhSubmitLock='0';},5000);
},true);

/* V87 — player keyboard/touch usability */
document.addEventListener('keydown',function(e){
  if(e.target && /INPUT|TEXTAREA|SELECT/.test(e.target.tagName)) return;
  var a=player && player();
  if(!a) return;
  if(e.code==='Space'){
    e.preventDefault();
    toggleRadio();
  }else if(e.code==='ArrowRight' && e.shiftKey){
    e.preventDefault();
    if(isFinite(a.duration)) a.currentTime=Math.min(a.duration,a.currentTime+10);
  }else if(e.code==='ArrowLeft' && e.shiftKey){
    e.preventDefault();
    if(isFinite(a.duration)) a.currentTime=Math.max(0,a.currentTime-10);
  }
});
