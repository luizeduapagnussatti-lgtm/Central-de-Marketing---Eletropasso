#!/usr/bin/env node
'use strict';

/**
 * Renderiza encarte HTML em PNG via Puppeteer.
 * Uso: node bin/render_encarte.js <html_path> <formato> <output_png> [timeout_ms]
 */

const fs = require('fs');
const path = require('path');

const VIEWPORTS = {
  '9x16':   { width: 1080, height: 1920, deviceScaleFactor: 2 },
  'status': { width: 1080, height: 1920, deviceScaleFactor: 2 },
  '1x1':    { width: 1080, height: 1080, deviceScaleFactor: 2 },
  '16x9':   { width: 1920, height: 1080, deviceScaleFactor: 2 },
  'a4':     { width: 2480, height: 3508, deviceScaleFactor: 1 },
};

async function main() {
  const [, , htmlPath, formato, outputPath, timeoutMs = '30000'] = process.argv;

  if (!htmlPath || !formato || !outputPath) {
    console.error('Uso: node render_encarte.js <html_path> <formato> <output_png> [timeout_ms]');
    process.exit(1);
  }

  if (!fs.existsSync(htmlPath)) {
    console.error('Arquivo HTML nao encontrado:', htmlPath);
    process.exit(1);
  }

  const viewport = VIEWPORTS[formato];
  if (!viewport) {
    console.error('Formato invalido:', formato);
    process.exit(1);
  }

  let puppeteer;
  try {
    puppeteer = require('puppeteer');
  } catch (e) {
    console.error('Puppeteer nao instalado. Execute: npm install');
    process.exit(1);
  }

  const outputDir = path.dirname(outputPath);
  if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
  }

  const browser = await puppeteer.launch({
    headless: 'new',
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
  });

  try {
    const page = await browser.newPage();
    await page.setViewport(viewport);

    const fileUrl = 'file:///' + path.resolve(htmlPath).replace(/\\/g, '/');
    await page.goto(fileUrl, {
      waitUntil: 'networkidle0',
      timeout: parseInt(timeoutMs, 10),
    });

    await page.evaluate(async () => {
      if (document.fonts && document.fonts.ready) {
        await document.fonts.ready;
      }

      const images = Array.from(document.querySelectorAll('img'));
      await Promise.all(
        images.map((img) => {
          if (img.complete && img.naturalWidth > 0) return Promise.resolve();
          return new Promise((resolve) => {
            img.onload = resolve;
            img.onerror = resolve;
            if (!img.complete) {
              setTimeout(resolve, 3000);
            } else {
              resolve();
            }
          });
        })
      );
    });

    // Breve pausa para background-image e layout estabilizarem
    await new Promise((r) => setTimeout(r, 250));

    await page.screenshot({
      type: 'png',
      fullPage: false,
      clip: {
        x: 0,
        y: 0,
        width: viewport.width,
        height: viewport.height,
      },
      path: outputPath,
    });

    console.log('PNG gerado:', outputPath);
  } catch (err) {
    console.error('Erro Puppeteer:', err.message);
    process.exit(1);
  } finally {
    await browser.close();
  }
}

main();
