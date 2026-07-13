const API = {
    productos: 'api/productos.php',
    categorias: 'api/categorias.php',
    config: 'api/config_site.php',
    slider: 'api/slider.php',
};

const MONEDA = '$';
const IMG_PLACEHOLDER = '<div class="placeholder-img"><i class="fas fa-candy-cane"></i></div>';

document.addEventListener('DOMContentLoaded', () => {
    loadSiteConfig();
    initSlider();
    cargarCategorias();
    cargarProductosDestacados();
    cargarTodosProductos();

    document.getElementById('searchInput')?.addEventListener('input', (e) => {
        cargarTodosProductos(e.target.value, '');
    });

    document.querySelector('.filtro-btn[data-categoria=""]')?.addEventListener('click', function() {
        document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        cargarTodosProductos(document.getElementById('searchInput').value, '');
    });

    document.getElementById('verMasBtn')?.addEventListener('click', () => {
        cargarTodosProductos('', '', true);
    });

    document.getElementById('menuToggle')?.addEventListener('click', () => {
        document.querySelector('.nav').classList.toggle('open');
    });

    document.querySelectorAll('.nav a').forEach(link => {
        link.addEventListener('click', () => {
            document.querySelector('.nav').classList.remove('open');
        });
    });
});

async function loadSiteConfig() {
    try {
        const res = await fetch(API.config);
        const cfg = await res.json();

        if (cfg.general) {
            const name = cfg.general.site_nombre || 'Chocolatier';
            const span = document.querySelector('.logo span');
            if (span) span.textContent = name;
            document.title = name + ' - ' + (cfg.general.site_descripcion || 'Bombones de chocolate');

            const logoEl = document.querySelector('.logo');
            const logoIcon = logoEl?.querySelector('i');
            const logoImg = logoEl?.querySelector('img');
            const logoType = cfg.general.logo_tipo || 'icon';
            if (logoType === 'image' && cfg.general.logo_url) {
                if (logoIcon) logoIcon.style.display = 'none';
                let img = logoEl.querySelector('.logo-img');
                if (!img) {
                    img = document.createElement('img');
                    img.className = 'logo-img';
                    img.alt = cfg.general.site_nombre || 'Logo';
                    img.style.cssText = 'height:40px;display:block;';
                    logoEl.insertBefore(img, logoEl.querySelector('span'));
                }
                img.src = cfg.general.logo_url;
                img.style.display = '';
            } else {
                if (logoIcon) {
                    logoIcon.style.display = '';
                    if (cfg.general.logo_icon) logoIcon.className = 'fas ' + cfg.general.logo_icon;
                }
                const img = logoEl?.querySelector('.logo-img');
                if (img) img.style.display = 'none';
            }

            const btns = document.querySelectorAll('.hero-buttons .btn');
            if (btns[0] && cfg.general.btn_primary) btns[0].textContent = cfg.general.btn_primary;
            if (btns[1] && cfg.general.btn_secondary) btns[1].textContent = cfg.general.btn_secondary;
        }

        if (cfg.hero) {
            const h1 = document.querySelector('.hero-content h1');
            if (h1 && cfg.hero.hero_titulo) h1.innerHTML = cfg.hero.hero_titulo;
            const heroP = document.querySelector('.hero-content > p');
            if (heroP && cfg.hero.hero_descripcion) heroP.textContent = cfg.hero.hero_descripcion;

            const opacidad = parseInt(cfg.hero.hero_overlay_opacity) / 10;
            const color = cfg.hero.hero_overlay_color || '#FFF9F3';
            const r = parseInt(color.slice(1,3), 16);
            const g = parseInt(color.slice(3,5), 16);
            const b = parseInt(color.slice(5,7), 16);
            let styleTag = document.getElementById('hero-overlay-style');
            if (!styleTag) {
                styleTag = document.createElement('style');
                styleTag.id = 'hero-overlay-style';
                document.head.appendChild(styleTag);
            }
            styleTag.textContent = `
                .hero-slider .slide::after {
                    background: linear-gradient(135deg, rgba(${r},${g},${b},${opacidad}) 0%, rgba(${r},${g},${b},${opacidad * 0.85}) 100%) !important;
                }
            `;
        }

        if (cfg.nosotros) {
            const nosotrosH2 = document.querySelector('#nosotros .nosotros-content h2');
            if (nosotrosH2 && cfg.nosotros.nosotros_titulo) nosotrosH2.textContent = cfg.nosotros.nosotros_titulo;
            const paragraphs = document.querySelectorAll('#nosotros .nosotros-content p');
            if (paragraphs[0] && cfg.nosotros.nosotros_texto1) paragraphs[0].textContent = cfg.nosotros.nosotros_texto1;
            if (paragraphs[1] && cfg.nosotros.nosotros_texto2) paragraphs[1].textContent = cfg.nosotros.nosotros_texto2;
        }

        if (cfg.valores) {
            const valorIcons = document.querySelectorAll('.valor i');
            const valorTitles = document.querySelectorAll('.valor h4');
            for (let i = 0; i < 3; i++) {
                const iconVal = cfg.valores['icono' + (i + 1)] || cfg.valores['valores_icono' + (i + 1)] || '';
                const titleVal = cfg.valores['titulo' + (i + 1)] || cfg.valores['valores_titulo' + (i + 1)] || '';
                if (valorIcons[i] && iconVal) valorIcons[i].className = 'fas ' + iconVal;
                if (valorTitles[i] && titleVal) valorTitles[i].textContent = titleVal;
            }
        }

        if (cfg.contacto) {
            const items = document.querySelectorAll('#contacto .contacto-item p');
            if (items[0] && cfg.contacto.contacto_ubicacion) items[0].textContent = cfg.contacto.contacto_ubicacion;
            if (items[1] && cfg.contacto.contacto_telefono) items[1].textContent = cfg.contacto.contacto_telefono;
            if (items[2] && cfg.contacto.contacto_email) items[2].textContent = cfg.contacto.contacto_email;
            if (items[3] && cfg.contacto.contacto_horario) items[3].textContent = cfg.contacto.contacto_horario;
        }

        if (cfg.footer) {
            const footerBrandP = document.querySelector('.footer-brand p');
            if (footerBrandP && cfg.footer.footer_descripcion) footerBrandP.textContent = cfg.footer.footer_descripcion;
        }
        const copyright = (cfg.general && cfg.general.footer_copyright) || (cfg.footer ? cfg.footer.footer_copyright : '') || '';
        if (copyright) {
            const footerBottom = document.querySelector('.footer-bottom p');
            if (footerBottom) footerBottom.innerHTML = copyright;
        }

        if (cfg.redes) {
            document.querySelectorAll('.social-links a, .social-links-contacto a').forEach(a => {
                const label = (a.getAttribute('aria-label') || '').toLowerCase();
                if (label === 'facebook' && cfg.redes.redes_facebook) a.href = cfg.redes.redes_facebook;
                if (label === 'instagram' && cfg.redes.redes_instagram) a.href = cfg.redes.redes_instagram;
                if (label === 'whatsapp' && cfg.redes.redes_whatsapp) a.href = cfg.redes.redes_whatsapp;
                if (label === 'tiktok' && cfg.redes.redes_tiktok) a.href = cfg.redes.redes_tiktok;
            });

            window._whatsappUrl = cfg.redes.redes_whatsapp || cfg.redes.whatsapp || 'https://wa.me/525512345678';
        }

        if (cfg.apariencia) {
            const map = {
                '--color-primary': 'apariencia_color_primary',
                '--color-primary-light': 'apariencia_color_primary_light',
                '--color-secondary': 'apariencia_color_secondary',
                '--color-accent': 'apariencia_color_accent',
                '--color-bg': 'apariencia_color_bg',
                '--color-bg-gray': 'apariencia_color_bg_gray',
                '--color-bg-brown': 'apariencia_color_bg_brown',
                '--color-text': 'apariencia_color_text',
                '--color-text-light': 'apariencia_color_text_light',
            };
            const root = document.documentElement;
            for (const [cssVar, configKey] of Object.entries(map)) {
                const val = cfg.apariencia[configKey];
                if (val) root.style.setProperty(cssVar, val);
            }
            const primary = cfg.apariencia.apariencia_color_primary || '#5D2E1B';
            const r = parseInt(primary.slice(1, 3), 16);
            const g = parseInt(primary.slice(3, 5), 16);
            const b = parseInt(primary.slice(5, 7), 16);
            root.style.setProperty('--shadow', `0 4px 20px rgba(${r},${g},${b},0.1)`);
            root.style.setProperty('--shadow-hover', `0 8px 30px rgba(${r},${g},${b},0.15)`);

            const btnPri = document.querySelector('.btn-primary');
            const btnSec = document.querySelector('.btn-secondary');
            if (btnPri && cfg.apariencia.apariencia_btn_primary_bg) {
                btnPri.style.background = cfg.apariencia.apariencia_btn_primary_bg;
            }
            if (btnPri && cfg.apariencia.apariencia_btn_primary_text) {
                btnPri.style.color = cfg.apariencia.apariencia_btn_primary_text;
            }
            if (btnSec && cfg.apariencia.apariencia_btn_secondary_border) {
                btnSec.style.borderColor = cfg.apariencia.apariencia_btn_secondary_border;
            }
            if (btnSec && cfg.apariencia.apariencia_btn_secondary_text) {
                btnSec.style.color = cfg.apariencia.apariencia_btn_secondary_text;
            }
        }
    } catch (e) {
        console.error('Error loading site config:', e);
    }
}

