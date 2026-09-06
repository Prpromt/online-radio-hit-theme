(()=>{
  const $=s=>document.querySelector(s), $$=s=>document.querySelectorAll(s);
  const audio=$('#audio'), progress=$('#progress'), volume=$('#volume');
  const currentTime=$('#currentTime'), duration=$('#duration'), stickyTime=$('#stickyTime');
  const title=$('#currentTitle'), artist=$('#currentArtist'), ticker=$('#tickerTitle');
  const stickyTitle=$('#stickyTitle'), stickyArtist=$('#stickyArtist'), eq=$('#eq'), coverArt=$('#coverArt');
  if(!audio) return;
  const songs=[
    ['Ночной город','KARAT','/assets/covers/night-city.svg'],
    ['Jumanji','KARAT & МЛАДШИЙ','/assets/covers/jumanji.svg'],
    ['Ты такая славная','Вячеслав Калинин','/assets/covers/slavnaya.svg'],
    ['Просто я ищу тебя','Новый артист','/assets/covers/iskayu.svg'],
    ['Летуаль','PELIKUANA','/assets/covers/letual.svg'],
    ['Когда расцветает сирень','Андрей Додонов','/assets/covers/siren.svg']
  ];
  const fallbacks=[
    'linear-gradient(145deg,#171b26,#6b315d 55%,#ff4fa3)','linear-gradient(145deg,#171b26,#314f73 48%,#ff4fa3)','linear-gradient(145deg,#f5f6f3,#b9c5d5 48%,#ff4fa3)','linear-gradient(145deg,#171b26,#49316e 48%,#ff4fa3)','linear-gradient(145deg,#fff0f7,#ff8bc4 48%,#171b26)','linear-gradient(145deg,#eaf2dc,#d9ff32 48%,#ff4fa3)'
  ];
  let index=0, repeat=false;
  const fmt=s=>Number.isFinite(s)?Math.floor(s/60)+':'+String(Math.floor(s%60)).padStart(2,'0'):'0:00';
  const trackButtons=()=>$$('[data-track]');
  function setArtwork(el,i){if(!el||!songs[i])return;const url=songs[i][2];el.style.backgroundImage=`linear-gradient(145deg,rgba(0,0,0,.02),rgba(255,79,163,.05)),url("${url}"),${fallbacks[i]}`;el.style.backgroundSize='cover';el.style.backgroundPosition='center';el.style.backgroundColor='#f1f2ef'}
  function artwork(i){setArtwork(coverArt,i);$$('.mini-cover,.chart-cover,.release-cover').forEach(b=>{const n=Number(b.dataset.track);if(Number.isInteger(n)&&songs[n])setArtwork(b,n)})}
  function setTrack(i,play=false){index=(i+songs.length)%songs.length;const[t,a]=songs[index];title.textContent=t;artist.textContent=a;ticker.textContent=t;stickyTitle.textContent=t;stickyArtist.textContent=a;artwork(index);audio.currentTime=0;if(play)audio.play().catch(()=>{});sync()}
  function sync(){const playing=!audio.paused;$('#mainPlay')?.classList.toggle('is-playing',playing);$('#listenBtn')?.classList.toggle('is-playing',playing);$('#stickyPlay')?.classList.toggle('is-playing',playing);eq?.classList.toggle('playing',playing);$('#mainPlay .icon.play')?.classList.toggle('is-pause',playing);$('#stickyPlay .play-shape')?.classList.toggle('is-pause',playing);trackButtons().forEach(b=>b.classList.toggle('is-playing',playing&&Number(b.dataset.track)===index))}
  async function toggle(){if(audio.paused){try{await audio.play()}catch(e){console.warn('Воспроизведение недоступно:',e)}}else audio.pause();sync()}
  $('#mainPlay')?.addEventListener('click',e=>{e.preventDefault();toggle()});$('#listenBtn')?.addEventListener('click',e=>{e.preventDefault();toggle()});$('#stickyPlay')?.addEventListener('click',e=>{e.preventDefault();toggle()});
  $$('[data-action="repeat"]').forEach(b=>b.addEventListener('click',()=>{repeat=!repeat;b.classList.toggle('active',repeat)}));$('[data-action="prev"]')?.addEventListener('click',()=>setTrack(index-1,true));$('[data-action="next"]')?.addEventListener('click',()=>setTrack(index+1,true));
  audio.addEventListener('play',sync);audio.addEventListener('pause',sync);audio.addEventListener('loadedmetadata',()=>{if(duration)duration.textContent=fmt(audio.duration)});audio.addEventListener('timeupdate',()=>{currentTime.textContent=fmt(audio.currentTime);stickyTime.textContent=fmt(audio.currentTime);if(audio.duration&&progress)progress.value=audio.currentTime/audio.duration*100});audio.addEventListener('ended',()=>repeat?audio.play().catch(()=>{}):setTrack(index+1,true));progress?.addEventListener('input',()=>{if(audio.duration)audio.currentTime=progress.value/100*audio.duration});volume?.addEventListener('input',()=>audio.volume=Number(volume.value));audio.volume=.8;
  function row(song,i){return `<div class="mini-track"><strong>${i+1}</strong><button class="mini-cover" data-track="${i}" aria-label="Слушать ${song[0]}"><span>${song[0][0]}</span></button><div class="track-copy"><b>${song[0]}</b><small>${song[1]}</small></div><button class="mini-play" data-track="${i}" aria-label="Воспроизвести"><span class="play-shape"></span></button><span class="mini-plays">${(1200-i*137).toLocaleString('ru-RU')}</span></div>`}
  $('#popular').innerHTML=songs.slice(0,3).map(row).join('');$('#chartRows').innerHTML=songs.slice(0,5).map((s,i)=>`<div class="chart-row"><strong>${i+1}</strong><button class="chart-cover" data-track="${i}" aria-label="Слушать ${s[0]}">${s[0][0]}</button><div><b>${s[0]}</b><small>${s[1]}</small></div><span class="wave"><i></i><i></i><i></i><i></i><i></i></span><em>${(3200-i*410).toLocaleString('ru-RU')}</em></div>`).join('');$('#releasesGrid').innerHTML=songs.map((s,i)=>`<article class="release-card"><button class="release-cover" data-track="${i}" aria-label="Слушать ${s[0]}"><span>${s[0][0]}</span><i class="play-icon"></i></button><div><small>ПРЕМЬЕРА</small><h3>${s[0]}</h3><p>${s[1]}</p></div></article>`).join('');
  $$('[data-track]').forEach(b=>b.addEventListener('click',e=>{e.preventDefault();setTrack(Number(b.dataset.track),true)}));artwork(index);
  const player=document.querySelector('.player');if(player&&!document.getElementById('embedCodeBtn')){const box=document.createElement('div');box.className='embed-tools';box.innerHTML='<a id="embedCodeBtn" href="/embed-code/">Встроить плеер</a>';player.appendChild(box)}
  const sticky=$('#sticky');let last=scrollY;addEventListener('scroll',()=>{const down=scrollY>last;sticky.classList.toggle('visible',down&&scrollY>280);last=scrollY},{passive:true});$('#mailForm')?.addEventListener('submit',e=>{e.preventDefault();$('#mailMsg').textContent='В тестовом стенде форма работает локально. На WordPress подключим обработчик.'});
})();
