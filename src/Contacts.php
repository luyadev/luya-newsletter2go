<?php

namespace luya\newsletter2go;

use Curl\Curl;
use luya\helpers\Json;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\VarDumper;

class Contacts extends BaseObject
{
    /**
     * @var string In newsletter2go those are `Adressbooks
     */
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

        $this->listId = strtolower($this->listId);
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
     * @return boolean|string The recipient id or false if error
     */
    public function create($email, array $attributes = [])
    {
        $token = $this->auth($this->username, $this->password, $this->authKey);

        $attributes = array_merge($attributes, ['email' => $email, 'list_id' => $this->listId]);

        $curl = new Curl;
        $curl->setHeader('Authorization', 'Bearer '. trim($token));
        $curl->setHeader('Content-Type', 'application/json');
        $request = $curl->post('https://api.newsletter2go.com/recipients', $attributes, true);

        $array = Json::decode($request->response);
        
        if ($request->isSuccess()) {
            return current($array['value'])['id'];   
        }

        return false;
    }

    /**
     * Which is also known as segment.
     *
     * @param string $groupId
     * @param string $recipientId
     * @return void
     */
    public function addToGroup($groupId, $recipientId)
    {
        $groupId = strtolower($groupId);
        $recipientId = strtolower($recipientId);
        $token = $this->auth($this->username, $this->password, $this->authKey);
        
        $curl = new Curl();
        $curl->setHeader('Authorization', 'Bearer ' . $token);
        $curl->setHeader('Content-Type', 'application/json');
        $request = $curl->post('https://api.newsletter2go.com/lists/'.$this->listId.'/groups/'.$groupId.'/recipients/'.$recipientId);

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