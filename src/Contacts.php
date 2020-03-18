<?php

namespace luya\newsletter2go;

use Curl\Curl;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

class Contacts extends BaseObject
{
    public $listId;

    public $accessToken;

    public function init()
    {
        parent::init();

        if (empty($this->listId) || empty($this->accessToken)) {
            throw new InvalidConfigException("The listId and accessToken property can not be empty.");
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
        $attributes = array_merge($attributes, ['email' => $email, 'listId' => $this->listId]);

        $request = (new Curl)->post('https://api.newsletter2go.com/recipients', $attributes);

        return $request->isSuccess();
    }
}