<?php

function t_module_list($result, $headers, $editId='', $editCallback=null, $addCallback=null)
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
    	echo "<tr id='edit-{$row['id']}'>";
        $first = true;
        foreach($headers as $colname => $title) {
            if ($first) {
                $first = false;
                $query_string = preg_replace('/edit=\d+&?/', '', $_SERVER['QUERY_STRING']); // Hacky solution to prevent double edit while still getting mode argument for hardware page
                if ($editId == $row['id']) {
                    echo "<td><a href='{$_SERVER['SCRIPT_NAME']}?$query_string'>{$row[$colname]}</a></td>";
                } else {
                    echo "<td><a href='{$_SERVER['SCRIPT_NAME']}?edit={$row['id']}&$query_string#edit-{$row['id']}'>{$row[$colname]}</a></td>";
                }
            } else {
                echo "<td>{$row[$colname]}</td>";
            }
        }
    	echo "</tr>\n";

        if ($editId != '' and $editId == $row['id']) {
            echo "<tr><td>&nbsp;</td><td colspan='" . ($result->field_count - 3) . "'>";
            $editCallback($row);
            echo "</td><td><form action='' method='POST' id='deleteForm'><input type='hidden' name='id' value='{$editId}' /><input type='hidden' name='doDelete' value='on' /><input type='button' value='DEL' onclick='confirmDelete()'></form></td></tr>";
        }

    }

    // Add table extra row.
    if ($addCallback) {
        echo "<tr id='add'><td><a onclick=\"toggleVisibility(document.getElementById('addTable'))\">Add</a></td><td colspan='" . ($result->field_count - 1) . "'>";
        $addCallback();
        echo "</td></tr>";
    }


    // Closing tags.
    echo "</table>";

    echo "</div></div>";
}

