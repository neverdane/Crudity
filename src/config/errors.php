<?php

use Neverdane\Crudity\Error;

return array(
    "Default" => array(
        Error::REQUIRED     => "Le champ {{name}} est requis.",
        Error::WRONG_FORMAT => "Le champ {{name}} n'est pas au bon format."
    ),
    "Validators" => array(
        "Email" => array(
            Error::WRONG_FORMAT => "{{value}} ne semble pas être un email valide."
        )
    )
);