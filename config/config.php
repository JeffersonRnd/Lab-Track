<?php
define('DB_HOST', 'sql103.infinityfree.com');
define('DB_NAME', 'if0_43408018_labtrack');
define('DB_USER', 'if0_43408018');
define('DB_PASS', 'jziy6mOHnC02o');

define('APP_NAME', 'LabTrack');
define('MAX_INTENTOS', 3);          // intentos fallidos antes de bloquear
define('MINUTOS_BLOQUEO', 15);      // duracion del bloqueo temporal
define('TIMEZONE', 'America/Lima');

date_default_timezone_set(TIMEZONE);
