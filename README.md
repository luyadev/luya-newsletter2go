<p align="center">
  <img src="https://raw.githubusercontent.com/luyadev/luya/master/docs/logo/luya-logo-0.2x.png" alt="LUYA Logo"/>
</p>

# LUYA Newsletter2Go

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)

A subscription widget and helper methods to work with newsletter2go.com serivce.

## Installation

Install the extension through composer:

```sh
composer require luyadev/luya-newsletter2go
```

## Usage

```php
<?php $widget = SubscribeFormWidget::begin([
  'accessToken' => 123,
  'listId' => 123,
  'username' => '...',
  'password' => '...',
  ]) ?>
    <?php if ($widget->isSubscribed): ?>
        <div class="alert alert-success">Thanks, your email address has been added to the subscription list.</div>
    <?php else: ?>
        <?php $form = ActiveForm::begin(); ?>
             <?= $form->field($widget->model, 'email'); ?>
             <?= Html::submitButton('Submit'); ?>
        <?php $form::end(); ?>
    <?php endif; ?>
<?php $widget::end(); ?>
```