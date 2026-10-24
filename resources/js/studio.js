// Gold3D studio: photo-based viewer (ring / necklace / bracelet / earring).
// Drag = rotate (turntable frames if provided, otherwise 3D tilt), wheel / slider = zoom.
const cfg = window.gold3dFeatureConfig || {};
const $ = id => document.getElementById(id);
const money = n => new Intl.NumberFormat('fa-IR').format(Math.round(n || 0)) + ' تومان';

// Replace these src paths with the real product photos (drop files in public/images/studio/).
// Optional `frames`: an ordered list of photos of the same piece from different angles (360° turntable).
const STUDIO_PHOTOS = {
    ring: {
        label: 'انگشتر',
        variants: [
            { key: 'classic', name: 'Classic Gold', src: '/images/studio/ring.png', frames: [] }
        ]
    },
    necklace: {
        label: 'گردنبند',
        variants: [
            { key: 'classic', name: 'Necklace Classic', src: '/images/studio/necklace.jpg', frames: [] }
        ]
    },
    bracelet: {
        label: 'دستبند',
        variants: [
            { key: 'classic', name: 'Bracelet Classic', src: '/images/studio/bracelet.png', frames: [] }
        ]
    },
    earring: {
        label: 'گوشواره',
        variants: [
            { key: 'classic', name: 'Earring Classic', src: '/images/studio/earring.png', frames: [] }
        ]
    }
};

// Gold tint applied to the photo for each metal colour (photos are not re-rendered).
const METAL_FILTERS = {
    yellow: 'none',
    white: 'grayscale(.75) brightness(1.08) contrast(1.02)',
    rose: 'sepia(.35) hue-rotate(-12deg) saturate(1.25)'
};

const GEM_PRICE = { none: 0, zircon: 450000, sapphire: 2200000, diamond: 12000000 };

let currentType = 'ring';
let currentVariant = 0;
let currentColor = 'yellow';
let currentKarat = 18;
let currentGem = 'none';

const view = {
    stage: null,
    img: null,
    frameIndex: 0,
    rotX: 0,
    rotY: 0,
    zoom: 1,
    dragging: false,
    lastX: 0,
    lastY: 0,
    dragAccum: 0,
    autoSway: false,
    swayTimer: null
};

function currentPhotoSet() {
    const set = STUDIO_PHOTOS[currentType] || STUDIO_PHOTOS.ring;
    return set.variants[currentVariant] || set.variants[0];
}

function calc() {
    const w = Number($('gold-weight')?.value || 4);
    const k = Number($('gold-karat')?.value || currentKarat || 18);
    const gem = $('gem')?.value || currentGem || 'none';
    const stone = GEM_PRICE[gem] || 0;
    const base = Number(cfg.ratePerGram || 0) * w * k / 18;
    const total = (base * (1 + Number(cfg.labor || 0) / 100) * (1 + Number(cfg.profit || 0) / 100) + stone) * (1 + Number(cfg.tax || 0) / 100);
    if ($('estimate')) $('estimate').textContent = cfg.ratePerGram ? money(total) : 'ثبت نرخ طلا لازم است';
    if ($('weight-output')) $('weight-output').textContent = w.toFixed(1).replace('.0', '') + ' گرم';
    if ($('stage-product-name')) $('stage-product-name').textContent = `${STUDIO_PHOTOS[currentType]?.label || 'زیور'} · ${currentPhotoSet().name} · ${k}K`;
    return Math.round(total);
}

function setActive(selector, element) {
    document.querySelectorAll(selector).forEach(x => x.classList.remove('active'));
    element?.classList.add('active');
}

function applyTransform() {
    if (!view.img) return;
    view.img.style.transform = `perspective(900px) rotateX(${view.rotX}deg) rotateY(${view.rotY}deg) scale(${view.zoom})`;
}

function applyMetal() {
    if (!view.img) return;
    view.img.style.filter = METAL_FILTERS[currentColor] || 'none';
}

function showFrame(index) {
    const set = currentPhotoSet();
    const frames = set.frames || [];
    if (!view.img) return;
    if (frames.length) {
        view.frameIndex = (index + frames.length) % frames.length;
        view.img.src = frames[view.frameIndex];
    }
}

