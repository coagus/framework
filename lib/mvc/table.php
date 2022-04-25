<?php
require_once 'db.php';

class Table {
    private $pdo;
    private $table;
    private $fields;
    private $values;
    private $where;
    private $update;
    
    public function __CONSTRUCT($table) {
        try {
            $strConnect  = 'mysql:host=' . HOST . ';dbname=' . DBNAME . ';charset=' . CHARSET;
            $this->table = $table;
            $this->pdo   = new PDO($strConnect, USR, PWD);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (Exception $e) {
            $this->showError($e,"Data Connect");
        }
    }
    
    private function fillFields($row) {
        $this->fields = "";
        $this->values = "";
        
        foreach ($row as $key => $value) {
            if ($value != '' && $key != 'id') {
                $this->fields .= $key . ',';
                $this->values .= "'" . $value . "',";
            }
        }
        
        $this->fields = substr($this->fields, 0, -1);
        $this->values = substr($this->values, 0, -1);
    }
    
    private function fillWhere($row) {
        $this->where = '';
        
        foreach ($row as $key => $value) {
            if ($value != '' && $value != null) {
                $this->where .= $key . "='" . $value . "' and ";
            }
        }
        
        $this->where = $this->where == '' ? '' : ' where ' . substr($this->where, 0, -4);
    }
    
    private function fillUpdate($row) {
        $this->update = '';
        
        foreach ($row as $key => $value) {
            if ($value != '' && $value != null && $key != 'Id') {
                $this->update .= $key . "='" . $value . "', ";
            }
        }
        
        $this->update = substr($this->update, 0, -2);
    }
    
    protected function createRow($row) {
        $this->fillFields($row);
        $qry = "INSERT INTO $this->table ($this->fields) VALUES ($this->values)";
        
        try {
            $stm = $this->pdo->prepare($qry);
            $stm->execute();
        }
        catch (Exception $e) {
            $this->showError($e,$qry);
        }
    }
    
    public function getList($limit = "1000") {
        try {
            $qry = "SELECT * FROM $this->table LIMIT $limit";
            $stm = $this->pdo->prepare($qry);
            $stm->execute();
            
            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch (Exception $e) {
            $this->showError($e,$qry);
        }
    }
    
    public function getById($Id) {
        try {
            $qry = "SELECT * FROM $this->table where Id = $Id";
            $stm = $this->pdo->prepare($qry);
            $stm->execute();
            
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch (Exception $e) {
            $this->showError($e,$qry);
        }
    }
    
    protected function getRowsWhere($row) {
        $this->fillWhere($row);
        
        try {
            $qry = "SELECT * FROM $this->table $this->where";
            
            $stm = $this->pdo->prepare($qry);
            $stm->execute();
            
            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch (Exception $e) {
            $this->showError($e,$qry);
        }
    }
    
    protected function updateRow($row) {
        $this->fillUpdate($row);
        $Id = $row['Id'];
        
        try {
            $qry = "UPDATE $this->table SET $this->update where Id = $Id";
            $stm = $this->pdo->prepare($qry);
            $stm->execute();
        }
        catch (Exception $e) {
            $this->showError($e,$qry);
        }
    }
    
    public function delete($Id) {
        try {
            $qry = "DELETE FROM $this->table WHERE Id = $Id";
            $stm = $this->pdo->prepare($qry);
            $stm->execute();
        }
        catch (Exception $e) {
            $this->showError($e,$qry);
        }
    }
    
    public function getQuery($qry, $limit) {
        try {
            $qry = "$qry LIMIT $limit";
            $stm = $this->pdo->prepare($qry);
            $stm->execute();
            
            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch (Exception $e) {
            $this->showError($e,$qry);
        }
    }
    
    public function executeQry($qry) {
        try {
            $stm = $this->pdo->prepare($qry);
            $stm->execute();
        }
        catch (Exception $e) {
            $this->showError($e,$qry);
        }
    }
    
    public function saveData($id, $data) {
        if ($this->validUnique($data)) {
            if ($id == '') {
                $this->createRow($data);
            } else {
                $this->updateRow($data);
            }
        } else {
            return false;
        }
        return true;
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    private function validUnique($row) {
        $qry = "SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = '".DBNAME."'
                AND TABLE_NAME = '".$this->table."'
                AND COLUMN_KEY IN('PRI', 'UNI')";
                
        foreach ($row as $key => $value) {
            if ($key == 'Id') {
                $id = $value;
                break;
            }
        }
                
        $uniqueList = $this->getQuery($qry,10);
        
        if ($uniqueList != null) {
            foreach ($uniqueList as $u) {
                foreach ($row as $key => $value) {
                    if ($key == $u->COLUMN_NAME && $key != 'Id') {
                        $qry = 'SELECT * FROM '.$this->table." WHERE $key = '$value' AND Id <> '$id'";
                        if (count($this->getQuery($qry,10)) > 0) {
                            $this->showWarning("Ingrese otro valor para el campo <strong>$key</strong> dado que ya existe un registro con el valor <strong>$value</strong>.");
                            return false;
                        }
                    }
                }
            }
        }
        
        return true;
    }
    
    public function showWarning($msg) {
        echo 
            '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>
                    <i class="fas fa-exclamation-triangle" style="font-size:17px"></i> 
                    Advertencia!
                </strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button> 
                <hr>'.$msg.'
            </div>';
    }
    
    public function showError($e,$qry) {
        $super = true; //$_SESSION["Usuario"] == 'coagus';
        $button = $super ? 
            '<button type="button" class="btn  btn-sm btn-link" data-toggle="modal" data-target="#detail">
                <i class="fas fa-eye" style="font-size:17px"></i>
            </button>' : '';
        $modal = $super ?
            '<div class="modal fade" id="detail" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Error en base de datos</h5>
                        </div>
                        <div class="modal-body"> <strong>Detalle del Error</strong> <br>
                            '. $e->getMessage(). ' <hr> <strong>Query</strong> <br> ' . $qry . '
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                                <i class="fas fa-times-circle" style="font-size:17px"></i>
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>' : '';
            
        echo 
            '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>
                    <i class="fas fa-bug" style="font-size:17px"></i> 
                    Error de Base de Datos!
                </strong>
                '.$button.'
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button> '.$modal.'
                <hr>
                Comuniquese con el administrador del sistema y explicar en qué momento sucedió el error.
            </div>';
    }
    
    public function showRegNoSuper($table, $r) {
        $isSuper = false;
        foreach($r as $key => $value) { 
            if ($table == 'Usuario' && $key == 'Usuario' && $value == 'coagus') {
                $isSuper = true;
                break;
            }
        } 
        return $_SESSION["Usuario"] == 'coagus' 
            || !$isSuper;
    }
    
    // For to edit
    public function isNotRequired($key) {
        return false;
    }
    
    public function hideFieldToEdit($key) {
        return false;
    }
    public function getCustomValue($key, $value) {
        return $value;
    }

    public function isTextArea($key) {
        return false;
    }

    // For to list 
    public function showField($key) {
        return true;
    }
    
    public function hideField($key) {
        $hide = array("Id");
        return in_array($key, $hide);
    }
    
    public function hideFieldView($key) {
        $hide = array("Id");
        return in_array($key, $hide);
    }

    public function isImage($key) {
        return false;
    }

    public function isMoney($key) {
        return false;
    }
    
    // Catálogos
    public function hasCatalogo($key) {
        return false;
    }
    
    public function getValCatalogo($key,$val) {
        return $val;
    }
    
    public function getCatalogo($key) {
        $catalogo = [];
        return $catalogo;
    }
    
    // Titulos
    public function getTitleList() {
        return $this->table.'s';
    }
    
    public function getTitleSingle() {
        return $this->table;
    }

    public function min($key) {        
        return '0';
    }
}
?>