#!/usr/bin/env node
'use strict';

/**
 * Gera logo_eletropasso_branca.png e logo_eletropasso_preta.png.
 * Prioriza logo_eletropasso_preta_source.png (PNG transparente enviado pelo usuario).
 * A versao branca e derivada da preta (preto -> branco, vermelho preservado).
 */
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer');

async function imageStats(page, dataUrl) {
  return page.evaluate(async (url) => {
    const img = await new Promise((resolve, reject) => {
      const el = new Image();
      el.onload = () => resolve(el);
      el.onerror = reject;
      el.src = url;
    });
    const canvas = document.createElement('canvas');
    canvas.width = img.width;
    canvas.height = img.height;
    canvas.getContext('2d').drawImage(img, 0, 0);
    const data = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height).data;
    let transparent = 0;
    let red = 0;
    let dark = 0;
    for (let i = 0; i < data.length; i += 4) {
      const r = data[i];
      const g = data[i + 1];
      const b = data[i + 2];
      const a = data[i + 3];
      if (a < 16) {
        transparent++;
        continue;
      }
      if (r > 90 && r > g + 24 && r > b + 24) red++;
      else if (r < 80 && g < 80 && b < 80) dark++;
    }
    return { width: canvas.width, height: canvas.height, transparent, red, dark };
  }, dataUrl);
}

async function deriveBrancaFromPreta(page, pretaDataUrl) {
  return page.evaluate(async (url) => {
    function isRed(r, g, b, a) {
      return a > 16 && r > 90 && r > g + 24 && r > b + 24;
    }

    const img = await new Promise((resolve, reject) => {
      const el = new Image();
      el.onload = () => resolve(el);
      el.onerror = reject;
      el.src = url;
    });

    const canvas = document.createElement('canvas');
    canvas.width = img.width;
    canvas.height = img.height;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(img, 0, 0);
    const pixels = ctx.getImageData(0, 0, canvas.width, canvas.height);

    for (let i = 0; i < pixels.data.length; i += 4) {
      const r = pixels.data[i];
      const g = pixels.data[i + 1];
      const b = pixels.data[i + 2];
      const a = pixels.data[i + 3];
      if (a < 16) continue;
      if (isRed(r, g, b, a)) continue;
      pixels.data[i] = 255;
      pixels.data[i + 1] = 255;
      pixels.data[i + 2] = 255;
    }

    ctx.putImageData(pixels, 0, 0);
    return canvas.toDataURL('image/png');
  }, pretaDataUrl);
}

async function processLogosFromSource(_sourcePath, outWhite, outBlack, outDefault, sourcePretaPath = null) {
  if (!sourcePretaPath || !fs.existsSync(sourcePretaPath)) {
    throw new Error('logo_eletropasso_preta_source.png nao encontrada. Copie a logo preta enviada pelo usuario.');
  }

  const pretaDataUrl =
    'data:image/png;base64,' + fs.readFileSync(sourcePretaPath).toString('base64');

  const browser = await puppeteer.launch({
    headless: 'new',
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
  });

  try {
    const page = await browser.newPage();
    await page.setContent('<!DOCTYPE html><html><body></body></html>');

    const stats = await imageStats(page, pretaDataUrl);
    if (stats.width < 180 || stats.transparent < 1000 || stats.red < 500) {
      throw new Error('logo_eletropasso_preta_source.png invalida ou incompleta.');
    }

    fs.copyFileSync(sourcePretaPath, outBlack);
    fs.copyFileSync(sourcePretaPath, outDefault);
    console.log('Preta: ' + stats.width + 'x' + stats.height + ' (copiada da fonte)');

    const brancaDataUrl = await deriveBrancaFromPreta(page, pretaDataUrl);
    fs.writeFileSync(outWhite, Buffer.from(brancaDataUrl.split(',')[1], 'base64'));
    console.log('Branca: derivada da preta (preto -> branco)');
    console.log('Logos OK');
  } finally {
    await browser.close();
  }
}

async function main() {
  const brandDir = path.join(__dirname, '..', 'assets', 'brand');
  const pretaSource = path.join(brandDir, 'logo_eletropasso_preta_source.png');

  if (!fs.existsSync(pretaSource)) {
    const cursorAsset =
      'C:/Users/Inside Eletrônica/.cursor/projects/c-xampp-htdocs-Central-de-marketing-dev/assets/c__Users_Inside_Eletr_nica_AppData_Roaming_Cursor_User_workspaceStorage_b71f852924507cf189d5c9bb4d7bf3ca_images_logo.eletropasso.preto-b77d41e0-ba24-4405-9314-be8fd3eed8b4.png';
    if (fs.existsSync(cursorAsset)) {
      fs.copyFileSync(cursorAsset, pretaSource);
      console.log('Preta source importada do anexo Cursor.');
    }
  }

  await processLogosFromSource(
    path.join(brandDir, 'logo_eletropasso_branca_source.png'),
    path.join(brandDir, 'logo_eletropasso_branca.png'),
    path.join(brandDir, 'logo_eletropasso_preta.png'),
    path.join(brandDir, 'logo_eletropasso.png'),
    pretaSource
  );
}

if (require.main === module) {
  main().catch((err) => {
    console.error(err);
    process.exit(1);
  });
}

module.exports = { processLogosFromSource };
