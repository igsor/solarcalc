<?php

require_once('init.php');

/** PARAMETERS **/

// Edit parameter.
$editId = '';
if (key_exists('edit', $_GET)) {
    $editId = $_GET['edit'];
}

// Database connection.
$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or die(mysqli_connect_error());

if (isset($_POST['doEdit']) or isset($_POST['doAdd'])) {
    // Handle form data.
    $data['name']           = isset($_POST['name'])         ? $db->escape_string($_POST['name'])        : die('Not enough arguments');
    $data['description']    = isset($_POST['description'])  ? $db->escape_string($_POST['description']) : ''; // Optional argument
    $data['power']          = isset($_POST['power'])        ? $db->escape_string($_POST['power'])       : die('Not enough arguments');
    $data['type']           = isset($_POST['type'])         ? $db->escape_string($_POST['type'])        : die('Not enough arguments');
    $data['voltage']        = isset($_POST['voltage'])      ? $db->escape_string($_POST['voltage'])     : die('Not enough arguments');
    $data['price']          = isset($_POST['price'])        ? $db->escape_string($_POST['price'])       : die('Not enough arguments');
    $data['stock']          = isset($_POST['stock'])        ? $db->escape_string($_POST['stock'])       : die('Not enough arguments');

    // Edit response.
    if (isset($_POST['doEdit'])) {
        // Check if id present
        if ($editId == '') {
            die('Argument error');
        }

        // Update the database.
        $db->query("
            UPDATE
                `load`
            SET
                  `name`        = '{$data['name']}'
                , `description` = '{$data['description']}'
                , `power`       = '{$data['power']}'
                , `type`        = '{$data['type']}'
                , `voltage`     = '{$data['voltage']}'
                , `price`       = '{$data['price']}'
                , `stock`       = '{$data['stock']}'        
            WHERE
                `id` = '" . $db->escape_string($editId) . "'
        ") or die(mysqli_error($db));

        // FIXME: Action?
    }

    // Add response.
    if (isset($_POST['doAdd'])) {
        // Update the database.
        $db->query("
            INSERT
            INTO `load` (
                  `name`
                , `description`
                , `power`
                , `type`
                , `voltage`
                , `price`
                , `stock`
                )
            VALUES (
                  '{$data['name']}'
                , '{$data['description']}'
                , '{$data['power']}'
                , '{$data['type']}'
                , '{$data['voltage']}'
                , '{$data['price']}'
                , '{$data['stock']}'
                )
        ") or die(mysqli_error($db));

        // Go to edit mode.
        $editId = $db->insert_id;
    }
}


/** PAGE CONTENT **/

// Layout start.
t_start();

// Edit display.
$editCallback = function($row) use ($db)
{
    $query = "SELECT * FROM `load` WHERE `id` = '{$row['id']}'";
    $result = $db->query($query) or die(mysqli_error($db));
    $data = $result->fetch_assoc();
    $result->free();
    t_editableLoad($data, 'doEdit', 'editTable');
};

$addCallback = function()
{
    t_editableLoad(array(), 'doAdd', 'addTable');
};

function t_editableLoad($data, $submitButtonName, $id)
{
    ?>
        <form action="<?php /* FIXME: INSERT URL */ ?>" method="POST">
        <table cellspacing=0 cellpadding=0 id="<?php echo $id; ?>">
            <?php
                if (key_exists('id', $data)) {
                    ?>
                        <tr>
                            <td class="tbl_key">id</td>
                            <td class="tbl_value"><?php echo $data['id']; ?></td>
                        </tr>
                    <?php
                }
            ?>
            <tr>
                <td class="tbl_key">Name</td>
                <td class="tbl_value"><input type="text" name="name" class="textinput" value="<?php echo isset($data['name']) ? $data['name'] : ''; ; ?>" required /></td>
            </tr>
            <tr>
                <td class="tbl_key">Description</td>
                <td class="tbl_value"><textarea name="description" class="selectinput"><?php echo isset($data['description']) ? $data['description'] : ''; ?></textarea></td>
            </tr>
            <tr>
                <td class="tbl_key">Power<?php echo T_Units::W; ?></td>
                <td class="tbl_value"><input type="text" name="power" class="textinput" value="<?php echo isset($data['power']) ? $data['power'] : ''; ?>" pattern="[\d.]+" required /></td>
            </tr>
            <tr>
                <td class="tbl_key">Type</td>
                <td class="tbl_value">
                    <select name="type" class="selectinput" required>
                        <option value="DC" <?php echo ((!isset($data['type']) or (isset($data['type']) and $data['type'] == "DC")) ? 'selected' : ''); ?>>DC</option>
                        <option value="AC" <?php echo ((isset($data['type']) and $data['type'] == "AC") ? 'selected' : ''); ?>>AC</option>
                    </select>
            </tr>
            <tr>
                <td class="tbl_key">Voltage<?php echo T_Units::V; ?></td>
                <td class="tbl_value"><input type="text" name="voltage" class="textinput" value="<?php echo isset($data['voltage']) ? $data['voltage'] : ''; ?>" pattern="[\d.]+" required /></td>
            </tr>
            <tr>
                <td class="tbl_key">Price<?php echo T_Units::CFA; ?></td>
                <td class="tbl_value"><input type="text" name="price" class="textinput" value="<?php echo isset($data['price']) ? $data['price'] : ''; ?>" pattern="[\d.]+" required /></td>
            </tr>
            <tr>
                <td class="tbl_key">Stock</td>
                <td class="tbl_value"><input type="number" name="stock" class="textinput" value="<?php echo isset($data['stock']) ? $data['stock'] : ''; ?>" pattern="[+-]?[\d]+" required /></td>
            </tr>
            <tr>
                <td class="tbl_key"></td>
                <td class="tbl_value">
                    <input type="button" value="Cancel" />
                    <input type="submit" name="<?php echo $submitButtonName; ?>" value="OK" />
                </td>
            </tr>
        </table>
        </form>
    <?php
}

// Table query.
$query = "SELECT `id`, `name`, `description`, `power`, `price`, `stock` FROM `load`"; // FIXME: Order
$headers = array(
    'name'        => 'Name'
  , 'description' => 'Description'
  , 'power'       => 'Power' . T_Units::W
  , 'price'       => 'Price' . T_Units::CFA
  , 'stock'       => 'Stock'
);

// Execute query and show table.
$result = $db->query($query) or die(mysqli_error($db));
t_scroll_table($result, $headers, $editId, $editCallback, $addCallback);
$result->free();

// Layout end.
$db->close();
t_end();

?>
