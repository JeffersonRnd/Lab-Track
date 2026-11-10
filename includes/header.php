<?php
// Requiere $titulo y $usuario (opcional) definidos antes de incluir
require_once __DIR__ . '/auth.php';
$usuario = $usuario ?? usuario_actual();
$actual = basename($_SERVER['SCRIPT_NAME']);
function nav_link(string $href, string $texto, string $actual): string
{
    $activo = basename($href) === $actual ? ' class="activo"' : '';
    return '<a href="' . e(base_url($href)) . '"' . $activo . '>' . e($texto) . '</a>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Panel') ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= e(base_url('assets/css/style.css')) ?>">
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <a class="brand" href="<?= e(base_url('index.php')) ?>"><?= APP_NAME ?></a>
        <?php if ($usuario): ?>
            <button class="nav-toggle" type="button" aria-label="Abrir menu" aria-expanded="false" id="navToggle">Menu</button>
            <nav class="nav" id="nav">
                <?php if ($usuario['rol'] === 'estudiante'): ?>
                    <?= nav_link('catalogo.php', 'Catalogo', $actual) ?>
                    <?= nav_link('reservar.php', 'Reservar', $actual) ?>
                    <?= nav_link('mis_reservas.php', 'Mis reservas', $actual) ?>
                <?php else: ?>
                    <?= nav_link('admin/solicitudes.php', 'Solicitudes', $actual) ?>
                    <?= nav_link('admin/devoluciones.php', 'Devoluciones', $actual) ?>
                    <?= nav_link('admin/estudiantes.php', 'Estudiantes', $actual) ?>
                    <?= nav_link('catalogo.php', 'Catalogo', $actual) ?>
                <?php endif; ?>
                <span class="nav-user"><?= e($usuario['nombre']) ?></span>
                <a class="btn-salir" href="<?= e(base_url('logout.php')) ?>">Salir</a>
            </nav>
        <?php endif; ?>
    </div>
</header>
<main class="container">
<?= flash_render() ?>
