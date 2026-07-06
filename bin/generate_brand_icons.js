/**
 * Gera PNGs do icone da Central de Marketing para favicon e PWA (instalacao Windows).
 */
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer');

const root = path.join(__dirname, '..');
const brandDir = path.join(root, 'assets', 'brand');
const svgPath = path.join(brandDir, 'icon_central_marketing.svg');
const sizes = [
  { name: 'icon_central_marketing-32.png', size: 32 },
  { name: 'icon_central_marketing-180.png', size: 180 },
  { name: 'icon_central_marketing-192.png', size: 192 },
  { name: 'icon_central_marketing-512.png', size: 512 },
];

async function main() {
  const svg = fs.readFileSync(svgPath, 'utf8');
  const browser = await puppeteer.launch({ headless: true });
  const page = await browser.newPage();

  for (const { name, size } of sizes) {
    const html = `<!DOCTYPE html><html><head><meta charset="utf-8"></head>
<body style="margin:0;background:transparent;">
<div id="icon" style="width:${size}px;height:${size}px;">${svg}</div>
</body></html>`;

    await page.setViewport({ width: size, height: size, deviceScaleFactor: 1 });
    await page.setContent(html, { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => {
      const el = document.querySelector('svg');
      if (el) {
        el.setAttribute('width', '100%');
        el.setAttribute('height', '100%');
      }
    });

    const out = path.join(brandDir, name);
    await page.screenshot({
      path: out,
      clip: { x: 0, y: 0, width: size, height: size },
      omitBackground: false,
    });
    console.log('OK:', name);
  }

  await browser.close();
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
