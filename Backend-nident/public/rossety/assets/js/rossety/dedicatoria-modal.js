'use strict';

/* ---- Sugerencias data ---- */
const SUGERENCIAS = {
  nacimientos:    [],
  agradecimiento: [
    "Gracias por cada palabra y cada momento compartido. Que todos tus días estén llenos de alegría y las mejores experiencias. Con mucho cariño y agradecimiento."
  ],
  amor: [
    "No necesitamos una fecha especial para celebrar lo mucho que nos queremos. Gracias por hacerme tan feliz. Te adoro.",
    "Mi corazón late solo para ti."
  ],
  condolencias:   [],
  aniversario: [
    "Feliz aniversario, amor mío.",
    "Te amo hoy más que nunca.",
    "Un año más juntos es un regalo."
  ],
  cumpleanos: [
    "¡Que este día sea tan especial como tú!",
    "Muchas felicidades en tu día.",
    "Que todos tus sueños se hagan realidad."
  ],
  matrimonio:     [],
  amistad: [
    "Gracias por ser mi amigo/a.",
    "Siempre estás ahí para mí.",
    "Tu amistad es un regalo valioso."
  ],
  mejorate: [
    "Pronto estarás de vuelta en pie.",
    "Te mando toda mi energía positiva.",
    "Que te recuperes muy pronto."
  ]
};

const MAX_CHARS = 300;
const WARN_AT   = 280;

/* ---- Element refs ---- */
const overlay        = document.getElementById('modalOverlay');
const modal          = document.getElementById('modal');
const openBtns       = [document.getElementById('openModalBtn'), document.getElementById('openModalBtn2')];
const closeBtn       = document.getElementById('closeModalBtn');
const cancelBtn      = document.getElementById('cancelBtn');
const guardarBtn     = document.getElementById('guardarBtn');
const chipsGrid      = document.getElementById('chipsGrid');
const chips          = Array.from(chipsGrid.querySelectorAll('.chip'));
const sugerenciasPlaceholder = document.getElementById('sugerenciasPlaceholder');
const sugerenciasList        = document.getElementById('sugerenciasList');
const includeCheck   = document.getElementById('includeCheck');
const editorWrapper  = document.getElementById('editorWrapper');
const msgEditor      = document.getElementById('msgEditor');
const charCounter    = document.getElementById('charCounter');
const toast          = document.getElementById('toast');
const giftNotice     = document.getElementById('giftNotice');
const toolbarBtns    = document.querySelectorAll('.toolbar-btn');

let toastTimer = null;
let selectedKey = null;

/* ============================================================
   MODAL OPEN / CLOSE
============================================================ */

function openModal() {
  overlay.classList.add('active');
  document.body.classList.add('modal-open');
  // Focus close button after animation
  setTimeout(() => closeBtn.focus(), 320);
}

function closeModal() {
  overlay.classList.remove('active');
  document.body.classList.remove('modal-open');
}

openBtns.forEach(btn => btn.addEventListener('click', openModal));
closeBtn.addEventListener('click', closeModal);
cancelBtn.addEventListener('click', closeModal);

// Close on overlay click (outside modal)
overlay.addEventListener('click', function (e) {
  if (e.target === overlay) closeModal();
});

// Close on Escape
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape' && overlay.classList.contains('active')) {
    closeModal();
  }
});

// Focus trap (Tab / Shift+Tab stays inside modal)
modal.addEventListener('keydown', function (e) {
  if (e.key !== 'Tab') return;
  const focusable = Array.from(
    modal.querySelectorAll('button, [href], input, [tabindex]:not([tabindex="-1"]), [contenteditable="true"]')
  ).filter(el => !el.disabled && el.offsetParent !== null);
  if (!focusable.length) return;
  const first = focusable[0];
  const last  = focusable[focusable.length - 1];
  if (e.shiftKey) {
    if (document.activeElement === first) { e.preventDefault(); last.focus(); }
  } else {
    if (document.activeElement === last)  { e.preventDefault(); first.focus(); }
  }
});

/* ============================================================
   CHIPS — radio selection
============================================================ */

