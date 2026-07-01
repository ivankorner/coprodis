<?php

define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 3600));
define('SESSION_NAME', $_ENV['SESSION_NAME'] ?? 'coprodis_session');
