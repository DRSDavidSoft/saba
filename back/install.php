<?php

include_once ('./inc/functions.php');
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo 'You can not call this function manually!';
    exit;
}

$db = db_connection();

$tables = [
    'users' => [
        'status' => table_exists('users'),
        'sql' => "CREATE TABLE `users2` (`id` int(10) UNSIGNED NOT NULL,`username` varchar(50) NOT NULL,`password` varchar(250) NOT NULL,`token` varchar(250) DEFAULT NULL,`token_lifetime` timestamp NULL DEFAULT NULL,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`deleted_at` timestamp NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
    ],
];
$missing_tables = [];
foreach ($tables as $key => $table) {
    if (!$table['status']) {
        $missing_tables[$key] = $table['sql'];
    }
}

echo 'Starting to create tables...<br><br>';

foreach ($missing_tables as $key => $table) {
    echo 'Creating table "'.$key.'" ... ';
    raw_query($table);
    echo 'Done!';
    echo '<br>';
}
