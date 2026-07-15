<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
require_once __DIR__ . '/../config.php';

$auth_error = '';
$logged_in = false;

$db = getDB();

// obtener credenciales desde la BD
$admin_user = $db->querySingle("SELECT value FROM site_config WHERE section='admin' AND key='admin_user'") ?: 'admin';
$admin_pass = $db->querySingle("SELECT value FROM site_config WHERE section='admin' AND key='admin_pass'") ?: 'chocolatier2026';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    if ($user === $admin_user && $pass === $admin_pass) {
        $_SESSION['admin_logged'] = true;
        $logged_in = true;
    } else {
        $auth_error = 'Usuario o contraseña incorrectos';
    }
}

if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    $logged_in = true;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - LUISICHOCOLATES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css?v=<?= time() ?>">
    <style>
        .admin-tabs { display:flex; gap:0; background:var(--color-bg-gray); border-radius:var(--radius-sm); overflow:hidden; margin-bottom:30px; flex-wrap:wrap; }
        .admin-tab { padding:12px 22px; cursor:pointer; font-weight:600; font-size:0.88rem; color:var(--color-text-light); border:none; background:transparent; transition:all 0.2s; white-space:nowrap; }
        .admin-tab:hover { color:var(--color-primary); background:rgba(93,46,27,0.05); }
        .admin-tab.active { background:var(--color-primary); color:#fff; }
        .tab-content { display:none; }
        .tab-content.active { display:block; }
        .config-card { background:var(--color-white); border-radius:var(--radius); box-shadow:var(--shadow); padding:30px; max-width:700px; }
        .config-card h3 { color:var(--color-primary); margin-bottom:20px; font-size:1.2rem; }
        .config-card .form-group { margin-bottom:16px; }
        .config-card .form-group label { font-size:0.85rem; font-weight:600; color:var(--color-primary); display:block; margin-bottom:6px; }
        .config-card .form-group input,
        .config-card .form-group textarea { width:100%; padding:10px 14px; border:2px solid #e0ddd8; border-radius:8px; font-size:0.92rem; transition:all 0.3s; outline:none; font-family:inherit; }
        .config-card .form-group input:focus,
        .config-card .form-group textarea:focus { border-color:var(--color-secondary); }
        .config-card .form-group textarea { resize:vertical; min-height:80px; }
        .save-row { margin-top:20px; display:flex; justify-content:flex-end; }
        .save-msg { margin-left:12px; align-self:center; font-weight:600; font-size:0.85rem; }
        .save-msg.ok { color:var(--color-success); }
        .save-msg.err { color:#d32f2f; }
        .form-group-checkbox label { display:flex !important; align-items:center; gap:8px; font-size:0.9rem !important; cursor:pointer; }
        .form-group-checkbox input[type="checkbox"] { width:auto !important; margin:0; }
    </style>
</head>
<body class="admin-body">

<?php if (!$logged_in): ?>
<div style="max-width:400px;margin:100px auto;padding:40px;background:#fff;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.1);">
    <div style="text-align:center;margin-bottom:30px;">
        <i class="fas fa-candy-cane" style="font-size:2.5rem;color:var(--color-secondary);"></i>
        <h2 style="color:var(--color-primary);margin-top:10px;">Panel Admin</h2>
    </div>
    <?php if ($auth_error): ?>
        <p style="color:#d32f2f;background:#fde8e8;padding:10px;border-radius:8px;margin-bottom:16px;font-size:0.9rem;"><?= $auth_error ?></p>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group" style="margin-bottom:16px;">
            <label>Usuario</label>
            <input type="text" name="user" required>
        </div>
        <div class="form-group" style="margin-bottom:24px;">
            <label>Contraseña</label>
            <input type="password" name="pass" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary btn-full">Ingresar</button>
    </form>
    <p style="text-align:center;margin-top:20px;font-size:0.8rem;color:#999;">
        <a href="../index.html" style="color:var(--color-secondary);">&larr; Volver a la tienda</a>
    </p>
</div>
<?php else: ?>
<header class="admin-header">
    <div class="container">
        <div class="header-content">
            <a href="index.php" style="font-weight:700;"><i class="fas fa-candy-cane"></i> Admin LUISICHOCOLATES</a>
            <div style="display:flex;gap:20px;align-items:center;">
                <a href="../index.html" target="_blank"><i class="fas fa-store"></i> Ver Tienda</a>
                <a href="?logout=1"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </div>
        </div>
    </div>
</header>

<div class="admin-container">
    <div class="admin-tabs">
        <button class="admin-tab active" onclick="switchTab('productos')"><i class="fas fa-box"></i> Productos</button>
        <button class="admin-tab" onclick="switchTab('hero')"><i class="fas fa-image"></i> Hero</button>
        <button class="admin-tab" onclick="switchTab('nosotros')"><i class="fas fa-info-circle"></i> Nosotros</button>
        <button class="admin-tab" onclick="switchTab('redes')"><i class="fas fa-share-alt"></i> Redes</button>
        <button class="admin-tab" onclick="switchTab('general')"><i class="fas fa-cog"></i> General</button>
        <button class="admin-tab" onclick="switchTab('valores')"><i class="fas fa-star"></i> Valores</button>
        <button class="admin-tab" onclick="switchTab('apariencia')"><i class="fas fa-palette"></i> Apariencia</button>
        <button class="admin-tab" onclick="switchTab('slider')"><i class="fas fa-images"></i> Slider</button>
        <button class="admin-tab" onclick="switchTab('adminlogin')"><i class="fas fa-lock"></i> Admin</button>
    </div>

    <!-- PRODUCTOS -->
    <div class="tab-content active" id="tab-productos">
        <details style="margin-bottom:24px;background:var(--color-white);border-radius:var(--radius);box-shadow:var(--shadow);padding:20px;">
            <summary style="cursor:pointer;font-weight:700;color:var(--color-primary);font-size:1rem;">
                <i class="fas fa-tags"></i> Gestionar Categorías
            </summary>
            <div style="margin-top:16px;">
                <div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
                    <input type="text" id="catNombreInput" placeholder="Nombre de categoría" style="flex:1;min-width:200px;padding:10px 14px;border:2px solid #e0ddd8;border-radius:8px;font-size:0.9rem;">
                    <button class="btn btn-primary" onclick="crearCategoria()" style="white-space:nowrap;"><i class="fas fa-plus"></i> Agregar</button>
                </div>
                <div class="admin-table">
                    <table>
                        <thead><tr><th>ID</th><th>Nombre</th><th>Slug</th><th>Productos</th><th>Acciones</th></tr></thead>
                        <tbody id="adminCategoriasList"></tbody>
                    </table>
                </div>
            </div>
        </details>
        <div class="admin-toolbar">
            <h2><i class="fas fa-box"></i> Productos</h2>
            <button class="btn btn-primary" onclick="abrirFormulario()">
                <i class="fas fa-plus"></i> Nuevo Producto
            </button>
        </div>
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Categoría</th>
                        <th>Destacado</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="adminProductos"></tbody>
            </table>
        </div>
    </div>

    <!-- HERO -->
    <div class="tab-content" id="tab-hero">
        <div class="config-card">
            <h3><i class="fas fa-image"></i> Sección Hero</h3>
            <div class="form-group">
                <label>Título (usa &lt;br&gt; para salto de línea, &lt;span&gt; para color)</label>
                <textarea id="cfg_hero_titulo" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea id="cfg_hero_descripcion" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Opacidad del overlay (0 = transparente, 10 = opaco)</label>
                <input type="range" id="cfg_hero_overlay_opacity" min="0" max="10" value="9" oninput="document.getElementById('hero_opacity_val').textContent=this.value">
                <span id="hero_opacity_val" style="font-weight:bold;color:var(--color-primary);font-size:1.3rem;">9</span>
            </div>
            <div class="form-group">
                <label>Color del overlay (hex)</label>
                <input type="color" id="cfg_hero_overlay_color" value="#fff9f3">
                <input type="text" id="cfg_hero_overlay_color_txt" value="#FFF9F3" style="margin-top:4px;font-size:0.8rem;">
            </div>
            <div class="save-row">
                <span class="save-msg" id="msg-hero"></span>
                <button class="btn btn-primary" onclick="saveHero()"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>

    <!-- NOSOTROS -->
    <div class="tab-content" id="tab-nosotros">
        <div class="config-card">
            <h3><i class="fas fa-info-circle"></i> Sección Nosotros</h3>
            <div class="form-group">
                <label>Imagen</label>
                <input type="file" id="cfg_nosotros_imagen" accept="image/*" onchange="uploadNosotrosImagen(this)">
                <input type="text" id="cfg_nosotros_imagen_url" placeholder="O pega URL directa" style="margin-top:8px;">
                <div id="nosotrosImagenPreview" style="margin-top:10px;max-width:200px;"></div>
            </div>
            <div class="form-group">
                <label>Título</label>
                <input type="text" id="cfg_nosotros_titulo">
            </div>
            <div class="form-group">
                <label>Texto 1</label>
                <textarea id="cfg_nosotros_texto1" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Texto 2</label>
                <textarea id="cfg_nosotros_texto2" rows="3"></textarea>
            </div>
            <div class="save-row">
                <span class="save-msg" id="msg-nosotros"></span>
                <button class="btn btn-primary" onclick="saveConfig('nosotros')"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>

    <!-- REDES -->
    <div class="tab-content" id="tab-redes">
        <div class="config-card">
            <h3><i class="fas fa-share-alt"></i> Redes Sociales</h3>
            <div class="form-group">
                <label><i class="fab fa-facebook"></i> URL Facebook</label>
                <input type="url" id="cfg_redes_redes_facebook">
            </div>
            <div class="form-group">
                <label><i class="fab fa-instagram"></i> URL Instagram</label>
                <input type="url" id="cfg_redes_redes_instagram">
            </div>
            <div class="form-group">
                <label><i class="fab fa-whatsapp"></i> URL WhatsApp</label>
                <input type="url" id="cfg_redes_redes_whatsapp">
            </div>
            <div class="form-group">
                <label><i class="fab fa-tiktok"></i> URL TikTok</label>
                <input type="url" id="cfg_redes_redes_tiktok">
            </div>
            <div class="save-row">
                <span class="save-msg" id="msg-redes"></span>
                <button class="btn btn-primary" onclick="saveConfig('redes')"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>

    <!-- GENERAL -->
    <div class="tab-content" id="tab-general">
        <div class="config-card">
            <h3><i class="fas fa-cog"></i> General</h3>
            <div class="form-group">
                <label>Nombre del sitio (título de la pestaña / logo)</label>
                <input type="text" id="cfg_general_site_nombre">
            </div>
            <div class="form-group">
                <label>Texto de copyright</label>
                <input type="text" id="cfg_general_footer_copyright">
            </div>
            <div class="form-group">
                <label>Tipo de logo</label>
                <select id="cfg_general_logo_tipo" onchange="toggleLogoTipo()">
                    <option value="icon">Ícono (FontAwesome)</option>
                    <option value="image">Imagen (PNG/JPG)</option>
                </select>
            </div>
            <div class="form-group" id="logoIconField">
                <label>Icono del logo (clase FontAwesome)</label>
                <input type="text" id="cfg_general_logo_icon" placeholder="fa-candy-cane">
            </div>
            <div class="form-group" id="logoImageField" style="display:none;">
                <label>Imagen del logo (PNG/JPG)</label>
                <input type="file" id="logoUploadInput" accept="image/*" onchange="uploadLogo(this)">
                <input type="text" id="cfg_general_logo_url" placeholder="O pega la URL directamente" style="margin-top:8px;">
                <div id="logoPreview" style="margin-top:8px;"></div>
            </div>
            <div class="form-group">
                <label>Texto del botón primario (ej: "Ver Productos")</label>
                <input type="text" id="cfg_general_btn_primary" placeholder="Ver Productos">
            </div>
            <div class="form-group">
                <label>Texto del botón secundario (ej: "Destacados")</label>
                <input type="text" id="cfg_general_btn_secondary" placeholder="Destacados">
            </div>
            <div class="save-row">
                <span class="save-msg" id="msg-general"></span>
                <button class="btn btn-primary" onclick="saveGeneral()"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>

    <!-- VALORES (sección nosotros) -->
    <div class="tab-content" id="tab-valores">
        <div class="config-card">
            <h3><i class="fas fa-star"></i> Valores (Sección Nosotros)</h3>
            <div class="form-group">
                <label>Valor 1 - Icono (clase FontAwesome)</label>
                <input type="text" id="cfg_valores_icono1" placeholder="fa-seedling">
            </div>
            <div class="form-group">
                <label>Valor 1 - Título</label>
                <input type="text" id="cfg_valores_titulo1" placeholder="Ingredientes Naturales">
            </div>
            <div class="form-group">
                <label>Valor 2 - Icono</label>
                <input type="text" id="cfg_valores_icono2" placeholder="fa-hand-holding-heart">
            </div>
            <div class="form-group">
                <label>Valor 2 - Título</label>
                <input type="text" id="cfg_valores_titulo2" placeholder="Hecho a Mano">
            </div>
            <div class="form-group">
                <label>Valor 3 - Icono</label>
                <input type="text" id="cfg_valores_icono3" placeholder="fa-award">
            </div>
            <div class="form-group">
                <label>Valor 3 - Título</label>
                <input type="text" id="cfg_valores_titulo3" placeholder="Calidad Premium">
            </div>
            <div class="save-row">
                <span class="save-msg" id="msg-valores"></span>
                <button class="btn btn-primary" onclick="saveConfig('valores')"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>

    <!-- APARIENCIA -->
    <div class="tab-content" id="tab-apariencia">
        <div class="config-card" style="max-width:100%;">
            <h3><i class="fas fa-palette"></i> Colores del Sitio</h3>
            <p style="color:var(--color-text-light);margin-bottom:20px;font-size:0.9rem;">Cambia los colores principales. Usa códigos hexadecimales (ej: #5D2E1B).</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>Color primario</label>
                    <input type="color" id="cfg_apariencia_color_primary" value="#5d2e1b">
                    <input type="text" id="cfg_apariencia_color_primary_txt" value="#5D2E1B" style="margin-top:4px;font-size:0.8rem;">
                </div>
                <div class="form-group">
                    <label>Color primario (hover)</label>
                    <input type="color" id="cfg_apariencia_color_primary_light" value="#7a3d28">
                    <input type="text" id="cfg_apariencia_color_primary_light_txt" value="#7A3D28" style="margin-top:4px;font-size:0.8rem;">
                </div>
                <div class="form-group">
                    <label>Color secundario</label>
                    <input type="color" id="cfg_apariencia_color_secondary" value="#c8956b">
                    <input type="text" id="cfg_apariencia_color_secondary_txt" value="#C8956B" style="margin-top:4px;font-size:0.8rem;">
                </div>
                <div class="form-group">
                    <label>Color acento</label>
                    <input type="color" id="cfg_apariencia_color_accent" value="#e8c9a8">
                    <input type="text" id="cfg_apariencia_color_accent_txt" value="#E8C9A8" style="margin-top:4px;font-size:0.8rem;">
                </div>
                <div class="form-group">
                    <label>Color de fondo</label>
                    <input type="color" id="cfg_apariencia_color_bg" value="#fff9f3">
                    <input type="text" id="cfg_apariencia_color_bg_txt" value="#FFF9F3" style="margin-top:4px;font-size:0.8rem;">
                </div>
                <div class="form-group">
                    <label>Color de fondo (secciones grises)</label>
                    <input type="color" id="cfg_apariencia_color_bg_gray" value="#f5ede6">
                    <input type="text" id="cfg_apariencia_color_bg_gray_txt" value="#F5EDE6" style="margin-top:4px;font-size:0.8rem;">
                </div>
                <div class="form-group">
                    <label>Color de fondo (sección oscura)</label>
                    <input type="color" id="cfg_apariencia_color_bg_brown" value="#3c1f10">
                    <input type="text" id="cfg_apariencia_color_bg_brown_txt" value="#3C1F10" style="margin-top:4px;font-size:0.8rem;">
                </div>
                <div class="form-group">
                    <label>Color de texto</label>
                    <input type="color" id="cfg_apariencia_color_text" value="#2c1810">
                    <input type="text" id="cfg_apariencia_color_text_txt" value="#2C1810" style="margin-top:4px;font-size:0.8rem;">
                </div>
                <div class="form-group">
                    <label>Color de texto (claro)</label>
                    <input type="color" id="cfg_apariencia_color_text_light" value="#6b4a3a">
                    <input type="text" id="cfg_apariencia_color_text_light_txt" value="#6B4A3A" style="margin-top:4px;font-size:0.8rem;">
                </div>
            </div>

            <h4 style="color:var(--color-primary);margin:24px 0 16px;"><i class="fas fa-palette"></i> Botones del Hero</h4>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>Color de fondo botón primario</label>
                    <input type="color" id="cfg_apariencia_btn_primary_bg" value="#5d2e1b">
                    <input type="text" id="cfg_apariencia_btn_primary_bg_txt" value="#5D2E1B" style="margin-top:4px;font-size:0.8rem;">
                </div>
                <div class="form-group">
                    <label>Color de texto botón primario</label>
                    <input type="color" id="cfg_apariencia_btn_primary_text" value="#ffffff">
                    <input type="text" id="cfg_apariencia_btn_primary_text_txt" value="#FFFFFF" style="margin-top:4px;font-size:0.8rem;">
                </div>
                <div class="form-group">
                    <label>Color borde botón secundario</label>
                    <input type="color" id="cfg_apariencia_btn_secondary_border" value="#5d2e1b">
                    <input type="text" id="cfg_apariencia_btn_secondary_border_txt" value="#5D2E1B" style="margin-top:4px;font-size:0.8rem;">
                </div>
                <div class="form-group">
                    <label>Color texto botón secundario</label>
                    <input type="color" id="cfg_apariencia_btn_secondary_text" value="#5d2e1b">
                    <input type="text" id="cfg_apariencia_btn_secondary_text_txt" value="#5D2E1B" style="margin-top:4px;font-size:0.8rem;">
                </div>
            </div>

            <div class="save-row">
                <span class="save-msg" id="msg-apariencia"></span>
                <button class="btn btn-primary" onclick="saveApariencia()"><i class="fas fa-save"></i> Guardar Colores</button>
            </div>
        </div>
    </div>

    <style>
        input[type="color"] { width:100%; height:44px; padding:2px; border:2px solid #e0ddd8; border-radius:8px; cursor:pointer; }
        input[type="color"]:hover { border-color:var(--color-secondary); }
    </style>

    <!-- SLIDER -->
    <div class="tab-content" id="tab-slider">
        <div class="config-card" style="max-width:100%;">
            <h3><i class="fas fa-images"></i> Slider del Hero</h3>
            <p style="color:var(--color-text-light);margin-bottom:16px;font-size:0.9rem;">Las imágenes se mostrarán como fondo del Hero, cambiando automáticamente.</p>

            <div class="form-group">
                <label>Intervalo entre imágenes (segundos)</label>
                <input type="number" id="cfg_slider_slider_intervalo" min="2" max="30" value="5">
            </div>

            <div style="display:flex;gap:12px;margin-bottom:24px;">
                <button class="btn btn-primary" onclick="saveSliderIntervalo()"><i class="fas fa-save"></i> Guardar Intervalo</button>
                <span class="save-msg" id="msg-slider"></span>
            </div>

            <h4 style="color:var(--color-primary);margin-bottom:12px;">Imágenes del Slider</h4>
            <div id="sliderList" style="display:flex;flex-direction:column;gap:12px;margin-bottom:16px;"></div>

            <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
                <div class="form-group" style="flex:0 0 auto;margin-bottom:0;">
                    <label>Subir imagen</label>
                    <input type="file" accept="image/*" onchange="uploadSliderImage(this)" style="font-size:0.85rem;">
                </div>
                <div class="form-group" style="flex:1;min-width:200px;margin-bottom:0;">
                    <label>O pegar URL</label>
                    <input type="text" id="newSliderUrl" placeholder="https://ejemplo.com/imagen.jpg">
                </div>
                <div class="form-group" style="width:80px;margin-bottom:0;">
                    <label>Orden</label>
                    <input type="number" id="newSliderOrden" value="0">
                </div>
                <button class="btn btn-primary" onclick="addSliderImage()"><i class="fas fa-plus"></i> Agregar</button>
            </div>
        </div>
    </div>

    <!-- ADMIN LOGIN -->
    <div class="tab-content" id="tab-adminlogin">
        <div class="config-card">
            <h3><i class="fas fa-lock"></i> Configuración de Admin</h3>
            <p style="color:var(--color-text-light);margin-bottom:16px;font-size:0.9rem;">Cambia el usuario y contraseña para acceder a este panel.</p>
            <div class="form-group">
                <label>Usuario actual: <strong id="current_user_display">admin</strong></label>
                <input type="text" id="cfg_admin_user" placeholder="Nuevo usuario">
            </div>
            <div class="form-group">
                <label>Nueva contraseña</label>
                <input type="text" id="cfg_admin_pass" placeholder="Nueva contraseña">
            </div>
            <div class="save-row">
                <span class="save-msg" id="msg-adminlogin"></span>
                <button class="btn btn-primary" onclick="saveAdminLogin()"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PRODUCTO -->
<div class="modal-overlay" id="productoModal">
    <div class="modal">
        <button class="modal-close" onclick="cerrarModal()">&times;</button>
        <h2 id="modalTitle" style="color:var(--color-primary);margin-bottom:24px;">Nuevo Producto</h2>
        <form id="productoForm">
            <input type="hidden" name="id" id="productoId">
            <div class="form-grid">
                <div class="form-group full">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" id="f_nombre" required>
                </div>
                <div class="form-group">
                    <label>Precio *</label>
                    <input type="number" name="precio" id="f_precio" step="0.01" required>
                </div>
                <div class="form-group form-group-checkbox">
                    <label>
                        <input type="checkbox" name="precio_a_convenir" id="f_precio_a_convenir" value="1" onchange="togglePrecio()">
                        Contactar al vendedor para precios
                    </label>
                </div>
                <div class="form-group">
                    <label>Categoría</label>
                    <div style="display:flex;gap:8px;">
                        <select name="categoria_id" id="f_categoria" style="flex:1;">
                            <option value="">Sin categoría</option>
                        </select>
                        <button type="button" class="btn-sm btn-edit" onclick="mostrarFormCategoria()" title="Nueva categoría" style="font-size:1.1rem;"><i class="fas fa-plus"></i></button>
                    </div>
                    <div id="newCatForm" style="display:none;margin-top:8px;gap:8px;flex-wrap:wrap;">
                        <input type="text" id="newCatNombre" placeholder="Nombre" style="flex:1;min-width:120px;padding:8px 12px;border:2px solid #e0ddd8;border-radius:6px;font-size:0.85rem;">
                        <button type="button" class="btn-sm btn-edit" onclick="crearCategoriaInline()" style="padding:8px 16px;">Crear</button>
                        <button type="button" class="btn-sm btn-delete" onclick="document.getElementById('newCatForm').style.display='none'" style="padding:8px 16px;">Cancelar</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" id="f_slug" placeholder="nombre-del-producto">
                </div>
                <div class="form-group">
                    <label>Imagen</label>
                    <input type="file" name="imagen" id="f_imagen" accept="image/*">
                    <input type="hidden" name="imagen_hidden" id="f_imagen_hidden">
                </div>
                <div class="form-group form-group-checkbox">
                    <label>
                        <input type="checkbox" name="destacado" id="f_destacado" value="1">
                        Destacado
                    </label>
                </div>
                <div class="form-group form-group-checkbox">
                    <label>
                        <input type="checkbox" name="activo" id="f_activo" value="1" checked>
                        Activo
                    </label>
                </div>
                <div class="form-group full">
                    <label>Descripción</label>
                    <textarea name="descripcion" id="f_descripcion" rows="3"></textarea>
                </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:20px;justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
const API_PROD = '../api/productos.php';
const API_CFG = '../api/config_site.php';
const API_UPDATE = '../api/update_config.php';
const API_CAT = '../api/categorias_admin.php';

 document.addEventListener('DOMContentLoaded', () => {
    cargarProductosAdmin();
    loadAllConfig();
    cargarCategoriasList();
});

function switchTab(name) {
    document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    event.target.closest('.admin-tab').classList.add('active');
    document.getElementById('tab-' + name).classList.add('active');
}

async function loadAllConfig() {
    try {
        const res = await fetch(API_CFG);
        const cfg = await res.json();
        if (cfg.hero) {
            document.getElementById('cfg_hero_titulo').value = cfg.hero.hero_titulo || '';
            document.getElementById('cfg_hero_descripcion').value = cfg.hero.hero_descripcion || '';
            const op = cfg.hero.hero_overlay_opacity || '9';
            document.getElementById('cfg_hero_overlay_opacity').value = op;
            document.getElementById('hero_opacity_val').textContent = op;
            const col = cfg.hero.hero_overlay_color || '#FFF9F3';
            document.getElementById('cfg_hero_overlay_color').value = col;
            document.getElementById('cfg_hero_overlay_color_txt').value = col;
        }
        if (cfg.nosotros) {
            document.getElementById('cfg_nosotros_titulo').value = cfg.nosotros.nosotros_titulo || '';
            document.getElementById('cfg_nosotros_texto1').value = cfg.nosotros.nosotros_texto1 || '';
            document.getElementById('cfg_nosotros_texto2').value = cfg.nosotros.nosotros_texto2 || '';
            const imgUrl = cfg.nosotros.nosotros_imagen_url || cfg.nosotros.nosotros_imagen || '';
            if (imgUrl) {
                document.getElementById('cfg_nosotros_imagen_url').value = imgUrl;
                document.getElementById('nosotrosImagenPreview').innerHTML = '<img src="' + imgUrl + '" style="width:100%;border-radius:8px;">';
            }
        }
        if (cfg.redes) {
            document.getElementById('cfg_redes_redes_facebook').value = cfg.redes.redes_facebook || '';
            document.getElementById('cfg_redes_redes_instagram').value = cfg.redes.redes_instagram || '';
            document.getElementById('cfg_redes_redes_whatsapp').value = cfg.redes.redes_whatsapp || '';
            document.getElementById('cfg_redes_redes_tiktok').value = cfg.redes.redes_tiktok || '';
        }
        if (cfg.general) {
            document.getElementById('cfg_general_site_nombre').value = cfg.general.site_nombre || '';
            document.getElementById('cfg_general_footer_copyright').value = cfg.general.footer_copyright || (cfg.footer ? cfg.footer.footer_copyright : '') || '';
            document.getElementById('cfg_general_logo_icon').value = cfg.general.logo_icon || '';
            document.getElementById('cfg_general_btn_primary').value = cfg.general.btn_primary || '';
            document.getElementById('cfg_general_logo_tipo').value = cfg.general.logo_tipo || 'icon';
            document.getElementById('cfg_general_logo_url').value = cfg.general.logo_url || '';
            const logo_tipo = cfg.general.logo_tipo || 'icon';
            document.getElementById('logoIconField').style.display = logo_tipo === 'image' ? 'none' : '';
            document.getElementById('logoImageField').style.display = logo_tipo === 'image' ? '' : 'none';
            if (logo_tipo === 'image' && cfg.general.logo_url) {
                document.getElementById('logoPreview').innerHTML = '<img src="' + cfg.general.logo_url + '" style="height:50px;border-radius:6px;" onerror="this.parentElement.innerHTML=\'<span style=color:red>Error</span>\'">';
            }
        }
        if (cfg.valores) {
            for (let i = 1; i <= 3; i++) {
                const icono = document.getElementById('cfg_valores_icono' + i);
                const titulo = document.getElementById('cfg_valores_titulo' + i);
                if (icono) icono.value = cfg.valores['icono' + i] || cfg.valores['valores_icono' + i] || '';
                if (titulo) titulo.value = cfg.valores['titulo' + i] || cfg.valores['valores_titulo' + i] || '';
            }
        }
        if (cfg.slider) {
            const intervalo = document.getElementById('cfg_slider_slider_intervalo');
            if (intervalo && cfg.slider.slider_intervalo) intervalo.value = cfg.slider.slider_intervalo;
        }
        if (cfg.apariencia) {
    const colorKeys = ['color_primary','color_primary_light','color_secondary','color_accent','color_bg','color_bg_gray','color_bg_brown','color_text','color_text_light','color_valor_icono','color_valor_texto','tamano_valor_icono','tamano_valor_texto','btn_primary_bg','btn_primary_text','btn_secondary_border','btn_secondary_text'];
            colorKeys.forEach(k => {
                const input = document.getElementById('cfg_apariencia_' + k);
                const txt = document.getElementById('cfg_apariencia_' + k + '_txt');
                if (input && cfg.apariencia['apariencia_' + k]) {
                    input.value = cfg.apariencia['apariencia_' + k];
                    if (txt) txt.value = cfg.apariencia['apariencia_' + k];
                }
            });
        }
    } catch (e) {
        console.error('Error loading config:', e);
    }
}

async function saveHero() {
    const msg = document.getElementById('msg-hero');
    const campos = [
        { key: 'hero_titulo', value: document.getElementById('cfg_hero_titulo').value },
        { key: 'hero_descripcion', value: document.getElementById('cfg_hero_descripcion').value },
        { key: 'hero_overlay_opacity', value: document.getElementById('cfg_hero_overlay_opacity').value },
        { key: 'hero_overlay_color', value: document.getElementById('cfg_hero_overlay_color_txt').value },
    ];
    let ok = true;
    for (const c of campos) {
        try {
            const r = await fetch(API_UPDATE, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({section:'hero', key:c.key, value:c.value}) });
            const d = await r.json();
            if (d.error) ok = false;
        } catch(e) { ok = false; }
    }
    msg.className = 'save-msg ' + (ok ? 'ok' : 'err');
    msg.textContent = ok ? 'Guardado correctamente' : 'Error';
    setTimeout(() => msg.textContent = '', 3000);
}

async function saveConfig(section) {
    const fields = document.querySelectorAll('#tab-' + section + ' .form-group input, #tab-' + section + ' .form-group textarea');
    const msgEl = document.getElementById('msg-' + section);
    let ok = true;

    for (const field of fields) {
        const rawKey = field.id.replace('cfg_' + section + '_', '');
        const key = rawKey.startsWith(section + '_') ? rawKey : section + '_' + rawKey;
        const value = field.value;
        try {
            const res = await fetch(API_UPDATE, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ section, key, value }),
            });
            const data = await res.json();
            if (data.error) ok = false;
        } catch (e) {
            ok = false;
        }
    }

    if (msgEl) {
        msgEl.className = 'save-msg ' + (ok ? 'ok' : 'err');
        msgEl.textContent = ok ? 'Guardado correctamente' : 'Error al guardar';
        setTimeout(() => { msgEl.textContent = ''; }, 3000);
    }
}

// ---- CRUD Productos (idéntico al original) ----

document.getElementById('productoForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('productoId').value;

    const data = {
        nombre: document.getElementById('f_nombre').value,
        slug: document.getElementById('f_slug').value || document.getElementById('f_nombre').value.toLowerCase().replace(/\s+/g, '-'),
        descripcion: document.getElementById('f_descripcion').value,
        precio: parseFloat(document.getElementById('f_precio').value) || 0,
        categoria_id: document.getElementById('f_categoria').value ? parseInt(document.getElementById('f_categoria').value) : null,
        destacado: document.getElementById('f_destacado').checked ? 1 : 0,
        activo: document.getElementById('f_activo').checked ? 1 : 0,
        precio_a_convenir: document.getElementById('f_precio_a_convenir').checked ? 1 : 0,
        imagen: document.getElementById('f_imagen_hidden').value || '',
    };

    const fileInput = document.getElementById('f_imagen');
    if (fileInput.files.length > 0) {
        const formData = new FormData();
        formData.append('imagen', fileInput.files[0]);
        try {
            const upRes = await fetch('../api/upload.php', { method: 'POST', body: formData });
            const upData = await upRes.json();
            if (upData.filename) data.imagen = upData.filename;
        } catch(e) {
            console.error('Error subiendo imagen:', e);
        }
    }

    try {
        let res;
        if (id) {
            res = await fetch(`${API_PROD}?id=${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });
        } else {
            res = await fetch(API_PROD, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });
        }
        const result = await res.json();
        if (!result.error) {
            cerrarModal();
            cargarProductosAdmin();
            alert(id ? 'Producto actualizado' : 'Producto creado');
        } else {
            alert('Error: ' + result.error);
        }
    } catch(e) {
        console.error('Error guardando producto:', e);
        alert('Error al guardar el producto');
    }
});

async function cargarProductosAdmin() {
    try {
        const res = await fetch(API_PROD + '?all=1');
        const data = await res.json();
        const productos = Array.isArray(data) ? data : (data.productos || []);
        const tbody = document.getElementById('adminProductos');
        tbody.innerHTML = productos.map(p => `
            <tr>
                <td>${p.id}</td>
                <td><strong>${p.nombre}</strong></td>
                <td>$${p.precio.toLocaleString('es-CL')}</td>
                <td>${p.categoria_nombre || '-'}</td>
                <td>${p.destacado ? '⭐' : '-'}</td>
                <td>${p.activo ? '✅' : '❌'}</td>
                <td>
                    <div class="admin-actions">
                        <button class="btn-sm btn-edit" onclick="editarProducto(${p.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-sm btn-delete" onclick="eliminarProducto(${p.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    } catch(e) {
        console.error('Error:', e);
    }
}

async function editarProducto(id) {
    try {
        const res = await fetch(`${API_PROD}?id=${id}&all=1`);
        const p = await res.json();
        if (p.error) return;

        document.getElementById('modalTitle').textContent = 'Editar Producto';
        document.getElementById('productoId').value = p.id;
        document.getElementById('f_nombre').value = p.nombre;
        document.getElementById('f_slug').value = p.slug;
        document.getElementById('f_descripcion').value = p.descripcion;
        document.getElementById('f_precio').value = p.precio;
        document.getElementById('f_categoria').value = p.categoria_id || '';
        document.getElementById('f_precio_a_convenir').checked = p.precio_a_convenir == 1;
        document.getElementById('f_destacado').checked = p.destacado == 1;
        document.getElementById('f_activo').checked = p.activo == 1;
        document.getElementById('f_imagen_hidden').value = p.imagen || '';
        togglePrecio();
        abrirModal();
    } catch(e) {
        console.error('Error:', e);
    }
}

async function eliminarProducto(id) {
    if (!confirm('¿Eliminar este producto?')) return;
    try {
        const res = await fetch(`${API_PROD}?id=${id}`, { method: 'DELETE' });
        const data = await res.json();
        if (!data.error) {
            cargarProductosAdmin();
        }
    } catch(e) {
        console.error('Error:', e);
    }
}

function abrirFormulario() {
    document.getElementById('modalTitle').textContent = 'Nuevo Producto';
    document.getElementById('productoForm').reset();
    document.getElementById('productoId').value = '';
    document.getElementById('f_activo').checked = true;
    document.getElementById('f_precio_a_convenir').checked = false;
    togglePrecio();
    abrirModal();
}

function abrirModal() {
    cargarSelectCategorias();
    document.getElementById('productoModal').classList.add('open');
}

function cerrarModal() {
    document.getElementById('productoModal').classList.remove('open');
}

async function cargarSelectCategorias(selectedId) {
    try {
        const res = await fetch(API_CAT);
        const cats = await res.json();
        const select = document.getElementById('f_categoria');
        if (!select) return;
        select.innerHTML = '<option value="">Sin categoría</option>';
        cats.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nombre;
            if (selectedId && c.id == selectedId) opt.selected = true;
            select.appendChild(opt);
        });
    } catch(e) {
        console.error('Error:', e);
    }
}

function mostrarFormCategoria() {
    document.getElementById('newCatForm').style.display = 'flex';
    document.getElementById('newCatNombre').focus();
}

async function crearCategoriaInline() {
    const nombre = document.getElementById('newCatNombre').value.trim();
    if (!nombre) { alert('Nombre requerido'); return; }
    try {
        const res = await fetch(API_CAT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre }),
        });
        const data = await res.json();
        if (data.error) { alert('Error: ' + data.error); return; }
        document.getElementById('newCatNombre').value = '';
        document.getElementById('newCatForm').style.display = 'none';
        await cargarSelectCategorias(data.id);
    } catch(e) { console.error(e); }
}

