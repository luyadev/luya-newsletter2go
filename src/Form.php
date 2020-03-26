<?php

namespace luya\newsletter2go;

use Curl\Curl;
use luya\helpers\Json;
use luya\newsletter2go\Auth;
use yii\base\BaseObject;

class Form extends BaseObject
{
    public $formId;
    
    /**
     * @var string The username of to generate and retrieve a token
     */
    public $username;

    /**
     * @var string The password (!!! ...) for the given user which is required to retrieve a token.
     */
    public $password;

    /**
     * @var string The auth key from the newsletter2go dashboard
     */
    public $authKey;

    public function create($email, array $attributes = [])
    {
        $token = Auth::auth($this->username, $this->password, $this->authKey);

        $attributes = array_merge($attributes, ['email' => $email]);

        $curl = new Curl;
        $curl->setHeader('Authorization', 'Bearer '. trim($token));
        $curl->setHeader('Content-Type', 'application/json');
        $request = $curl->post('https://api.newsletter2go.com/forms/submit/'.$this->formId, [
            'recipient' => $attributes,
        ], true);

        $array = Json::decode($request->response);
        
        if ($request->isSuccess()) {
            return true;
        }

        return false;
    }
}