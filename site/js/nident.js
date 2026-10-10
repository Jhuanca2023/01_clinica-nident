// Nident interactions. Reservations are ephemeral; only the product bag persists.
(() => {
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  const hero = document.querySelector('.n-hero');
  if (hero) {
    const slides = [...hero.querySelectorAll('.hero-slide')];
    const dots = [...hero.querySelectorAll('[data-go-slide]')];
    const pause = hero.querySelector('.carousel-pause');
    let current = 0, timer, manuallyPaused = reduced.matches;
    function show(index) {
      current = (index + slides.length) % slides.length;
      slides.forEach((slide, i) => {
        slide.classList.toggle('active', i === current);
        slide.setAttribute('aria-hidden', String(i !== current));
        slide.inert = i !== current;
        dots[i].setAttribute('aria-pressed', String(i === current));
      });
      hero.querySelector('.slide-counter').textContent = `0${current + 1} / 05`;
    }
    function stop() { clearInterval(timer); }
    function play() {
      stop();
      if (!manuallyPaused && !document.hidden && !hero.matches(':hover') && !hero.contains(document.activeElement)) {
        timer = setInterval(() => show(current + 1), 2000);
      }
      pause.textContent = manuallyPaused ? 'Reanudar' : 'Pausar';
      pause.setAttribute('aria-label', manuallyPaused ? 'Reanudar banners' : 'Pausar banners');
    }
    dots.forEach((dot, i) => dot.addEventListener('click', () => { show(i); play(); }));
    pause.addEventListener('click', () => { manuallyPaused = !manuallyPaused; play(); });
    hero.addEventListener('mouseenter', stop);
    hero.addEventListener('mouseleave', play);
    hero.addEventListener('focusin', stop);
    hero.addEventListener('focusout', () => setTimeout(play, 0));
    document.addEventListener('visibilitychange', play);
    reduced.addEventListener('change', () => { manuallyPaused = reduced.matches; play(); });
    window.addEventListener('pagehide', stop);
    play();
  }

  const details = [...document.querySelectorAll('.dental-accordion details')];
  const whyPhotos = [...document.querySelectorAll('.why-state-photo')];
  const captions = ['Tu bienestar empieza cuando te escuchamos.', 'Cuidado dental con tecnología y atención profesional.', 'Un espacio para ti, en un horario que te acompañe.'];
  details.forEach((detail, index) => detail.addEventListener('toggle', () => {
    if (!detail.open || !whyPhotos.length) return;
    whyPhotos.forEach((photo, i) => { photo.classList.toggle('active', i === index); photo.setAttribute('aria-hidden', String(i !== index)); });
    document.getElementById('why-caption').textContent = captions[index];
    document.querySelector('.why-photo-count').textContent = `0${index + 1} / 03`;
  }));

  async function initShop() {
    if (!document.querySelector('.product-grid')) return;
    const status = document.getElementById('shop-status');
    let products;
    try {
      const response = await fetch('assets/products.json');
      if (!response.ok) throw new Error('catalog');
      products = await response.json();
    } catch {
      status.textContent = 'No pudimos cargar la bolsa. Recarga la página para intentarlo de nuevo.';
      document.querySelectorAll('[data-add-product]').forEach(button => button.disabled = true);
      return;
    }
    let stored;
    try { stored = JSON.parse(localStorage.getItem('nident-bag') || '{}'); } catch { stored = {}; }
    const cart = {};
    products.forEach(product => {
      const quantity = Number(stored?.[product.id]);
      if (Number.isInteger(quantity) && quantity > 0) cart[product.id] = Math.min(quantity, 9);
    });
    let toastTimer;
    function notify(message) {
      clearTimeout(toastTimer);
      status.textContent = message;
      toastTimer = setTimeout(() => status.textContent = '', 4000);
    }
    function el(tag, className, text) {
      const element = document.createElement(tag);
      if (className) element.className = className;
      if (text !== undefined) element.textContent = text;
      return element;
    }
    const money = amount => `S/ ${amount.toFixed(2)}`;
    function render() {
      const list = document.getElementById('cart-items');
      list.replaceChildren();
      let total = 0, count = 0;
      products.forEach(product => {
        const quantity = cart[product.id];
        if (!quantity) return;
        total += quantity * product.price;
        count += quantity;
        const row = el('article', 'cart-item');
        const image = el('img'); image.src = 'assets/img/dental/' + product.image; image.alt = '';
        const body = el('div'); body.append(el('h3', '', product.name), el('p', '', money(product.price * quantity)));
        const controls = el('div', 'cart-quantity');
        for (const [label, change] of [['−', -1], ['+', 1]]) {
          const button = el('button', '', label); button.type = 'button';
          button.setAttribute('aria-label', `${change < 0 ? 'Quitar una unidad de' : 'Agregar una unidad de'} ${product.name}`);
          button.disabled = change > 0 && quantity >= 9;
          button.addEventListener('click', () => {
            const next = quantity + change;
            if (next <= 0) delete cart[product.id]; else cart[product.id] = next;
            render();
            notify('Bolsa actualizada.');
          });
          controls.append(button);
          if (change < 0) controls.append(el('span', '', String(quantity)));
        }
        const remove = el('button', 'remove-item', 'Quitar'); remove.type = 'button';
        remove.setAttribute('aria-label', `Quitar ${product.name} de la bolsa`);
        remove.addEventListener('click', () => { delete cart[product.id]; render(); notify('Producto retirado de la bolsa.'); });
        controls.append(remove); body.append(controls); row.append(image, body); list.append(row);
      });
      if (!count) list.append(el('p', 'small-note', 'Tu bolsa está vacía. Encuentra algo para tu rutina de cuidado.'));
      document.getElementById('cart-count').textContent = String(count);
      document.getElementById('cart-total').textContent = money(total);
      document.getElementById('cart-review').disabled = !count;
      document.getElementById('cart-result').hidden = true;
      try { localStorage.setItem('nident-bag', JSON.stringify(cart)); } catch { /* Bag remains usable in memory. */ }
    }
    document.querySelectorAll('[data-add-product]').forEach(button => button.addEventListener('click', () => {
      const product = products.find(item => item.id === button.dataset.addProduct);
      if (!product) return;
      if ((cart[product.id] || 0) >= 9) { notify('Puedes agregar hasta 9 unidades por producto.'); return; }
      cart[product.id] = (cart[product.id] || 0) + 1;
      render(); notify(`${product.name} agregado a tu bolsa.`);
    }));
    document.getElementById('cart-review').addEventListener('click', () => {
      const result = document.getElementById('cart-result');
      result.textContent = 'Tu selección está lista para revisar. Esta tienda es una demostración: no se ha realizado un pedido ni un cobro.';
      result.hidden = false;
    });
    let category = 'Todos';
    const search = document.getElementById('product-search');
    const normalize = text => text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    function filter() {
      let count = 0;
      document.querySelectorAll('.product-card').forEach(card => {
        const visible = (category === 'Todos' || card.dataset.category === category) && normalize(card.dataset.search).includes(normalize(search.value.trim()));
        card.hidden = !visible; if (visible) count++;
      });
      document.getElementById('no-products').hidden = count > 0;
    }
    document.querySelectorAll('[data-filter]').forEach(button => button.addEventListener('click', () => {
      category = button.dataset.filter;
      document.querySelectorAll('[data-filter]').forEach(item => item.setAttribute('aria-pressed', String(item === button)));
      filter();
    }));
    search.addEventListener('input', filter);
    render();
  }
  initShop();

  const form = document.getElementById('booking-form');
  if (form) {
    const fields = [...form.querySelectorAll('[data-book-step]')];
    const back = document.getElementById('booking-back');
    const next = document.getElementById('booking-next');
    const confirm = document.getElementById('booking-confirm');
    const error = document.getElementById('booking-error');
    const branchNames = { miraflores: 'Nident Miraflores', sanborja: 'Nident San Borja', olivos: 'Nident Los Olivos' };
    const photos = { '2': 'child-smile.webp', '1': 'services-img2.webp', '3': 'services-img3.webp', '0': 'services-img4.webp', '7': 'hero-woman.webp', '4': 'services-img1.webp', '5': 'compare-after1.webp', '6': 'hero-patient.webp' };
    const control = name => form.elements.namedItem(name);
    const localDate = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    const today = new Date(); today.setHours(0, 0, 0, 0);
    const end = new Date(today); end.setDate(end.getDate() + 90);
    control('fecha').min = localDate(today); control('fecha').max = localDate(end);
    const treatment = new URLSearchParams(location.search).get('tratamiento');
    if (treatment !== null && [...control('tratamiento').options].some(option => option.value === treatment)) control('tratamiento').value = treatment;
    let step = 0;
    function formatDate(value) {
      if (!value) return '';
      return new Date(value + 'T12:00:00').toLocaleDateString('es-PE', { weekday: 'short', day: 'numeric', month: 'long' });
    }
    function summary() {
      document.getElementById('summary-branch').textContent = branchNames[control('sede').value] || 'Elige una sede';
      document.getElementById('summary-service').textContent = control('tratamiento').value ? control('tratamiento').selectedOptions[0].textContent : 'Por seleccionar';
      document.getElementById('summary-doctor').textContent = control('profesional').selectedOptions[0].textContent;
      document.getElementById('summary-time').textContent = control('fecha').value ? `${formatDate(control('fecha').value)}${control('hora').value ? ' · ' + control('hora').value : ' · Elige horario'}` : 'Por seleccionar';
    }
    function updateGuardian() {
      const minor = control('edad').value !== '' && Number(control('edad').value) < 18;
      document.getElementById('guardian-label').hidden = !minor;
      control('tutor').required = minor;
    }
    function slots() {
      control('hora').value = '';
      const container = document.getElementById('time-slots'); container.replaceChildren();
      const value = control('fecha').value;
      if (!value) { const p = document.createElement('p'); p.textContent = 'Selecciona una fecha para ver los horarios.'; container.append(p); summary(); return; }
      const date = new Date(value + 'T12:00:00');
      if (!Number.isFinite(date.getTime()) || value < control('fecha').min || value > control('fecha').max) {
        const p = document.createElement('p'); p.textContent = 'Elige una fecha dentro de los próximos 90 días.'; container.append(p); summary(); return;
      }
      if (date.getDay() === 0) { const p = document.createElement('p'); p.textContent = 'Los domingos descansamos. Elige otra fecha.'; container.append(p); summary(); return; }
      const times = date.getDay() === 6 ? ['09:00', '10:00', '11:00', '12:00', '13:00'] : ['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
      const seed = [...control('sede').value + control('profesional').value].reduce((sum, char) => sum + char.charCodeAt(0), date.getDate());
      times.forEach((time, i) => {
        const button = document.createElement('button'); button.type = 'button'; button.textContent = time;
        const [hour] = time.split(':').map(Number);
        button.disabled = (i + seed) % 4 === 0 || (value === localDate(new Date()) && hour <= new Date().getHours());
        button.setAttribute('aria-label', `${time}${button.disabled ? ', no disponible' : ', disponible de ejemplo'}`);
        button.setAttribute('aria-pressed', 'false');
        button.addEventListener('click', () => {
          control('hora').value = time;
          container.querySelectorAll('button').forEach(item => item.setAttribute('aria-pressed', String(item === button)));
          error.hidden = true; summary();
        }); container.append(button);
      });
      if ([...container.querySelectorAll('button')].every(button => button.disabled)) {
        const p = document.createElement('p'); p.textContent = 'No quedan horarios para esta fecha. Elige otro día.'; container.append(p);
      }
      summary();
    }
    function showStep(index, focus = false) {
      step = index;
      fields.forEach((field, i) => field.hidden = i !== step);
      document.querySelectorAll('.booking-progress li').forEach((item, i) => { item.classList.toggle('active', i === step); if (i === step) item.setAttribute('aria-current', 'step'); else item.removeAttribute('aria-current'); });
      back.hidden = step === 0; next.hidden = step === 3; confirm.hidden = step !== 3; error.hidden = true;
      if (focus) { const legend = fields[step].querySelector('legend'); legend.tabIndex = -1; legend.focus(); }
      summary();
    }
    function fail(message, field) { error.textContent = message; error.hidden = false; field?.focus(); return false; }
    function validate() {
      if (step === 0 && !control('sede').value) return fail('Elige una sede para continuar.', form.querySelector('[name=sede]'));
      if (step === 1 && !control('tratamiento').value) return fail('Selecciona el tratamiento para tu visita.', control('tratamiento'));
      if (step === 2) {
        if (control('nombre').value.trim().length < 3) return fail('Escribe el nombre del paciente.', control('nombre'));
        if (control('edad').value === '' || !control('edad').checkValidity()) return fail('Introduce una edad entre 0 y 110 años.', control('edad'));
        if (control('telefono').value.replace(/\D/g, '').length < 7) return fail('Introduce un teléfono de al menos 7 dígitos.', control('telefono'));
        if (!control('correo').checkValidity()) return fail('Introduce un correo electrónico válido.', control('correo'));
        if (control('tutor').required && control('tutor').value.trim().length < 3) return fail('Añade el nombre del adulto acompañante.', control('tutor'));
      }
      if (step === 3) {
        if (!control('fecha').value || !control('fecha').checkValidity()) return fail('Elige una fecha válida dentro de los próximos 90 días.', control('fecha'));
        if (!control('hora').value) return fail('Selecciona uno de los horarios disponibles.', document.querySelector('#time-slots button:not(:disabled)'));
      }
      return true;
    }
    next.addEventListener('click', () => { if (validate()) showStep(step + 1, true); });
    back.addEventListener('click', () => showStep(step - 1, true));
    form.addEventListener('input', () => { updateGuardian(); error.hidden = true; summary(); });
    form.addEventListener('change', event => {
      if (['sede', 'profesional', 'fecha'].includes(event.target.name)) slots();
      if (event.target.name === 'tratamiento') {
        const picture = document.querySelector('.booking-service-photo img'); picture.src = 'assets/img/dental/' + (photos[event.target.value] || 'hero-woman.webp'); picture.alt = event.target.selectedOptions[0].textContent;
      }
      summary();
    });
    form.addEventListener('submit', event => {
      event.preventDefault();
      if (step < 3) { if (validate()) showStep(step + 1, true); return; }
      if (!validate()) return;
      const summaryBox = document.getElementById('booking-confirmation'); summaryBox.replaceChildren();
      const dl = document.createElement('dl');
      for (const [label, value] of [['Sede', branchNames[control('sede').value]], ['Paciente', control('nombre').value.trim()], ['Servicio', control('tratamiento').selectedOptions[0].textContent], ['Profesional', control('profesional').selectedOptions[0].textContent], ['Fecha', formatDate(control('fecha').value)], ['Horario', control('hora').value]]) {
        const dt = document.createElement('dt'); dt.textContent = label;
        const dd = document.createElement('dd'); dd.textContent = value;
        dl.append(dt, dd);
      }
      summaryBox.append(dl); form.hidden = true; document.querySelector('.booking-progress').hidden = true;
      const success = document.getElementById('booking-success'); success.hidden = false; success.tabIndex = -1; success.focus();
    });
    document.getElementById('booking-restart').addEventListener('click', () => {
      form.reset(); updateGuardian(); slots(); form.hidden = false; document.querySelector('.booking-progress').hidden = false;
      document.getElementById('booking-success').hidden = true; document.getElementById('booking-confirmation').replaceChildren(); showStep(0, true);
    });
    const initialPhoto = document.querySelector('.booking-service-photo img');
    if (control('tratamiento').value) { initialPhoto.src = 'assets/img/dental/' + photos[control('tratamiento').value]; initialPhoto.alt = control('tratamiento').selectedOptions[0].textContent; }
    updateGuardian(); showStep(0);
  }
})();
