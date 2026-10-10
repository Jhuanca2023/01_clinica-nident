(() => {
  const preferenceKey = 'nident-consent-v1';
  const retention = 183 * 24 * 60 * 60 * 1000;
  let choice = null;
  try {
    const saved = JSON.parse(localStorage.getItem(preferenceKey));
    if (saved && typeof saved.media === 'boolean' && Date.now() - saved.date < retention) choice = saved;
  } catch { /* Storage is optional. */ }
  const banner = document.createElement('section');
  banner.className = 'cookie-banner'; banner.setAttribute('aria-label', 'Tus preferencias de privacidad');
  banner.innerHTML = '<h2>Tu visita, tus preferencias.</h2><p>Usamos funciones necesarias para la bolsa y tus decisiones. Tú eliges si permites videos de YouTube. <a href="cookies.html">Conoce nuestra política</a>.</p><div class="cookie-banner-actions"><button class="btn pri" type="button" data-consent="all">Aceptar todas</button><button class="btn ghost" type="button" data-consent="necessary">Solo necesarias</button></div><button class="cookie-configure" type="button" data-cookie-settings>Configurar preferencias</button><p class="cookie-storage-note" hidden>No pudimos guardar la elección. Se aplicará durante esta visita.</p>';
  banner.hidden = !!choice;
  document.body.append(banner);
  const dialog = document.createElement('dialog');
  dialog.className = 'cookie-dialog'; dialog.setAttribute('aria-labelledby','cookie-heading');
  dialog.innerHTML = '<div class="cookie-dialog-head"><h2 id="cookie-heading">Configura tu privacidad.</h2><button type="button" aria-label="Cerrar preferencias"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button></div><p>Las funciones necesarias siempre están disponibles. Puedes cambiar el permiso del contenido externo cuando quieras.</p><div class="cookie-option"><div><strong>Necesarias</strong><small>Bolsa, preferencias y consulta local de solicitudes. No se utilizan para publicidad.</small></div><span>Siempre activas</span></div><label class="cookie-option"><div><strong>Contenido externo</strong><small>Permite cargar videos incrustados de YouTube. El proveedor recibirá datos de conexión al cargar el reproductor.</small></div><input type="checkbox" id="consent-media" aria-label="Permitir contenido externo"></label><div class="cookie-option"><div><strong>Analítica y publicidad</strong><small>Este sitio no tiene herramientas de analítica ni publicidad instaladas.</small></div><span>No activas</span></div><div class="cookie-dialog-actions"><button class="btn pri" type="button" data-consent="save">Guardar elección</button><button class="btn ghost" type="button" data-consent="necessary">Solo necesarias</button></div><p class="cookie-storage-note" hidden>No pudimos guardar la elección. Se aplicará durante esta visita.</p>';
  document.body.append(dialog);
  let previousFocus;
  function applyMedia() {
    document.querySelectorAll('iframe[data-cookie-src]').forEach(frame => {
      const allowed = !!choice?.media;
      if (allowed) frame.src = frame.dataset.cookieSrc;
      else frame.removeAttribute('src');
      frame.hidden = !allowed;
      const placeholder = frame.previousElementSibling;
      if (placeholder?.classList.contains('external-video-placeholder')) placeholder.hidden = allowed;
    });
  }
  function save(media) {
    choice = { media, date: Date.now() };
    let stored = true;
    try { localStorage.setItem(preferenceKey, JSON.stringify(choice)); } catch { stored = false; }
    applyMedia();
    if (stored) { banner.hidden = true; dialog.close(); }
    else {
      const host = dialog.open ? dialog : banner;
      host.querySelector('.cookie-storage-note').hidden = false;
      if (!dialog.open) { banner.querySelector('h2').textContent = 'Elección aplicada a esta visita.'; setTimeout(() => banner.hidden = true, 5000); }
    }
  }
  document.addEventListener('click', event => {
    const settings = event.target.closest('[data-cookie-settings]');
    if (settings) {
      previousFocus = settings; dialog.querySelector('#consent-media').checked = !!choice?.media;
      if (!dialog.open) dialog.showModal();
    }
    const consent = event.target.closest('[data-consent]');
    if (consent) save(consent.dataset.consent === 'all' || (consent.dataset.consent === 'save' && dialog.querySelector('#consent-media').checked));
  });
  dialog.querySelector('[aria-label="Cerrar preferencias"]').addEventListener('click', () => dialog.close());
  dialog.addEventListener('close', () => previousFocus?.focus({preventScroll:true}));
  applyMedia();

  const recordKey = 'nident-claims-session-v1';
  // No names, address, contact or complaint text are persisted or transmitted.
  async function fingerprint(documentNumber) {
    const bytes = new TextEncoder().encode(documentNumber.trim().toUpperCase());
    const hash = await crypto.subtle.digest('SHA-256', bytes);
    return [...new Uint8Array(hash)].map(b => b.toString(16).padStart(2, '0')).join('');
  }
  function records() { try { return JSON.parse(sessionStorage.getItem(recordKey) || '[]'); } catch { return []; } }
  const form = document.querySelector('#claim-form');
  if (form) {
    const steps = [...form.querySelectorAll('[data-claim-step]')];
    const progress = [...document.querySelectorAll('.claim-progress li')];
    const back = form.querySelector('[data-claim-back]'), next = form.querySelector('[data-claim-next]'), submit = form.querySelector('[data-claim-submit]');
    const error = document.querySelector('#claim-error');
    let step = 0, receipt = '';
    const labels = {tipoDocumento:'Tipo de doc.',documento:'N° documento',razon:'Razón social',nombres:'Nombres',paterno:'Ap. paterno',materno:'Ap. materno',telefono:'Teléfono',correo:'Email',departamento:'Departamento',provincia:'Provincia',distrito:'Distrito',direccion:'Dirección',representante:'Representante',documentoRepresentante:'Doc. representante'};
    function data() { return Object.fromEntries(new FormData(form)); }
    function review() {
      const summary = document.querySelector('#claim-summary'); summary.replaceChildren();
      const values = data();
      Object.entries(labels).forEach(([key,label]) => {
        if (!values[key]?.trim() || (key.startsWith('representante') && !form.menor.checked)) return;
        const dt = document.createElement('dt'), dd = document.createElement('dd');
        dt.textContent = label; dd.textContent = values[key]; summary.append(dt,dd);
      });
    }
    function show(number) {
      step = number;
      steps.forEach((fieldset,i) => { fieldset.hidden = i !== step; });
      progress.forEach((item,i) => { if(i===step)item.setAttribute('aria-current','step');else item.removeAttribute('aria-current'); });
      back.hidden = step === 0; next.hidden = step === 2; submit.hidden = step !== 2;
      error.hidden = true;
      if (step === 2) review();
      const legend = steps[step].querySelector('legend'); legend.tabIndex = -1; legend.focus();
    }
    function validate(fieldset) {
      const number = form.documento;
      number.setCustomValidity('');
      const type = form.tipoDocumento.value, value = number.value.trim();
      if (type === 'DNI' && !/^\d{8}$/.test(value)) number.setCustomValidity('El DNI debe tener 8 dígitos.');
      if (type === 'RUC' && !/^\d{11}$/.test(value)) number.setCustomValidity('El RUC debe tener 11 dígitos.');
      if (['Carnet de Extranjería','Pasaporte'].includes(type) && !/^[A-Za-z0-9]{6,12}$/.test(value)) number.setCustomValidity('Ingresa entre 6 y 12 letras o números.');
      form.telefono.setCustomValidity(/^[+\d ()-]{7,20}$/.test(form.telefono.value.trim())?'':'Ingresa un teléfono de al menos 7 caracteres.');
      const controls = [...fieldset.querySelectorAll('input,select,textarea')].filter(input => !input.closest('[hidden]'));
      let invalid;
      controls.forEach(input => {
        const valid = input.checkValidity() && (!input.required || !!input.value.trim());
        input.setAttribute('aria-invalid', String(!valid));
        if (!valid && !invalid) invalid = input;
      });
      if (invalid) { error.textContent = invalid.validationMessage || 'Completa los campos obligatorios del paso actual.'; error.hidden = false; invalid.focus(); invalid.reportValidity(); return false; }
      error.hidden = true; return true;
    }
    form.querySelector('#menor').addEventListener('change', event => {
      const group = document.querySelector('#guardian-fields'); group.hidden = !event.target.checked;
      group.querySelectorAll('input').forEach(input => { input.required = event.target.checked; if (!event.target.checked) input.value = ''; });
    });
    form.tipoDocumento.addEventListener('change', () => { form.documento.inputMode = ['DNI','RUC'].includes(form.tipoDocumento.value) ? 'numeric' : 'text'; });
    form.addEventListener('input', event => event.target.removeAttribute('aria-invalid'));
    next.addEventListener('click', () => { if (validate(steps[step])) show(step+1); });
    back.addEventListener('click', () => show(step-1));
    form.querySelector('[data-edit-claim]').addEventListener('click', () => show(0));
    form.addEventListener('submit', async event => {
      event.preventDefault();
      if (step < 2) { if(validate(steps[step]))show(step+1);return; }
      if (!validate(steps[2])) return;
      submit.disabled = true; submit.textContent = 'Preparando constancia…';
      try {
        const values = data(), date = new Date();
        const random = crypto.getRandomValues(new Uint32Array(1))[0].toString(36).slice(0,5).toUpperCase();
        const code = 'REC-'+date.getTime().toString(36).toUpperCase()+'-'+random;
        let saved = true;
        try {
          const items=records(); items.push({code,date:date.toISOString(),type:values.tipoSolicitud,documentHash:await fingerprint(values.documento),status:'Generado localmente — ejemplo'});
          sessionStorage.setItem(recordKey,JSON.stringify(items.slice(-30)));
        } catch { saved=false; }
        receipt='NIDENT — CONSTANCIA DE DEMOSTRACIÓN\nNo enviada a la clínica.\n\nCódigo: '+code+'\nFecha: '+date.toLocaleString('es-PE')+'\n\n'+Object.entries({...labels,tipoSolicitud:'Solicitud',sede:'Sede/canal',producto:'Producto o servicio',monto:'Monto S/',fechaHecho:'Fecha del hecho',detalle:'Descripción',pedido:'Solución solicitada'}).filter(([key])=>values[key]).map(([key,label])=>label+': '+values[key]).join('\n')+'\n\nNo constituye una presentación real ni inicia plazos de atención.';
        form.hidden=true; document.querySelector('.claim-progress').hidden=true;
        const panel=document.querySelector('#claim-receipt');panel.hidden=false;
        document.querySelector('#receipt-code').textContent=code;
        document.querySelector('#receipt-date').textContent=date.toLocaleString('es-PE')+(saved?'':' · No se pudo guardar la consulta local. Descarga tu copia.');
        document.querySelector('#receipt-status').href='estado-reclamo.html?codigo='+encodeURIComponent(code);
        if(!saved)document.querySelector('#receipt-status').hidden=true;
        panel.querySelector('h2').tabIndex=-1;panel.querySelector('h2').focus();
        // Clear personal values once the requested copy has been prepared in memory.
        form.reset();
      } catch { error.textContent='No pudimos preparar la constancia. Intenta de nuevo.';error.hidden=false;submit.disabled=false;submit.textContent='Generar constancia de ejemplo'; }
    });
    document.querySelector('#download-claim').addEventListener('click', () => {
      const url=URL.createObjectURL(new Blob([receipt],{type:'text/plain;charset=utf-8'}));
      const link=document.createElement('a');link.href=url;link.download='nident-constancia-ejemplo.txt';link.click();setTimeout(()=>URL.revokeObjectURL(url),1000);
    });
  }
  const search=document.querySelector('#claim-search');
  if(search){
    const result=document.querySelector('#search-result'),codeInput=search.querySelector('#codigoBusqueda'),documentInput=search.querySelector('#documentoBusqueda');
    codeInput.value=new URLSearchParams(location.search).get('codigo')||'';
    search.addEventListener('submit',async event=>{
      event.preventDefault();result.hidden=false;result.replaceChildren();
      const code=codeInput.value.trim().toUpperCase(),doc=documentInput.value.trim();
      if(!code&&!doc){result.textContent='Ingresa un código o un número de documento para buscar.';codeInput.focus();return;}
      try{
        const hash=doc?await fingerprint(doc):null;
        const matches=records().filter(item=>(!code||item.code===code)&&(!doc||item.documentHash===hash));
        if(!matches.length){result.textContent='No encontramos una constancia con esos datos en esta sesión. Revisa lo ingresado o crea una solicitud de ejemplo en esta misma pestaña.';return;}
        matches.forEach(item=>{
          const card=document.createElement('article'),heading=document.createElement('h3'),status=document.createElement('span'),date=document.createElement('p'),note=document.createElement('p');
          heading.textContent=item.code;status.className='status-tag';status.textContent=item.status;date.textContent=item.type+' · '+new Date(item.date).toLocaleString('es-PE');note.textContent='No se ha enviado ni recibido por la clínica. No hay una respuesta real pendiente.';
          card.append(heading,status,date,note);result.append(card);
        });
      }catch{result.textContent='La consulta local no está disponible en este navegador. Revisa la copia que descargaste.';}
    });
  }
})();
