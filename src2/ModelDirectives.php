<?php

namespace Francerz\PhpModel;

class ModelDirectives
{
    /**
     * Undocumented function
     *
     * @param string $name
     * @return boolean
     */
    static protected function isValidPropertyName($name)
    {
        if (is_string($name)) {
            return preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/',$name);
        }
        return false;
    }
}