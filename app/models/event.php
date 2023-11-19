<?php

namespace Holidays\Models;

use Phalcon\Mvc\Model;
use Holidays\Models\User;

class Event extends Model
{
    public $id;
    public $id_user;
    public $date;
    public $reason;
    public $register;

    public function initialize()
    {
        $this->setSource("events"); // Suponiendo que tu tabla se llama 'events'

        $this->belongsTo(
            'id_user',
            User::class,
            'id',
            ['alias' => 'user']
        );
    }

    // Sobrescribe el método beforeCreate
    public function beforeCreate()
    {
        // Establece la fecha actual
        $this->register = (new \DateTime())->format('Y-m-d H:i:s');
    }
}