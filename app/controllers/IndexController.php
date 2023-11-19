<?php

namespace Holidays\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Filter\FilterFactory;
use Holidays\Models\User;
use Holidays\Forms\Login as LoginForm;

class IndexController extends Controller
{

    public function indexAction()
    {
        $this->dispatcher->forward(
            [
                'controller' => 'index',
                'action'     => 'login',
                'namespace'     => 'Holidays\Controllers',
            ]
        );
    }

    public function loginAction()
    {
        /*
        $user = new User();
        $user->username = 'admin';
        $user->password = ($this->security->hash('demo'));
        $user->rol = 'admin';
        $user->save();
        die('ok');
        */
        $loginForm = new LoginForm();
        $this->view->form = $loginForm;

        if ($this->request->isGet()) {
            return;
        }
        $factory = new FilterFactory();
        $filter = $factory->newInstance();
        $username = $filter->sanitize($this->request->getPost('username'), 'special');
        $password = $filter->sanitize($this->request->getPost('password'), 'special');
        $remember = boolVal($this->request->getPost('remember'));

        if (!$loginForm->isValid($this->request->getPost())) {
            $this->flash->error('Failed login!');
            return;
        }
        // Buscar el usuario en la base de datos
        $user = User::findFirst([
            'conditions' => 'username = :username:',
            'bind'       => ['username' => $username],
        ]);

        if ($user && $this->security->checkHash($password, $user->password)) {
            // El usuario existe y la contraseña es correcta
            $this->session->set('auth', ['id' => $user->id, 'role' => $user->role]);
            if ($remember) {
                $this->createRememberMe($user);
            }
            return $this->response->redirect('/private'); // Redirigir a una ruta segura
        }

        $this->flash->error('Failed login!');
    }

    private function createRememberMe($user)
    {
        $token = $this->security->getRandom()->uuid(); // Generar un token único

        // Guardar el token en la base de datos o algún almacenamiento persistente asociado al usuario
        $user->rememberToken = $token;
        if (!$user->save()) {
            $this->dispatcher->forward([
                'controller' => 'errors',
                'action'     => 'route500',
                'namespace'  => 'Holidays\Controllers'
            ]);
        }
        // Crear la cookie
        $this->cookies->set(
            'RMU',      // Nombre de la cookie
            $token,     // Valor de la cookie (token)
            time() + 7 * 86400, // Expira en 7 días
        );
    }
}
