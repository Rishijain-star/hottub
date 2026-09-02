<?php
require 'vendor/autoload.php';

$client = new Google\Client();
$client->setAuthConfig('D:/laragon/www/HotTub/client_secret.json');
$client->addScope('https://www.googleapis.com/auth/analytics.manage.users');
$client->setAccessType('offline');
$client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');

echo "Yeh URL browser mein kholo:\n\n";
echo $client->createAuthUrl() . "\n\n";
echo "Code enter karo: ";
$code = trim(fgets(STDIN));

$token = $client->fetchAccessTokenWithAuthCode($code);
$client->setAccessToken($token);

use Google\Analytics\Admin\V1alpha\AccessBinding;
use Google\Analytics\Admin\V1alpha\CreateAccessBindingRequest;
use Google\Analytics\Admin\V1alpha\Client\AnalyticsAdminServiceClient;

$adminClient = new AnalyticsAdminServiceClient([
    'credentials' => \Google\ApiCore\CredentialsWrapper::build([
        'keyFile' => [
            'type' => 'authorized_user',
            'client_id' => $client->getClientId(),
            'client_secret' => $client->getClientSecret(),
            'refresh_token' => $token['refresh_token'],
        ]
    ]),
]);

$binding = new AccessBinding([
    'user' => 'hot-tub-dashboard@hot-tub-buyer-dashboard.iam.gserviceaccount.com',
    'roles' => ['predefinedRoles/viewer'],
]);

$request = new CreateAccessBindingRequest([
    'parent' => 'properties/538301904',
    'access_binding' => $binding,
]);

$result = $adminClient->createAccessBinding($request);
echo "Success: " . $result->getName();