<?php
return [
    'db_host' => getenv('PPD_DB_HOST') ?: 'localhost',
    'db_name' => getenv('PPD_DB_NAME') ?: 'ppd2026',
    'db_user' => getenv('PPD_DB_USER') ?: 'root',
    'db_pass' => getenv('PPD_DB_PASS') ?: '',
    'wa_number' => getenv('PPD_WA_NUMBER') ?: '628993998544',
];
