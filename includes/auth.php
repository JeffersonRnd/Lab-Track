<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function base_url(string $path = ''): string
{
    static $base = null;
    if ($base === null) {
        $root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
        $doc = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $base = ($doc && strpos($root, $doc) === 0) ? substr($root, strlen($doc)) : '';
        $base = rtrim($base, '/');
    }
    return $base . '/' . ltrim($path, '/');
}

function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . base_url($path));
    exit;
}

function usuario_actual(): ?array
{
    if (empty($_SESSION['uid'])) {
        return null;
    }
    $st = db()->prepare('SELECT id, codigo, nombre, rol, estado FROM usuarios WHERE id = ?');
    $st->execute([$_SESSION['uid']]);
    $u = $st->fetch();
    if (!$u) {
        session_destroy();
        return null;
    }
    return $u;
}

function requiere_login(?string $rol = null): array
{
    $u = usuario_actual();
    if (!$u) {
        redirect('login.php');
    }
    if ($rol !== null && $u['rol'] !== $rol) {
        redirect('index.php');
    }
    return $u;
}

// CSRF
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verificar(): void
{
    $t = $_POST['csrf'] ?? '';
    if (!is_string($t) || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(400);
        die('Solicitud invalida (token CSRF).');
    }
}

// Mensajes flash
function flash(string $tipo, string $msg): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'msg' => $msg];
}

function flash_render(): string
{
    $html = '';
    foreach ($_SESSION['flash'] ?? [] as $f) {
        $html .= '<div class="alert alert-' . e($f['tipo']) . '" role="alert">' . e($f['msg']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $html;
}
