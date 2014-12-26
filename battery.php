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

// Handle actions.
$fields = array('name', 'description', 'voltage', 'dod', 'loss', 'discharge', 'lifespan', 'capacity', 'max_const_current', 'max_peak_current', 'avg_const_current', 'max_humidity', 'max_temperature', 'price', 'stock');
$optionals = array('description');
$editId = handleModuleAction('panel', $fields, $optionals, $db, $editId, $_POST);

/** PAGE CONTENT **/

// Layout start.
t_start();

// Edit display.
$editCallback = function($row) use ($db)
{
    $query = "SELECT * FROM `battery` WHERE `id` = '{$row['id']}'";
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
        <form action="" method="POST">
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
                <td class="tbl_value"><textarea name="description"><?php echo isset($data['description']) ? $data['description'] : ''; ?></textarea></td>
            </tr>
            <tr>
                <td class="tbl_key">Voltage<?php echo T_Units::V; ?></td>
                <td class="tbl_value"><input type="text" name="voltage" class="textinput" value="<?php echo isset($data['voltage']) ? $data['voltage'] : ''; ?>" pattern="[\d.]+" required /></td>
            </tr>
            <tr>
                <td class="tbl_key">Depth of depletion<?php echo T_Units::Percent; ?></td>
                <td class="tbl_value"><input type="text" name="dod" class="textinput" value="<?php echo isset($data['dod']) ? $data['dod'] : ''; ?>" pattern="[\d.]+" required /></td>
            </tr>
            <tr>
                <td class="tbl_key">Loss<?php echo T_Units::Percent; ?></td>
                <td class="tbl_value"><input type="text" name="loss" class="textinput" value="<?php echo isset($data['loss']) ? $data['loss'] : ''; ?>" pattern="[\d.]+" required /></td>
            </tr>
            <tr>
                <td class="tbl_key">Discharge</td>
                <td class="tbl_value"><input type="text" name="discharge" class="textinput" value="<?php echo isset($data['discharge']) ? $data['discharge'] : ''; ?>" pattern="[\d.]+" required /></td>
            </tr>
            <tr>
                <td class="tbl_key">Lifespan<?php echo T_Units::Cycles; ?></td>
                <td class="tbl_value"><input type="number" name="lifespan" class="textinput" value="<?php echo isset($data['lifespan']) ? $data['lifespan'] : ''; ?>" pattern="\d+" required /></td>
            </tr>
            <tr>
                <td class="tbl_key">Capacity<?php echo T_Units::Ah; ?></td>
                <td class="tbl_value"><input type="number" name="capacity" class="textinput" value="<?php echo isset($data['capacity']) ? $data['capacity'] : ''; ?>" pattern="\d+" required /></td>
            </tr>
            <tr>
                <td class="tbl_key">Max. constant current<?php echo T_Units::A; ?></td>
                <td class="tbl_value"><input type="text" name="max_const_current" class="textinput" value="<?php echo isset($data['max_const_current']) ? $data['max_const_current'] : ''; ?>" pattern="[\d.]+" required /></td>
            </tr>
            <tr>
                <td class="tbl_key">max. peak current<?php echo T_Units::A; ?></td>
                <td class="tbl_value"><input type="text" name="max_peak_current" class="textinput" value="<?php echo isset($data['max_peak_current']) ? $data['max_peak_current'] : ''; ?>" pattern="[\d.]+" required /></td>
            </tr>
            <tr>
                <td class="tbl_key">Avg. constant current<?php echo T_Units::A; ?></td>
                <td class="tbl_value"><input type="text" name="avg_const_current" class="textinput" value="<?php echo isset($data['avg_const_current']) ? $data['avg_const_current'] : ''; ?>" pattern="[\d.]+" required /></td>
            </tr>
            <tr>
                <td class="tbl_key">Max. humidity<?php echo T_Units::Percent; ?></td>
                <td class="tbl_value"><input type="text" name="max_humidity" class="textinput" value="<?php echo isset($data['max_humidity']) ? $data['max_humidity'] : ''; ?>" pattern="[\d.]+" required /></td>
            </tr>
            <tr>
                <td class="tbl_key">Max. temperature<?php echo T_Units::DEG; ?></td>
                <td class="tbl_value"><input type="text" name="max_temperature" class="textinput" value="<?php echo isset($data['max_temperature']) ? $data['max_temperature'] : ''; ?>" pattern="[\d.]+" required /></td>
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
                    <input type="reset" value="Cancel" />
                    <input type="submit" name="<?php echo $submitButtonName; ?>" value="OK" />
                </td>
            </tr>
        </table>
        </form>
    <?php
}

// Table query.
$query = "SELECT `id`, `name`, `description`, `lifespan`, `capacity`, `price`, `stock` FROM `battery` ORDER BY `name`";
$headers = array(
      'name'        => 'Name'
    , 'description' => 'Description'
    , 'lifespan'    => 'Lifespan' . T_Units::H
    , 'capacity'    => 'Capacity' . T_Units::Ah
    , 'price'       => 'Price'    . T_Units::CFA
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
