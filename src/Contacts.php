<?php

namespace luya\newsletter2go;

use Curl\Curl;
use luya\helpers\Json;
use NL2GO\Newsletter2Go_REST_Api;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\VarDumper;

class Contacts extends BaseObject
{
    public $listId;

    public $username;

    public $password;

    public $authKey;

    public function init()
    {
        parent::init();

        if (empty($this->listId) || empty($this->username) || empty($this->password) || empty($this->authKey)) {
            throw new InvalidConfigException("The listId and username, password and authkey property can not be empty.");
        }
    }

    /**
     * Create a new entry (or update an existing by email).
     *
     * @param string $email
     * @param array $attributes A list of additional attributes:
     * - "phone": "+49123456789",
     * - "gender": "m",
     * - "first_name": "John",
     * - "last_name": "Doe",
     * - "is_unsubscribed": false,
     * - "is_blacklisted": false,
     * - "{{attribute_name}}": "attribute value"
     * @return boolean
     */
    public function create($email, array $attributes = [])
    {
        $api = new Newsletter2Go_REST_Api($this->authKey, $this->username, $this->password);

        $api->setSSLVerification(false);

        $x = $api->addRecipient($email, 'basil', 'suter', 'm');

        $t = $this->auth($this->username, $this->password, $this->authKey);

        $attributes = array_merge($attributes, ['email' => $email, 'listId' => $this->listId]);

        $curl = new Curl;
        $curl->setHeader('Authorization', 'Bearer '. trim($t));
        $curl->setHeader('Content-Type', 'application/json');
        $request = $curl->post('https://api.newsletter2go.com/recipients', ['email' => 'basil+test@zephir.ch']);

        VarDumper::dump($request, 10, true);
        return $request->isSuccess();
    }

    public function auth($username, $password, $authKey)
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