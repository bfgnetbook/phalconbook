<?php

namespace Holidays\Forms;

use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Check;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Forms\Form;

class Login extends Form
{
    public function initialize()
    {
        $username = new Text(
            'username'
        );
        $username->setAttribute('class', 'form-control');
        $username->setAttribute('placeholder', 'Username');
        $username->setAttribute('id', 'username');
        $username->addValidators([
            new PresenceOf([
                'message' => 'A value is required for :field'
            ])
        ]);

        $password = new Password(
            'password'
        );
        $password->setAttribute('class', 'form-control');
        $password->setAttribute('placeholder', 'Password');
        $password->setAttribute('id', 'password');
        $username->addValidators([
            new PresenceOf([
                'message' => 'A value is required for :field'
            ])
        ]);

        $remember = new Check(
            'remember'
        );
        $remember->setAttribute('class', 'form-check-input');
        $remember->setAttribute('value', 'true');

        $this->add($username);
        $this->add($password);
        $this->add($remember);
    }
}
