<?php

class ExtSearchForm extends SearchForm
{
    public $eav;

    public function setEav(array $eav)
    {
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
