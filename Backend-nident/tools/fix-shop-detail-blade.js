const fs = require('fs');

const bladePath = 'resources/views/web/pages/web_shopDetail.blade.php';
const cssPath = 'public/temp02/css/shop-detail.css';

let lines = fs.readFileSync(bladePath, 'utf8').split(/\r?\n/);
if (!lines[19]?.includes('<style>')) {
  throw new Error('Expected <style> at line 20');
}

const cssLines = lines.slice(21, 31); // rules only, skip comment and </style>
lines.splice(19, 13);
fs.writeFileSync(bladePath, lines.join('\n'), 'utf8');

const cssExtra = [
  '',
  '/* SD overrides (movidos desde blade inline) */',
  ...cssLines,
  '',
].join('\n');

fs.appendFileSync(cssPath, cssExtra, 'utf8');
console.log('OK: inline style removed from blade and appended to shop-detail.css');
