const fs = require('fs');
const path = require('path');

const html = fs.readFileSync(path.join(__dirname, '../../../public/temp02/index.html'), 'utf8');
const outDir = path.join(__dirname, '../../../resources/views/web/pages/Valento/partials');

const sections = [
  { name: 'topbar_nav', start: '<div id="topbar">', end: '<button type="button" id="scroll-top"' },
  { name: 'hero', start: '<section id="hero">', end: '<div class="stats-bar">' },
  { name: 'stats_ticker', start: '<div class="stats-bar">', end: '<div class="port-left-panel">' },
  { name: 'ejemplares_nav', start: '<div class="port-left-panel">', end: '<section id="ejemplares">' },
  { name: 'ejemplares_galeria', start: '<div id="ej-galeria"', end: '<section id="portafolio">' },
  { name: 'portafolio', start: '<section id="portafolio">', end: '<section id="hub">' },
  { name: 'cta', start: '<section id="cta">', end: '<footer>' },
  { name: 'footer', start: '<footer>', end: '<script src="js/main.js"' },
];

fs.mkdirSync(outDir, { recursive: true });

for (const s of sections) {
  const i0 = html.indexOf(s.start);
  const i1 = html.indexOf(s.end);
  if (i0 < 0 || i1 < 0) {
    console.warn('skip', s.name, i0, i1);
    continue;
  }
  let chunk = html.slice(i0, i1);
  if (s.name === 'hero') {
    chunk = chunk.replace(/onclick="[^"]*"/g, '');
  }
  const dest = path.join(outDir, s.name + '.blade.php');
  fs.writeFileSync(dest, chunk);
  console.log(s.name, chunk.length);
}
