<?php
include_once ('../inc/functions.php');

$response = [
    'status' => false,
    'message' => 'Request method must be as "POST"',
    'result' => null
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (checkUserToken()) {
        $users = get_all_records('users', ['id', 'username', 'token', 'created_at', 'updated_at', 'deleted_at']);
        $token = getBearerToken();
        foreach ($users as $key => $user) {
            $users[$key]['active'] = $users[$key]['token'] === $token;
            unset($users[$key]['token']);
        }
        $response['status'] = true;
        $response['message'] = 'User list generated successfully.';
        $response['result'] = $users;
    } else {
        $response['message'] = 'Not authorized.';
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
exit();