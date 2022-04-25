<?php
class Controller
{
    protected $table = null;
    private $controller;
    private $action;
    private $isCRUD = false;
    private $key;
    private $path;

    public function __CONSTRUCT($controller, $action)
    {
        $this->controller = $controller;
        $this->action = $action;

        if (
            !file_exists(__DIR__ . "/../../vie/$this->controller/$this->action.phtml")
            && (file_exists(__DIR__ . "/$this->action.phtml") || $this->action == "delete")
        ) {
            $pathObject = __DIR__ . "/../../mdl/$this->controller.php";

            if (file_exists($pathObject)) {
                require_once $pathObject;
                $table = ucwords($this->controller);
                $this->table = new $table();
                $this->isCRUD = true;
            } else {
                $error = "No existe mantenimiento de " . ucwords($this->controller) . " ($pathObject) ni vista $view a presentar";
                $this->showError($error);
                return;
            }
        }
    }

    protected function view($view = '')
    {
        $view = $view == '' ? $this->action : $view;
        $pathview  = __DIR__ . "/../../vie/$this->controller/$view.phtml";

        if (file_exists($pathview)) {
            require_once $pathview;
        } else {
            if ($this->isCRUD) {
                require_once __DIR__ . "/$view.phtml";
            } else {
                echo "No se encuentra la vista $view";
            }
        }
    }

    public function index()
    {
        $this->view();
    }

    public function viewReg()
    {
        $this->view();
    }

    public function edit()
    {
        $view = '';

        if (isset($_REQUEST['Id'])) {
            foreach ($this->table as $key => $value) {
                if ($key == 'Id') {
                    $this->table->Id = $_REQUEST['Id'] == "0" ? '' : $_REQUEST['Id'];
                } else {
                    $this->table->$key = $_REQUEST[$key];
                }
            }

            if ($this->table->save()) {
                $this->tableList = $this->table->getList();
                $view = 'index';
            }
        }

        $this->view($view);
    }

    public function delete()
    {
        if (isset($_REQUEST['Id']))
            $this->table->delete($_REQUEST['Id']);

        $this->view('index');
    }

    public function is_Date($str)
    {
        $str   = str_replace("/", "-", $str);
        $stamp = strtotime($str);

        if (strpos($str, "-") !== false && is_numeric($stamp)) {
            $month = date('m', $stamp);
            $day   = date('d', $stamp);
            $year  = date('Y', $stamp);

            return checkdate($month, $day, $year);
        }

        return false;
    }

    public function formUpLoad()
    {
        $this->key = $_REQUEST['key'];
        $this->view();
    }

    public function upLoadImage()
    {
        $this->key = $_REQUEST['key'];
        $errors = array();
        $file_name = $_FILES['file_img']['name'];
        $file_size = $_FILES['file_img']['size'];
        $file_tmp = $_FILES['file_img']['tmp_name'];
        $file_type = $_FILES['file_img']['type'];
        $tmp = explode('.', $_FILES['file_img']['name']);
        $file_ext = strtolower(end($tmp));

        $extensions = array("jpeg", "jpg", "png");

        if (in_array($file_ext, $extensions) === false) {
            $errors[] = "extension not allowed, please choose a JPEG or PNG file.";
        }

        if ($file_size > 2097152) {
            $errors[] = 'File size must be excately 2 MB';
        }

        if (empty($errors) == true) {
            $this->path = "img/upload/" . $file_name;
            move_uploaded_file($file_tmp, __DIR__ . "/../../" . $this->path);
        } else {
            $this->showError($errors);
        }
        $this->view();
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function showError($e)
    {
        echo
        '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>
                    <i class="fas fa-bug" style="font-size:17px"></i> 
                    Error Controlado!
                </strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <hr>
                ' . $e . '
            </div>';
    }
}
