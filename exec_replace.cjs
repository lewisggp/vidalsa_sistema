const fs = require('fs');

const appFile = 'vidalsa_mobile/App.js';
const newContentFile = 'new_movs.txt';

const app = fs.readFileSync(appFile, 'utf8');
const newMovs = fs.readFileSync(newContentFile, 'utf8');

const prefix = "// ─── PANTALLA DE MOVILIZACIONES ───────────────────────────────────────────────";
const suffix = "// ─── APP PRINCIPAL ────────────────────────────────────────────────────────────";

const startIdx = app.indexOf(prefix);
const endIdx = app.indexOf(suffix);

if (startIdx !== -1 && endIdx !== -1) {
  const result = app.substring(0, startIdx) + newMovs + '\n\n' + app.substring(endIdx);
  fs.writeFileSync(appFile, result, 'utf8');
  console.log('Reemplazo correcto!');
} else {
  console.error('No se encontro prefijo o sufijo');
}
