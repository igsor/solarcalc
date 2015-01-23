<?php

/*
 * Provides *t_start* and *t_end* functions to initialize and finalize
 * the page layout.
 */

function t_start($title='')
{
    echo "<!DOCTYPE html>\n";
    echo "<html>\n";

    _t_head($title);

    echo "<body>\n";

    echo "<div id='centralizer'>\n";

    _t_navigation();

    echo "<div id='content'>\n";
}

function t_end()
{

    echo "</div>\n"; // content

    _t_foot();

    echo "</div>\n"; // centralizer

    // close tags
    echo "</body>\n";

    echo "</html>\n";
}

function _t_head($title='')
{
    include('header.php');
}

function _t_navigation()
{
	include('navigation.php');
}

function _t_foot()
{
	include('footer.php');
}

// EOF //
