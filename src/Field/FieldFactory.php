<?php

namespace Neverdane\Crudity\Field;


class FieldFactory
{

    /**
     * Creates and return a Field
     * @param string $fieldType
     * @param array $fieldData
     * @return FieldInterface
     */
    public function createField($fieldType, $fieldData = array())
    {
        return new $fieldType($fieldData);
    }

}