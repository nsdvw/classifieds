<?php

class EavSearchForm extends SearchForm
{
    public $eav;
    public $model;

    public function __construct()
    {
        $model = new Ad;
        $this->model = $model;
    }
}
