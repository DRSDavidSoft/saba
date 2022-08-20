<?php
include_once ('../inc/functions.php');
$response = [
    'status' => false,
    'message' => 'Request method must be as "POST"',
    'result' => null
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['username'])) {
        $response['message'] = 'Required parameters are not provided properly.';
    } else {
        $username = $_POST['username'];
        if (checkUserToken()) {
            $user = get_record_by_field('users', 'username', $username);
            if ($user) {
                if ($user['token'] === getBearerToken()) {
                    $response['message'] = 'User can not be deleted by itself!';
                } else {
                    delete_record('users', $user['id']);
                    $response['status'] = true;
                    $response['message'] = 'User deleted successfully.';
                    $response['result'] = $user['id'];
                }
            } else {
                $response['message'] = 'User with provided username is not found.';
            }
        } else {
            $response['message'] = 'Not authorized.';
        }
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
exit();