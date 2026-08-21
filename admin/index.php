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
        <div class="admin-toolbar">
            <h2><i class="fas fa-box"></i> Categorías y Productos</h2>
        </div>
        <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
            <input type="text" id="catNombreInput" placeholder="Nueva categoría..." style="flex:1;min-width:200px;padding:10px 14px;border:2px solid #e0ddd8;border-radius:8px;font-size:0.9rem;">
            <input type="number" id="catOrdenInput" placeholder="Orden" value="0" min="0" style="width:80px;padding:10px 14px;border:2px solid #e0ddd8;border-radius:8px;font-size:0.9rem;text-align:center;">
            <button class="btn btn-primary" onclick="crearCategoria()" style="white-space:nowrap;"><i class="fas fa-plus"></i> Agregar</button>
        </div>
        <div id="acordeonCategorias"></div>
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

        .cat-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            background: var(--color-white);
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .cat-header:hover { box-shadow: var(--shadow-hover); }
        .cat-header .cat-arrow {
            font-size: 0.8rem;
            color: var(--color-primary);
            transition: transform 0.3s ease;
            min-width: 20px;
            text-align: center;
        }
        .cat-header.open .cat-arrow { transform: rotate(90deg); }
        .cat-header .cat-name {
            font-weight: 700;
            color: var(--color-primary);
            font-size: 1rem;
            flex: 1;
        }
        .cat-header .cat-count {
            background: var(--color-accent);
            color: var(--color-primary);
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .cat-header .cat-orden {
            width: 50px;
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.8rem;
            text-align: center;
        }
        .cat-header .cat-actions { display: flex; gap: 6px; }

        .cat-body {
            display: none;
            padding: 12px 16px 16px;
            background: var(--color-bg-gray);
            border-radius: 0 0 10px 10px;
            margin-top: -8px;
            margin-bottom: 12px;
        }
        .cat-body.open { display: block; }

        .cat-add-product {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 12px;
        }

        .prod-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            background: var(--color-white);
            border-radius: 8px;
            margin-bottom: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .prod-card img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            background: #eee;
        }
        .prod-card .prod-info { flex: 1; min-width: 0; }
        .prod-card .prod-name { font-weight: 600; color: var(--color-text); font-size: 0.9rem; }
        .prod-card .prod-price { color: var(--color-secondary); font-size: 0.85rem; }
        .prod-card .prod-badges { display: flex; gap: 6px; margin-top: 4px; }
        .prod-card .badge {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
        }
        .prod-card .badge-active { background: #d4edda; color: #155724; }
        .prod-card .badge-inactive { background: #f8d7da; color: #721c24; }
        .prod-card .badge-featured { background: #fff3cd; color: #856404; }
        .prod-card .prod-actions { display: flex; gap: 6px; }
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
                    <input type="number" name="precio" id="f_precio" step="0.01">
                </div>
                <div class="form-group form-group-checkbox">
                    <label>
                        <input type="checkbox" name="precio_a_convenir" id="f_precio_a_convenir" value="1" onchange="togglePrecio()">
                        Contactar al vendedor para precios
                    </label>
                </div>
                <div class="form-group">
                    <label>Categoría</label>
                    <select name="categoria_id" id="f_categoria">
                        <option value="">Sin categoría</option>
                    </select>
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
    loadAllConfig();
    cargarAcordeon();
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

// ---- CRUD Productos ----

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
        } catch(e) { console.error('Error subiendo imagen:', e); }
    }
    try {
        let res;
        if (id) {
            res = await fetch(`${API_PROD}?id=${id}`, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
        } else {
            res = await fetch(API_PROD, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
        }
        const result = await res.json();
        if (!result.error) { cerrarModal(); await cargarAcordeon(); alert(id ? 'Producto actualizado' : 'Producto creado'); }
        else { alert('Error del servidor: ' + result.error); }
    } catch(e) { console.error('Error guardando producto:', e); alert('Error al guardar'); }
});

function abrirModal() { document.getElementById('productoModal').classList.add('open'); }
function cerrarModal() { document.getElementById('productoModal').classList.remove('open'); }

function abrirFormulario() {
    document.getElementById('modalTitle').textContent = 'Nuevo Producto';
    document.getElementById('productoForm').reset();
    document.getElementById('productoId').value = '';
    document.getElementById('f_activo').checked = true;
    document.getElementById('f_precio_a_convenir').checked = false;
    togglePrecio();
    cargarSelectCategorias().then(() => abrirModal());
}

function abrirFormularioParaCategoria(catId) {
    document.getElementById('modalTitle').textContent = 'Nuevo Producto';
    document.getElementById('productoForm').reset();
    document.getElementById('productoId').value = '';
    document.getElementById('f_activo').checked = true;
    document.getElementById('f_precio_a_convenir').checked = false;
    togglePrecio();
    cargarSelectCategorias(catId);
    document.getElementById('f_categoria').value = catId;
    abrirModal();
}

async function cargarSelectCategorias(selectedId) {
    try {
        const res = await fetch(API_CAT + '?v=' + Date.now());
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
    } catch(e) { console.error(e); }
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
        document.getElementById('f_precio_a_convenir').checked = p.precio_a_convenir == 1;
        document.getElementById('f_destacado').checked = p.destacado == 1;
        document.getElementById('f_activo').checked = p.activo == 1;
        document.getElementById('f_imagen_hidden').value = p.imagen || '';
        togglePrecio();
        await cargarSelectCategorias(p.categoria_id);
        abrirModal();
    } catch(e) { console.error(e); }
}

