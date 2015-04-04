<?php

function t_helptext( $key ) {
    global $HELP_TEXT;

    if (!key_exists($key, $HELP_TEXT)) {
        $helptext = '';
    } else {
        $helptext = $HELP_TEXT[$key];
    }
    
    return " onmouseover='displayHelp(\"$helptext\")' onmouseout='displayHelp(\"\")'";
}

// EOF //
