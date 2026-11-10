<?php
require_once __DIR__ . '/../includes/funciones.php';
$usuario = requiere_login('encargado');
$titulo = 'Solicitudes pendientes';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $id = (int)($_POST['reserva_id'] ?? 0);
    $accion = $_POST['accion'] ?? '';

    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare("SELECT * FROM reservas WHERE id = ? AND estado = 'Pendiente' FOR UPDATE");
        $st->execute([$id]);
        $r = $st->fetch();

        if (!$r) {
            flash('error', 'La solicitud ya no esta pendiente.');
            $pdo->rollBack();
        } elseif ($accion === 'aprobar') {
            if (hay_cruce($pdo, (int)$r['equipo_id'], $r['fecha_inicio'], $r['fecha_fin'], $id)) {
                flash('error', 'No se puede aprobar: existe otra reserva aprobada que se cruza con ese horario.');
                $pdo->rollBack();
            } else {
                $pdo->prepare("UPDATE reservas SET estado = 'Aprobada' WHERE id = ?")->execute([$id]);
                $pdo->prepare("UPDATE equipos SET estado = 'Prestado' WHERE id = ?")->execute([$r['equipo_id']]);
                $pdo->commit();
                flash('success', 'Reserva aprobada. El equipo paso a estado Prestado.');
            }
        } elseif ($accion === 'rechazar') {
            $motivo = trim($_POST['motivo'] ?? '');
            if ($motivo === '') {
                flash('error', 'Debe indicar un motivo para rechazar la solicitud.');
                $pdo->rollBack();
            } else {
                $pdo->prepare("UPDATE reservas SET estado = 'Rechazada', motivo_rechazo = ? WHERE id = ?")
                    ->execute([mb_substr($motivo, 0, 255), $id]);
                $pdo->commit();
                flash('success', 'Reserva rechazada.');
            }
        } else {
            $pdo->rollBack();
        }
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('error', 'Ocurrio un error al procesar la solicitud.');
    }
    redirect('admin/solicitudes.php');
}

$pend = $pdo->query("SELECT r.id, r.fecha_inicio, r.fecha_fin, u.nombre AS estudiante, u.codigo, e.nombre AS equipo, e.codigo_inventario
    FROM reservas r
    JOIN usuarios u ON u.id = r.usuario_id
    JOIN equipos e ON e.id = r.equipo_id
    WHERE r.estado = 'Pendiente' ORDER BY r.fecha_inicio")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h1>Solicitudes pendientes</h1>

<?php if (!$pend): ?>
    <div class="alert alert-info">No hay solicitudes pendientes.</div>
<?php else: ?>
<div class="tabla-wrap">
    <table class="tabla">
        <thead><tr><th>Estudiante</th><th>Equipo</th><th>Fecha/Hora solicitada</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($pend as $p): ?>
            <tr>
                <td data-label="Estudiante"><?= e($p['estudiante']) ?> <span class="muted small">(<?= e($p['codigo']) ?>)</span></td>
                <td data-label="Equipo"><?= e($p['equipo']) ?> <span class="muted small">(<?= e($p['codigo_inventario']) ?>)</span></td>
                <td data-label="Fecha/Hora"><?= e(fmt_fecha($p['fecha_inicio'])) ?> a <?= e(date('H:i', strtotime($p['fecha_fin']))) ?></td>
                <td data-label="Acciones" class="acciones">
                    <form method="post" class="inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="reserva_id" value="<?= (int)$p['id'] ?>">
                        <input type="hidden" name="accion" value="aprobar">
                        <button class="btn btn-success" type="submit">Aprobar</button>
                    </form>
                    <details class="rechazo">
                        <summary class="btn btn-danger">Rechazar</summary>
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="reserva_id" value="<?= (int)$p['id'] ?>">
                            <input type="hidden" name="accion" value="rechazar">
                            <label for="motivo<?= (int)$p['id'] ?>">Motivo del rechazo (obligatorio)</label>
                            <textarea id="motivo<?= (int)$p['id'] ?>" name="motivo" rows="2" maxlength="255" required placeholder="Ej. Equipo en mantenimiento"></textarea>
                            <button class="btn btn-danger" type="submit">Confirmar rechazo</button>
                        </form>
                    </details>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
