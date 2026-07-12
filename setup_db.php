<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/api/DB.php';

$db = new DB();

$db->exec("CREATE TABLE IF NOT EXISTS categorias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS productos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    descripcion TEXT,
    precio REAL NOT NULL,
    precio_oferta REAL,
    imagen TEXT,
    categoria_id INTEGER,
    destacado INTEGER DEFAULT 0,
    activo INTEGER DEFAULT 1,
    precio_a_convenir INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS site_config (
    section TEXT NOT NULL,
    key TEXT NOT NULL,
    value TEXT,
    PRIMARY KEY (section, key)
)");

$db->exec("CREATE TABLE IF NOT EXISTS slider_images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    imagen TEXT NOT NULL,
    orden INTEGER DEFAULT 0,
    activo INTEGER DEFAULT 1
)");

// Insertar categorías por defecto
$categorias = [
    ['Bombones Artesanales', 'bombones-artesanales'],
    ['Tabletas de Chocolate', 'tabletas-chocolate'],
    ['Chocolate para Regalo', 'chocolate-regalo'],
    ['Trufas', 'trufas'],
    ['Ediciones Especiales', 'ediciones-especiales'],
];

foreach ($categorias as $cat) {
    $exists = $db->querySingle("SELECT id FROM categorias WHERE slug = '{$cat[1]}'");
    if (!$exists) {
        $stmt = $db->prepare("INSERT INTO categorias (nombre, slug) VALUES (:nombre, :slug)");
        $stmt->bindValue(':nombre', $cat[0], SQLITE3_TEXT);
        $stmt->bindValue(':slug', $cat[1], SQLITE3_TEXT);
        $stmt->execute();
    }
}

// Productos de ejemplo
$productos = [
    ['nombre' => 'Bombón de Chocolate Amargo 70%', 'slug' => 'bombon-chocolate-amargo-70', 'descripcion' => 'Delicioso bombón elaborado con chocolate amargo 70% cacao. Relleno de ganache de frambuesa. Ideal para los amantes del chocolate intenso.', 'precio' => 2500, 'precio_oferta' => 2200, 'imagen' => 'bombon-amargo.jpg', 'categoria_id' => 1, 'destacado' => 1],
    ['nombre' => 'Tableta de Chocolate con Leche y Almendras', 'slug' => 'tableta-chocolate-leche-almendras', 'descripcion' => 'Tableta de chocolate con leche premium con almendras tostadas. 200g de puro placer.', 'precio' => 3800, 'precio_oferta' => null, 'imagen' => 'tableta-almendras.jpg', 'categoria_id' => 2, 'destacado' => 1],
    ['nombre' => 'Caja Regalo 12 Bombones', 'slug' => 'caja-regalo-12-bombones', 'descripcion' => 'Elegante caja de regalo con 12 bombones surtidos. Perfecta para cualquier ocasión especial.', 'precio' => 15000, 'precio_oferta' => 12500, 'imagen' => 'caja-regalo.jpg', 'categoria_id' => 3, 'destacado' => 1],
    ['nombre' => 'Trufa de Chocolate Blanco y Maracuyá', 'slug' => 'trufa-chocolate-blanco-maracuya', 'descripcion' => 'Trufa artesanal de chocolate blanco con corazón de maracuyá. Una explosión de sabores tropicales.', 'precio' => 3200, 'precio_oferta' => null, 'imagen' => 'trufa-maracuya.jpg', 'categoria_id' => 4, 'destacado' => 0],
    ['nombre' => 'Bombón de Chocolate con Dulce de Leche', 'slug' => 'bombon-chocolate-dulce-leche', 'descripcion' => 'Bombón de chocolate semiamargo relleno de dulce de leche artesanal. Un clásico irresistible.', 'precio' => 2800, 'precio_oferta' => 2500, 'imagen' => 'bombon-ddl.jpg', 'categoria_id' => 1, 'destacado' => 0],
    ['nombre' => 'Chocolate Artesanal 55% Cacao', 'slug' => 'chocolate-artesanal-55', 'descripcion' => 'Chocolate artesanal 55% cacao, suave y cremoso. Ideal para derretir, cocinar o disfrutar solo.', 'precio' => 4500, 'precio_oferta' => null, 'imagen' => 'chocolate-55.jpg', 'categoria_id' => 2, 'destacado' => 0],
    ['nombre' => 'Cesta Gourmet Chocolate + Vino', 'slug' => 'cesta-gourmet-chocolate-vino', 'descripcion' => 'Cesta gourmet que incluye selección de bombones, tableta de chocolate artesanal y una botella de vino tinto. El regalo perfecto.', 'precio' => 25000, 'precio_oferta' => 22000, 'imagen' => 'cesta-gourmet.jpg', 'categoria_id' => 3, 'destacado' => 1],
    ['nombre' => 'Trufa de Chocolate Amargo con Menta', 'slug' => 'trufa-amargo-menta', 'descripcion' => 'Trufa de chocolate amargo 70% con un toque refrescante de menta natural.', 'precio' => 3000, 'precio_oferta' => null, 'imagen' => 'trufa-menta.jpg', 'categoria_id' => 4, 'destacado' => 0],
];

foreach ($productos as $prod) {
    $exists = $db->querySingle("SELECT id FROM productos WHERE slug = '{$prod['slug']}'");
    if (!$exists) {
        $stmt = $db->prepare("INSERT INTO productos (nombre, slug, descripcion, precio, precio_oferta, imagen, categoria_id, destacado)
            VALUES (:nombre, :slug, :descripcion, :precio, :precio_oferta, :imagen, :categoria_id, :destacado)");
        $stmt->bindValue(':nombre', $prod['nombre'], SQLITE3_TEXT);
        $stmt->bindValue(':slug', $prod['slug'], SQLITE3_TEXT);
        $stmt->bindValue(':descripcion', $prod['descripcion'], SQLITE3_TEXT);
        $stmt->bindValue(':precio', $prod['precio'], SQLITE3_FLOAT);
        $stmt->bindValue(':precio_oferta', $prod['precio_oferta'] ?? null, SQLITE3_FLOAT);
        $stmt->bindValue(':imagen', $prod['imagen'], SQLITE3_TEXT);
        $stmt->bindValue(':categoria_id', $prod['categoria_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':destacado', $prod['destacado'], SQLITE3_INTEGER);
        $stmt->execute();
    }
}

// Configuración inicial del sitio
$configs = [
    ['general', 'site_nombre', 'LUISICHOCOLATES'],
    ['general', 'site_descripcion', 'Bombones de chocolate artesanales, calidad premium'],
    ['general', 'logo_tipo', 'icon'],
    ['general', 'logo_icon', 'fa-candy-cane'],
    ['general', 'btn_primary', 'Ver Productos'],
    ['general', 'btn_secondary', 'Destacados'],
    ['hero', 'hero_titulo', 'Chocolate Artesanal<br><span>Hecho con Pasión</span>'],
    ['hero', 'hero_descripcion', 'Descubre nuestra colección de bombones y chocolates artesanales, elaborados con los mejores ingredientes para los paladares más exigentes.'],
    ['hero', 'hero_overlay_opacity', '9'],
    ['hero', 'hero_overlay_color', '#FFF9F3'],
    ['nosotros', 'nosotros_titulo', 'Nuestra Historia'],
    ['nosotros', 'nosotros_texto1', 'Somos una chocolatería artesanal apasionada por crear experiencias únicas a través del chocolate. Cada pieza es elaborada cuidadosamente con ingredientes seleccionados.'],
    ['nosotros', 'nosotros_texto2', 'Utilizamos cacao de origen, manteca de cacao pura y los mejores ingredientes naturales para garantizar un sabor inigualable en cada bocado.'],
    ['valores', 'valores_icono1', 'fa-seedling'],
    ['valores', 'valores_titulo1', 'Ingredientes Naturales'],
    ['valores', 'valores_icono2', 'fa-hand-holding-heart'],
    ['valores', 'valores_titulo2', 'Hecho a Mano'],
    ['valores', 'valores_icono3', 'fa-award'],
    ['valores', 'valores_titulo3', 'Calidad Premium'],
    ['contacto', 'contacto_ubicacion', 'Ciudad de México, MX'],
    ['contacto', 'contacto_telefono', '+52 55 1234 5678'],
    ['contacto', 'contacto_email', 'info@luisichocolates.mx'],
    ['contacto', 'contacto_horario', 'Lun-Sáb: 9:00 - 19:00'],
    ['footer', 'footer_descripcion', 'Chocolate artesanal hecho con amor y los mejores ingredientes.'],
    ['footer', 'footer_copyright', '&copy; 2026 LUISICHOCOLATES. Todos los derechos reservados.'],
    ['redes', 'redes_facebook', '#'],
    ['redes', 'redes_instagram', 'https://www.instagram.com/luisichocolates'],
    ['redes', 'redes_whatsapp', 'https://wa.me/584142300371'],
    ['redes', 'redes_tiktok', '#'],
    ['apariencia', 'apariencia_color_primary', '#5D2E1B'],
    ['apariencia', 'apariencia_color_primary_light', '#7A3D28'],
    ['apariencia', 'apariencia_color_secondary', '#C8956B'],
    ['apariencia', 'apariencia_color_accent', '#E8C9A8'],
    ['apariencia', 'apariencia_color_bg', '#FFF9F3'],
    ['apariencia', 'apariencia_color_bg_gray', '#F5EDE6'],
    ['apariencia', 'apariencia_color_bg_brown', '#3C1F10'],
    ['apariencia', 'apariencia_color_text', '#2C1810'],
    ['apariencia', 'apariencia_color_text_light', '#6B4A3A'],
    ['apariencia', 'apariencia_btn_primary_bg', '#5D2E1B'],
    ['apariencia', 'apariencia_btn_primary_text', '#FFFFFF'],
    ['apariencia', 'apariencia_btn_secondary_border', '#5D2E1B'],
    ['apariencia', 'apariencia_btn_secondary_text', '#5D2E1B'],
    ['slider', 'slider_intervalo', '5'],
    ['admin', 'admin_user', 'admin'],
    ['admin', 'admin_pass', 'chocolatier2026'],
];

foreach ($configs as $c) {
    $stmt = $db->prepare("INSERT INTO site_config (section, `key`, value) VALUES (:section, :key, :value)
        ON CONFLICT(section, `key`) DO UPDATE SET value = :value2");
    $stmt->bindValue(':section', $c[0], SQLITE3_TEXT);
    $stmt->bindValue(':key', $c[1], SQLITE3_TEXT);
    $stmt->bindValue(':value', $c[2], SQLITE3_TEXT);
    $stmt->bindValue(':value2', $c[2], SQLITE3_TEXT);
    $stmt->execute();
}

echo "Base de datos inicializada correctamente\n";