// ---- CATEGORÍAS (listado en productos) ----
async function cargarCategoriasList() {
    try {
        const res = await fetch(API_CAT);
        const cats = await res.json();
        const tbody = document.getElementById('adminCategoriasList');
        if (!tbody) return;
        tbody.innerHTML = cats.map(c => `
            <tr>
                <td>${c.id}</td>
                <td><input type="text" id="cat_name_${c.id}" value="${c.nombre}" style="padding:6px 10px;border:1px solid #ddd;border-radius:6px;font-size:0.85rem;width:100%;"></td>
                <td>${c.slug}</td>
                <td>${c.total_productos || 0}</td>
                <td>
                    <div class="admin-actions">
                        <button class="btn-sm btn-edit" onclick="actualizarCategoria(${c.id})"><i class="fas fa-save"></i></button>
                        <button class="btn-sm btn-delete" onclick="eliminarCategoria(${c.id})"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
    } catch(e) { console.error(e); }
}

async function crearCategoria() {
    const nombre = document.getElementById('catNombreInput').value.trim();
    if (!nombre) { alert('Nombre requerido'); return; }
    try {
        const res = await fetch(API_CAT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre }),
        });
        const data = await res.json();
        if (data.error) { alert('Error: ' + data.error); return; }
        document.getElementById('catNombreInput').value = '';
        cargarCategoriasList();
        cargarSelectCategorias(data.id);
    } catch(e) { console.error(e); }
}

async function actualizarCategoria(id) {
    const nombre = document.getElementById('cat_name_' + id).value.trim();
    if (!nombre) { alert('Nombre requerido'); return; }
    try {
        const res = await fetch(API_CAT + '?id=' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre }),
        });
        const data = await res.json();
        if (data.error) { alert('Error: ' + data.error); return; }
        cargarCategoriasList();
        cargarSelectCategorias();
    } catch(e) { console.error(e); }
}

async function eliminarCategoria(id) {
    if (!confirm('¿Eliminar esta categoría?')) return;
    try {
        const res = await fetch(API_CAT + '?id=' + id, { method: 'DELETE' });
        const data = await res.json();
        if (data.error) { alert('Error: ' + data.error); return; }
        cargarCategoriasList();
        cargarSelectCategorias();
    } catch(e) { console.error(e); }
}

document.getElementById('productoModal').addEventListener('click', (e) => {
    if (e.target === document.getElementById('productoModal')) cerrarModal();
});

async function saveAdminLogin() {
    const user = document.getElementById('cfg_admin_user').value;
    const pass = document.getElementById('cfg_admin_pass').value;
    const msg = document.getElementById('msg-adminlogin');
    if (!user && !pass) {
        msg.textContent = 'Llena al menos un campo';
        msg.className = 'save-msg err';
        setTimeout(() => msg.textContent = '', 3000);
        return;
    }
    let ok = true;
    if (user) {
        const r = await fetch(API_UPDATE, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ section: 'admin', key: 'admin_user', value: user }) });
        const d = await r.json();
        if (d.error) ok = false;
    }
    if (pass) {
        const r = await fetch(API_UPDATE, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ section: 'admin', key: 'admin_pass', value: pass }) });
        const d = await r.json();
        if (d.error) ok = false;
    }
    msg.className = 'save-msg ' + (ok ? 'ok' : 'err');
    msg.textContent = ok ? 'Credenciales guardadas' : 'Error';
    setTimeout(() => msg.textContent = '', 3000);
}

// ---- SLIDER ----
const API_SLIDER = '../api/slider.php';

document.addEventListener('DOMContentLoaded', () => {
    cargarSliderAdmin();
});

async function cargarSliderAdmin() {
    try {
        const res = await fetch(API_SLIDER);
        const images = await res.json();
        const list = document.getElementById('sliderList');
        list.innerHTML = images.map(img => `
            <div style="display:flex;align-items:center;gap:12px;padding:12px;background:var(--color-bg-gray);border-radius:8px;">
                <img src="${img.imagen}" style="width:80px;height:60px;object-fit:cover;border-radius:6px;" onerror="this.style.display='none'">
                <div style="flex:1;min-width:0;">
                    <input type="text" value="${img.imagen}" id="slider_img_${img.id}" style="width:100%;padding:6px 10px;border:1px solid #ddd;border-radius:6px;font-size:0.85rem;">
                </div>
                <input type="number" value="${img.orden}" id="slider_ord_${img.id}" style="width:60px;padding:6px;border:1px solid #ddd;border-radius:6px;font-size:0.85rem;">
                <button class="btn-sm btn-edit" onclick="updateSliderImage(${img.id})"><i class="fas fa-save"></i></button>
                <button class="btn-sm btn-delete" onclick="deleteSliderImage(${img.id})"><i class="fas fa-trash"></i></button>
            </div>
        `).join('');
    } catch(e) {
        console.error('Error loading slider admin:', e);
    }
}

async function updateSliderImage(id) {
    const url = document.getElementById('slider_img_' + id).value;
    const orden = parseInt(document.getElementById('slider_ord_' + id).value) || 0;
    try {
        await fetch(`${API_SLIDER}?id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ imagen: url, orden }),
        });
        cargarSliderAdmin();
    } catch(e) {
        console.error(e);
    }
}

