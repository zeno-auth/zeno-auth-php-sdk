<?php

use ZenoAuth\SDK\Config;
use ZenoAuth\SDK\ZenoAuth;

require '../vendor/autoload.php';

$config = new Config('http://localhost:8000', 'd6b679ab-a97e-48da-bd0c-1523926ce794', 'f1eb8f2219f42c8d2af18ec1597e53d13ef329f48ad6503c45');
$auth = new ZenoAuth($config);

$auth->issueToken('client_credentials', 'basic');

// Do something with your token
var_dump($auth->getAccessToken());
