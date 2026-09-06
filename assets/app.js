(function(){
'use strict';

var playing=false;
var activeAudio=null;
var radioQueueIndex=0,radioRepeat=false;

function player(){return document.getElementById('orh-stream')||document.getElementById('audio');}
function controls(){return document.querySelectorAll('[data-play],#mainPlay,#listenBtn,.sticky>button');}
function setPlayState(on){
  playing=!!on;
  controls().forEach(function(e){
    e.classList.toggle('is-playing',playing);
    var icon=e.classList.contains('orh-icon')?e:e.querySelector('.orh-icon,.play-shape');
    if(icon){icon.classList.toggle('is-pause',playing);icon.classList.remove('pause');}
    e.setAttribute('aria-label',playing?'Пауза':'Воспроизвести');
  });
  var eq=document.querySelector('.equalizer');if(eq)eq.classList.toggle('is-playing',playing);
}
function setCover(url){
  if(!url)return;
  document.querySelectorAll('[data-current-cover],[data-sticky-cover],.cover-v13').forEach(function(box){
    var img=box.querySelector('img');
    if(!img){img=document.createElement('img');box.insertBefore(img,box.firstChild);}
    img.src=url;img.alt='Обложка текущего трека';img.loading='eager';
    img.style.width='100%';img.style.height='100%';img.style.objectFit='cover';img.style.objectPosition='center';img.style.display='block';
    box.classList.add('has-cover');box.querySelectorAll('span,small').forEach(function(e){e.style.display='none';});
  });
}
function showSticky(force){var s=document.getElementById('stickyPlayer');if(!s)return;if(force||window.scrollY>520)s.classList.add('visible');else s.classList.remove('visible');}
function syncInitialPlayer(){var a=player();if(!a)return false;activeAudio=a;a.preload='auto';return !!(a.getAttribute('src')||a.currentSrc);}
function getQueue(){
  if(Array.isArray(window.ORH_QUEUE)&&window.ORH_QUEUE.length)return window.ORH_QUEUE;
  var q=[];
  document.querySelectorAll('[data-song]').forEach(function(e){
    var title=e.getAttribute('data-song')||'',artist=e.getAttribute('data-artist')||'',url=e.getAttribute('data-audio')||e.getAttribute('data-url')||'',cover=e.getAttribute('data-cover')||'';
    if(title&&!q.some(function(x){return x.title===title&&x.artist===artist;}))q.push({id:e.getAttribute('data-id')||'',title:title,artist:artist,url:url,cover:cover});
  });
  return q;
}
window.toggleRadio=function(){
  var a=player();if(!a)return;activeAudio=a;
  var src=a.getAttribute('src')||a.currentSrc;
  if(!src){setPlayState(false);return;}
  if(a._orhPlayPending)return;
  if(!a.paused){a.pause();return;}
  a._orhPlayPending=true;
  try{if(a.readyState===0)a.load();}catch(e){}
  var p;
  try{p=a.play();}catch(e){a._orhPlayPending=false;setPlayState(false);return;}
  if(p&&p.then)p.then(function(){a._orhPlayPending=false;setPlayState(true);showSticky(true);}).catch(function(){a._orhPlayPending=false;setPlayState(false);});
  else{a._orhPlayPending=false;setPlayState(true);showSticky(true);}
};
window.toggleActiveAudio=window.toggleRadio;
window.choose=function(title,artist,cover){
  try{sessionStorage.setItem('orh_last_ui_track',JSON.stringify({title:title||'',artist:artist||'',cover:cover||''}));}catch(e){}
  document.querySelectorAll('[data-current-song],[data-sticky-song],#currentTitle,#tickerTitle').forEach(function(e){e.textContent=title||'';});
  document.querySelectorAll('[data-current-artist],[data-sticky-artist],#currentArtist').forEach(function(e){e.textContent=artist||'';});
  if(cover)setCover(cover);
};
window.playSong=function(id,title,artist,url,cover){
  window.choose(title,artist,cover);if(!url)return;
  var a=player();if(!a){a=document.createElement('audio');a.id='orh-stream';a.preload='auto';document.body.appendChild(a);}
  a.src=url;a.preload='auto';activeAudio=a;
  var vol=document.querySelector('[data-volume],#volume');if(vol){a.volume=Number(vol.value);a.muted=(a.volume===0);}
  try{a.load();}catch(e){}
  var p=a.play();if(p&&p.then)p.then(function(){setPlayState(true);showSticky(true);countPlay(id);}).catch(function(){setPlayState(false);});
};
function countPlay(id){if(!id||!window.ORH||!ORH.ajax||!ORH.nonce)return;fetch(ORH.ajax,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'orh_count_play',nonce:ORH.nonce,song_id:id})}).catch(function(){});}
function subscribeForm(e){e.preventDefault();var input=document.getElementById('mailEmail'),msg=document.getElementById('mailMsg');if(!input||!msg||!window.ORH)return;fetch(ORH.ajax,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'orh_subscribe',nonce:ORH.subscribe_nonce||ORH.nonce,email:input.value})}).then(function(r){return r.json();}).then(function(d){msg.textContent=(d.data&&d.data.message)||'Готово.';if(d.success)e.target.reset();}).catch(function(){msg.textContent='Не удалось отправить заявку.';});}
window.subscribeForm=subscribeForm;
function findCurrentQueueIndex(){var q=getQueue(),a=player();if(!q.length||!a)return 0;var src=a.getAttribute('src')||a.currentSrc||'';for(var i=0;i<q.length;i++)if(q[i].url&&q[i].url===src)return i;return Math.max(0,radioQueueIndex);}
function radioLoadIndex(i,playNow){
  var q=getQueue();if(!q.length)return;if(i<0)i=q.length-1;if(i>=q.length)i=0;radioQueueIndex=i;var x=q[i];
  choose(x.title,x.artist,x.cover);
  if(!x.url){setPlayState(false);return;}
  var a=player();if(!a)return;a.src=x.url;a.preload='auto';activeAudio=a;
  var pr=document.querySelector('[data-progress],#progress'),ct=document.querySelector('[data-current-time],#currentTime'),du=document.querySelector('[data-duration],#duration');
  if(pr)pr.value=0;if(ct)ct.textContent='0:00';if(du)du.textContent='0:00';
  try{a.load();}catch(e){}
  if(playNow){var p=a.play();if(p&&p.then)p.then(function(){setPlayState(true);showSticky(true);countPlay(x.id);}).catch(function(){setPlayState(false);});}
}
window.radioPrevious=function(){radioQueueIndex=findCurrentQueueIndex()-1;radioLoadIndex(radioQueueIndex,true);};
window.radioNext=function(){radioQueueIndex=findCurrentQueueIndex()+1;radioLoadIndex(radioQueueIndex,true);};
window.toggleRepeat=function(){radioRepeat=!radioRepeat;document.querySelectorAll('[data-repeat-toggle],[data-repeat],#repeatBtn').forEach(function(b){b.classList.toggle('is-active',radioRepeat);b.setAttribute('aria-pressed',radioRepeat?'true':'false');});var a=player();if(a)a.loop=radioRepeat;};
function bindControls(){
  var main=document.getElementById('mainPlay'),listen=document.getElementById('listenBtn'),prev=document.getElementById('prevBtn'),next=document.getElementById('nextBtn'),sticky=document.querySelector('.sticky>button');
  if(main)main.addEventListener('click',function(){toggleRadio();});
  if(listen)listen.addEventListener('click',function(){toggleRadio();});
  if(prev)prev.addEventListener('click',function(){radioPrevious();});
  if(next)next.addEventListener('click',function(){radioNext();});
  if(sticky)sticky.addEventListener('click',function(){toggleRadio();});
  /* Repeat controls already use inline handlers in the current WordPress markup. Do not add a second listener. */
  document.querySelectorAll('[data-song]').forEach(function(el){el.addEventListener('click',function(e){e.preventDefault();var title=el.getAttribute('data-song')||'',artist=el.getAttribute('data-artist')||'',url=el.getAttribute('data-audio')||el.getAttribute('data-url')||'',cover=el.getAttribute('data-cover')||'';if(url)playSong(el.getAttribute('data-id')||'',title,artist,url,cover);else choose(title,artist,cover);});});
  var form=document.querySelector('form[data-mail],#mailForm');if(form)form.addEventListener('submit',subscribeForm);
}
function tryAutoplayRadio(){var a=player();if(!a)return false;activeAudio=a;var src=a.getAttribute('src')||a.currentSrc;if(!src)return false;a.autoplay=true;a.preload='auto';function audiblePlay(){if(!a.paused)return true;try{var p=a.play();if(p&&p.then)p.then(function(){setPlayState(true);showSticky(true);}).catch(function(){});return true;}catch(e){return false;}}if(a.readyState>=2&&!a._orhPlayPending)audiblePlay();else{a.addEventListener('canplay',audiblePlay,{once:true});a.addEventListener('loadeddata',audiblePlay,{once:true});}return true;}
function bindPlayer(){
  var a=player();if(!a)return;activeAudio=a;
  var progress=document.querySelector('[data-progress],#progress'),volume=document.querySelector('[data-volume],#volume');
  if(volume){volume.value=isFinite(a.volume)?a.volume:.8;volume.addEventListener('input',function(){var v=Math.max(0,Math.min(1,Number(volume.value)));a.volume=v;a.muted=(v===0);});}
  if(progress){var currentTimeEl=document.querySelector('[data-current-time],#currentTime'),durationEl=document.querySelector('[data-duration],#duration');var fmt=function(sec){sec=Math.max(0,Math.floor(Number(sec)||0));return Math.floor(sec/60)+':'+String(sec%60).padStart(2,'0');};var sync=function(){var d=a.duration;if(isFinite(d)&&d>0){progress.value=(a.currentTime/d)*100;if(durationEl)durationEl.textContent=fmt(d);}else{progress.value=0;if(durationEl)durationEl.textContent='0:00';}if(currentTimeEl)currentTimeEl.textContent=fmt(a.currentTime);var st=document.querySelector('[data-sticky-time]');if(st)st.textContent=fmt(a.currentTime);};a.addEventListener('timeupdate',sync);a.addEventListener('loadedmetadata',sync);a.addEventListener('durationchange',sync);progress.addEventListener('input',function(){if(isFinite(a.duration)&&a.duration>0)a.currentTime=(Number(progress.value)/100)*a.duration;});}
  radioQueueIndex=findCurrentQueueIndex();a.addEventListener('play',function(){setPlayState(true);});a.addEventListener('pause',function(){setPlayState(false);});a.addEventListener('ended',function(){if(radioRepeat)return;setPlayState(false);radioNext();});
}
document.addEventListener('DOMContentLoaded',function(){bindControls();bindPlayer();syncInitialPlayer();tryAutoplayRadio();setTimeout(function(){tryAutoplayRadio();},1200);window.addEventListener('scroll',function(){showSticky(false);},{passive:true});showSticky(false);});
document.addEventListener('submit',function(e){var form=e.target;if(!form||form.dataset.orhSubmitLock==='1')return;var btn=form.querySelector('button[type="submit"],input[type="submit"]');if(!btn)return;form.dataset.orhSubmitLock='1';setTimeout(function(){form.dataset.orhSubmitLock='0';},5000);},true);
document.addEventListener('keydown',function(e){if(e.target&&/INPUT|TEXTAREA|SELECT/.test(e.target.tagName))return;var a=player();if(!a)return;if(e.code==='Space'){e.preventDefault();toggleRadio();}else if(e.code==='ArrowRight'&&e.shiftKey){e.preventDefault();if(isFinite(a.duration))a.currentTime=Math.min(a.duration,a.currentTime+10);}else if(e.code==='ArrowLeft'&&e.shiftKey){e.preventDefault();if(isFinite(a.duration))a.currentTime=Math.max(0,a.currentTime-10);}});
})();