async function deleteSliderImage(id) {
    if (!confirm('Eliminar esta imagen del slider?')) return;
    try {
        await fetch(`${API_SLIDER}?id=${id}`, { method: 'DELETE' });
        cargarSliderAdmin();
    } catch(e) {
        console.error(e);
    }
}

async function uploadSliderImage(input) {
    if (!input.files.length) return;
    const formData = new FormData();
    formData.append('imagen', input.files[0]);
    try {
        const res = await fetch('../api/upload.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.filename) {
            document.getElementById('newSliderUrl').value = '/uploads/' + data.filename;
        }
    } catch(e) {
        console.error(e);
    }
    input.value = '';
}

async function uploadNosotrosImagen(input) {
    if (!input.files.length) return;
    const formData = new FormData();
    formData.append('imagen', input.files[0]);
    try {
        const res = await fetch('../api/upload.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.filename) {
            const url = '/uploads/' + data.filename;
            document.getElementById('cfg_nosotros_imagen_url').value = url;
            document.getElementById('nosotrosImagenPreview').innerHTML = '<img src="' + url + '" style="width:100%;border-radius:8px;">';
        }
    } catch(e) {
        console.error(e);
    }
    input.value = '';
}

async function addSliderImage() {
    const url = document.getElementById('newSliderUrl').value;
    const orden = parseInt(document.getElementById('newSliderOrden').value) || 0;
    if (!url) { alert('Ingresa una URL de imagen'); return; }
    try {
        await fetch(API_SLIDER, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ imagen: url, orden }),
        });
        document.getElementById('newSliderUrl').value = '';
        document.getElementById('newSliderOrden').value = '0';
        cargarSliderAdmin();
    } catch(e) {
        console.error(e);
    }
}

