<?php
namespace Neverdane\Crudity;

use Neverdane\Crudity\Exception\FileNotFoundException;

class Helper
{
    /**
     * @param string $file
     * @throws FileNotFoundException
     * @return string
     */
    public static function getFileAsVariable($file)
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
        }
        ob_start();
        include($file);
        $variable = ob_get_contents();
        ob_end_clean();
        return $variable;
    }
}
