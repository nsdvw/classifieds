<?php

class EavSearchForm extends SearchForm
{
    public $eav;
    public $model;

    public function __construct(Ad $model)
    {
        $this->model = $model;
    }

    public function setEav()
    {
        $eav = $this->model->eavAttributes;
        $variants = AttrVariant::getVariants($eav);
        foreach ($eav as $attr=>$val) {
            if (array_key_exists($attr, $variants)) {
                $this->eav[$attr] = $val;
            } else {
                $this->eav[$attr]['min'] = $val;
                $this->eav[$attr]['max'] = $val;
            }
        }
    }
}
