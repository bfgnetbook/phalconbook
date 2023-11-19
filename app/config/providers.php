<?php

declare(strict_types=1);

use Phalcon\Html\Escaper;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Session\Adapter\Stream as SessionAdapter;
use Phalcon\Session\Manager as SessionManager;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Di\DiInterface;
use Holidays\Library\Security;
use Holidays\Library\Authorize;
use Holidays\Library\HandleException;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Role;
use Phalcon\Acl\Component;
use Phalcon\Session\Bag;
use Phalcon\Encryption\Crypt;
use Phalcon\Http\Response\Cookies;
use Phalcon\Tag;

/**
 * Shared configuration service
 */
class ConfigProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'config',
            function () {
                return include APP_PATH . "/config/config.php";
            }
        );
    }
}

/**
 * The URL component is used to generate all kind of urls in the application
 */
class UrlProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'url',
            function () use ($di) {
                $config = $di->getShared('config');

                $url = new UrlResolver();
                $url->setBaseUri($config->application->baseUri);

                return $url;
            }
        );
    }
}

/**
 * Encrypt and decrypt provider
 */
class CryptProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'crypt',
            function () use ($di) {
                $crypt = new Crypt();
                $config = $di->getShared('config');
                // Set a global encryption key
                $crypt->setKey(
                    $config->application->key
                );

                return $crypt;
            }
        );
    }
}

/**
 * Cookies provider
 */
class CookiesProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'cookies',
            function () {
                $cookies = new Cookies();
                $cookies->useEncryption(true);
                return $cookies;
            }
        );
    }
}

/**
 * Setting up the view component
 */
class ViewProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'view',
            function () use ($di) {
                $config = $di->getShared('config');

                $view = new View();
                $view->setDI($di);
                $view->setViewsDir($config->application->viewsDir);

                $view->registerEngines(
                    [
                        '.volt' => function ($view) use ($config, $di) {

                            $volt = new VoltEngine($view, $di);

                            $volt->setOptions(
                                [
                                    'path' => $config->application->cacheDir,
                                    'separator' => '_'
                                ]
                            );

                            return $volt;
                        },
                        '.phtml' => PhpEngine::class
                    ]
                );

                return $view;
            }
        );
    }
}

/**
 * Database connection is created based in the parameters defined
 * in the configuration file
 */
class DbProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'db',
            function () use ($di) {
                $config = $di->getShared('config');


                $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
                $params = [
                    'host'     => $config->database->host,
                    'username' => $config->database->username,
                    'password' => $config->database->password,
                    'dbname'   => $config->database->dbname,
                    'charset'  => $config->database->charset
                ];

                if ($config->database->adapter == 'Postgresql') {
                    unset($params['charset']);
                }

                return new $class($params);
            }
        );
    }
}

/**
 * Start the session the first time some component request the session service
 */
class SessionProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'session',
            function () {
                $session = new SessionManager();
                $files = new SessionAdapter(
                    [
                        'savePath' => sys_get_temp_dir(),
                    ]
                );
                $session->setAdapter($files);
                $session->start();

                return $session;
            }
        );
    }
}

/**
 * Register the session flash service with the Twitter Bootstrap classes
 */
class FlashProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->set(
            'flash',
            function () {
                $escaper = new Escaper();
                $flash = new Flash($escaper);
                $flash->setImplicitFlush(false);
                $flash->setCssClasses(
                    [
                        'error'   => 'alert alert-danger',
                        'success' => 'alert alert-success',
                        'notice'  => 'alert alert-info',
                        'warning' => 'alert alert-warning'
                    ]
                );

                return $flash;
            }
        );
    }
}

/**
 * If the configuration specify the use of metadata adapter
 * use it or use memory otherwise
 */
class MetadataProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'modelsMetadata',
            function () {
                return new MetaDataAdapter();
            }
        );
    }
}

/**
 * Dispatcher provider
 * 
 */
class DispatcherProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'dispatcher',
            function () {
                $eventsManager = new EventsManager();
                $eventsManager->enablePriorities(true);
                $eventsManager->attach('dispatch:beforeException', new HandleException(), 50);
                $eventsManager->attach('dispatch:beforeExecuteRoute', new Security(), 150);
                $eventsManager->attach('dispatch:beforeExecuteRoute', new Authorize(), 200);
                $dispatcher = new Dispatcher();
                $dispatcher->setEventsManager($eventsManager);
                return $dispatcher;
            }
        );
    }
}

/**
 * ACL provider
 * 
 */
class AclProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared(
            'acl',
            function () use ($di) {
                $persistent = $di->getShared('sessionBag');
                if (isset($persistent->acl)) {
                    return $persistent->acl;
                }

                $acl = new AclList();
                $acl->setDefaultAction(\Phalcon\Acl\Enum::DENY);
                // Register roles
                $roles = [
                    new Role(
                        'manager',
                        'Administrator Access.'
                    ),
                    new Role(
                        'employed',
                        'Accounting Department Access.'
                    )
                ];
                foreach ($roles as $role) {
                    $acl->addRole($role);
                }

                $privateResources = [
                    'private'    => ['index', 'manipulation', 'delete', 'logout']
                ];
                foreach ($privateResources as $resource => $actions) {
                    $acl->addComponent(new Component($resource), $actions);
                }

                $adminResources = [
                    'private'    => ['apply'],
                    'btnApply'   => ['show']
                ];
                foreach ($adminResources as $resource => $actions) {
                    $acl->addComponent(new Component($resource), $actions);
                }

                $publicResources = [
                    'index'    => ['index', 'route404, route401', 'login'],
                ];
                foreach ($publicResources as $resource => $actions) {
                    $acl->addComponent(new Component($resource), $actions);
                }

                //Grant access to public areas to both users and guests
                foreach ($roles as $role) {
                    foreach ($publicResources as $resource => $actions) {
                        foreach ($actions as $action) {
                            $acl->allow($role->getName(), $resource, $action);
                        }
                    }
                }

                //Grant access to private area to role Users
                foreach ($privateResources as $resource => $actions) {
                    foreach ($actions as $action) {
                        $acl->allow('manager', $resource, $action);
                        $acl->allow('employed', $resource, $action);
                    }
                }

                //Grant access to private area to role Users
                foreach ($adminResources as $resource => $actions) {
                    foreach ($actions as $action) {
                        $acl->allow('manager', $resource, $action);
                    }
                }

                //The acl is stored in session, APC would be useful here too
                $persistent->acl = $acl;

                return $acl;
            }
        );
    }
}

class SessionBagProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $session = $di->getShared('session');
        $di->setShared(
            'sessionBag',
            function () use ($session) {
                return new Bag($session, 'bag');
            }
        );
    }
}

return [
    UrlProvider::class,
    ViewProvider::class,
    DbProvider::class,
    SessionProvider::class,
    FlashProvider::class,
    MetadataProvider::class,
    ConfigProvider::class,
    DispatcherProvider::class,
    AclProvider::class,
    SessionBagProvider::class,
    CryptProvider::class
];
