(()=>{
  const $=s=>document.querySelector(s), $$=s=>document.querySelectorAll(s);
  const audio=$('#audio');
  if(!audio)return;
  const title=$('#currentTitle'), artist=$('#currentArtist'), ticker=$('#tickerTitle');
  const progress=$('#progress'), volume=$('#volume'), currentTime=$('#currentTime'), duration=$('#duration');
  const mainPlay=$('#mainPlay'), listenBtn=$('#listenBtn'), eq=$('#eq');
  const menuBtn=$('#menuBtn'), nav=$('.site-nav'), player=$('.player'), playerCover=$('.cover');
  const songs=[
    ['Ночной город','KARAT','/assets/covers/night-city.svg'],['Jumanji','KARAT & МЛАДШИЙ','/assets/covers/jumanji.svg'],
    ['Ты такая славная','Вячеслав Калинин','/assets/covers/slavnaya.svg'],['Просто я ищу тебя','Новый артист','/assets/covers/iskayu.svg'],
    ['Летуаль','PELIKUANA','/assets/covers/letual.svg'],['Когда расцветает сирень','Андрей Додонов','/assets/covers/siren.svg']
  ];
  const fallback=['linear-gradient(145deg,#dfff18,#1a1c22 52%,#ff2875)','linear-gradient(145deg,#171b26,#314f73 48%,#ff2875)','linear-gradient(145deg,#f5f6f3,#b9c5d5 48%,#ff2875)','linear-gradient(145deg,#171b26,#49316e 48%,#ff2875)','linear-gradient(145deg,#fff0f7,#ff8bc4 48%,#171b26)','linear-gradient(145deg,#eaf2dc,#dfff18 48%,#ff2875)'];
  let index=0,repeat=false;
  const fmt=s=>Number.isFinite(s)?Math.floor(s/60)+':'+String(Math.floor(s%60)).padStart(2,'0'):'0:00';
  function artwork(i){if(!playerCover)return;playerCover.style.backgroundImage=`linear-gradient(145deg,rgba(255,255,255,.04),rgba(255,40,117,.08)),url("${songs[i][2]}"),${fallback[i]}`;playerCover.style.backgroundSize='cover';playerCover.style.backgroundPosition='center';}
  function sync(){const playing=!audio.paused;mainPlay?.classList.toggle('is-playing',playing);listenBtn?.classList.toggle('is-playing',playing);eq?.classList.toggle('playing',playing);mainPlay?.setAttribute('aria-label',playing?'Пауза':'Воспроизвести');mainPlay?.querySelector('.orh-icon')?.classList.toggle('is-pause',playing);}
  function setTrack(i,play=false){index=(i+songs.length)%songs.length;const[t,a]=songs[index];if(title)title.textContent=t;if(artist)artist.textContent=a;if(ticker)ticker.textContent=t;audio.currentTime=0;artwork(index);if(play)audio.play().catch(()=>{});sync();}
  function stop(e){e.preventDefault();e.stopImmediatePropagation();}
  function toggle(){if(audio.paused)audio.play().catch(e=>console.warn('Воспроизведение недоступно:',e));else audio.pause();sync();}
  mainPlay?.addEventListener('click',e=>{stop(e);toggle()},true);
  listenBtn?.addEventListener('click',e=>{stop(e);toggle()},true);
  $('#prevBtn')?.addEventListener('click',e=>{stop(e);setTrack(index-1,true)},true);
  $('#nextBtn')?.addEventListener('click',e=>{stop(e);setTrack(index+1,true)},true);
  $$('[aria-label="Повтор"]').forEach(btn=>btn.addEventListener('click',e=>{stop(e);repeat=!repeat;btn.classList.toggle('active',repeat);btn.setAttribute('aria-pressed',String(repeat))},true));
  $$('[data-track],[data-song]').forEach(btn=>btn.addEventListener('click',e=>{stop(e);const found=Number.isInteger(Number(btn.dataset.track))?Number(btn.dataset.track):songs.findIndex(s=>s[0]===btn.dataset.song);if(found>=0)setTrack(found,true)},true));
  audio.addEventListener('play',sync);audio.addEventListener('pause',sync);
  audio.addEventListener('loadedmetadata',()=>{if(duration)duration.textContent=fmt(audio.duration)});
  audio.addEventListener('timeupdate',()=>{if(currentTime)currentTime.textContent=fmt(audio.currentTime);if(duration)duration.textContent=fmt(audio.duration);if(progress&&audio.duration)progress.value=audio.currentTime/audio.duration*100});
  audio.addEventListener('ended',()=>repeat?audio.play().catch(()=>{}):setTrack(index+1,true));
  progress?.addEventListener('input',()=>{if(audio.duration)audio.currentTime=Number(progress.value)/100*audio.duration});
  volume?.addEventListener('input',()=>audio.volume=Number(volume.value));audio.volume=Number(volume?.value||.8);
  menuBtn?.addEventListener('click',e=>{stop(e);const open=nav?.classList.toggle('is-open');menuBtn.setAttribute('aria-expanded',String(Boolean(open)))},true);
  nav?.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{nav.classList.remove('is-open');menuBtn?.setAttribute('aria-expanded','false')}));
  $('#mailForm')?.addEventListener('submit',e=>{
    stop(e);
    const form=e.currentTarget,msg=$('#mailMsg'),input=form.querySelector('input[type="email"]'),button=form.querySelector('button');
    if(!input||!input.checkValidity()){input?.reportValidity();return;}
    if(button){button.disabled=true;button.textContent='Подписываем…';}
    if(msg){msg.className='mail-success';msg.textContent='Готово. В Preview подписка демонстрируется локально; после подключения WordPress адрес будет сохранён в единой рассылке.';}
    setTimeout(()=>{form.reset();if(button){button.disabled=false;button.textContent='Подписаться';}},1800);
  },true);
  if(player&&!$('#embedCodeBtn')){const box=document.createElement('div');box.className='embed-tools';box.innerHTML='<a id="embedCodeBtn" href="/embed-code/">Встроить плеер на сайт</a>';player.appendChild(box);}
  artwork(index);sync();
})();