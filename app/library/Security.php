<?php

namespace Holidays\Library;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Di\Injectable;
use Holidays\Models\User;

class Security extends Injectable
{
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        // Comprueba si el usuario está autenticado
        $controllerName = $dispatcher->getControllerName();
        if ($controllerName === 'errors') {
            return false;
        }
        if ($controllerName === 'private') {
            if (!$this->session->get('auth') && $this->cookies->has('RMU')) {
                $token = $this->cookies->get('RMU')->getValue();

                // Buscar el usuario con este token
                $user = User::findFirst([
                    'conditions' => 'rememberToken = :token:',
                    'bind'       => ['token' => $token],
                ]);

                if ($user) {
                    // Autenticar al usuario automáticamente
                    $this->session->set('auth', ['id' => $user->id, 'role' => $user->role]);
                }
            } else if (!$this->session->has('auth')) {
                $this->response->redirect('/login');
                return false; // Detiene la ejecución del controlador actual
            }
        }
        return true;
    }
}
