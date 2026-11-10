<?php
require_once __DIR__ . '/includes/funciones.php';
$usuario = requiere_login('estudiante');
$titulo = 'Mis reservas';

$st = db()->prepare('SELECT r.*, e.nombre AS equipo, e.codigo_inventario
    FROM reservas r JOIN equipos e ON e.id = r.equipo_id
    WHERE r.usuario_id = ? ORDER BY r.creado_en DESC, r.id DESC');
$st->execute([$usuario['id']]);
$reservas = $st->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<h1>Mis reservas</h1>
<p>Estado de su cuenta: <?= badge($usuario['estado']) ?></p>

<?php if (!$reservas): ?>
    <div class="alert alert-info">Aun no tiene reservas registradas.</div>
<?php else: ?>
<div class="tabla-wrap">
    <table class="tabla">
        <thead><tr><th>Equipo</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Detalle</th></tr></thead>
        <tbody>
        <?php foreach ($reservas as $r): ?>
            <tr>
                <td data-label="Equipo"><?= e($r['equipo']) ?> <span class="muted small">(<?= e($r['codigo_inventario']) ?>)</span></td>
                <td data-label="Inicio"><?= e(fmt_fecha($r['fecha_inicio'])) ?></td>
                <td data-label="Fin"><?= e(fmt_fecha($r['fecha_fin'])) ?></td>
                <td data-label="Estado"><?= badge($r['estado']) ?></td>
                <td data-label="Detalle">
                    <?php if ($r['estado'] === 'Rechazada'): ?>
                        Motivo: <?= e($r['motivo_rechazo']) ?>
                    <?php elseif ($r['estado'] === 'Devuelta'): ?>
                        Devuelto: <?= e(fmt_fecha($r['fecha_devolucion'])) ?><?= $r['penalizada'] ? ' (con retraso, penalizado)' : '' ?>
                    <?php else: ?>-<?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