function t_module_editableLoad($data, $submitButtonName, $id)
{
    for($i=0; $i<count($data); $i++) {
        $data[$i]['formid'] = "form_{$id}_{$i}";
    }

    $columns = function ($content) use ($data) {
        foreach($data as $item) {
            echo '<td class="tbl_value">' . $content($item) . '</td>';
        }
    };

    foreach($data as $item) {
        echo "<form id='{$item['formid']}'></form>";
    }

    ?>
        <table cellspacing=0 cellpadding=0 id="<?php echo $id; ?>">
        <?php
        if (any($data, function ($item) { return isset($item['id']) && $item['id'] != ''; })) {
            ?>
                <tr>
                    <td class="tbl_key">id</td>
                    <?php
                    $columns(function ($item) {
                        return "{$item['id']}<input type='hidden' name='id' value='{$item['id']}' form='{$item['formid']}'/>";
                        });
                    ?>
                </tr>
            <?php
        }
        ?>
        <tr>
            <td class="tbl_key">Name</td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='name' class='textinput' value='{$item['name']}' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Description</td>
            <?php
                $columns(function ($item) {
                    return "<textarea name='description' form='{$item['formid']}' class='something'>{$item['description']}</textarea>";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Power<?php echo T_Units::W; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='power' class='textinput' value='{$item['power']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Type</td>
            <?php
                $columns(function ($item) {
                    return   "<select name='type' class='selectinput' form='{$item['formid']}' required>"
                           . "<option value='DC'" . ($item['type'] == 'DC'?' selected':'') . ">DC</option>"
                           . "<option value='AC'" . ($item['type'] == 'AC'?' selected':'') . ">AC</option>"
                           . "</select>\n";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Voltage<?php echo T_Units::V; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='voltage' class='textinput' value='{$item['voltage']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Price<?php echo T_Units::CFA; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='price' class='textinput' value='{$item['price']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <?php if (any($data, function ($item) { return isset($item['stock']); })) { ?>
        <tr>
            <td class="tbl_key">Stock</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='stock' class='textinput' value='{$item['stock']}' pattern='[+-]?\d+' form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <?php if (any($data, function ($item) { return isset($item['amount']); })) { ?>
        <tr>
            <td class="tbl_key">Amount</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='amount' class='textinput' value='{$item['amount']}' pattern='\d+' min=0 form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <?php if (any($data, function ($item) { return isset($item['nighttime']); })) { ?>
        <tr>
            <td class="tbl_key">Night time</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='nighttime' class='textinput' value='{$item['nighttime']}' pattern='\d+' min=0 form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <?php if (any($data, function ($item) { return isset($item['daytime']); })) { ?>
        <tr>
            <td class="tbl_key">Day time</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='daytime' class='textinput' value='{$item['daytime']}' pattern='\d+' min=0 form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <?php if (any($data, function ($item) { return isset($item['sold']); })) { ?>
        <tr>
            <td class="tbl_key">Sold</td>
            <?php
                $columns(function ($item) {
                    return "<input type='checkbox' name='sold' class='textinput' form='{$item['formid']}' />";
                });
            ?>
        </tr>
        <?php } ?>
        <tr>
            <td class="tbl_key"></td>
            <?php
                $columns(function ($item) use ($submitButtonName) {
                    return   "<input type='reset' form='{$item['formid']}' value='Cancel' />\n"
                           . "<input type='submit' name='{$submitButtonName}' value='OK' form='{$item['formid']}' formaction='' formmethod='post'/>";
                });
            ?>
        </tr>
        </table>
    <?php
}

function t_module_editableHardware($data, $submitButtonName, $id)
{
    for($i=0; $i<count($data); $i++) {
        $data[$i]['formid'] = "form_{$id}_{$i}";
    }

    $columns = function ($content) use ($data) {
        foreach($data as $item) {
            echo '<td class="tbl_value">' . $content($item) . '</td>';
        }
    };

    foreach($data as $item) {
        echo "<form id='{$item['formid']}'></form>";
    }

    ?>
        <table cellspacing=0 cellpadding=0 id="<?php echo $id; ?>">
        <?php
        if (any($data, function ($item) { return isset($item['id']) && $item['id'] != ''; })) {
            ?>
                <tr>
                    <td class="tbl_key">id</td>
                    <?php
                    $columns(function ($item) {
                        return "{$item['id']}<input type='hidden' name='id' value='{$item['id']}' form='{$item['formid']}'/>";
                        });
                    ?>
                </tr>
            <?php
        }
        ?>
        <tr>
            <td class="tbl_key">Name</td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='name' class='textinput' value='{$item['name']}' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Description</td>
            <?php
                $columns(function ($item) {
                    return "<textarea name='description' form='{$item['formid']}' class='something'>{$item['description']}</textarea>";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Loss</td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='loss' class='textinput' value='{$item['loss']}' form='{$item['formid']}' pattern='\d+(.\d+)?' required /></td>";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Max. current<?php echo T_Units::A; ?></td>
            <?php
                $columns(function ($item) {
                        return "<input type='text' name='max_current' class='textinput' value='{$item['max_current']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Price<?php echo T_Units::CFA; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='price' class='textinput' value='{$item['price']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <?php if (any($data, function ($item) { return isset($item['stock']); })) { ?>
        <tr>
            <td class="tbl_key">Stock</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='stock' class='textinput' value='{$item['stock']}' pattern='[+-]?\d+' form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <?php if (any($data, function ($item) { return isset($item['amount']); })) { ?>
        <tr>
            <td class="tbl_key">Amount</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='amount' class='textinput' value='{$item['amount']}' pattern='\d+' min=0 form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <tr>
            <td class="tbl_key"></td>
            <?php
                $columns(function ($item) use ($submitButtonName) {
                    return   "<input type='reset' form='{$item['formid']}' value='Cancel' />\n"
                           . "<input type='submit' name='{$submitButtonName}' value='OK' form='{$item['formid']}' formaction='' formmethod='post'/>";
                });
            ?>
        </tr>
        </table>
    <?php
}

function t_module_editablePanel($data, $submitButtonName, $id)
{
    for($i=0; $i<count($data); $i++) {
        $data[$i]['formid'] = "form_{$id}_{$i}";
    }

    $columns = function ($content) use ($data) {
        foreach($data as $item) {
            echo '<td class="tbl_value">' . $content($item) . '</td>';
        }
    };

    foreach($data as $item) {
        echo "<form id='{$item['formid']}'></form>";
    }

    ?>
        <table cellspacing=0 cellpadding=0 id="<?php echo $id; ?>">
        <?php
        if (any($data, function ($item) { return isset($item['id']) && $item['id'] != ''; })) {
            ?>
                <tr>
                    <td class="tbl_key">id</td>
                    <?php
                    $columns(function ($item) {
                        return "{$item['id']}<input type='hidden' name='id' value='{$item['id']}' form='{$item['formid']}'/>";
                        });
                    ?>
                </tr>
            <?php
        }
        ?>
        <tr>
            <td class="tbl_key">Name</td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='name' class='textinput' value='{$item['name']}' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Description</td>
            <?php
                $columns(function ($item) {
                    return "<textarea name='description' form='{$item['formid']}' class='something'>{$item['description']}</textarea>";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Price<?php echo T_Units::CFA; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='price' class='textinput' value='{$item['price']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Voltage<?php echo T_Units::V; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='voltage' class='textinput' value='{$item['voltage']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Power<?php echo T_Units::W; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='power' class='textinput' value='{$item['power']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Peak Power<?php echo T_Units::W; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='peak_power' class='textinput' value='{$item['peak_power']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <?php if (any($data, function ($item) { return isset($item['stock']); })) { ?>
        <tr>
            <td class="tbl_key">Stock</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='stock' class='textinput' value='{$item['stock']}' pattern='[+-]?\d+' form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <?php if (any($data, function ($item) { return isset($item['amount']); })) { ?>
        <tr>
            <td class="tbl_key">Amount</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='amount' class='textinput' value='{$item['amount']}' pattern='\d+' min=0 form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <tr>
            <td class="tbl_key"></td>
            <?php
                $columns(function ($item) use ($submitButtonName) {
                    return   "<input type='reset' form='{$item['formid']}' value='Cancel' />\n"
                           . "<input type='submit' name='{$submitButtonName}' value='OK' form='{$item['formid']}' formaction='' formmethod='post'/>";
                });
            ?>
        </tr>
        </table>
    <?php
}

function t_module_editableBattery($data, $submitButtonName, $id)
{
    for($i=0; $i<count($data); $i++) {
        $data[$i]['formid'] = "form_{$id}_{$i}";
    }

    $columns = function ($content) use ($data) {
        foreach($data as $item) {
            echo '<td class="tbl_value">' . $content($item) . '</td>';
        }
    };

    foreach($data as $item) {
        echo "<form id='{$item['formid']}'></form>";
    }

    ?>
        <table cellspacing=0 cellpadding=0 id="<?php echo $id; ?>">
        <?php
        if (any($data, function ($item) { return isset($item['id']) && $item['id'] != ''; })) {
            ?>
                <tr>
                    <td class="tbl_key">id</td>
                    <?php
                    $columns(function ($item) {
                        return "{$item['id']}<input type='hidden' name='id' value='{$item['id']}' form='{$item['formid']}'/>";
                        });
                    ?>
                </tr>
            <?php
        }
        ?>
        <tr>
            <td class="tbl_key">Name</td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='name' class='textinput' value='{$item['name']}' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Description</td>
            <?php
                $columns(function ($item) {
                    return "<textarea name='description' form='{$item['formid']}' class='something'>{$item['description']}</textarea>";
                });
            ?>
        </tr>
        <tr>
            <td class="tbl_key">Voltage<?php echo T_Units::V; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='voltage' class='textinput' value='{$item['voltage']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="tbl_key">Depth of depletion<?php echo T_Units::Percent; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='dod' class='textinput' value='{$item['dod']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="tbl_key">Loss<?php echo T_Units::Percent; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='loss' class='textinput' value='{$item['loss']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="tbl_key">Discharge</td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='discharge' class='textinput' value='{$item['discharge']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="tbl_key">Lifespan<?php echo T_Units::Cycles; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='number' name='lifespan' class='textinput' value='{$item['lifespan']}' pattern='\d+' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="tbl_key">Capacity<?php echo T_Units::Ah; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='number' name='capacity' class='textinput' value='{$item['capacity']}' pattern='\d+' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="tbl_key">Max. constant current<?php echo T_Units::A; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='max_const_current' class='textinput' value='{$item['max_const_current']}' form='{$item['formid']}' pattern='\d+(.\d+)?' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="tbl_key">max. peak current<?php echo T_Units::A; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='max_peak_current' class='textinput' value='{$item['max_peak_current']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="tbl_key">Avg. constant current<?php echo T_Units::A; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='avg_const_current' class='textinput' value='{$item['avg_const_current']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="tbl_key">Max. humidity<?php echo T_Units::Percent; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='max_humidity' class='textinput' value='{$item['max_humidity']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="tbl_key">Max. temperature<?php echo T_Units::DEG; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='max_temperature' class='textinput' value='{$item['max_temperature']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="tbl_key">Price<?php echo T_Units::CFA; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='price' class='textinput' value='{$item['price']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <?php if (any($data, function ($item) { return isset($item['stock']); })) { ?>
        <tr>
            <td class="tbl_key">Stock</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='stock' class='textinput' value='{$item['stock']}' pattern='[+-]?\d+' form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <?php if (any($data, function ($item) { return isset($item['amount']); })) { ?>
        <tr>
            <td class="tbl_key">Amount</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='amount' class='textinput' value='{$item['amount']}' pattern='\d+' min=0 form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <tr>
            <td class="tbl_key"></td>
            <?php
                $columns(function ($item) use ($submitButtonName) {
                    return   "<input type='reset' form='{$item['formid']}' value='Cancel' />\n"
                           . "<input type='submit' name='{$submitButtonName}' value='OK' form='{$item['formid']}' formaction='' formmethod='post'/>";
                });
            ?>
        </tr>
        </table>
    <?php
}

// EOF //
