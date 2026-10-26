/* Kopere Proctoring documentation navigation and preferences. */
(() => {
  const root = document.documentElement;
  const header = document.querySelector('.site-header');
  const menu = document.querySelector('.menu-button');
  const nav = document.querySelector('.site-header nav');
  const actions = document.querySelector('.header-actions');
  const themeKey = 'kopere-proctoring-theme';
  const locale = /^pt/i.test(root.lang) ? 'pt_br' : 'en';
  const other = locale === 'pt_br' ? 'en' : 'pt_br';
  const switchUrl = target => {
    const path = location.pathname;
    const next = path.replace(/\/(?:pt_br|en)(?=\/|$)/, '/' + target);
    return next === path ? target + '/' : next + location.search + location.hash;
  };
  const langNav = document.createElement('div');
  langNav.className='language-switcher';
  langNav.setAttribute('aria-label',locale==='pt_br'?'Idioma':'Language');
  for (const [l,flag,label] of [['pt_br','🇧🇷','Português (Brasil)'],['en','🇺🇸','English']]) {
    const a = document.createElement('a'); a.className='language-flag-link'+(l===locale?' active':'');
    a.href=switchUrl(l); a.title=label; a.setAttribute('aria-label',label);
    if(l===locale)a.setAttribute('aria-current','page');
    const span=document.createElement('span');span.setAttribute('aria-hidden','true');span.textContent=flag;
    a.append(span);langNav.append(a);
  }
  if(actions)actions.prepend(langNav);
  const currentTheme=()=>root.dataset.theme==='dark'?'dark':'light';
  if(actions){
    const themeButton=document.createElement('button');themeButton.type='button';themeButton.className='theme-toggle';
    actions.insertBefore(themeButton,menu);
    const update=()=>{const dark=currentTheme()==='dark';themeButton.textContent=dark?'☀':'☾';
      themeButton.title=dark?'Light mode':'Dark mode';
      themeButton.setAttribute('aria-label',dark?(locale==='pt_br'?'Ativar modo claro':'Use light mode'):(locale==='pt_br'?'Ativar modo escuro':'Use dark mode'));
      themeButton.setAttribute('aria-pressed',dark?'true':'false');};
    themeButton.addEventListener('click',()=>{root.dataset.theme=currentTheme()==='dark'?'light':'dark';try{localStorage.setItem(themeKey,currentTheme());}catch(e){}update();});
    update();
  }
  window.addEventListener('scroll',()=>header?.classList.toggle('scrolled',window.scrollY>10),{passive:true});
  menu?.addEventListener('click',()=>{const open=!document.body.classList.contains('menu-open');document.body.classList.toggle('menu-open',open);
    menu.setAttribute('aria-expanded',String(open));menu.textContent=open?'×':'☰';});
  nav?.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{document.body.classList.remove('menu-open');if(menu){menu.setAttribute('aria-expanded','false');menu.textContent='☰';}}));
})();