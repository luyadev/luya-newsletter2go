<?php

namespace luya\newsletter2go;

use Curl\Curl;
use luya\helpers\Json;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

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
        $token = $this->auth($this->username, $this->password, $this->authKey);

        $attributes = array_merge($attributes, ['email' => $email, 'list_id' => $this->listId]);

        $curl = new Curl;
        $curl->setHeader('Authorization', 'Bearer '. trim($token));
        $curl->setHeader('Content-Type', 'application/json');
        $request = $curl->post('https://api.newsletter2go.com/recipients', $attributes, true);

        var_dump($request->response);
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