// Content remains visible if JavaScript or IntersectionObserver is unavailable.
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const reveals = document.querySelectorAll('.rv');
if ('IntersectionObserver' in window && !reducedMotion) {
  document.documentElement.classList.add('motion-ready');
  reveals.forEach(el => {
    if (el.getBoundingClientRect().top > window.innerHeight) el.classList.add('is-pending');
    el.style.transitionDelay = Math.min(Number(el.dataset.d || 0), 160) + 'ms';
  });
  const observer = new IntersectionObserver(entries => entries.forEach(entry => {
    if (!entry.isIntersecting) return;
    entry.target.classList.add('in');
    entry.target.classList.remove('is-pending');
    observer.unobserve(entry.target);
  }), { threshold: 0.08 });
  reveals.forEach(el => observer.observe(el));
}
document.querySelectorAll('[data-to]').forEach(el => { el.textContent = el.dataset.to; });
document.querySelectorAll('.ba input').forEach(input => {
  const update = () => {
    input.parentElement.style.setProperty('--split', input.value + '%');
    input.setAttribute('aria-valuetext', `${input.value} % antes y ${100 - input.value} % después`);
  };
  input.addEventListener('input', update);
  update();
});
document.querySelectorAll('.dental-accordion details').forEach(detail => {
  detail.addEventListener('toggle', () => {
    const mark = detail.querySelector('summary span');
    if (mark) mark.textContent = detail.open ? '−' : '+';
  });
});
const f=document.getElementById('cita');if(f){const rules={nombre:v=>v.trim().length>2,telefono:v=>/^[0-9 +]{7,}$/.test(v),correo:v=>/^\S+@\S+\.\S+$/.test(v),tratamiento:v=>!!v,fecha:v=>!!v};
const chk=i=>{const ok=rules[i.name](i.value);i.classList.toggle('ok',ok);i.classList.toggle('bad',!ok);return ok};
f.querySelectorAll('input,select').forEach(i=>i.addEventListener('input',()=>chk(i)));
const q=new URLSearchParams(location.search).get('tratamiento');if(q)f.tratamiento.value=q;
f.addEventListener('submit',e=>{e.preventDefault();const all=[...f.querySelectorAll('input,select')].map(chk);if(all.every(Boolean))document.getElementById('ok').hidden=false})}