async function eliminarProducto(id) {
    if (!confirm('¿Eliminar este producto?')) return;
    try {
        const res = await fetch(`${API_PROD}?id=${id}`, { method: 'DELETE' });
        const data = await res.json();
        if (!data.error) cargarAcordeon();
    } catch(e) { console.error(e); }
}

document.getElementById('productoModal').addEventListener('click', (e) => {
    if (e.target === document.getElementById('productoModal')) cerrarModal();
});

// ---- ACORDEÓN ----
async function cargarAcordeon() {
    try {
        const t = Date.now();
        const [catsRes, prodsRes] = await Promise.all([fetch(API_CAT + '?v=' + t), fetch(API_PROD + '?all=1&t=' + t)]);
        const cats = await catsRes.json();
        const prodsData = await prodsRes.json();
        const prods = Array.isArray(prodsData) ? prodsData : (prodsData.productos || []);
        const container = document.getElementById('acordeonCategorias');
        if (!container) return;
        if (!cats.length) {
            container.innerHTML = '<p style="color:var(--color-text-light);text-align:center;padding:40px;">No hay categorías. Crea una arriba.</p>';
            return;
        }
        container.innerHTML = cats.map(c => {
            const catProds = prods.filter(p => p.categoria_id == c.id);
            const safeNombre = c.nombre.replace(/'/g, "\\'");
            return `<div class="cat-item" data-id="${c.id}">
                <div class="cat-header" onclick="toggleAcordeon(${c.id})">
                    <span class="cat-arrow"><i class="fas fa-chevron-right"></i></span>
                    <span class="cat-name">${c.nombre}</span>
                    <span class="cat-count">${catProds.length} productos</span>
                    <input type="number" class="cat-orden" id="orden_${c.id}" value="${c.orden || 0}" min="0" onclick="event.stopPropagation()">
                    <button class="btn-sm btn-edit" onclick="guardarOrdenCat(${c.id})" title="Guardar posición"><i class="fas fa-save"></i></button>
                    <div class="cat-actions" onclick="event.stopPropagation()">
                        <button class="btn-sm btn-edit" onclick="editarCategoriaPrompt(${c.id}, '${safeNombre}', ${c.orden || 0})" title="Editar"><i class="fas fa-pen"></i></button>
                        <button class="btn-sm btn-delete" onclick="eliminarCategoria(${c.id})" title="Eliminar"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <div class="cat-body" id="cat-body-${c.id}">
                    <div class="cat-add-product">
                        <button class="btn btn-primary btn-sm" onclick="abrirFormularioParaCategoria(${c.id})"><i class="fas fa-plus"></i> Nuevo Producto</button>
                    </div>
                    ${catProds.length ? catProds.map(p => `<div class="prod-card">
                        <img src="${p.imagen || ''}" alt="${p.nombre}" onerror="this.style.display='none'">
                        <div class="prod-info">
                            <div class="prod-name">${p.nombre}</div>
                            <div class="prod-price">${p.precio_a_convenir ? 'A convenir' : '$' + (p.precio || 0).toLocaleString('es-CL')}</div>
                            <div class="prod-badges">
                                <span class="badge ${p.activo ? 'badge-active' : 'badge-inactive'}">${p.activo ? 'Activo' : 'Inactivo'}</span>
                                ${p.destacado ? '<span class="badge badge-featured">Destacado</span>' : ''}
                            </div>
                        </div>
                        <div class="prod-actions">
                            <button class="btn-sm btn-edit" onclick="editarProducto(${p.id})" title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn-sm btn-delete" onclick="eliminarProducto(${p.id})" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>`).join('') : '<p style="color:var(--color-text-light);text-align:center;padding:20px;font-size:0.9rem;">No hay productos</p>'}
                </div>
            </div>`;
        }).join('');

        const orphans = prods.filter(p => !p.categoria_id);
        if (orphans.length) {
            container.innerHTML += `<div class="cat-item" data-id="orphans" style="border:2px dashed #e74c3c;">
                <div class="cat-header" onclick="toggleAcordeon('orphans')" style="background:#fff5f5;">
                    <span class="cat-arrow"><i class="fas fa-chevron-right"></i></span>
                    <span class="cat-name" style="color:#e74c3c;">Sin categoría (huérfanos)</span>
                    <span class="cat-count" style="background:#f8d7da;color:#721c24;">${orphans.length} productos</span>
                </div>
                <div class="cat-body" id="cat-body-orphans">
                    ${orphans.map(p => `<div class="prod-card">
                        <img src="${p.imagen || ''}" alt="${p.nombre}" onerror="this.style.display='none'">
                        <div class="prod-info">
                            <div class="prod-name">${p.nombre}</div>
                            <div class="prod-price">${p.precio_a_convenir ? 'A convenir' : '$' + (p.precio || 0).toLocaleString('es-CL')}</div>
                            <div class="prod-badges">
                                <span class="badge ${p.activo ? 'badge-active' : 'badge-inactive'}">${p.activo ? 'Activo' : 'Inactivo'}</span>
                                ${p.destacado ? '<span class="badge badge-featured">Destacado</span>' : ''}
                            </div>
                        </div>
                        <div class="prod-actions">
                            <button class="btn-sm btn-edit" onclick="editarProducto(${p.id})" title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn-sm btn-delete" onclick="eliminarProducto(${p.id})" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>`).join('')}
                </div>
            </div>`;
        }
    } catch(e) { console.error('Error acordeón:', e); }
}

function toggleAcordeon(catId) {
    const header = document.querySelector('.cat-item[data-id="' + catId + '"] .cat-header');
    const body = document.getElementById('cat-body-' + catId);
    if (header && body) { header.classList.toggle('open'); body.classList.toggle('open'); }
}

function editarCategoriaPrompt(id, nombre, orden) {
    const n = prompt('Nombre:', nombre);
    if (n === null || !n.trim()) return;
    const o = prompt('Orden:', orden);
    if (o === null) return;
    fetch(API_CAT + '?id=' + id, { method: 'PUT', headers: {'Content-Type':'application/json'}, body: JSON.stringify({nombre: n.trim(), orden: parseInt(o)||0}) })
        .then(r => r.json()).then(() => cargarAcordeon());
}

async function guardarOrdenCat(id) {
    const valor = document.getElementById('orden_' + id).value;
    try {
        const res = await fetch(API_CAT + '?id=' + id, { method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify({orden: parseInt(valor)||0}) });
        const data = await res.json();
        if (!data.error) alert('Posición guardada');
    } catch(e) { alert('Error al guardar'); }
}

async function crearCategoria() {
    const nombre = document.getElementById('catNombreInput').value.trim();
    const orden = parseInt(document.getElementById('catOrdenInput').value) || 0;
    if (!nombre) { alert('Nombre requerido'); return; }
    try {
        const res = await fetch(API_CAT, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({nombre, orden}) });
        const data = await res.json();
        if (data.error) { alert('Error: ' + data.error); return; }
        document.getElementById('catNombreInput').value = '';
        document.getElementById('catOrdenInput').value = '0';
        cargarAcordeon();
    } catch(e) { console.error(e); }
}

async function eliminarCategoria(id) {
    if (!confirm('¿Eliminar esta categoría y todos sus productos?')) return;
    try {
        const res = await fetch(API_CAT + '?id=' + id, { method:'DELETE' });
        const data = await res.json();
        if (data.error) { alert('Error: ' + data.error); return; }
        cargarAcordeon();
    } catch(e) { console.error(e); }
}

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
