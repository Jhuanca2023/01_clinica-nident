const fs = require('fs');
const path = require('path');

const candidates = [
  path.join(__dirname, '../../../public/temp02/index.html'),
  'C:/Users/jhovani/Desktop/miweb/index.html',
];
const htmlPath = candidates.find((p) => fs.existsSync(p));
if (!htmlPath) throw new Error('index.html no encontrado');
const html = fs.readFileSync(htmlPath, 'utf8');
const sections = [
  { slug: 'pagina_web', id: 'ejemplares', titulo: 'Página web', titulo_em: 'profesional', sub: 'Quince ejemplos reales de páginas web profesionales listas para tu negocio.', count: 15 },
  { slug: 'landing_page', id: 'ejemplares-landing', titulo: 'Landing page', titulo_em: 'profesional', sub: 'Siete landing pages enfocadas en conversión para campañas, lanzamientos y captación de clientes.', count: 7 },
  { slug: 'tienda_virtual', id: 'ejemplares-tienda', titulo: 'Tienda', titulo_em: 'virtual', sub: 'Diez tiendas virtuales con catálogo, carrito y pagos listas para vender en línea.', count: 10 },
];

const re = /<article class="ejemplar-card"([^>]*)>([\s\S]*?)<\/article>/g;
const all = [];
let m;
while ((m = re.exec(html)) !== null) all.push({ attrs: m[1], body: m[2] });

let idx = 0;
const out = { secciones: [], items: [] };

for (const s of sections) {
  out.secciones.push({
    slug: s.slug,
    anchor_id: s.id,
    etiqueta: 'Ejemplares',
    titulo: s.titulo,
    titulo_destacado: s.titulo_em,
    subtitulo: s.sub,
    conteo_nav: s.count,
    orden: out.secciones.length + 1,
  });
  const grid = all.slice(idx, idx + s.count);
  idx += s.count;
  grid.forEach((card, i) => {
    const block = card.body;
    const attrs = card.attrs;
    const id = (attrs.match(/\bid="([^"]+)"/) || block.match(/\bid="([^"]+)"/) || [])[1] || '';
    const h3 = (block.match(/<h3>([^<]+)<\/h3>/) || [])[1] || '';
    const p = (block.match(/<div class="ejemplar-body">[\s\S]*?<p>([\s\S]*?)<\/p>/) || [])[1] || '';
    const img = (block.match(/src="([^"]+)"/) || [])[1] || '';
    const badge = (block.match(/ejemplar-badge">(\d+)/) || [])[1] || String(i + 1);
    const overlay = (block.match(/ejemplar-overlay-copy"><strong>([^<]+)<\/strong>/) || [])[1] || '';
    const href =
      (block.match(/class="ejemplar-link"[^>]*href="([^"]+)"/) || [])[1]
      || (block.match(/href="([^"]+)"[^>]*class="ejemplar-link"/) || [])[1]
      || '#';
    out.items.push({
      seccion_slug: s.slug,
      slug: id,
      nombre: h3,
      descripcion: p.replace(/\s+/g, ' ').trim(),
      url_imagen: img,
      numero_badge: badge,
      overlay_texto: overlay,
      url_demo: href,
      orden: i + 1,
    });
  });
}

const dest = path.join(__dirname, '../data/ejemplares.json');
fs.mkdirSync(path.dirname(dest), { recursive: true });
fs.writeFileSync(dest, JSON.stringify(out, null, 2));
console.log('secciones', out.secciones.length, 'items', out.items.length);
