document.addEventListener('DOMContentLoaded',()=>{
  const sidebar=document.querySelector('.sidebar');
  const toggle=document.querySelector('[data-sidebar-toggle]');
  if(!sidebar||!toggle)return;
  const setOpen=open=>{
    sidebar.classList.toggle('open',open);
    toggle.setAttribute('aria-expanded',open?'true':'false');
  };
  toggle.setAttribute('aria-expanded','false');
  toggle.addEventListener('click',()=>setOpen(!sidebar.classList.contains('open')));
  sidebar.querySelectorAll('a').forEach(link=>link.addEventListener('click',()=>{if(window.innerWidth<=760)setOpen(false)}));
  document.addEventListener('click',e=>{
    if(window.innerWidth<=760&&sidebar.classList.contains('open')&&!sidebar.contains(e.target)&&!toggle.contains(e.target))setOpen(false);
  });
  document.addEventListener('keydown',e=>{if(e.key==='Escape')setOpen(false)});
  window.addEventListener('resize',()=>{if(window.innerWidth>760)setOpen(false)});
});
