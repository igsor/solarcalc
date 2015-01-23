<?php

function t_module_list($result, $headers, $editId='', $editCallback=null, $addCallback=null)
{
    // Table header.
    echo "<table cellspacing='0' cellpadding='0' class='module-list'><tr class='module-list-head'>\n";
    foreach($headers as $colname => $title) {
        echo "<td>{$title}</th>\n";
    }
    echo "</tr>";
    echo "<tr><td colspan='" . count($headers) . "' class='module-list-headspace'></td></tr>";

    // Table body.
    $cnt = 1; // Start with even.
    while ($row = $result->fetch_assoc()) {

        // CSS Class
        $class = 'module-list-item';

        if (($cnt++) % 2) {
            $class .= ' list-even';
        } else {
            $class .= ' list-odd';
        }

        if ($editId != '' and $editId == $row['id']) {
            $class .= ' module-list-edit-title';
        }

        echo "<tr class='$class' id='edit-{$row['id']}'>";
        $first = true;
        foreach($headers as $colname => $title) {

            // First row.
            if ($first) {
                $first = false;
                $query_string = preg_replace('/edit=\d+&?/', '', $_SERVER['QUERY_STRING']); // Hacky solution to prevent double edit while still getting mode argument for hardware page
                if ($editId == $row['id']) {
                    echo "<td><a href='{$_SERVER['SCRIPT_NAME']}?$query_string'>{$row[$colname]}</a></td>";
                } else {
                    echo "<td><a href='{$_SERVER['SCRIPT_NAME']}?edit={$row['id']}&$query_string#edit-{$row['id']}'>{$row[$colname]}</a></td>";
                }
            } else { // Other rows.
                echo "<td" . (is_numeric($row[$colname])?" class='number'":'') . ">{$row[$colname]}</td>";
            }
        }
    	echo "</tr>\n";

        // Display edit form.
        if ($editId != '' and $editId == $row['id']) {
            echo "<tr class='module-list-item'><td>&nbsp;</td><td class='module-list-edit' colspan='" . ($result->field_count - 3) . "'>";
            $editCallback($row);
            echo "</td><td class='module-list-edit'><form action='' method='POST' id='deleteForm'><input type='hidden' name='id' value='{$editId}' /><input type='hidden' name='doDelete' value='on' /><button type='button' value='DEL' onclick='confirmDelete()'>Delete</button></form></td></tr>";
        }

    }

    // Add table extra row.
    if ($addCallback) {
        ?>
            <tr class='module-list-item'>
                <td>
                    <a onclick="toggleVisibility(document.getElementById('addTable'), 'table-row')">Add</a>
                </td>
                <td colspan='<?php echo ($result->field_count - 2); ?>'>&nbsp;</td>
            </tr>
            <tr class='module-list-item' id='addTable' style='display: none'>
                <td>&nbsp;</td>
                <td colspan='<?php echo ($result->field_count - 2); ?>'>
                    <?php $addCallback(); ?>
                </td>
            </tr>
        <?php
    }


    // Closing tags.
    echo "</table>";
}

