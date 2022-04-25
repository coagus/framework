<?php
require_once __DIR__ . '/../lib/mvc/table.php';

class User extends Table {
    public $Id = '';
    public $Name = '';
    public $User = '';
    public $Password = '';
    
    public function __CONSTRUCT() {
        parent::__construct('User');
    }
    
    // Control
    public function save() {
        return $this->saveData($this->Id, get_object_vars($this));
    }
    
    public function getWhere() {
        return $this->getRowsWhere(get_object_vars($this));
    }
    
    // Visualización al listar
    public function showField($name) {
        $show = array("Usuario", "Rol");
        return in_array($name, $show);
    }

    public function hideField($name) {
        $hide = array("Id", "Password");
        return in_array($name, $hide);
    }
    
    public function hideFieldView($key) {
        $hide = array("Id", "Password");
        return in_array($key, $hide);
    }
}
?>
