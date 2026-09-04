(()=>{
  const audio=document.getElementById('audio');
  const menu=document.getElementById('menuBtn'), nav=document.getElementById('mainNav');
  const title=document.getElementById('currentTitle'), artist=document.getElementById('currentArtist'), ticker=document.getElementById('tickerTitle');
  const stickyTitle=document.getElementById('stickyTitle'), stickyArtist=document.getElementById('stickyArtist');
  const songs=[['Ночной город','KARAT'],['Jumanji','KARAT & МЛАДШИЙ'],['Ты такая славная','Вячеслав Калинин'],['Просто я ищу тебя','Новый артист'],['Летуаль','PELIKUANA'],['Когда расцветает сирень','Андрей Додонов']];
  let current=0;
  const setTrack=(i,play=true)=>{
    current=(i+songs.length)%songs.length;
    const [t,a]=songs[current];
    title.textContent=t; artist.textContent=a; ticker.textContent=t; stickyTitle.textContent=t; stickyArtist.textContent=a;
    audio.currentTime=0;
    document.querySelectorAll('.mini-play,.mini-cover,.release-cover').forEach((el,n)=>el.classList.toggle('is-playing',n===current&&!audio.paused));
    if(play) audio.play().catch(()=>{});
  };
  const guard=(el,fn)=>el?.addEventListener('click',e=>{e.preventDefault();e.stopImmediatePropagation();fn(e)},true);
  document.querySelectorAll('.mini-play,.mini-cover,.release-cover').forEach((el,i)=>guard(el,()=>setTrack(i%songs.length,true)));
  guard(document.querySelector('[data-action="prev"]'),()=>setTrack(current-1,true));
  guard(document.querySelector('[data-action="next"]'),()=>setTrack(current+1,true));
  audio.addEventListener('play',()=>document.querySelectorAll('.mini-play,.mini-cover,.release-cover').forEach((el,i)=>el.classList.toggle('is-playing',i===current)));
  audio.addEventListener('pause',()=>document.querySelectorAll('.mini-play,.mini-cover,.release-cover').forEach(el=>el.classList.remove('is-playing')));
  menu?.addEventListener('click',e=>{e.preventDefault();e.stopImmediatePropagation();const open=nav.classList.toggle('is-open');menu.setAttribute('aria-expanded',String(open));menu.setAttribute('aria-label',open?'Закрыть меню':'Открыть меню')},true);
  nav?.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{nav.classList.remove('is-open');menu?.setAttribute('aria-expanded','false');menu?.setAttribute('aria-label','Открыть меню')}));
})();