function t_module_editableLoad($data, $submitButtonName, $id)
{
    for($i=0; $i<count($data); $i++) {
        $data[$i]['formid'] = "form_{$id}_{$i}";
    }

    $columns = function ($content, $class='form-table-value') use ($data) {
        foreach($data as $item) {
            echo "<td class='$class'>" . $content($item) . '</td>';
        }
    };

    foreach($data as $item) {
        echo "<form id='{$item['formid']}'></form>";
    }

    ?>
        <table class='form-table' cellspacing=0 cellpadding=0 id="<?php echo $id; ?>">
        <?php
        if (any($data, function ($item) { return isset($item['id']) && $item['id'] != ''; })) {
            ?>
                <tr>
                    <td class="form-table-key">id</td>
                    <?php
                    $columns(function ($item) {
                        return "{$item['id']}<input type='hidden' name='id' value='{$item['id']}' form='{$item['formid']}'/>";
                        }, 'form-table-value number');
                    ?>
                </tr>
            <?php
        }
        ?>
        <tr>
            <td class="form-table-key">Name</td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='name' value='{$item['name']}' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Description</td>
            <?php
                $columns(function ($item) {
                    return "<textarea name='description' form='{$item['formid']}'>{$item['description']}</textarea>";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Power<?php echo T_Units::W; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='power' class='number' value='{$item['power']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Type</td>
            <?php
                $columns(function ($item) {
                    return   "<select name='type' form='{$item['formid']}' required>"
                           . "<option value='DC'" . ($item['type'] == 'DC'?' selected':'') . ">DC</option>"
                           . "<option value='AC'" . ($item['type'] == 'AC'?' selected':'') . ">AC</option>"
                           . "</select>\n";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Voltage<?php echo T_Units::V; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='voltage' class='number' value='{$item['voltage']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Price<?php echo T_Units::CFA; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='price' class='number' value='{$item['price']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <?php if (any($data, function ($item) { return isset($item['stock']); })) { ?>
        <tr>
            <td class="form-table-key">Stock</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='stock' class='number' value='{$item['stock']}' pattern='[+-]?\d+' form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <?php if (any($data, function ($item) { return isset($item['amount']); })) { ?>
        <tr>
            <td class="form-table-key">Amount</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='amount' class='number' value='{$item['amount']}' pattern='\d+' min=0 form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <?php if (any($data, function ($item) { return isset($item['nighttime']); })) { ?>
        <tr>
            <td class="form-table-key">Night time</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='nighttime' class='number' value='{$item['nighttime']}' pattern='\d+' min=0 form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <?php if (any($data, function ($item) { return isset($item['daytime']); })) { ?>
        <tr>
            <td class="form-table-key">Day time</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='daytime' class='number' value='{$item['daytime']}' pattern='\d+' min=0 form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <?php if (any($data, function ($item) { return isset($item['sold']); })) { ?>
        <tr>
            <td class="form-table-key">Sold</td>
            <?php
                $columns(function ($item) {
                    return "<input type='checkbox' name='sold' form='{$item['formid']}' />";
                }, 'form-table-value number');
            ?>
        </tr>
        <?php } ?>
        <tr>
            <td class="form-table-key"></td>
            <?php
                $columns(function ($item) use ($submitButtonName) {
                    return "<button type='reset' class='button' form='{$item['formid']}' value='Cancel'>Cancel</button>
                            <button type='submit' class='button' name='{$submitButtonName}' value='OK' form='{$item['formid']}' formaction='' formmethod='post'>OK</button>";
                }, 'form-table-value form-table-action');
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

    $columns = function ($content, $class='form-table-value') use ($data) {
        foreach($data as $item) {
            echo "<td class='$class'>" . $content($item) . '</td>';
        }
    };

    foreach($data as $item) {
        echo "<form id='{$item['formid']}'></form>";
    }

    ?>
        <table class='form-table'cellspacing=0 cellpadding=0 id="<?php echo $id; ?>">
        <?php
        if (any($data, function ($item) { return isset($item['id']) && $item['id'] != ''; })) {
            ?>
                <tr>
                    <td class="form-table-key">id</td>
                    <?php
                    $columns(function ($item) {
                        return "{$item['id']}<input type='hidden' name='id' value='{$item['id']}' form='{$item['formid']}'/>";
                        }, 'form-table-value number');
                    ?>
                </tr>
            <?php
        }
        ?>
        <tr>
            <td class="form-table-key">Name</td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='name' value='{$item['name']}' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Description</td>
            <?php
                $columns(function ($item) {
                    return "<textarea name='description' form='{$item['formid']}' >{$item['description']}</textarea>";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Loss</td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='loss' class='number' value='{$item['loss']}' form='{$item['formid']}' pattern='\d+(.\d+)?' required /></td>";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Max. current<?php echo T_Units::A; ?></td>
            <?php
                $columns(function ($item) {
                        return "<input type='text' name='max_current' class='number' value='{$item['max_current']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Price<?php echo T_Units::CFA; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='price' class='number' value='{$item['price']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <?php if (any($data, function ($item) { return isset($item['stock']); })) { ?>
        <tr>
            <td class="form-table-key">Stock</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='stock' class='number' value='{$item['stock']}' pattern='[+-]?\d+' form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <?php if (any($data, function ($item) { return isset($item['amount']); })) { ?>
        <tr>
            <td class="form-table-key">Amount</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='amount' class='number' value='{$item['amount']}' pattern='\d+' min=0 form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <tr>
            <td class="form-table-key"></td>
            <?php
                $columns(function ($item) use ($submitButtonName) {
                    return "<button type='reset' class='button' form='{$item['formid']}' value='Cancel'>Cancel</button>
                            <button type='submit' class='button' name='{$submitButtonName}' value='OK' form='{$item['formid']}' formaction='' formmethod='post'>OK</button>";
                }, 'form-table-value form-table-action');
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

    $columns = function ($content, $class='form-table-value') use ($data) {
        foreach($data as $item) {
            echo "<td class='$class'>" . $content($item) . '</td>';
        }
    };

    foreach($data as $item) {
        echo "<form id='{$item['formid']}'></form>";
    }

    ?>
        <table class='form-table'cellspacing=0 cellpadding=0 id="<?php echo $id; ?>">
        <?php
        if (any($data, function ($item) { return isset($item['id']) && $item['id'] != ''; })) {
            ?>
                <tr>
                    <td class="form-table-key">id</td>
                    <?php
                    $columns(function ($item) {
                        return "{$item['id']}<input type='hidden' name='id' value='{$item['id']}' form='{$item['formid']}'/>";
                        }, 'form-table-value number');
                    ?>
                </tr>
            <?php
        }
        ?>
        <tr>
            <td class="form-table-key">Name</td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='name' value='{$item['name']}' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Description</td>
            <?php
                $columns(function ($item) {
                    return "<textarea name='description' form='{$item['formid']}'>{$item['description']}</textarea>";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Price<?php echo T_Units::CFA; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='price' class='number' value='{$item['price']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Voltage<?php echo T_Units::V; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='voltage' class='number' value='{$item['voltage']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Power<?php echo T_Units::W; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='power' class='number' value='{$item['power']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Peak Power<?php echo T_Units::W; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='peak_power' class='number' value='{$item['peak_power']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <?php if (any($data, function ($item) { return isset($item['stock']); })) { ?>
        <tr>
            <td class="form-table-key">Stock</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='stock' class='number' value='{$item['stock']}' pattern='[+-]?\d+' form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <?php if (any($data, function ($item) { return isset($item['amount']); })) { ?>
        <tr>
            <td class="form-table-key">Amount</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='amount' class='number' value='{$item['amount']}' pattern='\d+' min=0 form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <tr>
            <td class="form-table-key"></td>
            <?php
                $columns(function ($item) use ($submitButtonName) {
                    return "<button type='reset' class='button' form='{$item['formid']}' value='Cancel'>Cancel</button>
                            <button type='submit' class='button' name='{$submitButtonName}' value='OK' form='{$item['formid']}' formaction='' formmethod='post'>OK</button>";
                }, 'form-table-value form-table-action');
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

    $columns = function ($content, $class='form-table-value') use ($data) {
        foreach($data as $item) {
            echo "<td class='$class'>" . $content($item) . '</td>';
        }
    };

    foreach($data as $item) {
        echo "<form id='{$item['formid']}'></form>";
    }

    ?>
        <table class='form-table'cellspacing=0 cellpadding=0 id="<?php echo $id; ?>">
        <?php
        if (any($data, function ($item) { return isset($item['id']) && $item['id'] != ''; })) {
            ?>
                <tr>
                    <td class="form-table-key">id</td>
                    <?php
                    $columns(function ($item) {
                        return "{$item['id']}<input type='hidden' name='id' value='{$item['id']}' form='{$item['formid']}'/>";
                        }, 'form-table-value number');
                    ?>
                </tr>
            <?php
        }
        ?>
        <tr>
            <td class="form-table-key">Name</td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='name' value='{$item['name']}' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Description</td>
            <?php
                $columns(function ($item) {
                    return "<textarea name='description' form='{$item['formid']}'>{$item['description']}</textarea>";
                });
            ?>
        </tr>
        <tr>
            <td class="form-table-key">Voltage<?php echo T_Units::V; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='voltage' class='number' value='{$item['voltage']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="form-table-key">Depth of depletion<?php echo T_Units::Percent; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='dod' class='number' value='{$item['dod']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="form-table-key">Loss<?php echo T_Units::Percent; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='loss' class='number' value='{$item['loss']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="form-table-key">Discharge</td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='discharge' class='number' value='{$item['discharge']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="form-table-key">Lifespan<?php echo T_Units::Cycles; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='number' name='lifespan' class='number' value='{$item['lifespan']}' pattern='\d+' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="form-table-key">Capacity<?php echo T_Units::Ah; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='number' name='capacity' class='number' value='{$item['capacity']}' pattern='\d+' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="form-table-key">Max. constant current<?php echo T_Units::A; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='max_const_current' class='number' value='{$item['max_const_current']}' form='{$item['formid']}' pattern='\d+(.\d+)?' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="form-table-key">Max. peak current<?php echo T_Units::A; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='max_peak_current' class='number' value='{$item['max_peak_current']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="form-table-key">Avg. constant current<?php echo T_Units::A; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='avg_const_current' class='number' value='{$item['avg_const_current']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="form-table-key">Max. humidity<?php echo T_Units::Percent; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='max_humidity' class='number' value='{$item['max_humidity']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="form-table-key">Max. temperature<?php echo T_Units::DEG; ?></td>
        <?php
            $columns(function ($item) {
                return "<input type='text' name='max_temperature' class='number' value='{$item['max_temperature']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
            });
        ?>
        </tr>
        <tr>
            <td class="form-table-key">Price<?php echo T_Units::CFA; ?></td>
            <?php
                $columns(function ($item) {
                    return "<input type='text' name='price' class='number' value='{$item['price']}' pattern='\d+(.\d+)?' form='{$item['formid']}' required />";
                });
            ?>
        </tr>
        <?php if (any($data, function ($item) { return isset($item['stock']); })) { ?>
        <tr>
            <td class="form-table-key">Stock</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='stock' class='number' value='{$item['stock']}' pattern='[+-]?\d+' form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <?php if (any($data, function ($item) { return isset($item['amount']); })) { ?>
        <tr>
            <td class="form-table-key">Amount</td>
            <?php
                $columns(function ($item) {
                    return "<input type='number' name='amount' class='number' value='{$item['amount']}' pattern='\d+' min=0 form='{$item['formid']}' required/>";
                });
            ?>
        </tr>
        <?php } ?>
        <tr>
            <td class="form-table-key"></td>
            <?php
                $columns(function ($item) use ($submitButtonName) {
                    return "<button type='reset' class='button' form='{$item['formid']}' value='Cancel'>Cancel</button>
                            <button type='submit' class='button' name='{$submitButtonName}' value='OK' form='{$item['formid']}' formaction='' formmethod='post'>OK</button>";
                }, 'form-table-value form-table-action');
            ?>
        </tr>
        </table>
    <?php
}

// EOF //