function setPhoto() {
    if (!view.img) return;
    const set = currentPhotoSet();
    view.frameIndex = 0;
    view.img.src = (set.frames && set.frames[0]) || set.src;
    view.img.alt = `${STUDIO_PHOTOS[currentType]?.label || ''} ${set.name}`;
    view.img.onerror = () => {
        const msg = $('studio-message');
        if (msg) msg.textContent = 'عکس این زیور پیدا نشد؛ مسیر فایل را در studio.js بررسی کنید.';
    };
    $('studio-3d-loading').hidden = true;
    applyMetal();
    applyTransform();
}

function renderVariantCards() {
    const box = document.querySelector('.studio-models');
    const title = document.querySelector('[data-models-title]');
    const set = STUDIO_PHOTOS[currentType] || STUDIO_PHOTOS.ring;
    if (title) title.textContent = `مدل‌های ${set.label}`;
    if (!box) return;
    box.innerHTML = set.variants.map((v, i) => `
        <button type="button" class="model-card${i === currentVariant ? ' active' : ''}" data-variant="${i}">
          <img src="${v.src}" alt="${v.name}" loading="lazy">
          <span>${v.name}</span>
        </button>`).join('');
    box.querySelectorAll('.model-card').forEach(card => card.addEventListener('click', () => {
        currentVariant = Number(card.dataset.variant || 0);
        setActive('.model-card', card);
        setPhoto();
        calc();
    }));
}

function setZoom(value) {
    view.zoom = 1 + (Math.min(100, Math.max(0, value)) / 100) * 2.2;
    applyTransform();
}

function resetView() {
    view.rotX = 0;
    view.rotY = 0;
    view.zoom = 1;
    view.frameIndex = 0;
    if ($('stage-zoom')) $('stage-zoom').value = 0;
    setPhoto();
}

function initViewer() {
    const stage = $('ring-preview');
    if (!stage) return;
    view.stage = stage;
    view.img = document.createElement('img');
    view.img.className = 'studio-photo';
    view.img.draggable = false;
    stage.appendChild(view.img);
    $('studio-3d-loading').hidden = true;

    stage.addEventListener('pointerdown', e => {
        view.dragging = true;
        view.lastX = e.clientX;
        view.lastY = e.clientY;
        view.dragAccum = 0;
        stage.setPointerCapture?.(e.pointerId);
        stage.classList.add('is-grabbing');
    });
    stage.addEventListener('pointermove', e => {
        if (!view.dragging) return;
        const dx = e.clientX - view.lastX;
        const dy = e.clientY - view.lastY;
        view.lastX = e.clientX;
        view.lastY = e.clientY;
        const frames = currentPhotoSet().frames || [];
        if (frames.length) {
            view.dragAccum += dx;
            if (Math.abs(view.dragAccum) >= 28) {
                showFrame(view.frameIndex + (view.dragAccum > 0 ? -1 : 1));
                view.dragAccum = 0;
            }
        } else {
            view.rotY = Math.max(-40, Math.min(40, view.rotY + dx * 0.25));
            view.rotX = Math.max(-25, Math.min(25, view.rotX - dy * 0.2));
            applyTransform();
        }
    });
    const endDrag = () => { view.dragging = false; stage.classList.remove('is-grabbing'); };
    stage.addEventListener('pointerup', endDrag);
    stage.addEventListener('pointercancel', endDrag);
    stage.addEventListener('pointerleave', endDrag);

    stage.addEventListener('wheel', e => {
        e.preventDefault();
        const slider = $('stage-zoom');
        const next = Math.min(100, Math.max(0, Number(slider?.value || 0) - Math.sign(e.deltaY) * 6));
        if (slider) slider.value = next;
        setZoom(next);
    }, { passive: false });

    setPhoto();
    renderVariantCards();
}

