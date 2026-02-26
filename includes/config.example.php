<?php
// Example configuration template. Copy this file to `includes/config.php`
// or set environment variables equivalent to the values below.

// PROJECT
putenv('PROJECT_NAME=PROYECTOS DE VINCULACIÓN');
putenv('BASE_URL=/');

// DATABASE
putenv('DB_HOST=127.0.0.1');
putenv('DB_USER=root');
putenv('DB_PASS=your_db_password_here');
putenv('DB_NAME=db_proyectos_vinculacion');
putenv('DB_PORT=3306');

// SESSION
putenv('SESSION_TIME=3600');

// UPLOADS
putenv('UPLOAD_URL=/uploads/');
putenv('MAX_FILE_SIZE=5242880');
putenv('ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,pdf');

// SECURITY
putenv('HASH_KEY=change_this_to_a_secure_random_value');

// SMTP (optional)
putenv('SMTP_HOST=');
putenv('SMTP_USER=');
putenv('SMTP_PASS=');
putenv('SMTP_PORT=');
putenv('SMTP_SECURE=');

// Usage: copy this file and edit values, or set the same variables
// in your system environment or hosting panel. Do NOT commit real
// credentials to public repositories.

?>
