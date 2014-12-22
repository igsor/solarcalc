<?php

/*
 *
 *
 *
 */

function t_scroll_table($result, $headers, $editId='', $editCallback=null)
{
    // Surrounding div's.
    echo "<div class='fixed-table-container'><div class='header-background'> </div><div class='fixed-table-container-inner'>";

    // Table header.
    echo "<table cellspacing='0'><thead><tr>\n";
    foreach($headers as $colname => $title) {
        echo "<th class='first'><div class='th-inner'>{$title}</div></th>\n";
    }
    echo "</tr></thead><tbody>";

    // Table body.
    while ($row = $result->fetch_assoc()) {
    	echo "<tr>";
        $first = true;
        foreach($headers as $colname => $title) {
            if ($first) {
                $first = false;
                if ($editId == $row['id']) {
                    echo "<td><a href='{$_SERVER['SCRIPT_NAME']}'>{$row[$colname]}</a></td>";
                } else {
                    $query_string = preg_replace('/edit=\d+&?/', '', $_SERVER['QUERY_STRING']); // Hacky solution to prevent double edit while still getting mode argument for hardware page
                    echo "<td><a href='{$_SERVER['SCRIPT_NAME']}?edit={$row['id']}&$query_string'>{$row[$colname]}</a></td>";
                }
            } else {
                echo "<td>{$row[$colname]}</td>";
            }
        }
    	echo "</tr>\n";

        if ($editId != '' and $editId == $row['id']) {
            echo "<tr><td>&nbsp;</td><td colspan='" . ($result->field_count - 1) . "'>";
            $editCallback($row);
            echo "</td></tr>";
        }

    }

    // Closing tags.
    echo "</table>";
    echo "</div></div>";
}

// Will probably become obsolete once edit forms are introduced (followup story)
function t_details_table($data, $headers)
{
        echo '<table cellspacing=0 cellpadding=0>';
        foreach($headers as $key => $title) {
            echo "<tr><td class='tbl_key'>$title</td><td class='tbl_value'>{$data[$key]}</td></tr>";
        }
        echo '</table>';
}

// EOF //
