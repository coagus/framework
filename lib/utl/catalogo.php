<?php
class Catalogo {
    private $estadoProd = [
        '<span class="badge badge-pill badge-warning">En Proceso</span>' => '1',
        '<span class="badge badge-pill badge-info">Creado</span>' => '2', 
        '<span class="badge badge-pill badge-primary">Despachado</span>' => '3',
        '<span class="badge badge-pill badge-success">Entregado</span>' => '4',
        '<span class="badge badge-pill badge-danger">Devuelto</span>' => '5',
        '<span class="badge badge-pill badge-danger">Devuelto parcialmente</span>' => '6',
        '<span class="badge badge-pill badge-secondary">Inconcluso</span>' => '7'
        ];
        
    public function getListEstadoProd() {
        return $this->estadoProd;
    }
    
    public function getEstadoProd($codigo) {
        return array_search($codigo, $this->estadoProd);
    }
}