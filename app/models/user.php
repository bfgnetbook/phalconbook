<?php

namespace Holidays\Models;

use Phalcon\Mvc\Model;
use Holidays\Models\Event;

class User extends Model
{
    public $id;
    public $username;
    public $password;
    public $role;
    public $rememberToken;

    public function initialize()
    {
        $this->setSource("users"); // Suponiendo que tu tabla se llama 'users'

        $this->hasMany(
            'id',
            Event::class,
            'id_user',
            ['alias' => 'events']
        );
        
    }
}