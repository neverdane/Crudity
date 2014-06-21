<?php

return array(
    "Default" => array(
        Crudity_Error::REQUIRED     => "Le champ {{name}} est requis.",
        Crudity_Error::WRONG_FORMAT => "Le champ {{name}} n'est pas au bon format."
    ),
    "Validators" => array(
        "Email" => array(
            Crudity_Error::WRONG_FORMAT => "{{value}} ne semble pas être un email valide."
        )
    )
);