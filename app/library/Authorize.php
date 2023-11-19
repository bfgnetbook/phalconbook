<?php

namespace Holidays\Library;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Di\Injectable;

class Authorize extends Injectable
{
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        // Comprueba si el usuario está autenticado
        $controllerName = $dispatcher->getControllerName();
        if ($controllerName === 'errors') {
            return false;
        }
        $controllerName = $dispatcher->getControllerName();
        $actionName = $dispatcher->getActionName();
        if (!$this->session->has('auth')) {
            return true;
        }
        $auth = $this->session->get('auth');
        $acl = $this->acl;
        $role = $auth['role'];
        
        if (!$acl->isComponent($controllerName)) {
            $dispatcher->forward([
                'controller' => 'errors',
                'action'     => 'route404',
                'namespace'  => 'Holidays\Controllers'
            ]);
        
            return false;
        }

        $allowed = $acl->isAllowed($role, $controllerName, $actionName);
        if (!$allowed) {
            $dispatcher->forward([
                'controller' => 'errors',
                'action'     => 'route401',
                'namespace'  => 'Holidays\Controllers'
            ]);
            return false;
        }
        return true;
    }
}
