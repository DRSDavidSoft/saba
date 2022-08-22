<?php

include_once ('./inc/functions.php');
dd(APP_ROOT);
$db = db_connection();
if ($db) {
    $tables = [
        'users' => table_exists('users'),
    ];
    $missing_tables = [];
    foreach ($tables as $key => $table) {
        echo 'Table "'.$key.'" check: '.($table ? 'OK!' : 'Not found!');
        if (!$table) {
            $missing_tables[] = $key;
        }
        echo '<br>';
    }
    if (count($missing_tables) > 0) {
        ?>
        <form action="./install.php" method="POST">
            <span>Do you want to create missing ('<?php echo implode(', ', $missing_tables); ?>') tables?</span>
            <input type="hidden" name="status" value="create">
            <button type="submit"> Yes! </button>
        </form>
        <?php
    } else {
        if (count(get_all_records('users'))==0) {
            $result = insert_single_record('users', [
                'username' => 'test-user',
                'password' => password_hash('P@ssw0rd', PASSWORD_BCRYPT, ['cost' => DB_COST])
            ]);
            if ($result) {
                echo '<br>A default user created by following details:</br>';
                echo "-  Username: test-user</br>";
                echo "-  Password: P@ssw0rd</br></br>";
            }
        }
        echo "Project initialized successfully!</br>";
    }
} else {
    echo 'Error establishing connection to database "'.DB_NAME.'" with username "'.DB_USER.'" on "'.DB_HOST.'"<br>';
    echo 'Make sure to set correct values in config section.';
}
