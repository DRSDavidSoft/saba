<?php
define('SITE_NAME', 'Saba Backend Test Project');
define('DEBUG_MODE', 'debug'); // "debug" or "production"

//Token lifetme // default = 2 hours (120 Minutes), this lifetime will be added to the user`s token lifetime with every successful api request
define('TOKEN_LIFE', 120);

//App Root
define('APP_ROOT', dirname(dirname(__FILE__)));  // It's going to get a folder before current directory
define('URL_ROOT', '/');

//DB Params
define('DB_NAME', 'saba_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');

define('DB_COST', 10);

ini_set('display_errors', strtoupper(DEBUG_MODE)==='PRODUCTION' ? 0 : 1);
ini_set('display_startup_errors', strtoupper(DEBUG_MODE)==='PRODUCTION' ? 0 : 1);

function db_connection() {
    try {
        $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    } catch (Exception $e) {
        return mysqli_connect_error();
    }
    return $db;
}

function table_exists($table_name) {
    $db = db_connection();
    if ($db) {
        $query_string = "SHOW TABLES LIKE '".$table_name."';";
        $result = $db->query($query_string);
        $result = $result->fetch_assoc();
        $result = $result['Tables_in_saba_db ('.$table_name.')'] ?? false;
        return $result === $table_name;
    }
    return false;
}

function raw_query($query) {
    $db = db_connection();
    return $db ? $db->query($query) : false;
}

function insert_single_record(string $table_name, array $request) {
    $db = db_connection();
    if ($db) {
        $array_keys   = array_keys($request);
        $query_string = 'INSERT INTO '.$table_name.' ('.implode(', ', $array_keys).') VALUES (';
        $values       = [];
        foreach ($array_keys as $key) {
            $val = $request[$key];
            if (strtoupper(gettype($val)) == 'STRING') {
                $val = "'".$val."'";
            }
            $values[] = $val;
        }
        $query_string .= implode(', ', $values).');';
        if ($db->query($query_string) === true) {
            return get_record($table_name, $db->insert_id);
        }
    }
    return false;
}

function update_single_record(string $table_name, $id, array $request) {
    $db = db_connection();
    if ($db) {
        $array_keys   = array_keys($request);
        $query_string = 'UPDATE '.$table_name.' SET ';
        $rules = [];
        foreach ($array_keys as $key) {
            $val = $request[$key];
            if (strtoupper(gettype($val)) == 'STRING') {
                $val = "'".$val."'";
            }
            $rules[] = '`'.$key.'`='.$val;
        }
        $query_string .= implode(', ', $rules).' WHERE id='.$id.';';
        if ($db->query($query_string) === true) {
            return get_record($table_name, $id);
        }
    }
    return false;
}

function get_record(string $table_name, int $id) {
    return get_record_by_field($table_name, 'id', $id);
}

function get_record_by_field(string $table_name, string $field, $value) {
    $db = db_connection();
    if ($db) {
        if (strtoupper(gettype($value)) == 'STRING') {
            $value = "'".$value."'";
        }
        $query_string = 'SELECT * FROM '.$table_name.' WHERE '.$field.'='.$value.' LIMIT 1;';
        $record = $db->query($query_string);
        if ($record != false and $record->num_rows > 0) {
            return $record->fetch_assoc() ?? null;
        }
        return [];
    }
    return false;
}

function delete_record(string $table_name, int $id) {
    $db = db_connection();
    if ($db) {
        $query_string = 'DELETE FROM '.$table_name.' WHERE id='.$id;
        return $db->query($query_string) === true;
    }
    return false;
}

function get_all_records(string $table_name, $field_list = '*', $query = []) {
    $db = db_connection();
    if ($db) {

        $query_string = 'SELECT '. (gettype($field_list)=='array' ? implode(',', $field_list) : $field_list) .' FROM '.$table_name;
        if (count($query) > 0) {
            $query_string .= ' WHERE (';
            $query_array = [];
            foreach ($query as $single_query) {
                $query_array[] = implode('',$single_query);
            }
            $query_string .= implode(' AND ', $query_array).')';
        }
        $query_string .= ';';
        $records = $db->query($query_string);

        $result  = [];
        if ($records->num_rows ?? null > 0 === true) {
            while($row = $records->fetch_assoc()) {
                $result[] = $row;
            }
        }
        return $result;
    }
    return false;
}

function createToken($length = 100) {
    $bytes = random_bytes($length/2);
    return bin2hex($bytes);
}

function dd($value) {
    print_r($value);
    exit();
}

function getAuthorizationHeader() {
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

function getBearerToken() {
    $headers = getAuthorizationHeader();
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function checkUserToken() {
    $user = get_record_by_field('users', 'token', getBearerToken());
    if ($user) {
        $temp = new DateTime();
        return $user['token_lifetime'] > $temp->format('Y-m-d H:i:s');
    }
    return false;
}

function checkUserTokenWithPresence($username) {
    $user = get_record_by_field('users', 'token', getBearerToken());
    if ($user) {
        $temp = new DateTime();
        return ($user['token_lifetime'] > $temp->format('Y-m-d H:i:s') and strtoupper($user['token_lifetime']) != strtoupper($username));
    }
    return false;
}