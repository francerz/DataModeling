<?php

namespace Francerz\PhpModel;

class TypedCollection extends TypedItem
{
    private $items;

    public function __construct($type, $content)
    {
        $data = ArrayHelper::getAssociative($content);
        $items = ArrayHelper::getIndexed($content);
        
        parent::__construct($type, $data);
    }
}