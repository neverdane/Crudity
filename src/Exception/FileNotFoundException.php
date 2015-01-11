<?php
namespace Neverdane\Crudity\Exception;

class FileNotFoundException extends CrudityException
{
    /**
     * @param string $file
     *   The file to find
     */
    public function __construct($file)
    {
        parent::__construct(sprintf('The file "%s" was not found', $file));
    }
}
