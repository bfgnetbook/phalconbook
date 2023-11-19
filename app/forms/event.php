<?php

namespace Holidays\Forms;

use Phalcon\Forms\Element\Textarea;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\Callback;
use Phalcon\Forms\Form;

class Event extends Form
{
    public function initialize()
    {
        $date = new Date(
            'date'
        );
        $date->setAttribute('class', 'form-control');
        $date->setLabel('Date');
        $date->addValidators([
            new PresenceOf([
                'message' => 'A value is required for :field'
            ]),
            new Callback([
                "message" => "The date format is invalid.",
                "callback" => function ($data) {
                    $value = \DateTime::createFromFormat('Y-m-d', $data['date']);
                    return $value && $value->format('Y-m-d') === $data['date'];
                }
            ])
        ]);

        $reason = new Textarea(
            'reason'
        );
        $reason->setAttribute('class', 'form-control');
        $reason->setAttribute('rows', '4');
        $reason->setAttribute('maxlength', '250');
        $reason->setLabel('Reason');
        $reason->setFilters([
            'special',
            'striptags'
        ]);
        $reason->addValidators([
            new PresenceOf([
                'message' => 'A value is required for :field'
            ])
        ]);

        $this->add($date);
        $this->add($reason);
    }
}
