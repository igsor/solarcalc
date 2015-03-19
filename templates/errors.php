<?php

function t_argumentError() {
    fatal_error('Argument error');
}

function t_internalError() {
    fatal_error('Internal error');
}

function t_programmingError() {
    t_internalError();
}

?>
