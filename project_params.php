<?php

require_once('init.php');

// List of loads.
$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or die(mysqli_connect_error());
$result = $db->query("SELECT `id`, `name`, `stock` FROM `load`") or die(mysqli_error($db));
$loadProducts = ''; // SELECT options
$stock = array(); // Stock information
while($row = $result->fetch_assoc()) {
    $loadProducts .= "<option value='{$row['id']}'>{$row['name']}</option>\n";
    $stock[$row['id']] = $row['stock'];
}
$result->free();
$db->close();

// Start the layout.
t_start();

// Some extra javascript
echo '<script src="scripts/project_params.js"></script>';

// Create the form.
?>

<!---------------------- INSUFFICIENCY WARNING -------------------------->
<script>
<?php
foreach($stock as $key => $value) {
    echo "loadStock['$key'] = $value;\n";
}
?>
</script>
<div id="stockWarning" class="alert" style="display: none">Insufficient stock</div>

<!---------------------- LOAD TABLE -------------------------->

<table style="display:none">
  <tr class="tablerow" id="tpl_product">
    <td>

      <!------------------------------ PRODUCT SELECTION ------------------------------>

      <select class="selectinput" id="pselect-%i" name="load[%i][product]">
      <option value='remove'>&lt;Remove&gt;</option>
      <?php echo $loadProducts; ?>
      <option value='custom'>Custom</option>
      </select>
    </td>

    <!---------------------- PRODUCT PARAMETERS -------------------------->

    <td><input type="number" class="textinput" id="amount-%i" name="load[%i][amount]"     value="1" pattern="\d+" min="1" onchange="checkLoadStock(this)" required /></td>
    <td><input type="number" class="textinput" id="dayhr-%i" name="load[%i][dayhours]"   value=""  pattern="\d+" min="0" required /></td>
    <td><input type="number" class="textinput" id="nighthr-%i" name="load[%i][nighthours]" value=""  pattern="\d+" min="0" required /></td>
    <td><input type="checkbox" checked id="sell-%i" name="load[%i][sell]" onchange="checkLoadStock(this)" /></td>
  </tr>
</table>


<!---------------------- CUSTOM LOAD DEFINITION -------------------------->

<table cellspacing=0 cellpadding=0 class="customload" style="display: none" id="tpl_custom">
    <tr>
        <td class="tbl_key">Name</td>
        <td class="tbl_value"><input type="text" class="textinput" id="cname-%i" name="custom[%i][name]" value="" required /></td>
    </tr>
    <tr>
        <td class="tbl_key">Type</td>
        <td class="tbl_value">
            <select class="selectinput" style="width: 50px" name="custom[%i][type]" id="ctype-%i">
                <option>AC</option>
                <option selected>DC</option>
            </select>
    </td>
    </tr>
    <tr>
        <td class="tbl_key">Power<?php echo T_Units::W; ?></td>
        <td class="tbl_value"><input type="text" class="textinput" id="cpower-%i" name="custom[%i][power]" value="0.0" pattern="\d+(.\d+)?" required /></td>
    </tr>
    <tr>
        <td class="tbl_key">Voltage<?php echo T_Units::V; ?></td>
        <td class="tbl_value"><input type="text" class="textinput" id="cvoltage-%i" name="custom[%i][voltage]" value="12.0" pattern="\d+(.\d+)?" required /></td>
    </tr>
    <tr>
        <td class="tbl_key">Price<?php echo T_Units::CFA; ?></td>
        <td class="tbl_value"><input type="text" class="textinput" id="cprice-%i" name="custom[%i][price]" value="0.0" pattern="\d+(.\d+)?" required /></td>
    </tr>
    <tr>
        <td class="tbl_key">Stock</td>
        <td class="tbl_value"><input type="number" class="textinput" id="cstock-%i" name="custom[%i][stock]" value="1" pattern="\d+" required onchange="checkLoadStock(this)" /></td>
    </tr>
    <tr>
        <td class="tbl_key">Save</td>
        <td class="tbl_value"><input type="checkbox" id="csave-%i" name="custom[%i][save]" checked /></td>
    </tr>
</table>

<!---------------------- MAIN FORM -------------------------->

<form action="project_config.php" method="post" id="loadForm">

<h2>General information</h2>

<table>
  <tr>
    <td class="tbl_key">Sunlight<?php echo T_Units::H; ?></td>
    <td class="tbl_value"><input type="number" class="textinput" name="sunhours" value="5" min="0" pattern="\d+" required /></td>
  </tr>
</table>

<h2>Load information</h2>

<table cellspacing=0 cellpadding=0 class="loadtable" id="products">
  <tr class="tablehead">
    <td>Product</td>
    <td>Amount</td>
    <td>Day time<?php echo T_Units::H; ?></td>
    <td>Night time<?php echo T_Units::H; ?></td>
    <td>Sold</td>
  </tr>

  <!---------------------- ADD LOAD SELECTBOX -------------------------->

  <tr class="tablerow">
    <td>
      <select class="selectinput" onchange="addLoadProduct()" id="pselector"> <!-- Has no name such that it's not sent as form data. -->
      <option value='choose'>Choose...</option>
      <?php echo $loadProducts; ?>
      <option value='custom'>Custom</option>
      </select>
    </td>
    <td colspan=4>&nbsp;</td>
  </tr>
</table>

<input type="submit" class="acceptbutton" value="Search configurations >>" /> <!-- Has no name such that it's not sent as form data. -->

</form>

<?php t_end(); ?>