async function cargarCategorias() {
    try {
        const res = await fetch(API.categorias);
        const cats = await res.json();
        const contenedor = document.getElementById('categoriaFiltros');
        cats.forEach(cat => {
            const btn = document.createElement('button');
            btn.className = 'filtro-btn';
            btn.dataset.categoria = cat.slug;
            btn.textContent = cat.nombre;
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                cargarTodosProductos(document.getElementById('searchInput').value, cat.slug);
            });
            contenedor.appendChild(btn);
        });
    } catch (e) {
        console.error('Error al cargar categorías:', e);
    }
}

const ITEMS_PER_PAGE = 6;
let currentPage = 0;
let currentSearch = '';
let currentCategoria = '';

async function cargarProductosDestacados() {
    try {
        const res = await fetch(`${API.productos}?destacado=1&limit=3`);
        const data = await res.json();
        const productos = Array.isArray(data) ? data : (data.productos || []);
        const grid = document.getElementById('productosDestacados');
        grid.innerHTML = productos.map(p => crearCard(p)).join('');
    } catch (e) {
        console.error('Error cargando destacados:', e);
    }
}

async function cargarTodosProductos(search = '', categoria = '', append = false) {
    const loading = document.getElementById('loading');
    loading?.classList.add('active');

    if (!append) {
        currentPage = 0;
        currentSearch = search;
        currentCategoria = categoria;
    }

    try {
        const offset = currentPage * ITEMS_PER_PAGE;
        let url = `${API.productos}?limit=${ITEMS_PER_PAGE}&offset=${offset}`;
        if (currentSearch) url += `&search=${encodeURIComponent(currentSearch)}`;
        if (currentCategoria) url += `&categoria=${encodeURIComponent(currentCategoria)}`;

        const res = await fetch(url);
        const data = await res.json();
        const productos = Array.isArray(data) ? data : (data.productos || []);
        const total = data.total ?? productos.length;
        const grid = document.getElementById('todosProductos');

        if (append) {
            grid.innerHTML += productos.map(p => crearCard(p)).join('');
        } else {
            grid.innerHTML = productos.map(p => crearCard(p)).join('');
        }

        currentPage++;

        const verMasBtn = document.getElementById('verMasBtn');
        if (verMasBtn) {
            verMasBtn.style.display = (currentPage * ITEMS_PER_PAGE < total) ? 'inline-block' : 'none';
        }
    } catch (e) {
        console.error('Error cargando productos:', e);
    } finally {
        loading?.classList.remove('active');
    }
}

