<?php

namespace luya\newsletter2go;

use Curl\Curl;
use luya\helpers\Json;
use yii\base\BaseObject;

/**
 * Auth API
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 1.1.0
 */
class Auth extends BaseObject
{
    /**
     * Retrieve an auth token for the given user to perform api calls.
     *
     * @param string $username
     * @param string $password
     * @param string $authKey
     * @return stringThe token, or false if auth is failed.
     */
    public static function auth($username, $password, $authKey)
    {
        $curl = new Curl();
        $curl->setHeader('Authorization', 'Basic ' . base64_encode($authKey));
        $request = $curl->post('https://api.newsletter2go.com/oauth/v2/token', [
            'username' => $username,
            'password' => $password,
            'grant_type' => 'https://nl2go.com/jwt',
        ]);

        if (!$request->isSuccess()) {
            return false;
        }

        $json = Json::decode($request->response);

        return $json['access_token'];
    }
}