async function saveSliderIntervalo() {
    const value = document.getElementById('cfg_slider_slider_intervalo').value;
    const msg = document.getElementById('msg-slider');
    try {
        const r = await fetch(API_UPDATE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ section: 'slider', key: 'slider_intervalo', value }),
        });
        const d = await r.json();
        msg.className = 'save-msg ' + (d.error ? 'err' : 'ok');
        msg.textContent = d.error ? 'Error' : 'Guardado';
        setTimeout(() => msg.textContent = '', 3000);
    } catch(e) {
        msg.className = 'save-msg err';
        msg.textContent = 'Error';
    }
}

// ---- APARIENCIA (colores) ----
function syncApariencia(key) {
    const input = document.getElementById('cfg_apariencia_' + key);
    const txt = document.getElementById('cfg_apariencia_' + key + '_txt');
    if (input && txt) txt.value = input.value.toUpperCase();
}

function setupAparienciaSync() {
    const colorKeys = ['color_primary','color_primary_light','color_secondary','color_accent','color_bg','color_bg_gray','color_bg_brown','color_text','color_text_light'];
    colorKeys.forEach(k => {
        const input = document.getElementById('cfg_apariencia_' + k);
        if (input) {
            input.addEventListener('input', () => syncApariencia(k));
        }
    });
}

