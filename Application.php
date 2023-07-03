<?php
/** User: Gafour Tech **/

namespace app\core;

use app\controllers\SiteController;
use app\core\exception\NotFoundException;
use app\core\db\Database;
use app\core\db\UserModel;
/**

    * Class Application
    *
    * @author Gafour Panolong <gafopanolong.gafour@s.msumain.edu.ph>
    * @package app\core
    
**/

class Application
{   
    public static string $ROOT_DIR;

    public string $layout = 'main';
    public string $userClass;
    public Router $router; // Public property to hold the Router instance
    public Request $request;
    public Response $response;
    public Session $session;
    public Database $db;
    public View $view;
    public static Application $app;
    public ?Controller $controller = NULL;
    public ?UserModel $user;
    
    
    public function __construct($rootPath, array $config) 
    {   
       
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;

        $this->request = new Request(); // Initialize a new Request object
        $this->response = new Response(); // Initialize a new Response object
        $this->session = new Session(); // Initialize a new Session object
        $this->router = new Router($this->request, $this->response); // Initialize a new Router object
        $this->view = new View(); // Initialize a new View object

        $this->db = new Database($config['db']);
        $this->userClass = $config['userClass'];

        $primaryValue = $this->session->get('user');

        if ($primaryValue) {
            $userModel = new $this->userClass();
            $primaryKey = $userModel->primaryKey();
            $this->user = $userModel->findUser([$primaryKey => $primaryValue]);
        } else {
            $this->user = NULL;
        }
    }

    public static function isGuest()
    {
        return !self::$app->user;
    }

    public function run() {
        try {
            echo $this->router->resolve();
        } catch (\Exception $e) {
            $this->response->setStatusCode($e->getCode());
            echo $this->view->renderView('_error', [
                'exception' => $e
            ]);
        }
        
    }

    /**
     * @return \app\core\Controller
     */

     public function getController()
     {
        return $this->controller;
     }

     /**
     * @param \app\core\Controller $controller
     */

     public function setController(\app\core\Controller $controller): void
     {
        $this->controller = $controller;
     }

     public function login(UserModel $user)
     {
        $this->user = $user;
        $primaryKey = $user->primaryKey();
        $primaryValue = $user->{$primaryKey};
        $this->session->set('user', $primaryValue);
        return true;
     }

     public function logout()
     {
        $this->user = NULL;
        $this->session->remove('user');
     }
}
