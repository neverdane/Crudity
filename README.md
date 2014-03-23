## CRUDity

CRUDity is a PHP/jQuery plugin that simplifies, secures and automates all your forms integration.

## How it works

1) CRUDity parses your HTML form (no need to adapt the structure of your form to CRUDity), analyzes all its form elements and automatically creates a Crudity Form instance stored in session.
2) On submit, the jQuery part of CRUDity automatically sends all the form data through Ajax.
3) The submit is instantly caught in PHP by CRUDity, analyzed, secured, filtered and validated, depending on each input settings (highly customizable).
4) Depending on the action requested (Creating, Reading, Updating, Deleting an entry), CRUDity will automatically process this one.
5) On error, (validators especially), the jQuery part of CRUDity will show you customizable messages.

## Dependencies

Requires jQuery 1.8.x or higher.

## Examples

