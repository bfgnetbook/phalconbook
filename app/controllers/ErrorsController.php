<?php

namespace Holidays\Controllers;

use Phalcon\Mvc\Controller;

class ErrorsController extends Controller
{
    public function initialize()
    {
        $this->tag->title()->set('Oops!');
    }

    public function route404Action(): void
    {
        $this->response->setStatusCode(404);
    }

    public function route401Action(): void
    {
        $this->response->setStatusCode(401);
    }

    public function route500Action(): void
    {
        $this->response->setStatusCode(500);
    }
}