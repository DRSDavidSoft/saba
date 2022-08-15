<?php
include_once __DIR__ . '/../inc/functions.php';

function register()
{
    $response = [
        'status' => false,
        'message' => 'Request method must be as "POST"',
        'result' => null
    ];
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') 
        return $response;
    
    if ( !isset($_POST['username']) or !isset($_POST['password'])) {
        $response['message'] = 'Username or Password is not provided properly.';
        return $response;
    }

    $username = &$_POST['username'];
    $password = &$_POST['password'];
    $check = get_record_by_field('users', 'username', $username);
    
    if (isset($check['username'])) {
        $response['message'] = 'Username is already exists.';
        return $response;
    }

    $result = insert_single_record('users', [
        'username' => $username,
        'password' => $password
    ]);
    if (!isset($result['id'])) {
        $response['message'] = 'Error while creating user.';
        return $response;
    }

    $response['status'] = true;
    $response['message'] = 'User created successfully.';
    $response['result'] = [
        'username' => $username,
        'user_id' => $result['id']
    ];

    return $response;

}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(register());
exit();