<?php
require_once __DIR__ . '/auth.php';

const CATEGORIAS = ['Cómputo', 'Electrónica', 'Redes'];

function fmt_fecha(?string $dt): string
{
    return $dt ? date('d/m/Y H:i', strtotime($dt)) : '-';
}

function badge(string $estado): string
{
    $mapa = [
        'Disponible' => 'ok', 'Aprobada' => 'ok', 'Activo' => 'ok', 'Devuelta' => 'info',
        'Prestado' => 'warn', 'Pendiente' => 'warn',
        'Rechazada' => 'bad', 'Penalizado' => 'bad',
    ];
    return '<span class="badge badge-' . ($mapa[$estado] ?? 'info') . '">' . e($estado) . '</span>';
}

/** Hay una reserva aprobada que se cruza con el rango indicado para el equipo? */
function hay_cruce(PDO $pdo, int $equipoId, string $inicio, string $fin, ?int $excluirReserva = null): bool
{
    $sql = "SELECT COUNT(*) FROM reservas
            WHERE equipo_id = ? AND estado = 'Aprobada'
              AND fecha_inicio < ? AND fecha_fin > ?";
    $p = [$equipoId, $fin, $inicio];
    if ($excluirReserva) {
        $sql .= ' AND id <> ?';
        $p[] = $excluirReserva;
    }
    $st = $pdo->prepare($sql);
    $st->execute($p);
    return (int)$st->fetchColumn() > 0;
}
