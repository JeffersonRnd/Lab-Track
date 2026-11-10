<?php
require_once __DIR__ . '/includes/funciones.php';
$usuario = requiere_login('estudiante');
$titulo = 'Reservar equipo';
$pdo = db();

$errores = [];
$sel = ['equipo_id' => (int)($_GET['equipo'] ?? 0), 'fecha' => '', 'inicio' => '', 'fin' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $sel['equipo_id'] = (int)($_POST['equipo_id'] ?? 0);
    $sel['fecha'] = $_POST['fecha'] ?? '';
    $sel['inicio'] = $_POST['hora_inicio'] ?? '';
    $sel['fin'] = $_POST['hora_fin'] ?? '';

    $ini = DateTime::createFromFormat('Y-m-d H:i', $sel['fecha'] . ' ' . $sel['inicio']);
    $fin = DateTime::createFromFormat('Y-m-d H:i', $sel['fecha'] . ' ' . $sel['fin']);

    if ($usuario['estado'] === 'Penalizado') {
        $errores[] = 'Su cuenta esta penalizada por una devolucion tardia. Comuniquese con el encargado del laboratorio.';
    }
    if ($sel['equipo_id'] <= 0) {
        $errores[] = 'Seleccione un equipo.';
    }
    if (!$ini || !$fin) {
        $errores[] = 'Indique una fecha y un horario validos.';
    } else {
        if ($ini < new DateTime()) {
            $errores[] = 'La fecha y hora de inicio no pueden estar en el pasado.';
        }
        if ($fin <= $ini) {
            $errores[] = 'La hora de fin debe ser posterior a la hora de inicio.';
        }
    }

    if (!$errores) {
        $i = $ini->format('Y-m-d H:i:s');
        $f = $fin->format('Y-m-d H:i:s');
        try {
            $pdo->beginTransaction();
            // Bloqueo de fila para evitar condiciones de carrera entre reservas concurrentes
            $st = $pdo->prepare('SELECT id, estado FROM equipos WHERE id = ? FOR UPDATE');
            $st->execute([$sel['equipo_id']]);
            $eq = $st->fetch();

            $st = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE usuario_id = ? AND estado IN ('Pendiente','Aprobada')");
            $st->execute([$usuario['id']]);
            $activas = (int)$st->fetchColumn();

            if (!$eq || $eq['estado'] !== 'Disponible') {
                $errores[] = 'El equipo seleccionado no esta disponible.';
            } elseif ($activas >= 1) {
                $errores[] = 'Ya tiene un equipo reservado. Solo se permite un (1) equipo reservado de forma simultanea.';
            } elseif (hay_cruce($pdo, (int)$eq['id'], $i, $f)) {
                $errores[] = 'El equipo ya tiene una reserva aprobada que se cruza con ese horario.';
            } else {
                $pdo->prepare('INSERT INTO reservas (usuario_id, equipo_id, fecha_inicio, fecha_fin) VALUES (?,?,?,?)')
                    ->execute([$usuario['id'], $eq['id'], $i, $f]);
            }

            if ($errores) {
                $pdo->rollBack();
            } else {
                $pdo->commit();
                flash('success', 'Solicitud de reserva enviada. Queda pendiente de aprobacion por el encargado.');
                redirect('mis_reservas.php');
            }
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errores[] = 'Ocurrio un error al procesar la reserva. Intente nuevamente.';
        }
    }
}

$equipos = $pdo->query("SELECT id, nombre, codigo_inventario, categoria FROM equipos WHERE estado = 'Disponible' ORDER BY categoria, nombre")->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<h1>Reservar equipo</h1>

<?php if ($usuario['estado'] === 'Penalizado'): ?>
    <div class="alert alert-error">Su cuenta esta penalizada y no puede realizar reservas.</div>
<?php endif; ?>
<?php foreach ($errores as $m): ?>
    <div class="alert alert-error" role="alert"><?= e($m) ?></div>
<?php endforeach; ?>

<?php if (!$equipos): ?>
    <div class="alert alert-info">No hay equipos disponibles en este momento.</div>
<?php else: ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?>
    <div class="campo campo-full">
        <label for="equipo_id">Equipo disponible</label>
        <select name="equipo_id" id="equipo_id" required>
            <option value="">Seleccione...</option>
            <?php foreach ($equipos as $q): ?>
                <option value="<?= (int)$q['id'] ?>" <?= $q['id'] == $sel['equipo_id'] ? 'selected' : '' ?>>
                    <?= e($q['categoria'] . ' - ' . $q['nombre'] . ' (' . $q['codigo_inventario'] . ')') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="campo">
        <label for="fecha">Fecha de uso</label>
        <input type="date" id="fecha" name="fecha" min="<?= date('Y-m-d') ?>" value="<?= e($sel['fecha']) ?>" required>
    </div>
    <div class="campo">
        <label for="hora_inicio">Hora de inicio</label>
        <input type="time" id="hora_inicio" name="hora_inicio" value="<?= e($sel['inicio']) ?>" required>
    </div>
    <div class="campo">
        <label for="hora_fin">Hora de fin</label>
        <input type="time" id="hora_fin" name="hora_fin" value="<?= e($sel['fin']) ?>" required>
    </div>
    <div class="campo campo-full">
        <button type="submit" class="btn btn-primary" <?= $usuario['estado'] === 'Penalizado' ? 'disabled' : '' ?>>Solicitar reserva</button>
    </div>
</form>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
