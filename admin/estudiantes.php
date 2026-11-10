<?php
require_once __DIR__ . '/../includes/funciones.php';
$usuario = requiere_login('encargado');
$titulo = 'Estudiantes';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $id = (int)($_POST['usuario_id'] ?? 0);
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'perdonar') {
        $pdo->prepare("UPDATE usuarios SET estado = 'Activo' WHERE id = ? AND rol = 'estudiante'")->execute([$id]);
        flash('success', 'Penalizacion levantada.');
    } elseif ($accion === 'desbloquear') {
        $pdo->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ? AND rol = 'estudiante'")->execute([$id]);
        flash('success', 'Cuenta desbloqueada.');
    }
    redirect('admin/estudiantes.php');
}

$alumnos = $pdo->query("SELECT id, codigo, nombre, estado, bloqueado_hasta FROM usuarios WHERE rol = 'estudiante' ORDER BY nombre")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<h1>Estudiantes</h1>
<div class="tabla-wrap">
    <table class="tabla">
        <thead><tr><th>Codigo</th><th>Nombre</th><th>Estado</th><th>Cuenta</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($alumnos as $a): $bloq = $a['bloqueado_hasta'] && strtotime($a['bloqueado_hasta']) > time(); ?>
            <tr>
                <td data-label="Codigo"><?= e($a['codigo']) ?></td>
                <td data-label="Nombre"><?= e($a['nombre']) ?></td>
                <td data-label="Estado"><?= badge($a['estado']) ?></td>
                <td data-label="Cuenta"><?= $bloq ? '<span class="badge badge-bad">Bloqueada</span>' : '<span class="badge badge-ok">Normal</span>' ?></td>
                <td data-label="Acciones" class="acciones">
                    <?php if ($a['estado'] === 'Penalizado'): ?>
                        <form method="post" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="usuario_id" value="<?= (int)$a['id'] ?>">
                            <input type="hidden" name="accion" value="perdonar">
                            <button class="btn btn-success" type="submit">Levantar penalizacion</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($bloq): ?>
                        <form method="post" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="usuario_id" value="<?= (int)$a['id'] ?>">
                            <input type="hidden" name="accion" value="desbloquear">
                            <button class="btn btn-primary" type="submit">Desbloquear</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($a['estado'] !== 'Penalizado' && !$bloq): ?>-<?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
