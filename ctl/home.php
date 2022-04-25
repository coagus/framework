<?php
require_once __DIR__ . '/../lib/mvc/controller.php';
require_once __DIR__ . '/../mdl/user.php';

class HomeController extends Controller {
    private $error = '';
    
    public function __CONSTRUCT($action = 'index') {
        parent::__construct('home', $action);
    }
    
    public function login() {
        $this->view();
    }
    
    public function setLogin() {
        $view = 'login';
        
        if (isset($_REQUEST['User']) && $_REQUEST['User']!="" && $_REQUEST['Password']!="") {
            $usr  = $_REQUEST['User'];
            $pwd = isset($_REQUEST['Password']) ? $_REQUEST['Password'] : '';
            $this->error = "user: $usr / pass: $pwd <br>";
            
            if ($this->validate($usr, $pwd)) {
                $view = 'index';
            } else {
                $this->error = 'User incorrect';
            }
        } else {
            $this->error = 'Please insert user and password';
        }
        
        $this->view($view);
    }
    
    public function validate($usr, $pwd) {
        if (trim($usr) == '' || trim($pwd) == '')
            return false;
        
        $user = new User();
        $user->User = $usr;
        $user->Password = $pwd;
        $usr = $user->getWhere();
        $valid = count($usr) == 1;
        
        if ($valid) {
            $_SESSION["Id"] = $usr[0]->Id;
            $_SESSION["User"] = $usr[0]->User;
            $_SESSION["Name"] = $usr[0]->Name;
        }
        
        return $valid;
    }
    
    public function perfil() {
        $view = 'perfil';
        if (isset($_REQUEST['Id'])) {
            $user = new User();
            $user->Id = $_REQUEST['Id'];
            $user->Name = $_REQUEST['Name'];
            $user->Password = $_REQUEST['Password'];
            $user->save();
            $view = 'index';
        }
        $this->view($view);
    }
    
    public function getCurrentUser() {
        $usr = new User();
        return $usr->getById($_SESSION["Id"]);
    }
    
    public function logout() {
        session_destroy();
        $this->view('login');
    }
    
    public function getError() {
        return $this->error;
    }
}
?>