async function saveApariencia() {
    const colorKeys = ['color_primary','color_primary_light','color_secondary','color_accent','color_bg','color_bg_gray','color_bg_brown','color_text','color_text_light'];
    const msg = document.getElementById('msg-apariencia');
    let ok = true;

    for (const key of colorKeys) {
        const txt = document.getElementById('cfg_apariencia_' + key + '_txt');
        if (!txt) continue;
        const value = txt.value.trim();
        if (!value) continue;
        try {
            const r = await fetch(API_UPDATE, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ section: 'apariencia', key: 'apariencia_' + key, value: value }) });
            const d = await r.json();
            if (d.error) ok = false;
        } catch(e) {
            ok = false;
        }
    }

    msg.className = 'save-msg ' + (ok ? 'ok' : 'err');
    msg.textContent = ok ? 'Colores guardados' : 'Error al guardar';
    setTimeout(() => msg.textContent = '', 3000);
}

// call setup on DOMContentLoaded
document.addEventListener('DOMContentLoaded', setupAparienciaSync);
document.addEventListener('DOMContentLoaded', function() {
    const hc = document.getElementById('cfg_hero_overlay_color');
    const ht = document.getElementById('cfg_hero_overlay_color_txt');
    if (hc && ht) hc.addEventListener('input', function() { if (ht) ht.value = this.value.toUpperCase(); });
});

