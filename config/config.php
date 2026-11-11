<?php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'labtrack');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

define('APP_NAME', 'LabTrack');
define('MAX_INTENTOS', 3);          // intentos fallidos antes de bloquear
define('MINUTOS_BLOQUEO', 15);      // duracion del bloqueo temporal
define('TIMEZONE', 'America/Lima');

date_default_timezone_set(TIMEZONE);
