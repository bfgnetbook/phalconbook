<?php

namespace Holidays\Library;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class HandleException extends Injectable
{
    public function beforeException(Event $event, Dispatcher $dispatcher, \Exception $exception)
    {
        if ($exception instanceof DispatcherException) {
            switch ($exception->getCode()) {
                    // Error 404 - No encontrado
                case DispatcherException::EXCEPTION_HANDLER_NOT_FOUND:
                case DispatcherException::EXCEPTION_ACTION_NOT_FOUND:
                    $dispatcher->forward([
                        'controller' => 'errors',
                        'action'     => 'route404',
                        'namespace'  => 'Holidays\Controllers'
                    ]);
                    break;
                default:
                    $dispatcher->forward([
                        'controller' => 'errors',
                        'action'     => 'route500',
                        'namespace'  => 'Holidays\Controllers'
                    ]);
                    break;
            }
            return false;
        }

        return !$event->isStopped();
    }
}
