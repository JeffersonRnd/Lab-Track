<?php
require_once __DIR__ . '/../includes/funciones.php';
$usuario = requiere_login('encargado');
$titulo = 'Devoluciones';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $id = (int)($_POST['reserva_id'] ?? 0);
    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare("SELECT * FROM reservas WHERE id = ? AND estado = 'Aprobada' FOR UPDATE");
        $st->execute([$id]);
        $r = $st->fetch();

        if (!$r) {
            $pdo->rollBack();
            flash('error', 'La reserva no esta activa.');
        } else {
            $ahora = date('Y-m-d H:i:s');
            $tarde = $ahora > $r['fecha_fin'];

            $pdo->prepare("UPDATE reservas SET estado = 'Devuelta', fecha_devolucion = ?, penalizada = ? WHERE id = ?")
                ->execute([$ahora, $tarde ? 1 : 0, $id]);
            $pdo->prepare("UPDATE equipos SET estado = 'Disponible' WHERE id = ?")->execute([$r['equipo_id']]);
            if ($tarde) {
                $pdo->prepare("UPDATE usuarios SET estado = 'Penalizado' WHERE id = ?")->execute([$r['usuario_id']]);
            }
            $pdo->commit();
            flash($tarde ? 'warning' : 'success', $tarde
                ? 'Devolucion registrada con retraso. El estudiante ha sido marcado como Penalizado.'
                : 'Devolucion registrada. El equipo esta Disponible.');
        }
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('error', 'Ocurrio un error al registrar la devolucion.');
    }
    redirect('admin/devoluciones.php');
}

$q = trim($_GET['q'] ?? '');
$sql = "SELECT r.id, r.fecha_inicio, r.fecha_fin, u.nombre AS estudiante, u.codigo, e.nombre AS equipo, e.codigo_inventario
    FROM reservas r
    JOIN usuarios u ON u.id = r.usuario_id
    JOIN equipos e ON e.id = r.equipo_id
    WHERE r.estado = 'Aprobada'";
$params = [];
if ($q !== '') {
    $sql .= ' AND (u.nombre LIKE ? OR u.codigo LIKE ? OR e.nombre LIKE ? OR e.codigo_inventario LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like, $like, $like];
}
$sql .= ' ORDER BY r.fecha_fin';
$st = $pdo->prepare($sql);
$st->execute($params);
$activas = $st->fetchAll();
$ahora = date('Y-m-d H:i:s');

include __DIR__ . '/../includes/header.php';
?>
<h1>Devoluciones</h1>

<form method="get" class="filtros">
    <label for="q">Buscar reserva activa</label>
    <input type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Estudiante, codigo o equipo">
    <button class="btn btn-primary" type="submit">Buscar</button>
</form>

<?php if (!$activas): ?>
    <div class="alert alert-info">No hay reservas activas<?= $q !== '' ? ' para esa busqueda' : '' ?>.</div>
<?php else: ?>
<div class="tabla-wrap">
    <table class="tabla">
        <thead><tr><th>Estudiante</th><th>Equipo</th><th>Inicio</th><th>Fin acordado</th><th>Situacion</th><th>Accion</th></tr></thead>
        <tbody>
        <?php foreach ($activas as $a): $vencida = $ahora > $a['fecha_fin']; ?>
            <tr>
                <td data-label="Estudiante"><?= e($a['estudiante']) ?> <span class="muted small">(<?= e($a['codigo']) ?>)</span></td>
                <td data-label="Equipo"><?= e($a['equipo']) ?> <span class="muted small">(<?= e($a['codigo_inventario']) ?>)</span></td>
                <td data-label="Inicio"><?= e(fmt_fecha($a['fecha_inicio'])) ?></td>
                <td data-label="Fin"><?= e(fmt_fecha($a['fecha_fin'])) ?></td>
                <td data-label="Situacion"><?= $vencida ? '<span class="badge badge-bad">Fuera de plazo</span>' : '<span class="badge badge-ok">En plazo</span>' ?></td>
                <td data-label="Accion">
                    <form method="post" class="inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="reserva_id" value="<?= (int)$a['id'] ?>">
                        <button class="btn btn-primary" type="submit">Marcar Devolución</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