function initStudio() {
    initViewer();

    document.querySelectorAll('[data-color]').forEach(btn => btn.addEventListener('click', () => {
        setActive('[data-color]', btn); currentColor = btn.dataset.color;
        if ($('gold-color')) $('gold-color').value = currentColor;
        applyMetal(); calc();
    }));
    document.querySelectorAll('[data-karat]').forEach(btn => btn.addEventListener('click', () => {
        setActive('[data-karat]', btn); currentKarat = Number(btn.dataset.karat || 18);
        if ($('gold-karat')) $('gold-karat').value = currentKarat;
        calc();
    }));
    document.querySelectorAll('[data-gem]').forEach(btn => btn.addEventListener('click', () => {
        setActive('[data-gem]', btn); currentGem = btn.dataset.gem;
        if ($('gem')) $('gem').value = currentGem;
        calc();
    }));
    document.querySelectorAll('[data-type]').forEach(btn => btn.addEventListener('click', () => {
        setActive('[data-type]', btn);
        currentType = btn.dataset.type in STUDIO_PHOTOS ? btn.dataset.type : 'ring';
        currentVariant = 0;
        if ($('design-type')) $('design-type').value = currentType;
        renderVariantCards();
        resetView();
        calc();
    }));
    ['gold-weight','gold-karat','gem','gold-color','design-type','ring-size'].forEach(id => { $(id)?.addEventListener('input', calc); $(id)?.addEventListener('change', calc); });

    $('stage-zoom')?.addEventListener('input', e => setZoom(Number(e.target.value)));
    $('stage-reset')?.addEventListener('click', resetView);
    $('stage-auto')?.addEventListener('click', () => {
        view.autoSway = !view.autoSway;
        clearInterval(view.swayTimer);
        if (view.autoSway) {
            let t = 0;
            view.swayTimer = setInterval(() => {
                if (view.dragging) return;
                t += 0.04;
                view.rotY = Math.sin(t) * 22;
                applyTransform();
            }, 30);
        }
    });
    $('stage-fullscreen')?.addEventListener('click', async () => {
        const stage = $('ring-preview'); if (!stage) return;
        try { if (document.fullscreenElement) await document.exitFullscreen(); else await stage.requestFullscreen(); } catch (_) {}
    });

    document.querySelectorAll('.studio-tabs button, .studio-right-tabs button').forEach(btn => btn.addEventListener('click', () => {
        const group = btn.closest('.studio-tabs, .studio-right-tabs'); if (!group) return;
        group.querySelectorAll('button').forEach(x => x.classList.remove('active')); btn.classList.add('active');
    }));

    $('save-design')?.addEventListener('click', async () => {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!token) { if ($('studio-message')) $('studio-message').textContent = 'ابتدا وارد حساب شوید.'; return; }
        try {
            const res = await fetch(cfg.saveUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({
                    design_type: $('design-type')?.value || currentType,
                    estimated_price: calc(),
                    configuration: {
                        model: currentPhotoSet().key,
                        gold_color: $('gold-color')?.value || currentColor,
                        karat: $('gold-karat')?.value || currentKarat,
                        weight: $('gold-weight')?.value || 4,
                        gem: $('gem')?.value || currentGem,
                        ring_size: $('ring-size')?.value || 18
                    }
                })
            });
            const data = await res.json();
            if ($('studio-message')) $('studio-message').textContent = data.message || 'طرح ذخیره شد.';
        } catch (e) {
            if ($('studio-message')) $('studio-message').textContent = 'ذخیره ناموفق بود؛ اتصال حساب را بررسی کنید.';
        }
    });

    $('recommend-btn')?.addEventListener('click', async () => {
        const box = $('recommend-results'); if (!box) return;
        const u = new URL(cfg.recommendUrl, location.origin);
        if ($('recommend-category')?.value) u.searchParams.set('category', $('recommend-category').value);
        u.searchParams.set('limit', '6');
        box.innerHTML = '<div class="recommend-loading">در حال پیدا کردن پیشنهادهای مناسب...</div>';
        try {
            const response = await fetch(u, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error();
            const d = await response.json();
            const items = Array.isArray(d.items) ? d.items : [];
            box.innerHTML = items.map(x => {
                const p = x.product || x;
                const raw = String(p.image_path || '');
                const img = /^https?:\/\//.test(raw) ? raw : (raw ? '/' + raw.replace(/^\//, '') : '/images/product-ring-royal.svg');
                return `<a class="recommend-result-item" href="/products/${encodeURIComponent(p.slug)}"><img src="${esc(img)}" alt="${esc(p.name)}" loading="lazy"><span>${esc(p.name)}</span><b>${money(p.price)}</b></a>`;
            }).join('') || '<p class="recommend-empty">برای این دسته هنوز محصول موجودی ثبت نشده است.</p>';
        } catch (e) {
            box.innerHTML = '<p class="recommend-empty">دریافت پیشنهادها انجام نشد. دوباره تلاش کن.</p>';
        }
    });

    function esc(s) { return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); }
    calc();
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initStudio, { once: true }); else initStudio();
