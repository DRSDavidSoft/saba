<?php
include_once ('../inc/functions.php');
$response = [
    'status' => false,
    'message' => 'Request method must be as "POST"',
    'result' => null
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['username']) or  !isset($_POST['password'])) {
        $response['message'] = 'Username or Password is not provided properly.';
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $user = get_record_by_field('users', 'username', $username);
        if ($user and ($user['password'] ?? '') === sha1($password)) {
            $response['message'] = 'Login successful';
            $response['status'] = true;
            $token = createToken(60);
            $token_life = new DateTime();
            $token_life->add(DateInterval::createFromDateString(TOKEN_LIFE.' minutes'));
            $token_life = $token_life->format('Y-m-d H:i:s');
            update_single_record('users', $user['id'], ['token' => $token, 'token_lifetime' => $token_life]);
            $response['result'] = [
                'token' => $token,
                'lifetime' => $token_life
            ];
        } else {
            $response['message'] = 'Username or Password is not valid.';
        }
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
exit();