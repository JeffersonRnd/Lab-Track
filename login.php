<?php
require_once __DIR__ . '/includes/auth.php';

if (usuario_actual()) {
    redirect('index.php');
}

$error = '';
$alerta = '';
$codigo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $codigo = trim($_POST['codigo'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pdo = db();

    $st = $pdo->prepare('SELECT * FROM usuarios WHERE codigo = ?');
    $st->execute([$codigo]);
    $u = $st->fetch();

    if ($u && $u['bloqueado_hasta'] && strtotime($u['bloqueado_hasta']) > time()) {
        $min = (int)ceil((strtotime($u['bloqueado_hasta']) - time()) / 60);
        $alerta = "Alerta de seguridad: la cuenta esta bloqueada temporalmente por intentos fallidos. Intente nuevamente en $min minuto(s).";
    } elseif ($u && password_verify($pass, $u['password'])) {
        $pdo->prepare('UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?')->execute([$u['id']]);
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$u['id'];
        redirect('index.php');
    } else {
        $error = 'Credenciales inválidas';
        if ($u) {
            // Si el bloqueo anterior ya vencio, reiniciar el contador
            $intentos = ($u['bloqueado_hasta'] ? 0 : (int)$u['intentos_fallidos']) + 1;
            if ($intentos >= MAX_INTENTOS) {
                $pdo->prepare('UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?')
                    ->execute([MINUTOS_BLOQUEO, $u['id']]);
                $error = '';
                $alerta = 'Alerta de seguridad: ' . MAX_INTENTOS . ' intentos fallidos consecutivos. La cuenta ha sido bloqueada temporalmente por ' . MINUTOS_BLOQUEO . ' minutos.';
            } else {
                $pdo->prepare('UPDATE usuarios SET intentos_fallidos = ?, bloqueado_hasta = NULL WHERE id = ?')->execute([$intentos, $u['id']]);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesion - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= e(base_url('assets/css/style.css')) ?>">
</head>
<body class="login-body">
<main class="login-card">
    <h1><?= APP_NAME ?></h1>
    <p class="muted">Prestamo de equipos de laboratorio</p>

    <?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?php if ($alerta): ?><div class="alert alert-warning" role="alert"><?= e($alerta) ?></div><?php endif; ?>

    <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <label for="codigo">Codigo de estudiante</label>
        <input type="text" id="codigo" name="codigo" value="<?= e($codigo) ?>" required>

        <label for="password">Contrasena</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
    </form>
    
</main>
</body>
</html>
