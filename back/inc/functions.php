<?php
define('SITE_NAME', 'Saba Backend Test Project');

//Token lifetme // default = 2 hours (120 Minutes), this lifetime will be added to the user`s token lifetime with every successful api request
define('TOKEN_LIFE', '120');

//App Root
define('APP_ROOT', dirname(dirname(__FILE__)));
define('URL_ROOT', '/');

//DB Params
define('DB_NAME', 'saba_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');

function db_connection() {
    return mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
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
//        return $query_string;
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
            $rules[] = $key.'='.$val;
        }
        $query_string .= implode(', ', $rules).' WHERE id='.$id.';';
        if ($db->query($query_string) === true) {
            return get_record($table_name, $db->insert_id);
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
        if ($record != false) {
            if ($record->num_rows > 0) {
                return $record->fetch_assoc() ?? null;
            }
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

function get_all_records(string $table_name, $query = []) {
    $db = db_connection();
    if ($db) {
        $query_string = 'SELECT * FROM '.$table_name;
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
            while($row = $result->fetch_assoc()) {
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