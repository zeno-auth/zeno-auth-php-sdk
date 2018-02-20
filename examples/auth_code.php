<?php

use ZenoAuth\SDK\Config;
use ZenoAuth\SDK\ZenoAuth;

require '../vendor/autoload.php';

$config = new Config(
    'http://localhost:8000',
    'd6b679ab-a97e-48da-bd0c-1523926ce794',
    'f1eb8f2219f42c8d2af18ec1597e53d13ef329f48ad6503c45'
);
$auth = new ZenoAuth($config);

$scheme = isset($_SERVER['HTTPS']) ? "https" : "http";
$currentUri = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/auth_code.php';

if (!isset($_GET['action'])) {
    if (null === $user = $auth->getUser()) {
        header('Location: ' . $auth->login('code', 'basic email', $currentUri . '?action=auth'));
        exit;
    }

    header('Location: ' . $currentUri . '?action=welcome');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'auth') {
    if (null === $code = $auth->getAuthorizationCode()) {
        echo '<h1>Cannot Authenticate!!</h1>';
        exit;
    }

    $auth->issueToken(
        'authorization_code',
        'basic email',
        [
            'code'         => $auth->getAuthorizationCode(),
            'redirect_uri' => $currentUri . '?action=auth',
            'state'        => $auth->getState(),
        ]
    );

    if (null === $auth->getAccessToken()) {
        echo '<h1>Failed Authenticate!!</h1>';
    }

    header('Location: ' . $currentUri . '?action=welcome');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'welcome') {
    if (null === $auth->getUser()) {
        header('Location: ' . $currentUri);
        exit;
    }

    echo '<h1>Hello ' . $auth->getUser()->getName() . ', Your Email: ' . $auth->getUser()->getEmail() . '</h1>';
}
