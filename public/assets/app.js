(()=>{
'use strict';
const $=s=>document.querySelector(s), $$=s=>document.querySelectorAll(s);
const audio=$('#audio');
if(!audio)return;
const title=$('#currentTitle'), artist=$('#currentArtist'), ticker=$('#tickerTitle');
const progress=$('#progress'), volume=$('#volume'), currentTime=$('#currentTime'), duration=$('#duration');
const mainPlay=$('#mainPlay'), listenBtn=$('#listenBtn'), eq=$('#eq'), menuBtn=$('#menuBtn'), nav=$('.site-nav'), player=$('.player'), playerCover=$('.cover');
const songs=[
 ['Ночной город','KARAT','/assets/covers/night-city.svg'],['Jumanji','KARAT & МЛАДШИЙ','/assets/covers/jumanji.svg'],
 ['Ты такая славная','Вячеслав Калинин','/assets/covers/slavnaya.svg'],['Просто я ищу тебя','Новый артист','/assets/covers/iskayu.svg'],
 ['Летуаль','PELIKУANA','/assets/covers/letual.svg'],['Когда расцветает сирень','Андрей Додонов','/assets/covers/siren.svg'],
 ['Мираж','Светлана Рапацкая','/assets/covers/mirage.svg'],['Тёмная ночь','Новый артист','/assets/covers/dark-night.svg'],
 ['Ивушка','Новый артист','/assets/covers/ivushka.svg'],['Твои Зелёные глаза','Новый артист','/assets/covers/green-eyes.svg']
];
let index=0, repeat=false;
const hasAudio=Boolean((audio.getAttribute('src')||'').trim());
const fmt=s=>Number.isFinite(s)?Math.floor(s/60)+':'+String(Math.floor(s%60)).padStart(2,'0'):'0:00';
function artwork(i){if(!playerCover)return;playerCover.classList.add('has-art');playerCover.style.backgroundImage='url("'+songs[i][2]+'")';playerCover.style.backgroundSize='cover';playerCover.style.backgroundPosition='center';}
function sync(){const on=hasAudio&&!audio.paused;mainPlay?.classList.toggle('is-playing',on);listenBtn?.classList.toggle('is-playing',on);eq?.classList.toggle('playing',on);mainPlay?.setAttribute('aria-label',on?'Пауза':'Воспроизвести');mainPlay?.querySelector('.orh-icon')?.classList.toggle('is-pause',on);}
function notice(text,type='info'){if(!player)return;let n=$('#playerNotice');if(!n){n=document.createElement('div');n.id='playerNotice';n.className='player-notice';n.setAttribute('role','status');n.setAttribute('aria-live','polite');player.appendChild(n);}n.className='player-notice '+type;n.textContent=text;clearTimeout(notice.t);notice.t=setTimeout(()=>{n.textContent='';},4200);}
function play(){if(!hasAudio){notice('В Preview интерфейс плеера готов. Реальный эфир подключается после добавления радиопотока.');return;}audio.play().then(sync).catch(()=>{notice('Источник аудио недоступен в Preview.','error');sync();});}
function setTrack(i,shouldPlay=false){index=(i+songs.length)%songs.length;const t=songs[index][0],a=songs[index][1];if(title)title.textContent=t;if(artist)artist.textContent=a;if(ticker)ticker.textContent=t;if(hasAudio)audio.currentTime=0;artwork(index);sync();if(shouldPlay)play();}
function stop(e){e.preventDefault();e.stopImmediatePropagation();}
function toggle(e){if(e)stop(e);if(!hasAudio){notice('В Preview выбранный трек отображается, но реальный радиопоток ещё не подключён.');return;}if(audio.paused)play();else audio.pause();sync();}
mainPlay?.addEventListener('click',toggle,true);listenBtn?.addEventListener('click',toggle,true);
$('#prevBtn')?.addEventListener('click',e=>{stop(e);setTrack(index-1,true);},true);
$('#nextBtn')?.addEventListener('click',e=>{stop(e);setTrack(index+1,true);},true);
$$('[aria-label="Повтор"]').forEach(btn=>btn.addEventListener('click',e=>{stop(e);repeat=!repeat;btn.classList.toggle('active',repeat);btn.setAttribute('aria-pressed',String(repeat));notice(repeat?'Повтор включён':'Повтор выключен');},true));
$$('[data-track],[data-song]').forEach(btn=>btn.addEventListener('click',e=>{stop(e);let n=Number(btn.dataset.track);if(!Number.isInteger(n)||btn.dataset.track==='')n=songs.findIndex(s=>s[0]===btn.dataset.song);if(n>=0)setTrack(n,true);},true));
audio.addEventListener('play',sync);audio.addEventListener('pause',sync);audio.addEventListener('error',()=>{notice('Источник аудио недоступен в Preview. Плеер работает в режиме демонстрации.','error');sync();});
audio.addEventListener('loadedmetadata',()=>{if(duration)duration.textContent=fmt(audio.duration);});
audio.addEventListener('timeupdate',()=>{if(currentTime)currentTime.textContent=fmt(audio.currentTime);if(duration)duration.textContent=fmt(audio.duration);if(progress&&audio.duration)progress.value=audio.currentTime/audio.duration*100;});
audio.addEventListener('ended',()=>repeat?play():setTrack(index+1,true));
progress?.addEventListener('input',()=>{if(audio.duration)audio.currentTime=Number(progress.value)/100*audio.duration;});
volume?.addEventListener('input',()=>{audio.volume=Number(volume.value);});
audio.volume=Number(volume?.value||.8);
let menuOpen=false,returnFocus=null;
function closeMenu(restore=true){if(!nav?.classList.contains('is-open'))return;nav.classList.remove('is-open');menuOpen=false;menuBtn?.setAttribute('aria-expanded','false');menuBtn?.setAttribute('aria-label','Открыть меню');document.body.classList.remove('menu-open');if(restore&&returnFocus){returnFocus.focus();returnFocus=null;}}
function openMenu(){if(!nav)return;returnFocus=document.activeElement;nav.classList.add('is-open');menuOpen=true;menuBtn?.setAttribute('aria-expanded','true');menuBtn?.setAttribute('aria-label','Закрыть меню');document.body.classList.add('menu-open');setTimeout(()=>nav.querySelector('a')?.focus(),0);}
menuBtn?.addEventListener('click',e=>{stop(e);menuOpen?closeMenu():openMenu();},true);
nav?.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>closeMenu(false)));
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeMenu();});
document.addEventListener('click',e=>{if(nav&&menuBtn&&menuOpen&&!nav.contains(e.target)&&!menuBtn.contains(e.target))closeMenu();});
function normalizeNavigation(){const set=(selector,href)=>document.querySelectorAll(selector).forEach(a=>a.setAttribute('href',href));set('.popular-card .more','#overview');set('.chart-card .more','#chart');set('.artists-card .more','#artists');set('.news-card-overview .more','#news');set('.promotion-hub .more','/artist-levels/');set('.promotion-group','/artist-levels/');set('.quick-links a[href="#promotion-directions"]','/artist-levels/');set('.artist-mini','#artists');set('.news-overview-item[href="#promotion-directions"]','#news');set('.header-button','/artist-levels/');set('.hero-v13 .actions .secondary','#chart');}
function initActiveNav(){const links=[...document.querySelectorAll('.site-nav a')];const hashLinks=links.filter(a=>(a.getAttribute('href')||'').startsWith('#'));const targets=hashLinks.map(a=>({a,id:a.getAttribute('href').slice(1),el:document.getElementById(a.getAttribute('href').slice(1))})).filter(x=>x.el);const levelLink=links.find(a=>a.getAttribute('href')==='/artist-levels/'||a.getAttribute('href')==='/artist-levels');if(levelLink&&location.pathname.replace(/\/$/,'')==='/artist-levels')levelLink.classList.add('is-active');if(!targets.length)return;const mark=id=>targets.forEach(x=>x.a.classList.toggle('is-active',x.id===id));const observer=new IntersectionObserver(entries=>{const v=entries.filter(e=>e.isIntersecting).sort((a,b)=>b.intersectionRatio-a.intersectionRatio)[0];if(v)mark(v.target.id);},{rootMargin:'-25% 0px -55% 0px',threshold:[.05,.2,.5]});targets.forEach(x=>observer.observe(x.el));hashLinks.forEach(a=>a.addEventListener('click',()=>mark(a.getAttribute('href').slice(1))));}
function initMail(){const form=$('#mailForm');if(!form)return;form.addEventListener('submit',e=>{stop(e);const input=form.querySelector('input[type="email"]'),consent=form.querySelector('.mail-consent input'),msg=$('#mailMsg'),button=form.querySelector('button');if(!input||!input.checkValidity()){if(msg)msg.textContent='Введите корректный email.';input?.reportValidity();return;}if(consent&&!consent.checked){if(msg)msg.textContent='Подтвердите согласие с документами.';consent.reportValidity();return;}if(button){button.disabled=true;button.textContent='Подписываем…';}if(msg)msg.textContent='Готово. В Preview форма работает в демонстрационном режиме.';setTimeout(()=>{form.reset();if(button){button.disabled=false;button.textContent='Подписаться';}},1800);},true);}
function cleanupReleaseCards(){document.querySelectorAll('.release-card').forEach(card=>{card.querySelectorAll('button:not(.release-cover),.release-play,.listen-button,.release-listen').forEach(b=>b.remove());const cover=card.querySelector('.release-cover');if(cover){cover.type='button';cover.setAttribute('aria-label',cover.getAttribute('aria-label')||'Слушать');}});}
function addReleases(){const grid=$('.release-grid');if(!grid||grid.dataset.tenReady)return;grid.dataset.tenReady='1';const existing=grid.querySelectorAll('.release-card').length;for(let i=existing;i<songs.length;i++){const t=songs[i][0],a=songs[i][1],card=document.createElement('article');card.className='release-card';card.innerHTML='<button type="button" class="release-cover" data-song="'+t+'" aria-label="Слушать '+t+'"><span>'+t.slice(0,1)+'</span><i>▶</i></button><small>ПРЕМЬЕРА</small><h3>'+t+'</h3><p>'+a+'</p>';grid.appendChild(card);card.querySelector('.release-cover')?.addEventListener('click',e=>{stop(e);setTrack(i,true);},true);}}
normalizeNavigation();initActiveNav();initMail();cleanupReleaseCards();addReleases();cleanupReleaseCards();artwork(index);sync();
})();
