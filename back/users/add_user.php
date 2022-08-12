<?php
include_once ('../inc/functions.php');

$response = [
    'status' => false,
    'message' => 'Request method must be as "POST"',
    'result' => null
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['username']) or !isset($_POST['password'])) {
        $response['message'] = 'Username or Password is not provided properly.';
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $check = get_record_by_field('users', 'username', $username);
        if (isset($check['username'])) {
            $response['message'] = 'Username is already exists.';
        } else {
            $result = insert_single_record('users', [
                'username' => $username,
                'password' => $password
            ]);
            if (isset($result['id'])) {
                $response['status'] = true;
                $response['message'] = 'User created successfully.';
                $response['result'] = [
                    'username' => $username,
                    'user_id' => $result['id']
                ];
            } else {
                $response['message'] = 'Error while creating user.';
            }
        }
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
exit();