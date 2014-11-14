<?php
namespace Neverdane\Crudity;

class Helper
{
    const CRUDITY_LIB_URI = "http://crudity.lib";

    public static function getFileAsVariable($file)
    {
        ob_start();
        include($file);
        $variable = ob_get_contents();
        ob_end_clean();
        return $variable;
    }

    public static function includeResources($applicationEnv)
    {
        if ($applicationEnv === "dev") {
            ?>
            <script src="<?php echo self::CRUDITY_LIB_URI; ?>/crudity/js/crudity.js"></script>
            <script src="<?php echo self::CRUDITY_LIB_URI; ?>/crudity/js/getHiddenDimensions.js"></script>
            <script src="<?php echo self::CRUDITY_LIB_URI; ?>/crudity/js/riplace.js"></script>
            <script src="<?php echo self::CRUDITY_LIB_URI; ?>/crudity/js/selectly.js"></script>
            <link rel="stylesheet" href="<?php echo self::CRUDITY_LIB_URI; ?>/crudity/css/crudity.css">
            <?php
        }
    }
}
