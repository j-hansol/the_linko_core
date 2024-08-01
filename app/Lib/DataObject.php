<?php

namespace App\Lib;

class DataObject {
    function __construct(private array $data) {}

    public function __set(string $name, mixed $value) : void {$this->data[$name] = $value;}
    public function __get(string $name) : mixed {return $this->data[$name] ?? null;}
    public function __isset(string $name) : bool {return isset($this->data[$name]);}
    public function __unset(string $name) : void {if(isset($this->data[$name])) unset($this->data[$name]);}
}
