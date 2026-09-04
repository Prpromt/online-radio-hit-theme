(()=>{
  const $=s=>document.querySelector(s), $$=s=>document.querySelectorAll(s);
  const audio=$('#audio'), progress=$('#progress'), volume=$('#volume');
  const currentTime=$('#currentTime'), duration=$('#duration'), stickyTime=$('#stickyTime');
  const title=$('#currentTitle'), artist=$('#currentArtist'), ticker=$('#tickerTitle');
  const stickyTitle=$('#stickyTitle'), stickyArtist=$('#stickyArtist'), eq=$('#eq');
  if(!audio) return;
  const songs=[['Ночной город','KARAT'],['Jumanji','KARAT & МЛАДШИЙ'],['Ты такая славная','Вячеслав Калинин'],['Просто я ищу тебя','Новый артист'],['Летуаль','PELIKUANA'],['Когда расцветает сирень','Андрей Додонов']];
  let index=0, repeat=false;
  const fmt=s=>Number.isFinite(s)?Math.floor(s/60)+':'+String(Math.floor(s%60)).padStart(2,'0'):'0:00';
  const buttons=()=>$$('.mini-play,.mini-cover,.release-cover');
  function renderTrack(i,play=false){
    index=(i+songs.length)%songs.length;
    const [t,a]=songs[index];
    title.textContent=t; artist.textContent=a; ticker.textContent=t; stickyTitle.textContent=t; stickyArtist.textContent=a;
    audio.currentTime=0;
    buttons().forEach(b=>b.classList.remove('is-playing'));
    if(play) audio.play().catch(()=>{});
    sync();
  }
  function sync(){
    const playing=!audio.paused;
    $$('[data-action="play"]').forEach(b=>b.classList.toggle('is-playing',playing));
    $('#mainPlay')?.classList.toggle('is-playing',playing); $('#listenBtn')?.classList.toggle('is-playing',playing); $('#stickyPlay')?.classList.toggle('is-playing',playing);
    eq?.classList.toggle('playing',playing);
    $$('.play-shape,.icon.play').forEach(el=>el.classList.toggle('pause',playing));
    buttons().forEach((b,i)=>b.classList.toggle('is-playing',playing && i===index));
  }
  async function toggle(){
    if(audio.paused){try{await audio.play()}catch(e){console.warn('Воспроизведение недоступно:',e)}}else audio.pause();
    sync();
  }
  $('#mainPlay')?.addEventListener('click',e=>{e.preventDefault();toggle()});
  $('#listenBtn')?.addEventListener('click',e=>{e.preventDefault();toggle()});
  $('#stickyPlay')?.addEventListener('click',e=>{e.preventDefault();toggle()});
  $$('[data-action="repeat"]').forEach(b=>b.addEventListener('click',()=>{repeat=!repeat;$$('[data-action="repeat"]').forEach(x=>x.classList.toggle('active',repeat))}));
  $('[data-action="prev"]')?.addEventListener('click',()=>renderTrack(index-1,true));
  $('[data-action="next"]')?.addEventListener('click',()=>renderTrack(index+1,true));
  audio.addEventListener('play',sync); audio.addEventListener('pause',sync);
  audio.addEventListener('loadedmetadata',()=>{if(duration)duration.textContent=fmt(audio.duration)});
  audio.addEventListener('timeupdate',()=>{currentTime.textContent=fmt(audio.currentTime);stickyTime.textContent=fmt(audio.currentTime);if(audio.duration)progress.value=audio.currentTime/audio.duration*100});
  audio.addEventListener('ended',()=>repeat?audio.play().catch(()=>{}):renderTrack(index+1,true));
  progress?.addEventListener('input',()=>{if(audio.duration)audio.currentTime=progress.value/100*audio.duration});
  volume?.addEventListener('input',()=>audio.volume=Number(volume.value)); audio.volume=.8;
  function row(song,i){return `<div class="mini-track"><strong>${i+1}</strong><button class="mini-cover" data-track="${i}" aria-label="Слушать ${song[0]}"><span>${song[0][0]}</span></button><div class="track-copy"><b>${song[0]}</b><small>${song[1]}</small></div><button class="mini-play" data-track="${i}" aria-label="Воспроизвести"><span class="play-shape"></span></button><span class="mini-plays">${(1200-i*137).toLocaleString('ru-RU')}</span></div>`}
  $('#popular').innerHTML=songs.slice(0,3).map(row).join('');
  $('#chartRows').innerHTML=songs.slice(0,5).map((s,i)=>`<div class="chart-row"><strong>${i+1}</strong><span class="chart-cover">${s[0][0]}</span><div><b>${s[0]}</b><small>${s[1]}</small></div><span class="wave"><i></i><i></i><i></i><i></i><i></i></span><em>${(3200-i*410).toLocaleString('ru-RU')}</em></div>`).join('');
  $('#releasesGrid').innerHTML=songs.map((s,i)=>`<article class="release-card"><button class="release-cover" data-track="${i}" aria-label="Слушать ${s[0]}"><span>${s[0][0]}</span><i class="play-icon"></i></button><div><small>ПРЕМЬЕРА</small><h3>${s[0]}</h3><p>${s[1]}</p></div></article>`).join('');
  $$('.mini-play,.mini-cover,.release-cover').forEach(b=>b.addEventListener('click',e=>{e.preventDefault();renderTrack(Number(b.dataset.track)||0,true)}));
  const sticky=$('#sticky'); let last=scrollY;
  addEventListener('scroll',()=>{const down=scrollY>last;sticky.classList.toggle('visible',down&&scrollY>280);last=scrollY},{passive:true});
  $('#mailForm')?.addEventListener('submit',e=>{e.preventDefault();$('#mailMsg').textContent='В тестовом стенде форма работает локально. На WordPress подключим обработчик.'});
})();
