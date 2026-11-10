<?php
// Punto de entrada raiz: redirige segun sesion y rol
require_once __DIR__ . '/includes/auth.php';

$u = usuario_actual();
if (!$u) {
    redirect('login.php');
}
redirect($u['rol'] === 'encargado' ? 'admin/solicitudes.php' : 'catalogo.php');
