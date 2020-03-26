<?php

namespace luya\newsletter2go\widgets;

use Yii;
use luya\base\DynamicModel;
use luya\base\Widget;
use luya\newsletter2go\Contacts;
use luya\newsletter2go\Form;
use yii\base\InvalidConfigException;

/**
 * Subscribe Form Widget.
 *
 * ```php
 * <?php $widget = SubscribeFormWidget::begin(['accessToken' => 123, 'listId' => 123]) ?>
 *     <?php if ($widget->isSubscribed): ?>
 *         <div class="alert alert-success">Thanks, your email address has been added to the subscription list.</div>
 *     <?php else: ?>
 *         <?php $form = ActiveForm::begin(); ?>
 *              <?= $form->field($widget->model, 'email'); ?>
 *              <?= Html::submitButton('Submit'); ?>
 *         <?php $form::end(); ?>
 *     <?php endif; ?>
 * <?php $widget::end(); ?>
 * ```
 *
 * @property DynamicModel $model
 * @property string $modelEmail
 * @property boolean $isSubscribed
 */
class SubscribeFormWidget extends Widget
{
    const MAIL_SUBSCRIBE_SUCCESS = 'mailSubscribeSuccess';

    /**
     * @var integer The list id where the subscribes should be added (also known as adressbook).
     */
    public $listId;

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

    /**
     * @var string The group id (also known as segment) to add the user, if not provided the user will not aded to a group.
     */
    public $groupId;

    /**
     * @var string If defined the values will be posted to the form api.
     */
    public $formId;

    /**
     * @var boolean Whether the form should be used to unsubscribe or subscribe users.
     */
    public $unsubscribed = false;

    /**
     * @var string The name of attribute which contains the email adresse.
     */
    public $emailAttributeName = 'email';

    /**
     * @var array A list of attributes the {{luya\base\DynamicModel}} should contain, those attributes will be sent to nl2go unless other defined in
     * {{$sendAttributes}} definition.
     */
    public $attributes = ['email'];

    /**
     * @var array The validation rules for the model, each attribute in {{SubScribeFormWidget::$attributes}} must have at least one rule.
     */
    public $attributeRules = [
        [['email'], 'required'],
        [['email'], 'email'],
    ];

    /**
     * @var array An array define the attribute labels for an attribute, internal the attribute label values
     * will be wrapped into the `Yii::t()` method.
     *
     * ```
     * 'attributeLabels' => [
     *     'email' => 'E-Mail-Adresse',
     * ],
     * ```
     */
    public $attributeLabels = [];

    /**
     * @var array A list of attribute hints, where key is the attribute and value the hint message.
     */
    public $attributeHints = [];

    /**
     * @var array A list of attributes which should be send to newsletter2go, if empty all attributes from {{$attributes}} will be taken.
     * This is mainly used to remove attributes which should not be sent, like captcha attributes or similar.
     */
    public $sendAttributes = [];

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();


        if (empty($this->username) || empty($this->password) || empty($this->authKey)) {
            throw new InvalidConfigException("The listId and accessToken properties can not be empty.");
        }

        if ($this->getModel()->load(Yii::$app->request->post()) && $this->getModel()->validate()) {

            if ($this->formId) {
                $form = new Form([
                    'authKey' => $this->authKey,
                    'username' => $this->username,
                    'password' => $this->password,
                    'formId' => $this->formId,
                ]);
                if ($form->create($this->getModelEmail(), $this->getSendValues())) {
                    Yii::$app->session->setFlash(self::MAIL_SUBSCRIBE_SUCCESS);
                }
            } else {
                $subscribe = new Contacts([
                    'authKey' => $this->authKey,
                    'username' => $this->username,
                    'password' => $this->password,
                    'listId' => $this->listId,
                ]);
    
                $recipientId = $subscribe->create($this->getModelEmail(), $this->getSendValues());
    
                if ($recipientId) {
    
                    if ($this->groupId) {
                        $subscribe->addToGroup($this->groupId, $recipientId);
                    }
                    
                    Yii::$app->session->setFlash(self::MAIL_SUBSCRIBE_SUCCESS);
                }
            }
        }

        ob_start();
    }

    /**
     * Get the values which should be submited to the nl2go api
     *
     * @return array
     */
    private function getSendValues()
    {
        $attributes = $this->sendAttributes ? $this->sendAttributes : $this->attributes;

        $values = [];
        foreach ($attributes as $attributeName) {
            $values[$attributeName] = $this->model->{$attributeName};
        }
        $values['is_unsubscribed'] = $this->unsubscribed;
        
        return $values;
    }

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        $content = ob_get_clean();

        return $content;
    }

    private $_model;

    /**
     * Getter method for the Model
     *
     * @return DynamicModel
     */
    public function getModel()
    {
        if ($this->_model === null) {
            $this->_model = new DynamicModel($this->attributes);
            $this->_model->attributeLabels = $this->attributeLabels;
            $this->_model->attributeHints = $this->attributeHints;
            foreach ($this->attributeRules as $rule) {
                $this->_model->addRule($rule[0], $rule[1]);
            }
        }

        return $this->_model;
    }

    /**
     * Return the Model attribute email value.
     *
     * @return string The E-Mail adresse attributes value.
     */
    public function getModelEmail()
    {
        return $this->getModel()->{$this->emailAttributeName};
    }


    /**
     * Whether mail confirmation has been done and user is subscribed to the list.
     *
     * @return boolean
     */
    public function getIsSubscribed()
    {
        return Yii::$app->session->getFlash(self::MAIL_SUBSCRIBE_SUCCESS);
    }
}