/**
 * Editor visual Fabric.js — workstation admin de modelos de encarte.
 */
(function () {
  'use strict';

  if (typeof fabric === 'undefined' || typeof EDITOR_DATA === 'undefined') {
    return;
  }

  const ASSET_LIBRARY = [
    {
      id: 'tag_oferta',
      label: 'Tag Oferta',
      category: 'varejo',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="#b91c1c" d="M0 80V229.5c0 17 6.7 33.3 18.7 45.3l176 176c25 25 65.5 25 90.5 0L418.7 317.3c25-25 25-65.5 0-90.5L245.3 53.3C233.3 41.3 217 34.5 200 34.5H80c-44.2 0-80 35.8-80 80zm112 0a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/></svg>',
    },
    {
      id: 'percent',
      label: '% Desconto',
      category: 'varejo',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="#b91c1c" d="M374.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-320 320c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l320-320zM128 128A64 64 0 1 0 128 0a64 64 0 1 0 0 128zm256 256a64 64 0 1 0 0-128 64 64 0 1 0 0 128z"/></svg>',
    },
    {
      id: 'megaphone',
      label: 'Promocao',
      category: 'varejo',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="#b91c1c" d="M544 32c-17.7 0-32 14.3-32 32V320c0 17.7 14.3 32 32 32s32-14.3 32-32V64c0-17.7-14.3-32-32-32zM416 96c-17.7 0-32 14.3-32 32V352c0 17.7 14.3 32 32 32s32-14.3 32-32V128c0-17.7-14.3-32-32-32zM320 128H64c-35.3 0-64 28.7-64 64V320c0 35.3 28.7 64 64 64H320c17.7 0 32-14.3 32-32V160c0-17.7-14.3-32-32-32z"/></svg>',
    },
    {
      id: 'arrow',
      label: 'Seta',
      category: 'varejo',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="#b91c1c" d="m438.6 278.6-160 160c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L338.8 288 32 288c-17.7 0-32-14.3-32-32s14.3-32 32-32l306.7 0L233.4 118.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l160 160c12.5 12.5 12.5 32.8 0 45.3z"/></svg>',
    },
    {
      id: 'bolt',
      label: 'Raio',
      category: 'eletrica',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="#eab308" d="M349.4 44.6c5.9-13.7 1.5-29.7-10.6-38.5s-28.6-8-39.9 1.8l-256 224c-10 8.8-13.6 22.9-8.9 35.3S50.7 288 64 288H175.5L98.6 467.4c-5.9 13.7-1.5 29.7 10.6 38.5s28.6 8 39.9-1.8l256-224c10-8.8 13.6-22.9 8.9-35.3s-22.2-19.5-35.7-19.5H272.5L349.4 44.6z"/></svg>',
    },
    {
      id: 'lightbulb',
      label: 'Lampada',
      category: 'eletrica',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="#eab308" d="M272 384c9.6-31.4 29.5-59.3 49.2-86.2c0 0 0 0 0 0c5.2-7.1 10.4-14.2 15.4-21.4c19.8-28.5 31.4-63 31.4-100.3C368 78.8 289.2 0 192 0S16 78.8 16 176c0 37.3 11.6 71.9 31.4 100.3c5 7.2 10.2 14.3 15.4 21.4c0 0 0 0 0 0c19.8 26.9 39.7 54.8 49.2 86.2H272zM192 512c44.2 0 80-35.8 80-80H112c0 44.2 35.8 80 80 80z"/></svg>',
    },
    {
      id: 'plug',
      label: 'Tomada',
      category: 'eletrica',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="#94a3b8" d="M32 32C14.3 32 0 46.3 0 64S14.3 96 32 96H64V224H32c-17.7 0-32 14.3-32 32s14.3 32 32 32H96V448c0 17.7 14.3 32 32 32s32-14.3 32-32V320H288v128c0 17.7 14.3 32 32 32s32-14.3 32-32V320h64c17.7 0 32-14.3 32-32s-14.3-32-32-32H320V96h32c17.7 0 32-14.3 32-32s-14.3-32-32-32H32z"/></svg>',
    },
    {
      id: 'shield',
      label: 'Seguranca',
      category: 'eletrica',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="#16a34a" d="M256 0c4.6 0 9.2 1 13.4 2.9L457.7 82.8c22 9.3 38.4 31 38.3 57.2c-.5 99.2-41.3 280.9-213.6 363.2c-16.7 8-36.1 8-52.8 0C57.3 420.1 16.5 238.4 16 139.2c-.1-26.2 16.4-47.9 38.3-57.2L242.7 2.9C246.8 1 251.4 0 256 0z"/></svg>',
    },
    {
      id: 'wrench',
      label: 'Ferramenta',
      category: 'eletrica',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="#94a3b8" d="M352 320c88.4 0 160-71.6 160-160c0-15.3-2.2-30.1-6.2-44.2L433 146.5c-3.2 8.8-9.8 15.9-18.1 19.6L382 176.8c-8.3 3.7-17.9 3.3-25.8-.9l-46.5-26.2c-8-4.5-13.6-12.1-15.5-20.9l-5.9-26.6c-1.9-8.8-7.5-16.4-15.5-20.9L231.8 76.4c-7.9-4.2-17.5-4.6-25.8-.9l-32.9 14.7c-8.3 3.7-14.9 10.8-18.1 19.6l-6.8 18.7c-4 14.1-6.2 28.9-6.2 44.2c0 88.4 71.6 160 160 160zM96 320c-53 0-96 43-96 96s43 96 96 96H416c53 0 96-43 96-96s-43-96-96-96H96z"/></svg>',
    },
    {
      id: 'truck',
      label: 'Entrega',
      category: 'eletrica',
      svg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="#b91c1c" d="M48 0C21.5 0 0 21.5 0 48V368c0 26.5 21.5 48 48 48H64c0 53 43 96 96 96s96-43 96-96H384c0 53 43 96 96 96s96-43 96-96h32c17.7 0 32-14.3 32-32s-14.3-32-32-32H576V256 64 48H48zM416 256H576l-64-96H416v96zM160 464a48 48 0 1 1 0-96 48 48 0 1 1 0 96zm288 0a48 48 0 1 1 0-96 48 48 0 1 1 0 96z"/></svg>',
    },
  ];

  const CATEGORY_LABELS = {
    varejo: 'Varejo',
    eletrica: 'Eletrica / Ferramentas',
  };

  const alertSlot = document.getElementById('editor-alert');
  const painelProps = document.getElementById('painel-props');
  const propsTexto = document.getElementById('props-texto');
  const propsTextoDinamico = document.getElementById('props-texto-dinamico');
  const propsZona = document.getElementById('props-zona');
  const propsImagem = document.getElementById('props-imagem');
  const propsVetor = document.getElementById('props-vetor');
  const propsComum = document.getElementById('props-comum');
  const propsEmpty = document.getElementById('props-empty');

  let formato = EDITOR_DATA.formato || EDITOR_DATA.modelo.formatos_suportados[0] || '9x16';
  let dims = EDITOR_DATA.formatos[formato] || EDITOR_DATA.formatos['9x16'];
  let DISPLAY_SCALE = calcDisplayScale(dims);
  let canvas = null;
  let palcoObject = null;
  let fundoRect = null;
  let propsObj = null;
  let syncingProps = false;
  let zoneCounter = 0;

  const CUSTOM_PROPS = ['name', 'isProductZone', 'zoneId', 'isDynamicText', 'textType', 'linkedZone'];

  const DYNAMIC_TEXT_DEFAULTS = {
    nome_produto: { text: '[NOME_PRODUTO]', fontSize: 28, fill: '#ffffff', fontWeight: '700', linethrough: false },
    preco_normal: { text: '[PRECO_NORMAL]', fontSize: 22, fill: '#9ca3af', fontWeight: '400', linethrough: true },
    preco_promo: { text: '[PRECO_PROMO]', fontSize: 72, fill: '#dc2626', fontWeight: '900', linethrough: false },
    unidade: { text: '[UNIDADE]', fontSize: 18, fill: '#d1d5db', fontWeight: '600', linethrough: false },
  };

  function calcDisplayScale(d) {
    return Math.min(680 / d.width, 900 / d.height);
  }

  function showEditorAlert(msg, type = 'error') {
    if (!alertSlot) return;
    showAlert(alertSlot, msg, type);
  }

  function canvasSize() {
    return {
      width: Math.round(dims.width * DISPLAY_SCALE),
      height: Math.round(dims.height * DISPLAY_SCALE),
    };
  }

  function assetUrl(relative) {
    if (!relative) return '';
    if (/^https?:\/\//.test(relative) || relative.startsWith('data:')) {
      return relative;
    }
    const base = EDITOR_DATA.assetsBase || '';
    return base + relative.replace(/^\//, '');
  }

  function isProductZone(obj) {
    return obj?.isProductZone === true;
  }

  function isDynamicText(obj) {
    return obj?.isDynamicText === true;
  }

  function isTextObject(obj) {
    return obj && ['i-text', 'text', 'textbox'].includes(obj.type);
  }

  function isImageObject(obj) {
    return obj && obj.type === 'image';
  }

  function isVectorObject(obj) {
    return obj && ['path', 'group'].includes(obj.type) && !isProductZone(obj);
  }

  function isProtectedObject(obj) {
    return obj && (obj.name === 'fundo-editor' || obj.name === 'palco');
  }

  function isEditableObject(obj) {
    if (!obj || isProtectedObject(obj)) return false;
    return (
      isTextObject(obj) ||
      isImageObject(obj) ||
      isVectorObject(obj) ||
      isProductZone(obj) ||
      isDynamicText(obj)
    );
  }

  function isTypingInForm() {
    const el = document.activeElement;
    if (!el) return false;
    const tag = el.tagName;
    if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return true;
    return el.isContentEditable === true;
  }

  function isEditingCanvasText() {
    const obj = canvas?.getActiveObject();
    return !!(obj && obj.isEditing);
  }

  function reescalarObject(obj, factor) {
    const copy = { ...obj };
    const numericKeys = ['left', 'top', 'width', 'height', 'fontSize', 'rx', 'ry', 'radius', 'strokeWidth'];
    numericKeys.forEach((key) => {
      if (typeof copy[key] === 'number') {
        copy[key] *= factor;
      }
    });

    if (copy.shadow && typeof copy.shadow === 'object') {
      copy.shadow = { ...copy.shadow };
      ['offsetX', 'offsetY', 'blur'].forEach((key) => {
        if (typeof copy.shadow[key] === 'number') {
          copy.shadow[key] *= factor;
        }
      });
    }

    return copy;
  }

  function reescalarState(state, factor) {
    const scaled = JSON.parse(JSON.stringify(state));
    if (Array.isArray(scaled.objects)) {
      scaled.objects = scaled.objects.map((obj) => reescalarObject(obj, factor));
    }
    return scaled;
  }

  function findObjectByName(name) {
    return canvas.getObjects().find((obj) => obj.name === name) || null;
  }

  function rgbaToHex(color) {
    if (!color) return '#000000';
    if (color.startsWith('#')) return color.slice(0, 7);
    const match = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
    if (!match) return '#000000';
    const r = parseInt(match[1], 10).toString(16).padStart(2, '0');
    const g = parseInt(match[2], 10).toString(16).padStart(2, '0');
    const b = parseInt(match[3], 10).toString(16).padStart(2, '0');
    return `#${r}${g}${b}`;
  }

  function hexToRgba(hex, alpha) {
    const h = hex.replace('#', '');
    const r = parseInt(h.slice(0, 2), 16);
    const g = parseInt(h.slice(2, 4), 16);
    const b = parseInt(h.slice(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
  }

  function getActiveTarget() {
    const active = canvas?.getActiveObject();
    if (!active) return null;
    if (active.type === 'activeSelection' && active._objects?.length === 1) {
      return active._objects[0];
    }
    if (active.type === 'activeSelection') return null;
    return active;
  }

  function enforceBackgroundOrder() {
    if (palcoObject) canvas.sendToBack(palcoObject);
    if (fundoRect) canvas.sendToBack(fundoRect);
  }

  function closePropsPanel() {
    propsObj = null;
    painelProps?.classList.add('hidden');
    propsTexto?.classList.add('hidden');
    propsTextoDinamico?.classList.add('hidden');
    propsZona?.classList.add('hidden');
    propsImagem?.classList.add('hidden');
    propsVetor?.classList.add('hidden');
    propsComum?.classList.add('hidden');
    propsEmpty?.classList.remove('hidden');
  }

  function populateZoneProps(obj) {
    syncingProps = true;
    const zoneIdEl = document.getElementById('prop-zone-id');
    if (zoneIdEl) zoneIdEl.value = String(obj.zoneId ?? '');
    syncingProps = false;
  }

  function populateDynamicTextProps(obj) {
    syncingProps = true;
    const linkedEl = document.getElementById('prop-linked-zone');
    if (linkedEl) linkedEl.value = String(obj.linkedZone ?? 1);
    syncingProps = false;
  }

  function openPropsPanel(obj) {
    if (!isEditableObject(obj)) {
      closePropsPanel();
      return;
    }

    propsObj = obj;
    painelProps?.classList.remove('hidden');
    propsEmpty?.classList.add('hidden');

    propsTexto?.classList.add('hidden');
    propsTextoDinamico?.classList.add('hidden');
    propsZona?.classList.add('hidden');
    propsImagem?.classList.add('hidden');
    propsVetor?.classList.add('hidden');
    propsComum?.classList.add('hidden');

    if (isProductZone(obj)) {
      propsZona?.classList.remove('hidden');
      populateZoneProps(obj);
      return;
    }

    propsComum?.classList.remove('hidden');

    if (isDynamicText(obj)) {
      propsTexto?.classList.remove('hidden');
      propsTextoDinamico?.classList.remove('hidden');
      populateTextProps(obj);
      populateDynamicTextProps(obj);
      return;
    }

    if (isTextObject(obj)) {
      propsTexto?.classList.remove('hidden');
      populateTextProps(obj);
    } else if (isImageObject(obj)) {
      propsImagem?.classList.remove('hidden');
      populateImageProps(obj);
    } else if (isVectorObject(obj)) {
      propsVetor?.classList.remove('hidden');
      populateVectorProps(obj);
    }
  }

  function populateTextProps(obj) {
    syncingProps = true;

    const fontSize = Math.round(obj.fontSize || 16);
    const fontSizeEl = document.getElementById('prop-font-size');
    const fontSizeVal = document.getElementById('prop-font-size-val');
    if (fontSizeEl) fontSizeEl.value = String(fontSize);
    if (fontSizeVal) fontSizeVal.textContent = String(fontSize);

    const colorEl = document.getElementById('prop-text-color');
    if (colorEl) colorEl.value = rgbaToHex(String(obj.fill || '#ffffff'));

    document.querySelectorAll('.props-align-btn').forEach((btn) => {
      btn.classList.toggle('is-active', btn.dataset.align === (obj.textAlign || 'left'));
    });

    const shadow = obj.shadow || {};
    const sx = document.getElementById('prop-shadow-x');
    const sy = document.getElementById('prop-shadow-y');
    const sb = document.getElementById('prop-shadow-blur');
    const sc = document.getElementById('prop-shadow-color');

    const offsetX = Math.round(shadow.offsetX || 0);
    const offsetY = Math.round(shadow.offsetY || 0);
    const blur = Math.round(shadow.blur || 0);

    if (sx) sx.value = String(offsetX);
    if (sy) sy.value = String(offsetY);
    if (sb) sb.value = String(blur);
    document.getElementById('prop-shadow-x-val').textContent = String(offsetX);
    document.getElementById('prop-shadow-y-val').textContent = String(offsetY);
    document.getElementById('prop-shadow-blur-val').textContent = String(blur);
    if (sc) sc.value = rgbaToHex(String(shadow.color || '#000000'));

    syncingProps = false;
  }

  function populateImageProps(obj) {
    syncingProps = true;
    const opacityEl = document.getElementById('prop-opacity');
    const opacityVal = document.getElementById('prop-opacity-val');
    const pct = Math.round((obj.opacity ?? 1) * 100);
    if (opacityEl) opacityEl.value = String(pct);
    if (opacityVal) opacityVal.textContent = String(pct);
    syncingProps = false;
  }

  function getVectorFill(obj) {
    if (obj.type === 'group' && obj._objects?.length) {
      return obj._objects[0]?.fill || '#b91c1c';
    }
    return obj.fill || '#b91c1c';
  }

  function setVectorFill(obj, color) {
    if (obj.type === 'group' && obj._objects) {
      obj._objects.forEach((child) => child.set('fill', color));
    } else {
      obj.set('fill', color);
    }
  }

  function populateVectorProps(obj) {
    syncingProps = true;
    const fillEl = document.getElementById('prop-vetor-fill');
    const opacityEl = document.getElementById('prop-vetor-opacity');
    const opacityVal = document.getElementById('prop-vetor-opacity-val');
    if (fillEl) fillEl.value = rgbaToHex(String(getVectorFill(obj)));
    const pct = Math.round((obj.opacity ?? 1) * 100);
    if (opacityEl) opacityEl.value = String(pct);
    if (opacityVal) opacityVal.textContent = String(pct);
    syncingProps = false;
  }

  function applyTextShadowFromControls() {
    if (!propsObj || syncingProps) return;

    const offsetX = parseInt(document.getElementById('prop-shadow-x')?.value || '0', 10);
    const offsetY = parseInt(document.getElementById('prop-shadow-y')?.value || '0', 10);
    const blur = parseInt(document.getElementById('prop-shadow-blur')?.value || '0', 10);
    const colorHex = document.getElementById('prop-shadow-color')?.value || '#000000';

    document.getElementById('prop-shadow-x-val').textContent = String(offsetX);
    document.getElementById('prop-shadow-y-val').textContent = String(offsetY);
    document.getElementById('prop-shadow-blur-val').textContent = String(blur);

    if (blur === 0 && offsetX === 0 && offsetY === 0) {
      propsObj.set('shadow', null);
    } else {
      propsObj.set(
        'shadow',
        new fabric.Shadow({
          color: hexToRgba(colorHex, 0.85),
          blur,
          offsetX,
          offsetY,
        })
      );
    }

    canvas.renderAll();
  }

  function bringForwardActive() {
    const obj = getActiveTarget();
    if (!obj || isProtectedObject(obj)) return;
    canvas.bringForward(obj);
    enforceBackgroundOrder();
    canvas.renderAll();
    refreshLayersList();
  }

  function bringToFrontActive() {
    const obj = getActiveTarget();
    if (!obj || isProtectedObject(obj)) return;
    canvas.bringToFront(obj);
    enforceBackgroundOrder();
    canvas.renderAll();
    refreshLayersList();
  }

  function sendBackwardActive() {
    const obj = getActiveTarget();
    if (!obj || isProtectedObject(obj)) return;
    canvas.sendBackwards(obj);
    enforceBackgroundOrder();
    canvas.renderAll();
    refreshLayersList();
  }

  function sendToBackSafe() {
    const obj = getActiveTarget();
    if (!obj || isProtectedObject(obj)) return;
    canvas.sendToBack(obj);
    enforceBackgroundOrder();
    canvas.renderAll();
    refreshLayersList();
  }

  function removeActiveObject() {
    const obj = getActiveTarget();
    if (!obj || isProtectedObject(obj)) {
      showEditorAlert('Elemento protegido ou nenhum selecionado.');
      return;
    }
    canvas.remove(obj);
    canvas.discardActiveObject();
    canvas.renderAll();
    closePropsPanel();
    refreshLayersList();
  }

  function bindKeyboardDelete() {
    document.addEventListener('keydown', (e) => {
      if (e.key !== 'Delete' && e.key !== 'Backspace') return;
      if (isTypingInForm() || isEditingCanvasText()) return;

      const obj = getActiveTarget();
      if (!obj || isProtectedObject(obj)) return;

      e.preventDefault();
      removeActiveObject();
    });
  }

  function bindPropsPanel() {
    document.getElementById('btn-fechar-props')?.addEventListener('click', () => {
      canvas.discardActiveObject();
      canvas.renderAll();
      closePropsPanel();
    });

    document.getElementById('prop-font-size')?.addEventListener('input', (e) => {
      if (!propsObj || syncingProps) return;
      const size = parseInt(e.target.value, 10);
      document.getElementById('prop-font-size-val').textContent = String(size);
      propsObj.set('fontSize', size);
      canvas.renderAll();
    });

    document.getElementById('prop-text-color')?.addEventListener('input', (e) => {
      if (!propsObj || syncingProps) return;
      propsObj.set('fill', e.target.value);
      canvas.renderAll();
    });

    document.querySelectorAll('.props-align-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        if (!propsObj) return;
        const align = btn.dataset.align || 'left';
        propsObj.set('textAlign', align);
        document.querySelectorAll('.props-align-btn').forEach((b) => {
          b.classList.toggle('is-active', b === btn);
        });
        canvas.renderAll();
      });
    });

    ['prop-shadow-x', 'prop-shadow-y', 'prop-shadow-blur'].forEach((id) => {
      document.getElementById(id)?.addEventListener('input', applyTextShadowFromControls);
    });
    document.getElementById('prop-shadow-color')?.addEventListener('input', applyTextShadowFromControls);

    document.getElementById('prop-opacity')?.addEventListener('input', (e) => {
      if (!propsObj || syncingProps) return;
      const pct = parseInt(e.target.value, 10);
      document.getElementById('prop-opacity-val').textContent = String(pct);
      propsObj.set('opacity', pct / 100);
      canvas.renderAll();
    });

    document.getElementById('prop-vetor-fill')?.addEventListener('input', (e) => {
      if (!propsObj || syncingProps || !isVectorObject(propsObj)) return;
      setVectorFill(propsObj, e.target.value);
      canvas.renderAll();
    });

    document.getElementById('prop-vetor-opacity')?.addEventListener('input', (e) => {
      if (!propsObj || syncingProps || !isVectorObject(propsObj)) return;
      const pct = parseInt(e.target.value, 10);
      document.getElementById('prop-vetor-opacity-val').textContent = String(pct);
      propsObj.set('opacity', pct / 100);
      canvas.renderAll();
    });

    document.getElementById('prop-duplicar')?.addEventListener('click', () => {
      const obj = propsObj;
      if (!obj || !isImageObject(obj)) return;
      obj.clone((cloned) => {
        cloned.set({
          left: (obj.left || 0) + 20,
          top: (obj.top || 0) + 20,
          name: (obj.name || 'elemento') + '_copy',
        });
        canvas.add(cloned);
        canvas.setActiveObject(cloned);
        canvas.renderAll();
        openPropsPanel(cloned);
        refreshLayersList();
      });
    });

    document.getElementById('prop-layer-forward')?.addEventListener('click', bringForwardActive);
    document.getElementById('prop-layer-front')?.addEventListener('click', bringToFrontActive);
    document.getElementById('prop-layer-backward')?.addEventListener('click', sendBackwardActive);
    document.getElementById('prop-layer-back')?.addEventListener('click', sendToBackSafe);
    document.getElementById('prop-excluir')?.addEventListener('click', removeActiveObject);

    document.getElementById('prop-linked-zone')?.addEventListener('input', (e) => {
      if (!propsObj || syncingProps || !isDynamicText(propsObj)) return;
      const val = Math.max(1, Math.min(24, parseInt(e.target.value, 10) || 1));
      propsObj.set('linkedZone', val);
      canvas.renderAll();
    });
  }

  function syncZoneCounterFromCanvas() {
    let max = 0;
    canvas?.getObjects().forEach((obj) => {
      if (obj.isProductZone && obj.zoneId) {
        max = Math.max(max, obj.zoneId);
      }
    });
    zoneCounter = max;
  }

  function addProductZone() {
    zoneCounter += 1;
    const w = dims.width * DISPLAY_SCALE * 0.28;
    const h = dims.height * DISPLAY_SCALE * 0.2;
    const cx = canvas.width / 2 - w / 2;
    const cy = canvas.height / 2 - h / 2;

    const rect = new fabric.Rect({
      width: w,
      height: h,
      fill: 'rgba(59, 130, 246, 0.15)',
      stroke: '#3b82f6',
      strokeWidth: 2,
      strokeDashArray: [8, 4],
      rx: 8,
      ry: 8,
    });

    const label = new fabric.Text(`Zona de Produto ${zoneCounter}`, {
      fontSize: 14 * DISPLAY_SCALE,
      fill: '#93c5fd',
      fontWeight: '700',
      originX: 'center',
      originY: 'center',
      left: w / 2,
      top: h / 2,
    });

    const group = new fabric.Group([rect, label], {
      left: cx,
      top: cy,
      name: `zona_produto_${zoneCounter}`,
      isProductZone: true,
      zoneId: zoneCounter,
    });

    canvas.add(group);
    canvas.setActiveObject(group);
    canvas.renderAll();
    openPropsPanel(group);
    refreshLayersList();
    showEditorAlert(`Zona de produto ${zoneCounter} adicionada.`, 'success');
  }

  function addDynamicText(textType) {
    const cfg = DYNAMIC_TEXT_DEFAULTS[textType];
    if (!cfg) return;

    const itext = new fabric.IText(cfg.text, {
      left: canvas.width / 2 - 80,
      top: canvas.height / 2,
      fontSize: cfg.fontSize * DISPLAY_SCALE,
      fill: cfg.fill,
      fontWeight: cfg.fontWeight,
      linethrough: cfg.linethrough,
      fontFamily: 'Bebas Neue, Oswald, Impact, sans-serif',
      name: `${textType}_${Date.now()}`,
      isDynamicText: true,
      textType,
      linkedZone: 1,
    });

    canvas.add(itext);
    canvas.setActiveObject(itext);
    canvas.renderAll();
    openPropsPanel(itext);
    refreshLayersList();
    showEditorAlert(`Variavel ${cfg.text} adicionada.`, 'success');
  }

  function bindProdutosTab() {
    document.getElementById('btn-add-zona')?.addEventListener('click', addProductZone);

    document.querySelectorAll('.var-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        const textType = btn.dataset.vartype;
        if (textType) addDynamicText(textType);
      });
    });
  }

  function updateCanvasThumb(dataUrl) {
    const thumb = document.getElementById('preview-canvas-thumb');
    const empty = document.getElementById('preview-canvas-empty');
    if (!thumb || !dataUrl) return;
    thumb.src = dataUrl;
    thumb.hidden = false;
    if (empty) empty.hidden = true;
  }

  function captureCanvasThumb() {
    return canvas.toDataURL({ format: 'png', multiplier: 0.25 });
  }

  function resizeCanvasWrapper() {
    const size = canvasSize();
    const wrapper = document.getElementById('canvas-wrapper');
    wrapper.style.width = size.width + 'px';
    wrapper.style.height = size.height + 'px';
    if (canvas) {
      canvas.setWidth(size.width);
      canvas.setHeight(size.height);
    }
    return size;
  }

  function applyFormato(novoFormato) {
    if (!novoFormato || novoFormato === formato || !EDITOR_DATA.formatos[novoFormato]) {
      return;
    }

    const oldW = canvas.width;
    const oldH = canvas.height;
    const scaleX = (EDITOR_DATA.formatos[novoFormato].width * calcDisplayScale(EDITOR_DATA.formatos[novoFormato])) / oldW;
    const scaleY = (EDITOR_DATA.formatos[novoFormato].height * calcDisplayScale(EDITOR_DATA.formatos[novoFormato])) / oldH;

    formato = novoFormato;
    dims = EDITOR_DATA.formatos[formato];
    DISPLAY_SCALE = calcDisplayScale(dims);

    canvas.getObjects().forEach((obj) => {
      if (obj.name === 'fundo-editor') {
        obj.set({
          width: dims.width * DISPLAY_SCALE,
          height: dims.height * DISPLAY_SCALE,
          left: 0,
          top: 0,
        });
        return;
      }

      if (obj.name === 'palco') {
        const imgW = obj.width || 1;
        const imgH = obj.height || 1;
        obj.set({
          left: 0,
          top: 0,
          scaleX: (dims.width * DISPLAY_SCALE) / imgW,
          scaleY: (dims.height * DISPLAY_SCALE) / imgH,
        });
        return;
      }

      obj.set({
        left: (obj.left || 0) * scaleX,
        top: (obj.top || 0) * scaleY,
        scaleX: (obj.scaleX || 1) * scaleX,
        scaleY: (obj.scaleY || 1) * scaleY,
      });

      if (isTextObject(obj)) {
        obj.set('fontSize', (obj.fontSize || 16) * scaleY);
      }

      obj.setCoords();
    });

    resizeCanvasWrapper();
    enforceBackgroundOrder();
    canvas.renderAll();
    refreshLayersList();

    const fundos = EDITOR_DATA.config?.fundos || {};
    const palcoPath = fundos[formato] || '';
    const statusEl = document.getElementById('fundo-status');
    if (statusEl) {
      statusEl.textContent = palcoPath ? `Palco: ${palcoPath}` : 'Nenhum palco para este formato.';
    }
  }

  function insertAssetIcon(asset) {
    fabric.loadSVGFromString(asset.svg, (objects, options) => {
      let obj = fabric.util.groupSVGElements(objects, options);
      if (!obj) return;

      const targetSize = Math.min(dims.width, dims.height) * DISPLAY_SCALE * 0.12;
      const objW = obj.width || 100;
      const objH = obj.height || 100;
      const scale = targetSize / Math.max(objW, objH);

      obj.set({
        name: 'asset_' + asset.id,
        left: canvas.width / 2 - (objW * scale) / 2,
        top: canvas.height / 2 - (objH * scale) / 2,
        scaleX: scale,
        scaleY: scale,
        originX: 'left',
        originY: 'top',
      });

      canvas.add(obj);
      canvas.setActiveObject(obj);
      canvas.renderAll();
      openPropsPanel(obj);
      refreshLayersList();
      showEditorAlert(`"${asset.label}" adicionado ao canvas.`, 'success');
    });
  }

  function renderAssetLibrary() {
    const container = document.getElementById('asset-library');
    if (!container) return;

    container.innerHTML = '';
    const categories = [...new Set(ASSET_LIBRARY.map((a) => a.category))];

    categories.forEach((cat) => {
      const section = document.createElement('div');
      section.className = 'asset-category';

      const title = document.createElement('h5');
      title.className = 'asset-category-title';
      title.textContent = CATEGORY_LABELS[cat] || cat;
      section.appendChild(title);

      const grid = document.createElement('div');
      grid.className = 'asset-grid';

      ASSET_LIBRARY.filter((a) => a.category === cat).forEach((asset) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'asset-item';
        btn.title = asset.label;
        btn.innerHTML = asset.svg + `<span>${asset.label}</span>`;
        btn.addEventListener('click', () => insertAssetIcon(asset));
        grid.appendChild(btn);
      });

      section.appendChild(grid);
      container.appendChild(section);
    });
  }

  function initCanvas() {
    const size = resizeCanvasWrapper();

    canvas = new fabric.Canvas('editor-canvas', {
      width: size.width,
      height: size.height,
      preserveObjectStacking: true,
      selection: true,
      backgroundColor: '#ffffff',
    });

    canvas.on('selection:created', (e) => {
      refreshLayersList();
      openPropsPanel(getActiveTarget() || e.selected?.[0]);
    });

    canvas.on('selection:updated', (e) => {
      refreshLayersList();
      openPropsPanel(getActiveTarget() || e.selected?.[0]);
    });

    canvas.on('selection:cleared', () => {
      refreshLayersList();
      closePropsPanel();
    });

    canvas.on('object:added', refreshLayersList);
    canvas.on('object:removed', refreshLayersList);
    canvas.on('object:modified', () => {
      refreshLayersList();
      if (propsObj && getActiveTarget() === propsObj) {
        if (isTextObject(propsObj) || isDynamicText(propsObj)) populateTextProps(propsObj);
        if (isDynamicText(propsObj)) populateDynamicTextProps(propsObj);
        if (isProductZone(propsObj)) populateZoneProps(propsObj);
        if (isImageObject(propsObj)) populateImageProps(propsObj);
        if (isVectorObject(propsObj)) populateVectorProps(propsObj);
      }
    });
  }

  function isFabricOnlyModel() {
    return (EDITOR_DATA.modelo?.arquivo_template || '') === 'modelo_fabric.php';
  }

  function initBaseCanvasObjects() {
    const config = EDITOR_DATA.config || {};
    const cores = config.cores || {};
    const fundos = config.fundos || {};

    fundoRect = new fabric.Rect({
      name: 'fundo-editor',
      left: 0,
      top: 0,
      width: dims.width * DISPLAY_SCALE,
      height: dims.height * DISPLAY_SCALE,
      fill: cores.fundo || '#ffffff',
      selectable: false,
      evented: false,
    });
    canvas.add(fundoRect);
    canvas.sendToBack(fundoRect);

    const palcoPath = fundos[formato] || fundos['9x16'] || '';
    if (palcoPath) {
      fabric.Image.fromURL(
        assetUrl(palcoPath),
        (img) => {
          if (!img) return;
          img.set({
            name: 'palco',
            left: 0,
            top: 0,
            scaleX: (dims.width * DISPLAY_SCALE) / (img.width || 1),
            scaleY: (dims.height * DISPLAY_SCALE) / (img.height || 1),
            selectable: true,
            evented: true,
          });
          palcoObject = img;
          canvas.add(img);
          enforceBackgroundOrder();
          canvas.renderAll();
          refreshLayersList();
        },
        { crossOrigin: 'anonymous' }
      );
    }

    canvas.renderAll();
    refreshLayersList();
  }

  function initDefaultObjects() {
    const config = EDITOR_DATA.config || {};
    const cores = config.cores || {};
    const textos = config.textos || {};

    initBaseCanvasObjects();

    if (isFabricOnlyModel()) {
      return;
    }

    const textDefaults = [
      {
        name: 'titulo_linha1',
        text: textos.titulo_linha1 || 'PROMOCAO',
        left: dims.width * DISPLAY_SCALE * 0.08,
        top: dims.height * DISPLAY_SCALE * 0.04,
        fontSize: 72 * DISPLAY_SCALE,
        fill: '#ffffff',
        fontWeight: '900',
        fontFamily: 'Bebas Neue, Oswald, Impact, sans-serif',
      },
      {
        name: 'titulo_linha2',
        text: textos.titulo_linha2 || 'FECHA MES',
        left: dims.width * DISPLAY_SCALE * 0.08,
        top: dims.height * DISPLAY_SCALE * 0.09,
        fontSize: 96 * DISPLAY_SCALE,
        fill: cores.primary || '#dc2626',
        fontWeight: '900',
        fontFamily: 'Bebas Neue, Oswald, Impact, sans-serif',
      },
      {
        name: 'footer_endereco',
        text: textos.footer_endereco || 'Av. Brasil, Centro',
        left: dims.width * DISPLAY_SCALE * 0.05,
        top: dims.height * DISPLAY_SCALE * 0.92,
        fontSize: 28 * DISPLAY_SCALE,
        fill: '#ffffff',
        fontWeight: '900',
      },
      {
        name: 'footer_whatsapp',
        text: textos.footer_whatsapp || '(54) 9 9999-9999',
        left: dims.width * DISPLAY_SCALE * 0.55,
        top: dims.height * DISPLAY_SCALE * 0.92,
        fontSize: 28 * DISPLAY_SCALE,
        fill: '#ffffff',
        fontWeight: '900',
        textAlign: 'right',
      },
    ];

    textDefaults.forEach((cfg) => {
      canvas.add(
        new fabric.IText(cfg.text, {
          name: cfg.name,
          left: cfg.left,
          top: cfg.top,
          fontSize: cfg.fontSize,
          fill: cfg.fill,
          fontWeight: cfg.fontWeight || '700',
          fontFamily: cfg.fontFamily || 'Segoe UI, Arial, sans-serif',
          textAlign: cfg.textAlign || 'left',
        })
      );
    });

    canvas.renderAll();
    refreshLayersList();
  }

  function loadFabricState() {
    const saved = EDITOR_DATA.config?.fabric_state;
    if (saved?.objects?.length) {
      const scaledState = reescalarState(saved, DISPLAY_SCALE);
      canvas.loadFromJSON(scaledState, () => {
        canvas.getObjects().forEach((obj) => {
          if (obj.name === 'palco') palcoObject = obj;
          if (obj.name === 'fundo-editor') fundoRect = obj;
        });
        syncZoneCounterFromCanvas();
        enforceBackgroundOrder();
        canvas.renderAll();
        refreshLayersList();
        updateCanvasThumb(captureCanvasThumb());
      });
    } else if (isFabricOnlyModel()) {
      initBaseCanvasObjects();
    } else {
      initDefaultObjects();
    }
  }

  function coletarCores() {
    const cores = {};
    document.querySelectorAll('input[type="color"][data-cor]').forEach((el) => {
      cores[el.dataset.cor] = el.value;
    });
    return cores;
  }

  function coletarTextos() {
    const textos = { ...(EDITOR_DATA.config?.textos || {}) };
    document.querySelectorAll('[data-texto]').forEach((el) => {
      textos[el.dataset.texto] = el.value.trim();
    });
    return textos;
  }

  function coletarIcones() {
    return EDITOR_DATA.config?.icones || { auto: true, tipo: 'emoji', mapa: {} };
  }

  function syncCorFundo() {
    const cor = document.getElementById('cor-fundo')?.value;
    if (!cor) return;
    const rect = findObjectByName('fundo-editor');
    if (rect) {
      rect.set('fill', cor);
      canvas.renderAll();
    }
  }

  function bindColorPickers() {
    document.querySelectorAll('input[type="color"][data-cor]').forEach((picker) => {
      const chave = picker.dataset.cor;
      const hexInput = document.querySelector(`[data-cor-hex="${chave}"]`);
      if (!hexInput) return;

      picker.addEventListener('input', () => {
        hexInput.value = picker.value;
        if (chave === 'fundo') syncCorFundo();
      });

      hexInput.addEventListener('input', () => {
        let val = hexInput.value.trim();
        if (!val.startsWith('#')) val = '#' + val;
        if (/^#[0-9a-fA-F]{6}$/.test(val)) {
          picker.value = val.toLowerCase();
          if (chave === 'fundo') syncCorFundo();
        }
      });
    });
  }

  function bindTextInputs() {
    document.querySelectorAll('[data-texto]').forEach((input) => {
      input.addEventListener('input', () => {
        const name = input.dataset.texto;
        const obj = findObjectByName(name);
        if (obj && isTextObject(obj)) {
          obj.set('text', input.value);
          canvas.renderAll();
        }
      });
    });
  }

  function bindTabs() {
    const tabs = document.querySelectorAll('.editor-tab');
    const panels = {
      upload: document.getElementById('tab-upload'),
      biblioteca: document.getElementById('tab-biblioteca'),
      fundo: document.getElementById('tab-fundo'),
      textos: document.getElementById('tab-textos'),
      produtos: document.getElementById('tab-produtos'),
      camadas: document.getElementById('tab-camadas'),
    };

    tabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        const target = tab.dataset.tab;
        tabs.forEach((t) => {
          t.classList.toggle('is-active', t === tab);
          t.setAttribute('aria-selected', t === tab ? 'true' : 'false');
        });
        Object.entries(panels).forEach(([key, panel]) => {
          if (!panel) return;
          const active = key === target;
          panel.classList.toggle('hidden', !active);
          panel.hidden = !active;
        });
        if (target === 'camadas') refreshLayersList();
      });
    });
  }

  function refreshLayersList() {
    const list = document.getElementById('lista-camadas');
    if (!list || !canvas) return;

    list.innerHTML = '';
    const objects = canvas.getObjects().filter((obj) => obj.name !== 'fundo-editor');
    const active = getActiveTarget();

    [...objects].reverse().forEach((obj, index) => {
      const li = document.createElement('li');
      let label = obj.name || obj.type || `Objeto ${index + 1}`;
      if (isProductZone(obj)) {
        label = `Zona de Produto ${obj.zoneId || ''}`.trim();
      } else if (isDynamicText(obj)) {
        label = obj.text || `[${obj.textType || 'var'}]`;
      }
      li.className = 'editor-layer-item' + (active === obj ? ' is-active' : '');
      li.textContent = label;
      li.addEventListener('click', () => {
        canvas.setActiveObject(obj);
        canvas.renderAll();
        openPropsPanel(obj);
        refreshLayersList();
      });
      list.appendChild(li);
    });
  }

  function bindLayerControls() {
    document.getElementById('btn-trazer-frente')?.addEventListener('click', bringForwardActive);
    document.getElementById('btn-enviar-fundo')?.addEventListener('click', sendBackwardActive);
    document.getElementById('btn-remover-camada')?.addEventListener('click', removeActiveObject);
    document.getElementById('btn-atualizar-camadas')?.addEventListener('click', refreshLayersList);
  }

  function bindUploadElemento() {
    const input = document.getElementById('input-upload-elemento');
    if (!input) return;

    input.addEventListener('change', async () => {
      const file = input.files?.[0];
      if (!file) return;

      const fd = new FormData();
      fd.append('elemento', file);
      fd.append('modelo_id', String(EDITOR_DATA.modelo.id));

      showLoader('Removendo fundo...');
      try {
        const url = new URL('api/index.php', window.location.href);
        url.searchParams.set('recurso', 'modelo');
        url.searchParams.set('acao', 'upload_elemento');
        const resp = await fetch(url, { method: 'POST', body: fd });
        const json = await resp.json();
        if (!json.success) throw new Error(json.error || 'Erro no upload.');

        fabric.Image.fromURL(
          assetUrl(json.data.url),
          (img) => {
            if (!img) {
              showEditorAlert('Falha ao carregar imagem no canvas.');
              return;
            }
            const maxW = dims.width * DISPLAY_SCALE * 0.35;
            const scale = maxW / (img.width || 1);
            img.set({
              name: 'elemento_' + Date.now(),
              left: dims.width * DISPLAY_SCALE * 0.3,
              top: dims.height * DISPLAY_SCALE * 0.3,
              scaleX: scale,
              scaleY: scale,
              shadow: new fabric.Shadow({
                color: 'rgba(0,0,0,0.8)',
                blur: 15,
                offsetX: -15,
                offsetY: 20,
              }),
            });
            canvas.add(img);
            canvas.setActiveObject(img);
            canvas.renderAll();
            openPropsPanel(img);
            refreshLayersList();
            showEditorAlert(
              json.data.status === 'ok' ? 'Elemento adicionado (fundo removido).' : 'Elemento adicionado (rembg indisponivel).',
              json.data.status === 'ok' ? 'success' : 'error'
            );
          },
          { crossOrigin: 'anonymous' }
        );
      } catch (err) {
        showEditorAlert(err.message);
      } finally {
        hideLoader();
        input.value = '';
      }
    });
  }

  function bindUploadFundo() {
    const input = document.getElementById('input-upload-fundo');
    const statusEl = document.getElementById('fundo-status');
    if (!input) return;

    input.addEventListener('change', async () => {
      const file = input.files?.[0];
      if (!file) return;

      const fd = new FormData();
      fd.append('id', String(EDITOR_DATA.modelo.id));
      fd.append('formato', formato);
      fd.append('fundo', file);

      showLoader('Enviando palco...');
      try {
        const url = new URL('api/index.php', window.location.href);
        url.searchParams.set('recurso', 'modelo');
        url.searchParams.set('acao', 'upload_fundo');
        const resp = await fetch(url, { method: 'POST', body: fd });
        const json = await resp.json();
        if (!json.success) throw new Error(json.error || 'Erro ao enviar palco.');

        const fundos = json.data?.modelo?.config_visual?.fundos || {};
        const palcoPath = fundos[formato] || '';
        if (statusEl) statusEl.textContent = palcoPath ? `Palco: ${palcoPath}` : 'Palco salvo.';

        if (palcoPath) {
          EDITOR_DATA.config.fundos = fundos;
          if (palcoObject) {
            canvas.remove(palcoObject);
            palcoObject = null;
          }
          fabric.Image.fromURL(
            assetUrl(palcoPath + '?v=' + Date.now()),
            (img) => {
              if (!img) return;
              img.set({
                name: 'palco',
                left: 0,
                top: 0,
                scaleX: (dims.width * DISPLAY_SCALE) / (img.width || 1),
                scaleY: (dims.height * DISPLAY_SCALE) / (img.height || 1),
              });
              palcoObject = img;
              canvas.add(img);
              enforceBackgroundOrder();
              canvas.renderAll();
              refreshLayersList();
            },
            { crossOrigin: 'anonymous' }
          );
        }

        showEditorAlert('Palco atualizado.', 'success');
      } catch (err) {
        showEditorAlert(err.message);
      } finally {
        hideLoader();
        input.value = '';
      }
    });
  }

  function bindSalvar() {
    document.getElementById('btn-salvar')?.addEventListener('click', async () => {
      const btn = document.getElementById('btn-salvar');
      btn.disabled = true;

      try {
        const previewDataUrl = captureCanvasThumb();
        const fabricState = canvas.toObject(CUSTOM_PROPS.concat(['tipo', 'visible']));
        const stateReal = reescalarState(fabricState, 1 / DISPLAY_SCALE);
        stateReal.version = fabric.version || '5.3.0';

        const payload = {
          id: EDITOR_DATA.modelo.id,
          nome_exibicao: document.getElementById('input-nome')?.value.trim() || EDITOR_DATA.modelo.nome_exibicao,
          formatos_suportados: EDITOR_DATA.modelo.formatos_suportados,
          max_itens_default: EDITOR_DATA.modelo.max_itens_default,
          thumbnail: previewDataUrl,
          config_visual: {
            cores: coletarCores(),
            textos: coletarTextos(),
            icones: coletarIcones(),
            fundos: EDITOR_DATA.config?.fundos || {},
            fabric_state: stateReal,
          },
        };

        await apiCall('modelo', 'salvar', { method: 'POST', body: payload });
        EDITOR_DATA.config = payload.config_visual;
        updateCanvasThumb(previewDataUrl);
        showEditorAlert('Modelo salvo.', 'success');
      } catch (err) {
        showEditorAlert(err.message);
      } finally {
        btn.disabled = false;
      }
    });
  }

  function bindExportarPng() {
    document.getElementById('btn-exportar-png')?.addEventListener('click', async () => {
      const btn = document.getElementById('btn-exportar-png');
      btn.disabled = true;
      showLoader('Gerando PNG...');

      try {
        await apiCall('modelo', 'gerar_preview', {
          method: 'POST',
          body: { id: EDITOR_DATA.modelo.id },
        });

        updateCanvasThumb(captureCanvasThumb());
        showEditorAlert('Preview PNG gerado via Puppeteer.', 'success');
      } catch (err) {
        showEditorAlert(err.message);
      } finally {
        hideLoader();
        btn.disabled = false;
      }
    });
  }

  function bindFormatoSelect() {
    const select = document.getElementById('select-formato');
    if (!select) return;

    select.addEventListener('change', () => {
      const novo = select.value;
      applyFormato(novo);
      showEditorAlert(`Formato alterado para ${EDITOR_DATA.formatos[novo]?.label || novo}.`, 'success');
    });
  }

  function init() {
    initCanvas();
    loadFabricState();
    renderAssetLibrary();
    bindTabs();
    bindColorPickers();
    bindTextInputs();
    bindLayerControls();
    bindPropsPanel();
    bindKeyboardDelete();
    bindUploadElemento();
    bindUploadFundo();
    bindSalvar();
    bindExportarPng();
    bindFormatoSelect();
    bindProdutosTab();
    closePropsPanel();

    const fundos = EDITOR_DATA.config?.fundos || {};
    const palcoPath = fundos[formato] || '';
    const statusEl = document.getElementById('fundo-status');
    if (statusEl && palcoPath) {
      statusEl.textContent = `Palco: ${palcoPath}`;
    }
  }

  document.addEventListener('DOMContentLoaded', init);
})();
