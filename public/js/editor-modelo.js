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
  const propsCard = document.getElementById('props-card');
  const propsImagem = document.getElementById('props-imagem');
  const propsPalco = document.getElementById('props-palco');
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
  let palcoEditMode = false;
  let palcoFitMode = 'contain';
  let contextMenuTarget = null;
  let editorDirty = false;
  let editorReady = false;

  const CUSTOM_PROPS = ['name', 'isProductZone', 'zoneId', 'isProductCard', 'productIndex', 'cardPart', 'isDynamicText', 'textType', 'linkedZone', 'isLocked'];

  const PROMO_TEXT_DEFAULTS = {
    titulo_linha1: {
      fallback: 'PROMOCAO',
      leftRatio: 0.08,
      topRatio: 0.04,
      fontSize: 72,
      fill: '#ffffff',
      fontWeight: '900',
      fontFamily: 'Bebas Neue, Oswald, Impact, sans-serif',
    },
    titulo_linha2: {
      fallback: 'FECHA MES',
      leftRatio: 0.08,
      topRatio: 0.09,
      fontSize: 96,
      fill: null,
      fontWeight: '900',
      fontFamily: 'Bebas Neue, Oswald, Impact, sans-serif',
    },
    badge_oferta: {
      fallback: 'Oferta',
      leftRatio: 0.05,
      topRatio: 0.15,
      fontSize: 36,
      fill: '#ffffff',
      fontWeight: '700',
      fontFamily: 'Bebas Neue, Oswald, Impact, sans-serif',
    },
    texto_legal: {
      fallback: 'Ofertas validas enquanto durarem os estoques.',
      leftRatio: 0.02,
      topRatio: 0.95,
      fontSize: 14,
      fill: '#ffffff',
      fontWeight: '400',
      fontFamily: 'Segoe UI, Arial, sans-serif',
    },
  };

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

  function markEditorDirty() {
    if (!editorReady) return;
    editorDirty = true;
  }

  function resetEditorDirty() {
    editorDirty = false;
  }

  function confirmLeaveEditor() {
    if (!editorDirty) return true;
    return window.confirm('Voce tem alteracoes nao salvas. Deseja sair sem salvar?');
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
    return obj?.isProductZone === true || obj?.isProductCard === true;
  }

  function isProductCard(obj) {
    return obj?.isProductCard === true;
  }

  function getProductCardRoot(obj) {
    if (!obj) return null;
    if (isProductCard(obj)) return obj;
    if (obj.group && isProductCard(obj.group)) return obj.group;
    return null;
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
    return obj && ['path', 'group'].includes(obj.type) && !isProductZone(obj) && !isProductCard(obj);
  }

  function isPalcoObject(obj) {
    return obj?.name === 'palco' && obj?.type === 'image';
  }

  function isObjectLocked(obj) {
    return obj?.isLocked === true;
  }

  function isContextMenuTarget(obj) {
    return isEditableObject(obj);
  }

  function setObjectLocked(obj, locked) {
    if (!obj || isProtectedObject(obj)) return;

    const opts = {
      isLocked: locked,
      lockMovementX: locked,
      lockMovementY: locked,
      lockScalingX: locked,
      lockScalingY: locked,
      lockRotation: locked,
      hasControls: !locked,
      borderColor: locked ? '#64748b' : '#b91c1c',
      cornerColor: locked ? '#64748b' : '#ffffff',
    };

    if (isTextObject(obj) || isDynamicText(obj)) {
      opts.editable = !locked;
    }

    obj.set(opts);
    obj.setCoords();
    canvas?.renderAll();
    refreshLayersList();
    if (propsObj === obj) updateLockButtonState(obj);
  }

  function updateLockButtonState(obj) {
    const btn = document.getElementById('prop-bloquear');
    if (!btn || !obj) return;
    const locked = isObjectLocked(obj);
    btn.textContent = locked ? 'Desbloquear elemento' : 'Bloquear elemento';
    btn.classList.toggle('is-active', locked);
  }

  function setPropsPanelDisabled(disabled) {
    if (!painelProps) return;
    painelProps.querySelectorAll('.editor-props-body input, .editor-props-body button').forEach((el) => {
      if (el.id === 'prop-bloquear' || el.id === 'btn-fechar-props') return;
      el.disabled = disabled;
    });
  }

  function applyLockStatesFromCanvas() {
    canvas?.getObjects().forEach((obj) => {
      if (obj.isLocked) setObjectLocked(obj, true);
    });
  }

  function generateCopyName(name) {
    const base = String(name || 'elemento').replace(/(_copy[_a-z0-9]*)+$/i, '');
    return `${base}_copy_${Date.now().toString(36).slice(-4)}`;
  }

  function duplicateObject(obj) {
    if (!obj || isProtectedObject(obj)) {
      showEditorAlert('Este elemento nao pode ser duplicado.');
      return;
    }
    if (isObjectLocked(obj)) {
      showEditorAlert('Desbloqueie o elemento antes de duplicar.');
      return;
    }

    obj.clone((cloned) => {
      if (!cloned) return;

      cloned.set({
        left: (obj.left || 0) + 24,
        top: (obj.top || 0) + 24,
        name: generateCopyName(obj.name),
        isLocked: false,
        lockMovementX: false,
        lockMovementY: false,
        lockScalingX: false,
        lockScalingY: false,
        lockRotation: false,
        hasControls: true,
        editable: isTextObject(cloned) || isDynamicText(cloned),
      });

      if (isProductCard(cloned)) {
        syncZoneCounterFromCanvas();
        zoneCounter += 1;
        updateCardProductIndex(cloned, zoneCounter);
        cloned.set({
          left: (obj.left || 0) + 48,
          top: (obj.top || 0) + 48,
        });
      } else if (isProductZone(cloned) && !isProductCard(cloned)) {
        syncZoneCounterFromCanvas();
        zoneCounter += 1;
        cloned.set({
          zoneId: zoneCounter,
          name: `zona_produto_${zoneCounter}`,
        });
      }

      canvas.add(cloned);
      enforceBackgroundOrder();
      canvas.setActiveObject(cloned);
      canvas.renderAll();
      openPropsPanel(cloned);
      refreshLayersList();
      showEditorAlert('Elemento duplicado.', 'success');
    }, CUSTOM_PROPS);
  }

  function getPromoTextValue(textKey) {
    const input = document.querySelector(`[data-texto="${textKey}"]`);
    if (input && input.value.trim() !== '') return input.value.trim();
    const configVal = EDITOR_DATA.config?.textos?.[textKey];
    if (configVal) return String(configVal);
    return PROMO_TEXT_DEFAULTS[textKey]?.fallback || '';
  }

  function createPromoTextObject(textKey) {
    const cfg = PROMO_TEXT_DEFAULTS[textKey];
    if (!cfg) return null;

    const cores = EDITOR_DATA.config?.cores || {};
    const fill = cfg.fill || cores.primary || '#dc2626';

    return new fabric.IText(getPromoTextValue(textKey), {
      name: textKey,
      left: dims.width * DISPLAY_SCALE * cfg.leftRatio,
      top: dims.height * DISPLAY_SCALE * cfg.topRatio,
      fontSize: cfg.fontSize * DISPLAY_SCALE,
      fill,
      fontWeight: cfg.fontWeight || '700',
      fontFamily: cfg.fontFamily || 'Segoe UI, Arial, sans-serif',
    });
  }

  function focusPromoText(textKey) {
    if (!PROMO_TEXT_DEFAULTS[textKey]) return;

    let obj = findObjectByName(textKey);
    if (!obj) {
      obj = createPromoTextObject(textKey);
      if (!obj) return;
      canvas.add(obj);
      enforceBackgroundOrder();
    } else if (isTextObject(obj)) {
      obj.set('text', getPromoTextValue(textKey));
    }

    canvas.setActiveObject(obj);
    canvas.renderAll();
    openPropsPanel(obj);
    refreshLayersList();
    showEditorAlert('Arraste o texto no canvas para posicionar.', 'success');
  }

  function hideContextMenu() {
    const menu = document.getElementById('editor-context-menu');
    if (!menu) return;
    menu.hidden = true;
    menu.classList.add('hidden');
    contextMenuTarget = null;
  }

  function showContextMenu(clientX, clientY, obj) {
    const menu = document.getElementById('editor-context-menu');
    if (!menu || !obj) return;

    contextMenuTarget = obj;
    canvas.setActiveObject(obj);
    canvas.renderAll();
    openPropsPanel(obj);

    const lockItem = menu.querySelector('[data-action="lock"]');
    if (lockItem) {
      lockItem.textContent = isObjectLocked(obj) ? 'Desbloquear' : 'Bloquear';
    }

    menu.style.left = `${clientX}px`;
    menu.style.top = `${clientY}px`;
    menu.hidden = false;
    menu.classList.remove('hidden');
  }

  function handleContextMenuAction(action) {
    const obj = contextMenuTarget || getActiveTarget();
    hideContextMenu();
    if (!obj || isProtectedObject(obj)) return;

    switch (action) {
      case 'duplicate':
        if (resolveProductBlockIndex(obj) || isProductCard(obj) || getProductCardRoot(obj)) {
          duplicarBlocoProduto();
        } else {
          duplicateObject(obj);
        }
        break;
      case 'delete':
        canvas.setActiveObject(obj);
        removeActiveObject();
        break;
      case 'lock':
        setObjectLocked(obj, !isObjectLocked(obj));
        openPropsPanel(obj);
        break;
      case 'forward':
        canvas.setActiveObject(obj);
        bringForwardActive();
        break;
      case 'backward':
        canvas.setActiveObject(obj);
        sendBackwardActive();
        break;
      case 'front':
        canvas.setActiveObject(obj);
        bringToFrontActive();
        break;
      default:
        break;
    }
  }

  function isProtectedObject(obj) {
    return obj && (obj.name === 'fundo-editor' || obj.name === 'palco');
  }

  function isCardPartObject(obj) {
    return !!(obj?.cardPart && getProductCardRoot(obj));
  }

  function isEditableObject(obj) {
    if (!obj || isProtectedObject(obj)) return false;
    return (
      isTextObject(obj) ||
      isImageObject(obj) ||
      isVectorObject(obj) ||
      isProductZone(obj) ||
      isProductCard(obj) ||
      isDynamicText(obj) ||
      isCardPartObject(obj)
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

    if (copy.type === 'image') {
      ['left', 'top', 'scaleX', 'scaleY'].forEach((key) => {
        if (typeof copy[key] === 'number') {
          copy[key] *= factor;
        }
      });
    } else {
      const numericKeys = ['left', 'top', 'width', 'height', 'fontSize', 'rx', 'ry', 'radius', 'strokeWidth'];
      numericKeys.forEach((key) => {
        if (typeof copy[key] === 'number') {
          copy[key] *= factor;
        }
      });
    }

    if (Array.isArray(copy.objects)) {
      copy.objects = copy.objects.map((child) => reescalarObject(child, factor));
    }

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

  function fitFundoRectToCanvas() {
    const rect = findObjectByName('fundo-editor');
    if (!rect) return;
    rect.set({
      left: 0,
      top: 0,
      width: canvas.width,
      height: canvas.height,
      scaleX: 1,
      scaleY: 1,
    });
    rect.setCoords();
    fundoRect = rect;
  }

  function palcoIntrinsicSize(obj) {
    const el = obj._originalElement || (typeof obj.getElement === 'function' ? obj.getElement() : null);
    if (el && el.naturalWidth > 0 && el.naturalHeight > 0) {
      return { w: el.naturalWidth, h: el.naturalHeight };
    }
    const w = Number(obj.width) || 0;
    const h = Number(obj.height) || 0;
    if (w > 0 && h > 0) {
      return { w, h };
    }
    return null;
  }

  function ensurePalcoClipPath(obj) {
    if (!obj || !canvas) return;
    obj.clipPath = new fabric.Rect({
      left: 0,
      top: 0,
      width: canvas.width,
      height: canvas.height,
      absolutePositioned: true,
    });
  }

  function updatePalcoClipPaths() {
    if (!canvas) return;
    canvas.getObjects().forEach((obj) => {
      if (obj.name === 'palco') {
        ensurePalcoClipPath(obj);
      }
    });
  }

  function applyPalcoFit(obj, size, mode) {
    if (!obj || !size || !canvas) return;

    const targetW = canvas.width;
    const targetH = canvas.height;
    const scaleContain = Math.min(targetW / size.w, targetH / size.h);
    const scaleCover = Math.max(targetW / size.w, targetH / size.h);
    const scale = mode === 'cover' ? scaleCover : scaleContain;
    const displayW = size.w * scale;
    const displayH = size.h * scale;

    obj.set({
      left: (targetW - displayW) / 2,
      top: (targetH - displayH) / 2,
      originX: 'left',
      originY: 'top',
      scaleX: scale,
      scaleY: scale,
      angle: 0,
      lockRotation: true,
      uniformScaling: true,
    });
    obj.setCoords();
    ensurePalcoClipPath(obj);
    palcoObject = obj;
    palcoFitMode = mode;
    markEditorDirty();
  }

  /** Garante escala uniforme do palco — evita recorte por width/height inconsistentes. */
  function fitPalcoToCanvas(mode) {
    if (!canvas) return;
    const fitMode = mode || palcoFitMode || 'contain';

    canvas.getObjects().forEach((obj) => {
      if (obj.name !== 'palco' || obj.type !== 'image') return;

      const size = palcoIntrinsicSize(obj);
      if (!size) {
        if (typeof obj.once === 'function') {
          obj.once('loaded', () => fitPalcoToCanvas(fitMode));
        }
        return;
      }

      applyPalcoFit(obj, size, fitMode);
    });
  }

  function isPalcoTransformHealthy(obj) {
    const sx = obj.scaleX || 1;
    const sy = obj.scaleY || 1;
    if (Math.abs(sx - sy) > 0.05) return false;

    const size = palcoIntrinsicSize(obj);
    if (!size) return false;

    const dw = size.w * sx;
    const dh = size.h * sy;
    if (dw < canvas.width * 0.2 || dh < canvas.height * 0.2) return false;
    if (dw > canvas.width * 10 || dh > canvas.height * 10) return false;

    return true;
  }

  function extractPalcoTransformFromState(state) {
    const palco = state?.objects?.find((o) => o.name === 'palco');
    if (!palco) return null;

    return {
      left: palco.left,
      top: palco.top,
      scaleX: palco.scaleX,
      scaleY: palco.scaleY,
    };
  }

  function getPalcoUniformScale(obj) {
    return ((obj.scaleX || 1) + (obj.scaleY || 1)) / 2;
  }

  function setPalcoUniformScale(obj, scale) {
    const size = palcoIntrinsicSize(obj);
    if (!size || !canvas) return;

    const displayW = size.w * scale;
    const displayH = size.h * scale;
    obj.set({
      scaleX: scale,
      scaleY: scale,
      left: canvas.width / 2 - displayW / 2,
      top: canvas.height / 2 - displayH / 2,
    });
    obj.setCoords();
    canvas.renderAll();
    markEditorDirty();
  }

  function setPalcoEditHint(visible) {
    const hint = document.getElementById('palco-edit-hint');
    if (!hint) return;
    hint.hidden = !visible;
    hint.classList.toggle('hidden', !visible);
  }

  function enterPalcoEditMode() {
    const obj = findObjectByName('palco');
    if (!obj || !canvas) return;

    palcoEditMode = true;
    ensurePalcoClipPath(obj);
    obj.set({
      selectable: true,
      evented: true,
      lockRotation: true,
      uniformScaling: true,
      hasControls: true,
      hasBorders: true,
    });
    canvas.setActiveObject(obj);
    canvas.renderAll();
    openPropsPanel(obj);
    setPalcoEditHint(true);
    showEditorAlert(
      'Modo ajuste: arraste para mover · cantos para zoom · Esc ou duplo clique para sair',
      'success'
    );
  }

  function exitPalcoEditMode() {
    if (!palcoEditMode) return;
    palcoEditMode = false;
    setPalcoEditHint(false);
    canvas?.discardActiveObject();
    canvas?.renderAll();
  }

  function stripPalcoFromState(state) {
    const copy = JSON.parse(JSON.stringify(state));
    if (Array.isArray(copy.objects)) {
      copy.objects = copy.objects.filter((obj) => obj.name !== 'palco');
    }
    return copy;
  }

  function loadPalcoFromConfig(savedTransformReal, done, defaultFitMode) {
    const fundos = EDITOR_DATA.config?.fundos || {};
    const palcoPath = fundos[formato] || fundos['9x16'] || '';
    if (!palcoPath) {
      if (typeof done === 'function') done();
      return;
    }

    const existing = findObjectByName('palco');
    if (existing) {
      canvas.remove(existing);
      if (palcoObject === existing) palcoObject = null;
    }

    fabric.Image.fromURL(
      assetUrl(palcoPath) + '?v=' + Date.now(),
      (img) => {
        if (!img) {
          if (typeof done === 'function') done();
          return;
        }

        img.set({
          name: 'palco',
          left: 0,
          top: 0,
          originX: 'left',
          originY: 'top',
          selectable: true,
          evented: true,
          lockRotation: true,
          uniformScaling: true,
        });
        canvas.add(img);

        if (savedTransformReal) {
          const t = reescalarObject({ type: 'image', ...savedTransformReal }, DISPLAY_SCALE);
          img.set({
            left: t.left,
            top: t.top,
            scaleX: t.scaleX,
            scaleY: t.scaleY,
          });
        }

        const size = palcoIntrinsicSize(img) || { w: img.width || 1, h: img.height || 1 };
        let forcedRefit = false;

        if (savedTransformReal) {
          if (!isPalcoTransformHealthy(img)) {
            applyPalcoFit(img, size, defaultFitMode || 'contain');
            forcedRefit = true;
          } else {
            ensurePalcoClipPath(img);
            palcoObject = img;
          }
        } else {
          applyPalcoFit(img, size, defaultFitMode || 'contain');
          forcedRefit = true;
        }

        enforceBackgroundOrder();
        canvas.renderAll();
        refreshLayersList();

        const shouldAutoEdit = !savedTransformReal || forcedRefit;
        if (typeof done === 'function') done(shouldAutoEdit);
      },
      { crossOrigin: 'anonymous' }
    );
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
    propsCard?.classList.add('hidden');
    propsImagem?.classList.add('hidden');
    propsPalco?.classList.add('hidden');
    propsVetor?.classList.add('hidden');
    propsComum?.classList.add('hidden');
    propsEmpty?.classList.remove('hidden');
    setPropsPanelDisabled(false);
  }

  function populateZoneProps(obj) {
    syncingProps = true;
    const zoneIdEl = document.getElementById('prop-zone-id');
    if (zoneIdEl) zoneIdEl.value = String(obj.zoneId ?? '');
    syncingProps = false;
  }

  function populateCardProps(obj) {
    syncingProps = true;
    const cardIndexEl = document.getElementById('prop-card-index');
    if (cardIndexEl) cardIndexEl.value = String(obj.productIndex ?? obj.zoneId ?? 1);
    syncingProps = false;
  }

  function updateCardProductIndex(card, index) {
    if (!card) return;
    card.set({
      productIndex: index,
      zoneId: index,
      name: `card_produto_${index}`,
    });
    const children = card.getObjects ? card.getObjects() : [];
    children.forEach((child) => {
      if (child.isDynamicText) {
        child.set({ linkedZone: index });
      }
    });
    card.setCoords();
  }

  function countProductCardsOnCanvas() {
    let count = 0;
    canvas?.getObjects().forEach((obj) => {
      if (isProductCard(obj)) count += 1;
      else if (obj.isProductZone && !obj.isProductCard) count += 1;
    });
    return count;
  }

  function populateDynamicTextProps(obj) {
    syncingProps = true;
    const linkedEl = document.getElementById('prop-linked-zone');
    if (linkedEl) linkedEl.value = String(obj.linkedZone ?? 1);
    syncingProps = false;
  }

  function openPropsPanel(obj) {
    if (isPalcoObject(obj)) {
      propsObj = obj;
      painelProps?.classList.remove('hidden');
      propsEmpty?.classList.add('hidden');
      propsTexto?.classList.add('hidden');
      propsTextoDinamico?.classList.add('hidden');
      propsZona?.classList.add('hidden');
      propsCard?.classList.add('hidden');
      propsImagem?.classList.add('hidden');
      propsVetor?.classList.add('hidden');
      propsComum?.classList.add('hidden');
      propsPalco?.classList.remove('hidden');
      populatePalcoProps(obj);
      return;
    }

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
    propsCard?.classList.add('hidden');
    propsImagem?.classList.add('hidden');
    propsPalco?.classList.add('hidden');
    propsVetor?.classList.add('hidden');
    propsComum?.classList.add('hidden');

    const cardRoot = getProductCardRoot(obj);
    if (cardRoot && isProductCard(cardRoot) && obj !== cardRoot) {
      if (isDynamicText(obj) || isTextObject(obj)) {
        propsTexto?.classList.remove('hidden');
        populateTextProps(obj);
        updateLockButtonState(cardRoot);
        setPropsPanelDisabled(isObjectLocked(cardRoot));
        return;
      }
      propsCard?.classList.remove('hidden');
      propsComum?.classList.remove('hidden');
      populateCardProps(cardRoot);
      updateLockButtonState(cardRoot);
      setPropsPanelDisabled(isObjectLocked(cardRoot));
      return;
    }

    if (isProductCard(obj)) {
      propsCard?.classList.remove('hidden');
      propsComum?.classList.remove('hidden');
      populateCardProps(obj);
      updateLockButtonState(obj);
      setPropsPanelDisabled(isObjectLocked(obj));
      return;
    }

    if (isProductZone(obj) && !isProductCard(obj)) {
      propsZona?.classList.remove('hidden');
      propsComum?.classList.remove('hidden');
      populateZoneProps(obj);
      updateLockButtonState(obj);
      setPropsPanelDisabled(isObjectLocked(obj));
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

    updateLockButtonState(obj);
    setPropsPanelDisabled(isObjectLocked(obj));
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

  function populatePalcoProps(obj) {
    syncingProps = true;
    const size = palcoIntrinsicSize(obj);
    const containScale = size
      ? Math.min(canvas.width / size.w, canvas.height / size.h)
      : getPalcoUniformScale(obj);
    const currentScale = getPalcoUniformScale(obj);
    const zoomPct = Math.max(25, Math.min(300, Math.round((currentScale / containScale) * 100)));

    syncPalcoZoomControls(zoomPct);
    syncingProps = false;
  }

  function syncPalcoZoomControls(zoomPct) {
    ['prop-palco-zoom', 'fundo-palco-zoom'].forEach((id) => {
      const el = document.getElementById(id);
      if (el) el.value = String(zoomPct);
    });
    ['prop-palco-zoom-val', 'fundo-palco-zoom-val'].forEach((id) => {
      const el = document.getElementById(id);
      if (el) el.textContent = String(zoomPct);
    });
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
    if (isObjectLocked(obj)) {
      showEditorAlert('Elemento bloqueado. Desbloqueie antes de excluir.');
      return;
    }
    canvas.remove(obj);
    canvas.discardActiveObject();
    canvas.renderAll();
    syncZoneCounterFromCanvas();
    closePropsPanel();
    refreshLayersList();
  }

  function bindKeyboardDelete() {
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && palcoEditMode) {
        e.preventDefault();
        exitPalcoEditMode();
        return;
      }

      if (e.key !== 'Delete' && e.key !== 'Backspace') return;
      if (isTypingInForm() || isEditingCanvasText()) return;

      const obj = getActiveTarget();
      if (!obj || isProtectedObject(obj)) return;

      e.preventDefault();
      removeActiveObject();
    });
  }

  function bindPalcoProps() {
    const bindPalcoButtons = (ids, handler) => {
      ids.forEach((id) => {
        document.getElementById(id)?.addEventListener('click', handler);
      });
    };

    bindPalcoButtons(['prop-palco-edit', 'fundo-palco-edit'], () => {
      enterPalcoEditMode();
    });

    bindPalcoButtons(['prop-palco-fit-contain', 'fundo-palco-fit-contain'], () => {
      const obj = findObjectByName('palco');
      if (!obj) return;
      const size = palcoIntrinsicSize(obj);
      if (!size) return;
      applyPalcoFit(obj, size, 'contain');
      canvas.renderAll();
      populatePalcoProps(obj);
      showEditorAlert('Imagem inteira visivel no canvas.', 'success');
    });

    bindPalcoButtons(['prop-palco-fit-cover', 'fundo-palco-fit-cover'], () => {
      const obj = findObjectByName('palco');
      if (!obj) return;
      const size = palcoIntrinsicSize(obj);
      if (!size) return;
      applyPalcoFit(obj, size, 'cover');
      canvas.renderAll();
      populatePalcoProps(obj);
      showEditorAlert('Fundo preenche todo o canvas.', 'success');
    });

    ['prop-palco-zoom', 'fundo-palco-zoom'].forEach((id) => {
      document.getElementById(id)?.addEventListener('input', (e) => {
        const obj = findObjectByName('palco');
        if (!obj || syncingProps) return;

        const size = palcoIntrinsicSize(obj);
        if (!size) return;

        const pct = parseInt(e.target.value, 10) / 100;
        const containScale = Math.min(canvas.width / size.w, canvas.height / size.h);
        syncPalcoZoomControls(parseInt(e.target.value, 10));
        setPalcoUniformScale(obj, containScale * pct);
        ensurePalcoClipPath(obj);
        populatePalcoProps(obj);
      });
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
      if (propsObj) duplicateObject(propsObj);
    });

    document.getElementById('prop-duplicar-comum')?.addEventListener('click', () => {
      if (propsObj) duplicateObject(propsObj);
    });

    document.getElementById('prop-bloquear')?.addEventListener('click', () => {
      if (!propsObj || isProtectedObject(propsObj)) return;
      setObjectLocked(propsObj, !isObjectLocked(propsObj));
      openPropsPanel(propsObj);
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
      if (obj.isProductCard && obj.productIndex) {
        max = Math.max(max, obj.productIndex);
      } else if (obj.isProductZone && obj.zoneId) {
        max = Math.max(max, obj.zoneId);
      } else if (obj.isDynamicText && obj.linkedZone) {
        max = Math.max(max, obj.linkedZone);
      }
    });
    zoneCounter = max;
  }

  function resolveProductBlockIndex(obj) {
    if (!obj) return null;
    if (isProductCard(obj)) return obj.productIndex || obj.zoneId || null;
    if (isDynamicText(obj)) return obj.linkedZone || null;
    if (isProductZone(obj) && !isProductCard(obj)) return obj.zoneId || null;
    if (obj.type === 'activeSelection' && obj.getObjects) {
      const indices = obj.getObjects()
        .map((item) => resolveProductBlockIndex(item))
        .filter((n) => n !== null && n !== undefined);
      if (indices.length > 0) return Math.min(...indices);
    }
    const cardRoot = getProductCardRoot(obj);
    if (cardRoot) return cardRoot.productIndex || cardRoot.zoneId || null;
    return null;
  }

  function collectLooseBlockObjects(productIndex) {
    const index = parseInt(String(productIndex), 10);
    if (!index || !canvas) return [];

    return canvas.getObjects().filter((obj) => {
      if (isProductCard(obj)) return false;
      if (obj.isProductZone && !obj.isProductCard && obj.zoneId === index) return true;
      if (obj.isDynamicText && obj.linkedZone === index) return true;
      return false;
    });
  }

  function finalizeProductCardGroup(group, productIndex) {
    if (!group) return;

    group.getObjects().forEach((child) => {
      if (child.isDynamicText && child.textType) {
        child.set({
          cardPart: child.textType,
          linkedZone: productIndex,
          isDynamicText: true,
        });
      } else if (child.isProductZone && !child.isProductCard && child.type === 'group') {
        child.set({ cardPart: 'foto', zoneId: productIndex });
      } else if (child.type === 'rect' && !child.cardPart) {
        child.set({ cardPart: 'foto' });
      }
    });

    group.set({
      isProductCard: true,
      isProductZone: true,
      productIndex,
      zoneId: productIndex,
      name: `card_produto_${productIndex}`,
      subTargetCheck: true,
      originX: 'left',
      originY: 'top',
    });

    updateCardProductIndex(group, productIndex);
  }

  function inferProductIndexFromObjects(objects) {
    for (const obj of objects) {
      const idx = resolveProductBlockIndex(obj);
      if (idx) return idx;
    }
    return null;
  }

  function agruparSelecaoComoBloco() {
    const active = canvas?.getActiveObject();
    if (!active) {
      showEditorAlert('Selecione os elementos do produto no canvas (Shift+clique em cada um).');
      return;
    }

    if (isProductCard(active)) {
      showEditorAlert('Este bloco ja esta agrupado.');
      return;
    }

    if (active.type !== 'activeSelection') {
      showEditorAlert('Selecione todos os elementos do bloco juntos (Shift+clique ou arraste a selecao).');
      return;
    }

    const objects = active.getObjects();
    if (objects.length < 2) {
      showEditorAlert('Selecione ao menos 2 elementos (zona de foto + textos).');
      return;
    }

    syncZoneCounterFromCanvas();
    let productIndex = inferProductIndexFromObjects(objects);
    if (!productIndex) {
      zoneCounter += 1;
      productIndex = zoneCounter;
    } else {
      zoneCounter = Math.max(zoneCounter, productIndex);
    }

    const group = active.toGroup();
    finalizeProductCardGroup(group, productIndex);
    canvas.setActiveObject(group);
    canvas.renderAll();
    openPropsPanel(group);
    refreshLayersList();
    showEditorAlert(`Bloco do produto ${productIndex} agrupado. Agora voce pode mover tudo junto.`, 'success');
  }

  function duplicateLooseProductBlock(sourceIndex) {
    const sources = collectLooseBlockObjects(sourceIndex);
    if (sources.length === 0) {
      showEditorAlert(`Nenhum elemento encontrado para o produto ${sourceIndex}.`);
      return;
    }

    syncZoneCounterFromCanvas();
    zoneCounter += 1;
    const newIndex = zoneCounter;
    const offsetX = 56;
    const offsetY = 56;
    const clones = [];
    let pending = sources.length;

    sources.forEach((src) => {
      src.clone((cloned) => {
        if (!cloned) {
          pending -= 1;
          return;
        }

        cloned.set({
          left: (src.left || 0) + offsetX,
          top: (src.top || 0) + offsetY,
          isLocked: false,
        });

        if (cloned.isDynamicText) {
          cloned.set({
            linkedZone: newIndex,
            cardPart: cloned.textType || cloned.cardPart || '',
          });
        }

        if (cloned.isProductZone && !cloned.isProductCard) {
          cloned.set({
            zoneId: newIndex,
            name: `zona_produto_${newIndex}`,
            cardPart: 'foto',
          });
        }

        canvas.add(cloned);
        clones.push(cloned);
        pending -= 1;

        if (pending === 0) {
          enforceBackgroundOrder();
          if (clones.length === 1) {
            canvas.setActiveObject(clones[0]);
          } else {
            const selection = new fabric.ActiveSelection(clones, { canvas });
            canvas.setActiveObject(selection);
          }
          canvas.renderAll();
          refreshLayersList();
          showEditorAlert(
            `Bloco do produto ${newIndex} criado (${clones.length} elementos). Posicione e use "Agrupar selecao" para mover junto.`,
            'success'
          );
        }
      }, CUSTOM_PROPS);
    });
  }

  function duplicarBlocoProduto() {
    const active = canvas?.getActiveObject();
    if (!active) {
      showEditorAlert('Selecione um bloco ou qualquer elemento do produto no canvas.');
      return;
    }

    if (isProductCard(active)) {
      duplicateProductCard(active);
      return;
    }

    const cardRoot = getProductCardRoot(active);
    if (cardRoot) {
      duplicateProductCard(cardRoot);
      return;
    }

    const sourceIndex = resolveProductBlockIndex(active);
    if (!sourceIndex) {
      showEditorAlert('Nao foi possivel identificar o produto. Verifique o vinculo (Produto N.) dos textos e da zona de foto.');
      return;
    }

    duplicateLooseProductBlock(sourceIndex);
  }

  function createCardText(textType, left, top, productIndex, cfg) {
    return new fabric.IText(cfg.text, {
      left,
      top,
      fontSize: cfg.fontSize * DISPLAY_SCALE,
      fill: cfg.fill,
      fontWeight: cfg.fontWeight,
      linethrough: cfg.linethrough,
      fontFamily: 'Bebas Neue, Oswald, Impact, sans-serif',
      originX: 'left',
      originY: 'top',
      cardPart: textType,
      isDynamicText: true,
      textType,
      linkedZone: productIndex,
    });
  }

  function addProductCard() {
    syncZoneCounterFromCanvas();
    zoneCounter += 1;
    const productIndex = zoneCounter;
    const cardW = dims.width * DISPLAY_SCALE * 0.44;
    const cardH = dims.height * DISPLAY_SCALE * 0.16;
    const cx = canvas.width / 2 - cardW / 2;
    const cy = canvas.height / 2 - cardH / 2;
    const fotoW = cardW * 0.36;
    const fotoH = cardH * 0.82;
    const textX = cardW * 0.4;

    const fotoRect = new fabric.Rect({
      left: cardW * 0.03,
      top: cardH * 0.09,
      width: fotoW,
      height: fotoH,
      fill: 'rgba(59, 130, 246, 0.12)',
      stroke: '#3b82f6',
      strokeWidth: 2,
      strokeDashArray: [6, 3],
      rx: 6,
      ry: 6,
      originX: 'left',
      originY: 'top',
      cardPart: 'foto',
    });

    const nome = createCardText('nome_produto', textX, cardH * 0.1, productIndex, DYNAMIC_TEXT_DEFAULTS.nome_produto);
    const precoDe = createCardText('preco_normal', textX, cardH * 0.42, productIndex, DYNAMIC_TEXT_DEFAULTS.preco_normal);
    const precoPor = createCardText('preco_promo', textX, cardH * 0.58, productIndex, DYNAMIC_TEXT_DEFAULTS.preco_promo);
    const unidade = createCardText('unidade', textX, cardH * 0.82, productIndex, DYNAMIC_TEXT_DEFAULTS.unidade);

    const badge = new fabric.Text(`Produto ${productIndex}`, {
      left: cardW * 0.03,
      top: cardH * 0.02,
      fontSize: 11 * DISPLAY_SCALE,
      fill: '#93c5fd',
      fontWeight: '700',
      originX: 'left',
      originY: 'top',
      cardPart: 'label',
      selectable: false,
      evented: false,
    });

    const group = new fabric.Group([fotoRect, nome, precoDe, precoPor, unidade, badge], {
      left: cx,
      top: cy,
      originX: 'left',
      originY: 'top',
      name: `card_produto_${productIndex}`,
      isProductCard: true,
      isProductZone: true,
      productIndex,
      zoneId: productIndex,
      subTargetCheck: true,
    });

    canvas.add(group);
    canvas.setActiveObject(group);
    canvas.renderAll();
    openPropsPanel(group);
    refreshLayersList();
    showEditorAlert(`Card do produto ${productIndex} adicionado.`, 'success');
  }

  function duplicateProductCard(card) {
    if (!card || !isProductCard(card)) return;
    if (isObjectLocked(card)) {
      showEditorAlert('Desbloqueie o card antes de duplicar.');
      return;
    }

    syncZoneCounterFromCanvas();
    zoneCounter += 1;
    const newIndex = zoneCounter;

    card.clone((cloned) => {
      if (!cloned) return;
      cloned.set({
        left: (card.left || 0) + 48,
        top: (card.top || 0) + 48,
        isLocked: false,
      });
      updateCardProductIndex(cloned, newIndex);
      canvas.add(cloned);
      enforceBackgroundOrder();
      canvas.setActiveObject(cloned);
      canvas.renderAll();
      openPropsPanel(cloned);
      refreshLayersList();
      showEditorAlert(`Card do produto ${newIndex} criado.`, 'success');
    }, CUSTOM_PROPS);
  }

  function addProductZone() {
    syncZoneCounterFromCanvas();
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
      originX: 'left',
      originY: 'top',
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
    document.getElementById('btn-add-card')?.addEventListener('click', addProductCard);
    document.getElementById('btn-add-zona')?.addEventListener('click', addProductZone);
    document.getElementById('btn-duplicar-bloco')?.addEventListener('click', duplicarBlocoProduto);
    document.getElementById('btn-agrupar-bloco')?.addEventListener('click', agruparSelecaoComoBloco);
    document.getElementById('btn-duplicar-card')?.addEventListener('click', duplicarBlocoProduto);

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
    return canvas.toDataURL({ format: 'jpeg', quality: 0.85, multiplier: 0.15 });
  }

  function resizeCanvasWrapper() {
    const size = canvasSize();
    const wrapper = document.getElementById('canvas-wrapper');
    wrapper.style.width = size.width + 'px';
    wrapper.style.height = size.height + 'px';
    if (canvas) {
      canvas.setWidth(size.width);
      canvas.setHeight(size.height);
      updatePalcoClipPaths();
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
        fitPalcoToCanvas(palcoFitMode);
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

    canvas.on('object:added', () => {
      refreshLayersList();
      markEditorDirty();
    });
    canvas.on('object:removed', () => {
      refreshLayersList();
      markEditorDirty();
    });
    canvas.on('object:modified', () => {
      refreshLayersList();
      markEditorDirty();
      if (propsObj && getActiveTarget() === propsObj) {
        if (isPalcoObject(propsObj)) populatePalcoProps(propsObj);
        if (isTextObject(propsObj) || isDynamicText(propsObj)) populateTextProps(propsObj);
        if (isDynamicText(propsObj)) populateDynamicTextProps(propsObj);
        if (isProductZone(propsObj)) populateZoneProps(propsObj);
        if (isImageObject(propsObj) && !isPalcoObject(propsObj)) populateImageProps(propsObj);
        if (isVectorObject(propsObj)) populateVectorProps(propsObj);
      }
    });

    canvas.on('mouse:dblclick', (opt) => {
      const target = opt.target;
      if (isPalcoObject(target)) {
        if (palcoEditMode) exitPalcoEditMode();
        else enterPalcoEditMode();
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
      loadPalcoFromConfig(null, (autoEdit) => {
        canvas.renderAll();
        if (autoEdit) enterPalcoEditMode();
        resetEditorDirty();
        editorReady = true;
      }, 'contain');
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
        name: 'badge_oferta',
        text: textos.badge_oferta || 'Oferta',
        left: dims.width * DISPLAY_SCALE * 0.05,
        top: dims.height * DISPLAY_SCALE * 0.15,
        fontSize: 36 * DISPLAY_SCALE,
        fill: '#ffffff',
        fontWeight: '700',
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
      {
        name: 'texto_legal',
        text: textos.texto_legal || 'Ofertas validas enquanto durarem os estoques.',
        left: dims.width * DISPLAY_SCALE * 0.02,
        top: dims.height * DISPLAY_SCALE * 0.95,
        fontSize: 14 * DISPLAY_SCALE,
        fill: '#ffffff',
        fontWeight: '400',
        fontFamily: 'Segoe UI, Arial, sans-serif',
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

    const fundos = EDITOR_DATA.config?.fundos || {};
    const palcoPath = fundos[formato] || fundos['9x16'] || '';
    if (!palcoPath) {
      resetEditorDirty();
      editorReady = true;
    }
  }

  function loadFabricState() {
    const saved = EDITOR_DATA.config?.fabric_state;
    if (saved?.objects?.length) {
      const palcoTransform = extractPalcoTransformFromState(saved);
      const scaledState = reescalarState(stripPalcoFromState(saved), DISPLAY_SCALE);
      canvas.loadFromJSON(scaledState, () => {
        canvas.getObjects().forEach((obj) => {
          if (obj.name === 'fundo-editor') fundoRect = obj;
        });
        fitFundoRectToCanvas();
        loadPalcoFromConfig(palcoTransform, (autoEdit) => {
          applyLockStatesFromCanvas();
          syncZoneCounterFromCanvas();
          enforceBackgroundOrder();
          canvas.renderAll();
          refreshLayersList();
          updateCanvasThumb(captureCanvasThumb());
          if (autoEdit) enterPalcoEditMode();
          resetEditorDirty();
          editorReady = true;
        });
      });
    } else if (isFabricOnlyModel()) {
      initBaseCanvasObjects();
      const fundos = EDITOR_DATA.config?.fundos || {};
      const palcoPath = fundos[formato] || fundos['9x16'] || '';
      if (!palcoPath) {
        resetEditorDirty();
        editorReady = true;
      }
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
        markEditorDirty();
      });

      hexInput.addEventListener('input', () => {
        let val = hexInput.value.trim();
        if (!val.startsWith('#')) val = '#' + val;
        if (/^#[0-9a-fA-F]{6}$/.test(val)) {
          picker.value = val.toLowerCase();
          if (chave === 'fundo') syncCorFundo();
          markEditorDirty();
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
        markEditorDirty();
      });
    });

    document.querySelectorAll('[data-posicionar-texto]').forEach((btn) => {
      btn.addEventListener('click', () => {
        focusPromoText(btn.dataset.posicionarTexto || '');
      });
    });
  }

  function bindContextMenu() {
    const menu = document.getElementById('editor-context-menu');
    if (!menu || !canvas) return;

    canvas.wrapperEl?.addEventListener('contextmenu', (e) => e.preventDefault());
    canvas.upperCanvasEl?.addEventListener('contextmenu', (e) => e.preventDefault());

    canvas.on('mouse:down', (opt) => {
      if (opt.e.button !== 2) return;
      opt.e.preventDefault();
      const target = opt.target;
      if (!isContextMenuTarget(target)) {
        hideContextMenu();
        return;
      }
      showContextMenu(opt.e.clientX, opt.e.clientY, target);
    });

    menu.querySelectorAll('[data-action]').forEach((item) => {
      item.addEventListener('click', (e) => {
        e.preventDefault();
        handleContextMenuAction(item.dataset.action || '');
      });
    });

    document.addEventListener('click', (e) => {
      if (!menu.contains(e.target)) hideContextMenu();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') hideContextMenu();
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
      if (isObjectLocked(obj)) {
        label = '🔒 ' + label;
      }
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

  const UPLOAD_TIMEOUT_MS = 90000;

  function fetchWithTimeout(url, options = {}, timeoutMs = UPLOAD_TIMEOUT_MS) {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), timeoutMs);
    const merged = { ...options, signal: controller.signal };

    return fetch(url, merged).finally(() => clearTimeout(timer));
  }

  function readImageDimensions(file) {
    return new Promise((resolve, reject) => {
      const url = URL.createObjectURL(file);
      const img = new Image();
      img.onload = () => {
        URL.revokeObjectURL(url);
        resolve({ width: img.naturalWidth || img.width, height: img.naturalHeight || img.height });
      };
      img.onerror = () => {
        URL.revokeObjectURL(url);
        reject(new Error('Nao foi possivel ler as dimensoes da imagem.'));
      };
      img.src = url;
    });
  }

  function isPalcoSizedImage(imgW, imgH) {
    const canvasW = dims.width;
    const canvasH = dims.height;
    const tolerance = 0.15;
    const wRatio = imgW / canvasW;
    const hRatio = imgH / canvasH;
    return wRatio >= 1 - tolerance && wRatio <= 1 + tolerance
      && hRatio >= 1 - tolerance && hRatio <= 1 + tolerance;
  }

  function activateEditorTab(tabName) {
    const tab = document.querySelector(`.editor-tab[data-tab="${tabName}"]`);
    if (tab) tab.click();
  }

  function bindUploadElemento() {
    const input = document.getElementById('input-upload-elemento');
    if (!input) return;

    input.addEventListener('change', async () => {
      const file = input.files?.[0];
      if (!file) return;

      try {
        const { width: imgW, height: imgH } = await readImageDimensions(file);
        if (isPalcoSizedImage(imgW, imgH)) {
          activateEditorTab('fundo');
          showEditorAlert(
            `Imagem ${imgW}×${imgH}px tem o tamanho do canvas (${dims.width}×${dims.height}). Use a aba Fundo para enviar o palco completo — evita travamento no Rembg.`,
            'error'
          );
          input.value = '';
          return;
        }
      } catch (err) {
        showEditorAlert(err.message || 'Erro ao validar imagem.');
        input.value = '';
        return;
      }

      const fd = new FormData();
      fd.append('elemento', file);
      fd.append('modelo_id', String(EDITOR_DATA.modelo.id));

      showLoader('Removendo fundo do elemento...');
      try {
        const url = new URL('api/index.php', window.location.href);
        url.searchParams.set('recurso', 'modelo');
        url.searchParams.set('acao', 'upload_elemento');
        const resp = await fetchWithTimeout(url, { method: 'POST', body: fd });
        const json = await resp.json();
        if (!json.success) throw new Error(json.error || 'Erro no upload.');

        fabric.Image.fromURL(
          assetUrl(json.data.url),
          (img) => {
            if (!img) {
              showEditorAlert('Falha ao carregar imagem no canvas.');
              return;
            }
            const iw = img._originalElement?.naturalWidth || img.width || 1;
            const maxW = dims.width * DISPLAY_SCALE * 0.35;
            const scale = maxW / iw;
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
        const msg = err.name === 'AbortError'
          ? 'Upload excedeu o tempo limite (90s). Tente uma imagem menor ou use a aba Fundo para palco completo.'
          : err.message;
        showEditorAlert(msg);
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

      showLoader('Carregando fundo...');
      try {
        const url = new URL('api/index.php', window.location.href);
        url.searchParams.set('recurso', 'modelo');
        url.searchParams.set('acao', 'upload_fundo');
        const resp = await fetchWithTimeout(url, { method: 'POST', body: fd });
        const json = await resp.json();
        if (!json.success) throw new Error(json.error || 'Erro ao enviar palco.');

        const fundos = json.data?.modelo?.config_visual?.fundos || {};
        const palcoPath = fundos[formato] || '';
        if (statusEl) statusEl.textContent = palcoPath ? `Palco: ${palcoPath}` : 'Palco salvo.';

        if (palcoPath) {
          EDITOR_DATA.config.fundos = fundos;
          exitPalcoEditMode();
          loadPalcoFromConfig(null, () => {
            enterPalcoEditMode();
            canvas.renderAll();
            markEditorDirty();
          }, 'contain');
        }

        showEditorAlert('Palco enviado. Ajuste posicao e zoom com duplo clique no fundo.', 'success');
      } catch (err) {
        const msg = err.name === 'AbortError'
          ? 'Upload excedeu o tempo limite (90s). Tente uma imagem menor ou use a aba Fundo para palco completo.'
          : err.message;
        showEditorAlert(msg);
      } finally {
        hideLoader();
        input.value = '';
      }
    });
  }

  function ensureCardPartsBeforeSave() {
    canvas?.getObjects().forEach((obj) => {
      if (isProductCard(obj)) {
        finalizeProductCardGroup(obj, obj.productIndex || obj.zoneId || 1);
      }
    });
  }

  function normalizePalcoSrcBeforeSave() {
    const palcoObj = findObjectByName('palco');
    if (!palcoObj) return;

    const cleanSrc = EDITOR_DATA.config?.fundos?.[formato]
      || EDITOR_DATA.config?.fundos?.['9x16']
      || '';
    if (cleanSrc) {
      palcoObj.set('src', cleanSrc);
    }
  }

  function bindSalvar() {
    document.getElementById('btn-salvar')?.addEventListener('click', async () => {
      const btn = document.getElementById('btn-salvar');
      btn.disabled = true;
      let redirecting = false;

      try {
        fitFundoRectToCanvas();
        updatePalcoClipPaths();
        ensureCardPartsBeforeSave();
        normalizePalcoSrcBeforeSave();
        canvas.renderAll();

        const previewDataUrl = captureCanvasThumb();
        const fabricState = canvas.toObject(CUSTOM_PROPS.concat(['tipo', 'visible', 'cardPart']));
        const stateReal = reescalarState(fabricState, 1 / DISPLAY_SCALE);
        stateReal.version = fabric.version || '5.3.0';

        const cardCount = countProductCardsOnCanvas();
        const maxItens = cardCount > 0 ? cardCount : EDITOR_DATA.modelo.max_itens_default;

        const payload = {
          id: EDITOR_DATA.modelo.id,
          nome_exibicao: document.getElementById('input-nome')?.value.trim() || EDITOR_DATA.modelo.nome_exibicao,
          formatos_suportados: EDITOR_DATA.modelo.formatos_suportados,
          max_itens_default: maxItens,
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
        resetEditorDirty();
        hideLoader();
        showEditorAlert('Modelo salvo com sucesso.', 'success');
        redirecting = true;
        window.location.assign('gerenciar-modelos.php');
        return;
      } catch (err) {
        hideLoader();
        console.error('[editor-modelo] Erro ao salvar:', err);
        showEditorAlert(err.message || 'Erro ao salvar modelo. Verifique o console.');
      } finally {
        if (!redirecting) {
          btn.disabled = false;
        }
      }
    });
  }

  function bindNavigationGuard() {
    document.getElementById('btn-voltar')?.addEventListener('click', (e) => {
      if (!confirmLeaveEditor()) {
        e.preventDefault();
      }
    });

    document.querySelector('.editor-breadcrumb a[href="gerenciar-modelos.php"]')?.addEventListener('click', (e) => {
      if (!confirmLeaveEditor()) {
        e.preventDefault();
      }
    });

    window.addEventListener('beforeunload', (e) => {
      if (!editorDirty) return;
      e.preventDefault();
      e.returnValue = '';
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
    bindContextMenu();
    bindLayerControls();
    bindPropsPanel();
    bindPalcoProps();
    bindKeyboardDelete();
    bindUploadElemento();
    bindUploadFundo();
    bindSalvar();
    bindExportarPng();
    bindFormatoSelect();
    bindProdutosTab();
    bindNavigationGuard();
    closePropsPanel();
    activateEditorTab('fundo');

    document.getElementById('input-nome')?.addEventListener('input', markEditorDirty);

    const fundos = EDITOR_DATA.config?.fundos || {};
    const palcoPath = fundos[formato] || '';
    const statusEl = document.getElementById('fundo-status');
    if (statusEl && palcoPath) {
      statusEl.textContent = `Palco: ${palcoPath}`;
    }
  }

  document.addEventListener('DOMContentLoaded', init);
})();
