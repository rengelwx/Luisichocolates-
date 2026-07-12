<?php
require_once __DIR__ . '/config.php';

$db = getDB();

$db->exec("CREATE TABLE IF NOT EXISTS site_config (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    section TEXT NOT NULL,
    `key` TEXT NOT NULL,
    value TEXT,
    UNIQUE(section, `key`)
)");

$db->exec("CREATE TABLE IF NOT EXISTS slider_images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    imagen TEXT NOT NULL,
    orden INTEGER DEFAULT 0,
    activo INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$defaults = [
    ['hero', 'hero_titulo', "Chocolate Artesanal<br><span>Hecho con Pasión</span>"],
    ['hero', 'hero_descripcion', "Descubre nuestra colección de bombones y chocolates artesanales, elaborados con los mejores ingredientes para los paladares más exigentes."],
    ['hero', 'hero_overlay_opacity', "9"],
    ['hero', 'hero_overlay_color', "#FFF9F3"],
    ['nosotros', 'nosotros_titulo', "Nuestra Historia"],
    ['nosotros', 'nosotros_texto1', "Somos una chocolatería artesanal apasionada por crear experiencias únicas a través del chocolate. Cada pieza es elaborada cuidadosamente con ingredientes seleccionados."],
    ['nosotros', 'nosotros_texto2', "Utilizamos cacao de origen, manteca de cacao pura y los mejores ingredientes naturales para garantizar un sabor inigualable en cada bocado."],
    ['contacto', 'contacto_ubicacion', "Ciudad de México, MX"],
    ['contacto', 'contacto_telefono', "+52 55 1234 5678"],
    ['contacto', 'contacto_email', "info@chocolatier.mx"],
    ['contacto', 'contacto_horario', "Lun-Sáb: 9:00 - 19:00"],
    ['footer', 'footer_descripcion', "Chocolate artesanal hecho con amor y los mejores ingredientes."],
    ['footer', 'footer_copyright', "&copy; 2026 LUISICHOCOLATES. Todos los derechos reservados."],
    ['redes', 'redes_facebook', "#"],
    ['redes', 'redes_instagram', "#"],
    ['redes', 'redes_whatsapp', "#"],
    ['redes', 'redes_tiktok', "#"],
    ['general', 'site_nombre', "Chocolatier Artesanal"],
    ['general', 'site_descripcion', "Bombones de chocolate artesanales"],
    ['general', 'logo_tipo', "icon"],           // icon | image
    ['general', 'logo_icon', "fa-candy-cane"],
    ['general', 'logo_url', ""],               // URL de la imagen PNG/JPG
    ['general', 'btn_primary', "Ver Productos"],
    ['general', 'btn_secondary', "Destacados"],
    ['valores', 'valores_icono1', "fa-seedling"],
    ['valores', 'valores_titulo1', "Ingredientes Naturales"],
    ['valores', 'valores_icono2', "fa-hand-holding-heart"],
    ['valores', 'valores_titulo2', "Hecho a Mano"],
    ['valores', 'valores_icono3', "fa-award"],
    ['valores', 'valores_titulo3', "Calidad Premium"],
    ['admin', 'admin_user', "admin"],
    ['admin', 'admin_pass', "chocolatier2026"],
    ['slider', 'slider_intervalo', "5"],
    ['apariencia', 'apariencia_color_primary', '#5D2E1B'],
    ['apariencia', 'apariencia_color_primary_light', '#7A3D28'],
    ['apariencia', 'apariencia_color_secondary', '#C8956B'],
    ['apariencia', 'apariencia_color_accent', '#E8C9A8'],
    ['apariencia', 'apariencia_color_bg', '#FFF9F3'],
    ['apariencia', 'apariencia_color_bg_gray', '#F5EDE6'],
    ['apariencia', 'apariencia_color_bg_brown', '#3C1F10'],
    ['apariencia', 'apariencia_color_text', '#2C1810'],
    ['apariencia', 'apariencia_color_text_light', '#6B4A3A'],
    ['apariencia', 'apariencia_color_valor_icono', '#C8956B'],
    ['apariencia', 'apariencia_color_valor_texto', '#5D2E1B'],
    ['apariencia', 'apariencia_tamano_valor_icono', '2'],
    ['apariencia', 'apariencia_tamano_valor_texto', '0.85'],
    ['apariencia', 'apariencia_btn_primary_bg', '#5D2E1B'],
    ['apariencia', 'apariencia_btn_primary_text', '#FFFFFF'],
    ['apariencia', 'apariencia_btn_secondary_border', '#5D2E1B'],
    ['apariencia', 'apariencia_btn_secondary_text', '#5D2E1B'],
];

// slider defaults
$slider_count = $db->querySingle("SELECT COUNT(*) FROM slider_images");
if ($slider_count == 0) {
    $slider_defaults = [
        ['https://images.unsplash.com/photo-1606312619070-d48b4c652a52?w=1200', 0],
        ['https://images.unsplash.com/photo-1606313562750-1c4f0a0c2bcf?w=1200', 1],
        ['https://images.unsplash.com/photo-1606312619070-d48b4c652a52?w=1200', 2],
    ];
    $stmt = $db->prepare("INSERT INTO slider_images (imagen, orden) VALUES (:imagen, :orden)");
    foreach ($slider_defaults as [$img, $orden]) {
        $stmt->bindValue(':imagen', $img);
        $stmt->bindValue(':orden', $orden);
        $stmt->execute();
        $stmt->reset();
    }
}

$stmt = $db->prepare("INSERT OR IGNORE INTO site_config (section, `key`, value) VALUES (:section, :key, :value)");
foreach ($defaults as [$section, $key, $value]) {
    $stmt->bindValue(':section', $section);
    $stmt->bindValue(':key', $key);
    $stmt->bindValue(':value', $value);
    $stmt->execute();
    $stmt->reset();
}

echo "✅ Tabla site_config creada y datos por defecto insertados.\n";
