<?php
require_once __DIR__ . '/includes/funciones.php';
$usuario = requiere_login();
$titulo = 'Catalogo de equipos';

$cat = $_GET['categoria'] ?? '';
if (!in_array($cat, CATEGORIAS, true)) {
    $cat = '';
}

if ($cat !== '') {
    $st = db()->prepare('SELECT nombre, codigo_inventario, categoria, estado FROM equipos WHERE categoria = ? ORDER BY nombre');
    $st->execute([$cat]);
} else {
    $st = db()->query('SELECT nombre, codigo_inventario, categoria, estado FROM equipos ORDER BY categoria, nombre');
}
$equipos = $st->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<h1>Catalogo de equipos</h1>

<form method="get" class="filtros">
    <label for="categoria">Categoria</label>
    <select name="categoria" id="categoria" onchange="this.form.submit()">
        <option value="">Todas</option>
        <?php foreach (CATEGORIAS as $c): ?>
            <option value="<?= e($c) ?>" <?= $c === $cat ? 'selected' : '' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
    </select>
    <noscript><button class="btn btn-primary" type="submit">Filtrar</button></noscript>
</form>

<?php if (!$equipos): ?>
    <div class="alert alert-info">No hay equipos disponibles en esta categoría</div>
<?php else: ?>
    <div class="tabla-wrap">
        <table class="tabla">
            <thead>
                <tr><th>Equipo</th><th>Codigo de inventario</th><th>Categoria</th><th>Estado</th></tr>
            </thead>
            <tbody>
            <?php foreach ($equipos as $q): ?>
                <tr>
                    <td data-label="Equipo"><?= e($q['nombre']) ?></td>
                    <td data-label="Codigo"><?= e($q['codigo_inventario']) ?></td>
                    <td data-label="Categoria"><?= e($q['categoria']) ?></td>
                    <td data-label="Estado"><?= badge($q['estado']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if ($usuario['rol'] === 'estudiante'): ?>
    <p><a class="btn btn-primary" href="<?= e(base_url('reservar.php')) ?>">Reservar un equipo</a></p>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
