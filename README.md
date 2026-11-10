# LabTrack - Prestamo de equipos de laboratorio

Proyecto PHP 8 + MySQL (PDO) basado en las historias de usuario HU-01 a HU-05.

## Instalacion en XAMPP
1. Copie la carpeta `labtrack` en `C:\xampp\htdocs\`.
2. Inicie Apache y MySQL desde el panel de XAMPP.
3. Abra http://localhost/phpmyadmin, pestana Importar, y cargue `database/labtrack.sql`
   (crea la base `labtrack` con datos de prueba).
4. Si cambio la clave de root de MySQL, edite `config/config.php`.
5. Abra http://localhost/labtrack/

## Credenciales de prueba
| Rol | Codigo | Contrasena |
|---|---|---|
| Encargado | ADMIN01 | admin123 |
| Estudiante | n00247055 (Angel Renato Cortez Diaz) | cortez |
| Estudiante | n00373217 (Ketty Milagros Cueva Marin) | cueva |
| Estudiante | n00368981 (Mauricio Javier Escobar Castrejon) | escobar |
| Estudiante | n00355503 (Luis Eduardo Herrera Ramirez) | herrera |
| Estudiante | n00345124 (Angel Jefferson Bazan Avelino) | bazan |

## Reglas implementadas
- HU-01: login por codigo; mensaje "Credenciales inválidas"; 3 fallos consecutivos bloquean la cuenta 15 min (configurable).
- HU-02: catalogo con filtro Cómputo / Electrónica / Redes y mensaje de vacio.
- HU-03: reserva con fecha y horas; bloqueo por cruce con reserva aprobada; maximo 1 reserva activa (Pendiente o Aprobada) por estudiante; estudiantes penalizados no pueden reservar.
- HU-04: panel del encargado con Aprobar / Rechazar (motivo obligatorio). Al aprobar el equipo pasa a Prestado.
- HU-05: devolucion; el equipo vuelve a Disponible; si la hora actual supera la hora de fin, el estudiante queda Penalizado (el encargado puede levantar la penalizacion en la seccion Estudiantes).
