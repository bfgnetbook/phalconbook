<?php

namespace Holidays\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;
use Holidays\Models\Event;
use Holidays\Forms\Event as EventForm;
use Phalcon\Paginator\Adapter\NativeArray;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class PrivateController extends Controller
{

    public function initialize()
    {
        $this->view->setRenderLevel(
            View::LEVEL_LAYOUT
        );
    }

    public function indexAction()
    {
        $auth = $this->session->get('auth');
        $id_user = $auth['id'];
        $role = $auth['role'];
        $currentPage = $this->request->getQuery('page', 'int', 1);
        $rowsperpage = 10;
        $modelParameter = [
            'conditions' => 'id_user = :id_user:',
            'bind' => ['id_user' => $id_user]
        ];
        if ($role === 'manager') {
            $modelParameter = [];
        }
        /*
        $user = User::findFirst($id_user);
        if ($user) {
            $events = $user->events;
        }
        $paginator   = new NativeArray(
            [
                "data"      => $events->toArray(),
                "limit"      => $rowsperpage,
                "page"       => $currentPage,
            ]
        );*/
        $paginator   = new PaginatorModel(
            [
                "model"      => Event::class,
                "parameters" => $modelParameter,
                "limit"      => $rowsperpage,
                "page"       => $currentPage,
            ]
        );
        $this->view->showBtn = false;
        if ($this->acl->isAllowed($role, 'btnApply', 'show')) {
            $this->view->showBtn = true;
        }
        $this->view->events = $paginator->paginate();
    }

    public function logoutAction()
    {
        $this->session->destroy();
        // delete cookie server
        $this->cookies->delete('RMU');
        // delete cookie browser
        $this->cookies->get("RMU")->delete();
        return $this->response->redirect('/');
    }

    public function manipulationAction()
    {
        $id = $this->dispatcher->getParam('id');
        $this->view->id = (is_null($id)) ? '' : $id;
        $newForm =  new EventForm();
        $event = new Event();
        $this->view->form = $newForm;
        if ($this->request->isGet()) {
            if (!is_null($id)) {
                $event = Event::findFirst($id);
                $newForm =  new EventForm($event);
                $this->view->form = $newForm;
            }
            return;
        }
        if (!$newForm->isValid($this->request->getPost())) {
            $this->flash->error('Failed save!');
            return;
        }
        $date = $this->request->getPost('date');
        $reason = $this->request->getPost('reason');
        $id = $this->request->getPost('id');
        if (!empty($id)) {
            $event = Event::findFirst($id);
        }
        $auth = $this->session->get('auth');
        $id_user = $auth['id'];
        $event->date = $date;
        $event->reason = $reason;
        $event->id_user = $id_user;
        if (!$event->save()) {
            $this->flash->error('Failed save!');
            return;
        }
        $this->response->redirect('/private');
        return;
    }

    public function deleteAction()
    {
        $id = $this->dispatcher->getParam('id');
        $this->view->id = $id;
        if ($this->request->isGet()) {
            return;
        }
        $id = $this->request->getPost('id');
        $event = Event::findFirst($id);
        if (!$event->delete()) {
            $this->flash->error('Failed delete!');
            return;
        }
        $this->response->redirect('/private');
        return;
    }

    public function applyAction()
    {
        $id = $this->dispatcher->getParam('id');
        $this->view->id = $id;
        if ($this->request->isGet()) {
            return;
        }
        $id = $this->request->getPost('id');
        $option = $this->request->getPost('option');
        $event = Event::findFirst($id);
        $event->apply = $option;
        if (!$event->save()) {
            $this->flash->error('Failed delete!');
            return;
        }
        $this->response->redirect('/private');
        return;
    }
}