function contactarWhatsApp(nombre) {
    const url = window._whatsappUrl || 'https://wa.me/525512345678';
    const baseUrl = url.replace(/\?.*$/, '');
    const msg = encodeURIComponent(`Hola! Me interesa el producto: ${nombre}`);
    window.open(`${baseUrl}?text=${msg}`, '_blank');
}

function crearCard(producto) {
    const esConvenir = producto.precio_a_convenir == 1;
    const tieneOferta = !esConvenir && producto.precio_oferta && producto.precio_oferta < producto.precio;
    const descuento = tieneOferta ? Math.round((1 - producto.precio_oferta / producto.precio) * 100) : 0;
    const imagen = producto.imagen
        ? `<img src="uploads/${producto.imagen}" alt="${producto.nombre}" loading="lazy" onerror="this.onerror=null;this.parentElement.className='image-wrapper placeholder';this.innerHTML='<i class=\\'fas fa-candy-cane\\'></i>'">`
        : IMG_PLACEHOLDER;

    return `
        <div class="product-card" data-id="${producto.id}">
            <div class="image-wrapper">
                ${tieneOferta ? `<span class="product-badge">-${descuento}%</span>` : ''}
                ${imagen}
            </div>
            <div class="product-info">
                ${producto.categoria_nombre ? `<p class="product-categoria">${producto.categoria_nombre}</p>` : ''}
                <h3>${producto.nombre}</h3>
                <p class="product-desc">${producto.descripcion || ''}</p>
                <div class="product-precio">
                    ${esConvenir ? '<span class="precio-convenir">Precio a convenir</span>' : `<span class="precio-actual">${MONEDA}${(tieneOferta ? producto.precio_oferta : producto.precio).toLocaleString('es-CL')}</span>`}
                    ${!esConvenir && tieneOferta ? `<span class="precio-oferta">${MONEDA}${producto.precio.toLocaleString('es-CL')}</span>` : ''}
                    ${!esConvenir && tieneOferta ? `<span class="precio-descuento">-${descuento}%</span>` : ''}
                </div>
                <div class="product-actions">
                    <button class="btn-whatsapp" onclick="contactarWhatsApp('${producto.nombre.replace(/'/g, "\\'")}')">
                        <i class="fab fa-whatsapp"></i> Contactar
                    </button>
                    <button class="btn-detail" onclick="verDetalle(${producto.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}

async function verDetalle(id) {
    try {
        const res = await fetch(`${API.productos}?id=${id}`);
        const p = await res.json();
        if (p.error) return;

        const esConvenir = p.precio_a_convenir == 1;
        const tieneOferta = !esConvenir && p.precio_oferta && p.precio_oferta < p.precio;
        const descuento = tieneOferta ? Math.round((1 - p.precio_oferta / p.precio) * 100) : 0;
        const imagen = p.imagen
            ? `<img src="uploads/${p.imagen}" alt="${p.nombre}" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\\'placeholder-img\\'><i class=\\'fas fa-candy-cane\\'></i></div>'">`
            : IMG_PLACEHOLDER;

        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay open';
        overlay.innerHTML = `
            <div class="modal">
                <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">&times;</button>
                <div class="modal-body">
                    ${imagen}
                    <div>
                        ${p.categoria_nombre ? `<p class="product-categoria">${p.categoria_nombre}</p>` : ''}
                        <h2 style="font-size:1.5rem;color:var(--color-primary);margin-bottom:10px;">${p.nombre}</h2>
                        <p style="color:var(--color-text-light);margin-bottom:20px;line-height:1.7;">${p.descripcion || 'Sin descripción disponible.'}</p>
                        <div class="product-precio" style="margin-bottom:20px;">
                            ${esConvenir ? '<span class="precio-convenir" style="font-size:1.5rem;">Precio a convenir</span>' : `<span class="precio-actual" style="font-size:1.8rem;">${MONEDA}${(tieneOferta ? p.precio_oferta : p.precio).toLocaleString('es-CL')}</span>`}
                            ${!esConvenir && tieneOferta ? `<span class="precio-oferta" style="font-size:1.2rem;">${MONEDA}${p.precio.toLocaleString('es-CL')}</span>` : ''}
                            ${!esConvenir && tieneOferta ? `<span class="precio-descuento" style="font-size:0.9rem;">-${descuento}%</span>` : ''}
                        </div>
                        <button class="btn btn-whatsapp-lg" onclick="contactarWhatsApp('${p.nombre.replace(/'/g, "\\'")}'); this.closest('.modal-overlay').remove();">
                            <i class="fab fa-whatsapp"></i> Contactar por WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) overlay.remove();
        });
    } catch (e) {
        console.error('Error al cargar detalle:', e);
    }
}

let sliderTimer = null;
let sliderIndex = 0;
let sliderImages = [];

async function initSlider() {
    try {
        const res = await fetch(API.slider);
        sliderImages = await res.json();
        if (!sliderImages.length) return;

        const container = document.getElementById('heroSlider');
        const dotsContainer = document.getElementById('sliderDots');
        if (!container) return;

        container.innerHTML = sliderImages.map((img, i) =>
            `<div class="slide ${i === 0 ? 'active' : ''}" style="background-image:url('${img.imagen}')"></div>`
        ).join('');

        if (dotsContainer) {
            dotsContainer.innerHTML = sliderImages.map((img, i) =>
                `<div class="dot ${i === 0 ? 'active' : ''}" data-index="${i}"></div>`
            ).join('');
            dotsContainer.querySelectorAll('.dot').forEach(dot => {
                dot.addEventListener('click', () => {
                    goToSlide(parseInt(dot.dataset.index));
                });
            });
        }

        let cfgRes = await fetch(API.config);
        let cfg = await cfgRes.json();
        let intervalo = (cfg.slider && cfg.slider.slider_intervalo) ? parseInt(cfg.slider.slider_intervalo) * 1000 : 5000;

        sliderTimer = setInterval(nextSlide, intervalo);
    } catch (e) {
        console.error('Error loading slider:', e);
    }
}

function nextSlide() {
    goToSlide(sliderIndex + 1);
}

function goToSlide(index) {
    const slides = document.querySelectorAll('#heroSlider .slide');
    const dots = document.querySelectorAll('#sliderDots .dot');
    if (!slides.length) return;

    sliderIndex = ((index % slides.length) + slides.length) % slides.length;

    slides.forEach((s, i) => s.classList.toggle('active', i === sliderIndex));
    dots.forEach((d, i) => d.classList.toggle('active', i === sliderIndex));
}