async function saveGeneral() {
    const msg = document.getElementById('msg-general');
    const campos = [
        { key: 'site_nombre', value: document.getElementById('cfg_general_site_nombre').value },
        { key: 'footer_copyright', value: document.getElementById('cfg_general_footer_copyright').value },
        { key: 'logo_tipo', value: document.getElementById('cfg_general_logo_tipo').value },
        { key: 'logo_icon', value: document.getElementById('cfg_general_logo_icon').value },
        { key: 'logo_url', value: document.getElementById('cfg_general_logo_url').value },
        { key: 'btn_primary', value: document.getElementById('cfg_general_btn_primary').value },
        { key: 'btn_secondary', value: document.getElementById('cfg_general_btn_secondary').value },
    ];
    let ok = true;
    for (const { key, value } of campos) {
        try {
            const r = await fetch(API_UPDATE, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ section: 'general', key, value }) });
            const d = await r.json();
            if (d.error) ok = false;
        } catch (e) { ok = false; }
    }
    msg.className = 'save-msg ' + (ok ? 'ok' : 'err');
    msg.textContent = ok ? 'Guardado correctamente' : 'Error al guardar';
    setTimeout(() => msg.textContent = '', 3000);
}

async function uploadLogo(input) {
    if (!input.files.length) return;
    const formData = new FormData();
    formData.append('imagen', input.files[0]);
    try {
        const res = await fetch('../api/upload.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.filename) {
            const url = '/uploads/' + data.filename;
            document.getElementById('cfg_general_logo_url').value = url;
            document.getElementById('logoPreview').innerHTML = '<img src="' + url + '" style="height:50px;border-radius:6px;">';
        }
    } catch(e) {
        console.error('Error uploading logo:', e);
    }
}

function toggleLogoTipo() {
    const tipo = document.getElementById('cfg_general_logo_tipo').value;
    document.getElementById('logoIconField').style.display = tipo === 'image' ? 'none' : '';
    document.getElementById('logoImageField').style.display = tipo === 'image' ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const logoUrlInput = document.getElementById('cfg_general_logo_url');
    if (logoUrlInput) {
        logoUrlInput.addEventListener('input', function() {
            const preview = document.getElementById('logoPreview');
            if (this.value) {
                preview.innerHTML = '<img src="' + this.value + '" style="height:50px;border-radius:6px;" onerror="this.parentElement.innerHTML=\'<span style=color:red>Error, la imagen no cargó</span>\'">';
            } else {
                preview.innerHTML = '';
            }
        });
    }
});

function togglePrecio() {
    const chk = document.getElementById('f_precio_a_convenir');
    const precio = document.getElementById('f_precio');
    if (chk.checked) {
        precio.disabled = true;
        precio.value = '';
    } else {
        precio.disabled = false;
    }
}
</script>
<?php endif; ?>
</body>
</html>