function selectChip(chip) {
  // Deselect all
  chips.forEach(c => {
    c.classList.remove('selected');
    c.setAttribute('aria-checked', 'false');
  });
  // Select clicked
  chip.classList.add('selected');
  chip.setAttribute('aria-checked', 'true');
  selectedKey = chip.dataset.key;
  renderSugerencias(selectedKey);
}

chips.forEach(chip => {
  chip.addEventListener('click', () => selectChip(chip));
  // Keyboard: Space / Enter selects
  chip.addEventListener('keydown', e => {
    if (e.key === ' ' || e.key === 'Enter') { e.preventDefault(); selectChip(chip); }
  });
});

/* ============================================================
   SUGERENCIAS RENDER
============================================================ */

function renderSugerencias(key) {
  sugerenciasList.innerHTML = '';
  const items = SUGERENCIAS[key] || [];

  if (items.length === 0) {
    sugerenciasPlaceholder.textContent = 'No hay sugerencias para este motivo.';
    sugerenciasPlaceholder.style.display = 'block';
    sugerenciasList.style.display = 'none';
    return;
  }

  sugerenciasPlaceholder.style.display = 'none';
  sugerenciasList.style.display = 'flex';

  items.forEach(text => {
    const btn = document.createElement('button');
    btn.className = 'sugerencia-pill';
    btn.type = 'button';
    btn.textContent = text;

    btn.addEventListener('click', () => {
      insertSugerencia(text);
      // Pulse animation
      btn.classList.remove('pulse');
      void btn.offsetWidth; // reflow to restart
      btn.classList.add('pulse');
      btn.addEventListener('animationend', () => btn.classList.remove('pulse'), { once: true });
    });

    sugerenciasList.appendChild(btn);
  });
}

/* Insert suggestion text into editor */
function insertSugerencia(text) {
  msgEditor.focus();
  // Clear existing content and set new text
  msgEditor.textContent = text;
  // Move cursor to end
  const range = document.createRange();
  const sel   = window.getSelection();
  range.selectNodeContents(msgEditor);
  range.collapse(false);
  sel.removeAllRanges();
  sel.addRange(range);
  updateCharCounter();
}

/* ============================================================
   INCLUDE CHECKBOX — enable / disable editor
============================================================ */

includeCheck.addEventListener('change', function () {
  if (this.checked) {
    editorWrapper.classList.remove('disabled');
  } else {
    editorWrapper.classList.add('disabled');
  }
});

/* ============================================================
   TOOLBAR — execCommand
============================================================ */

toolbarBtns.forEach(btn => {
  btn.addEventListener('mousedown', function (e) {
    e.preventDefault(); // keep editor focus
    const cmd = this.dataset.cmd;
    if (cmd) {
      document.execCommand(cmd, false, null);
      msgEditor.focus();
    }
  });
});

/* ============================================================
   CHARACTER COUNTER
============================================================ */

function updateCharCounter() {
  const text = msgEditor.textContent || '';
  const len  = text.length;
  charCounter.textContent = `${len} / ${MAX_CHARS}`;

  if (len > MAX_CHARS) {
    // Trim to max
    const trimmed = text.slice(0, MAX_CHARS);
    msgEditor.textContent = trimmed;
    // Move cursor to end
    const range = document.createRange();
    const sel   = window.getSelection();
    range.selectNodeContents(msgEditor);
    range.collapse(false);
    sel.removeAllRanges();
    sel.addRange(range);
    charCounter.textContent = `${MAX_CHARS} / ${MAX_CHARS}`;
  }

  if (len > WARN_AT) {
    charCounter.classList.add('warning');
  } else {
    charCounter.classList.remove('warning');
  }
}

msgEditor.addEventListener('input', updateCharCounter);
msgEditor.addEventListener('keyup', updateCharCounter);
msgEditor.addEventListener('paste', function () {
  // After paste, update on next tick (content not yet in DOM)
  setTimeout(updateCharCounter, 0);
});

/* ============================================================
   GUARDAR — toast + notice
============================================================ */

guardarBtn.addEventListener('click', function () {
  closeModal();
  showToast();
  giftNotice.classList.remove('hidden');
});

function showToast() {
  if (toastTimer) clearTimeout(toastTimer);
  toast.classList.add('show');
  toastTimer = setTimeout(() => toast.classList.remove('show'), 2500);
}
