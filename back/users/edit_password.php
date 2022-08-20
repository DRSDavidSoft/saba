<?php
include_once ('../inc/functions.php');

$response = [
    'status' => false,
    'message' => 'Request method must be as "POST"',
    'result' => null
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['username']) or !isset($_POST['password']) or !isset($_POST['password_confirmation'])) {
        $response['message'] = 'Username or Password is not provided properly.';
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password_confirmation = $_POST['password_confirmation'];
        if (checkUserToken()) {
            if ($password === $password_confirmation) {
                $username = $_POST['username'];
                $password = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => DB_COST]);
                $check = get_record_by_field('users', 'username', $username);
                if (!isset($check['username'])) {
                    $response['message'] = 'User is not found.';
                } else {
                    $result = update_single_record('users', $check['id'], [
                        'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => DB_COST])
                    ]);
                    if ($result) {
                        $response['status'] = true;
                        $response['message'] = 'Password has been changed successfully.';
                        $response['result'] = [
                            'user' => $check['username']
                        ];
                    } else {
                        $response['message'] = 'Error while creating user.';
                    }
                }
            } else {
                $response['message'] = 'Password and it`s confirmation doe`s not match properly.';
            }
        } else {
            $response['message'] = 'Not authorized.';
        }
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
exit();