<?php

// Get the budget from project id. Return an array of an associative array with keys product, amount, price.
function project_budget($db, $id)
{
    $id = $db->escape_string($id); // Just to be sure.
    $data = [];

    // Get data from load.
    $result = $db->query("
        SELECT
            `name` as 'product', `amount`, `price`
        FROM
            `project_load`
        WHERE
            `project` = '{$id}' AND `sold` = true
    ") or fatal_error(mysqli_error($db));

    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    // Get data from ordinary tables.
    $tables = ['panel', 'battery', 'inverter', 'controller'];
    foreach($tables as $table) {
        $result = $db->query("
            SELECT
                `name` as 'product', `amount`, `price`
            FROM
                `project_{$table}`
            WHERE
                `project` = '{$id}'
        ") or fatal_error(mysqli_error($db));

        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    return $data;
}
