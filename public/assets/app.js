(()=>{
  const $=s=>document.querySelector(s), $$=s=>document.querySelectorAll(s);
  const audio=$('#audio');
  if(!audio)return;

  const title=$('#currentTitle'), artist=$('#currentArtist'), ticker=$('#tickerTitle');
  const progress=$('#progress'), volume=$('#volume'), currentTime=$('#currentTime'), duration=$('#duration');
  const mainPlay=$('#mainPlay'), listenBtn=$('#listenBtn'), eq=$('#eq');
  const menuBtn=$('#menuBtn'), nav=$('.site-nav');
  const playerCover=$('.cover');

  const songs=[
    ['Ночной город','KARAT','/assets/covers/night-city.svg'],
    ['Jumanji','KARAT & МЛАДШИЙ','/assets/covers/jumanji.svg'],
    ['Ты такая славная','Вячеслав Калинин','/assets/covers/slavnaya.svg'],
    ['Просто я ищу тебя','Новый артист','/assets/covers/iskayu.svg'],
    ['Летуаль','PELIKUANA','/assets/covers/letual.svg'],
    ['Когда расцветает сирень','Андрей Додонов','/assets/covers/siren.svg']
  ];
  const fallback=[
    'linear-gradient(145deg,#dfff18,#1a1c22 52%,#ff2875)',
    'linear-gradient(145deg,#171b26,#314f73 48%,#ff2875)',
    'linear-gradient(145deg,#f5f6f3,#b9c5d5 48%,#ff2875)',
    'linear-gradient(145deg,#171b26,#49316e 48%,#ff2875)',
    'linear-gradient(145deg,#fff0f7,#ff8bc4 48%,#171b26)',
    'linear-gradient(145deg,#eaf2dc,#dfff18 48%,#ff2875)'
  ];

  let index=0;
  let repeat=false;
  const fmt=s=>Number.isFinite(s)?Math.floor(s/60)+':'+String(Math.floor(s%60)).padStart(2,'0'):'0:00';

  function setArtwork(i){
    if(!playerCover)return;
    const url=songs[i]?.[2];
    playerCover.style.backgroundImage=`linear-gradient(145deg,rgba(255,255,255,.04),rgba(255,40,117,.08)),url("${url}"),${fallback[i]||fallback[0]}`;
    playerCover.style.backgroundSize='cover';
    playerCover.style.backgroundPosition='center';
  }

  function sync(){
    const playing=!audio.paused;
    mainPlay?.classList.toggle('is-playing',playing);
    listenBtn?.classList.toggle('is-playing',playing);
    eq?.classList.toggle('playing',playing);
    mainPlay?.setAttribute('aria-label',playing?'Пауза':'Воспроизвести');
    mainPlay?.querySelector('.orh-icon')?.classList.toggle('is-pause',playing);
    $$('[data-track]').forEach(el=>el.classList.toggle('is-playing',playing&&Number(el.dataset.track)===index));
  }

  function setTrack(i,play=false){
    index=(i+songs.length)%songs.length;
    const [t,a]=songs[index];
    if(title)title.textContent=t;
    if(artist)artist.textContent=a;
    if(ticker)ticker.textContent=t;
    audio.currentTime=0;
    setArtwork(index);
    if(play)audio.play().catch(()=>{});
    sync();
  }

  async function toggle(){
    if(audio.paused){
      try{await audio.play()}catch(e){console.warn('Воспроизведение недоступно:',e)}
    }else audio.pause();
    sync();
  }

  mainPlay?.addEventListener('click',e=>{e.preventDefault();toggle()});
  listenBtn?.addEventListener('click',e=>{e.preventDefault();toggle()});
  $('#prevBtn')?.addEventListener('click',()=>setTrack(index-1,true));
  $('#nextBtn')?.addEventListener('click',()=>setTrack(index+1,true));
  $$('[aria-label="Повтор"]').forEach(btn=>btn.addEventListener('click',()=>{
    repeat=!repeat;
    btn.classList.toggle('active',repeat);
    btn.setAttribute('aria-pressed',String(repeat));
  }));

  $$('[data-track]').forEach(btn=>btn.addEventListener('click',e=>{
    e.preventDefault();
    setTrack(Number(btn.dataset.track),true);
  }));
  $$('[data-song]').forEach(btn=>btn.addEventListener('click',e=>{
    e.preventDefault();
    const t=btn.dataset.song;
    const found=songs.findIndex(s=>s[0]===t);
    if(found>=0)setTrack(found,true);
    else{
      if(title)title.textContent=t;
      if(artist)artist.textContent=btn.dataset.artist||'';
      if(ticker)ticker.textContent=t;
      audio.currentTime=0;
      audio.play().catch(()=>{});
    }
  }));

  audio.addEventListener('play',sync);
  audio.addEventListener('pause',sync);
  audio.addEventListener('loadedmetadata',()=>{if(duration)duration.textContent=fmt(audio.duration)});
  audio.addEventListener('timeupdate',()=>{
    if(currentTime)currentTime.textContent=fmt(audio.currentTime);
    if(duration)duration.textContent=fmt(audio.duration);
    if(progress&&audio.duration)progress.value=audio.currentTime/audio.duration*100;
  });
  audio.addEventListener('ended',()=>repeat?audio.play().catch(()=>{}):setTrack(index+1,true));
  progress?.addEventListener('input',()=>{if(audio.duration)audio.currentTime=Number(progress.value)/100*audio.duration});
  volume?.addEventListener('input',()=>audio.volume=Number(volume.value));
  audio.volume=Number(volume?.value||.8);

  menuBtn?.addEventListener('click',()=>{
    const open=nav?.classList.toggle('is-open');
    menuBtn.setAttribute('aria-expanded',String(Boolean(open)));
  });
  nav?.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{
    nav.classList.remove('is-open');
    menuBtn?.setAttribute('aria-expanded','false');
  }));

  $('#mailForm')?.addEventListener('submit',e=>{
    e.preventDefault();
    const msg=$('#mailMsg');
    if(msg)msg.textContent='В Preview форма работает локально. В WordPress используется реальный обработчик подписки.';
  });

  if($('.player')&&!$('#embedCodeBtn')){
    const box=document.createElement('div');
    box.className='embed-tools';
    box.innerHTML='<a id="embedCodeBtn" href="/embed-code/">Встроить плеер на сайт</a>';
    $('.player').appendChild(box);
  }

  setArtwork(index);
  sync();
})();