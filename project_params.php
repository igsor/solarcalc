<?php

require_once('init.php');

// List of loads.
$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or fatal_error(mysqli_connect_error());
$result = $db->query("SELECT `id`, `name`, `stock` FROM `load`") or fatal_error(mysqli_error($db));
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
  <tr class="project-load-item" id="tpl_product">
    <td>

      <!------------------------------ PRODUCT SELECTION ------------------------------>

      <select id="pselect-%i" name="load[%i][product]">
      <option value='remove'>&lt;Remove&gt;</option>
      <?php echo $loadProducts; ?>
      <option value='custom'>Custom</option>
      </select>
    </td>

    <!---------------------- PRODUCT PARAMETERS -------------------------->

    <td><input type="number" class="number" id="amount-%i" name="load[%i][amount]"     value="1" pattern="\d+" min="1" onchange="checkLoadStock(this)" required /></td>
    <td><input type="number" class="number" id="dayhr-%i" name="load[%i][dayhours]"   value=""  pattern="\d+" min="0" required /></td>
    <td><input type="number" class="number" id="nighthr-%i" name="load[%i][nighthours]" value=""  pattern="\d+" min="0" required /></td>
    <td><input type="checkbox" checked id="sell-%i" name="load[%i][sell]" onchange="checkLoadStock(this)" /></td>
    <td></td>
  </tr>
</table>


<!---------------------- CUSTOM LOAD DEFINITION -------------------------->

<table cellspacing=0 cellpadding=0 class="project-load-custom" style="display: none" id="tpl_custom">
    <tr>
        <td class="form-table-key">Name</td>
        <td class="form-table-value"><input type="text" id="cname-%i" name="custom[%i][name]" value="" required /></td>
    </tr>
    <tr>
        <td class="form-table-key">Type</td>
        <td class="form-table-value">
            <select name="custom[%i][type]" id="ctype-%i">
                <option>AC</option>
                <option selected>DC</option>
            </select>
    </td>
    </tr>
    <tr>
        <td class="form-table-key">Power<?php echo T_Units::W; ?></td>
        <td class="form-table-value"><input type="text" class="number" id="cpower-%i" name="custom[%i][power]" value="0.0" pattern="\d+(.\d+)?" required /></td>
    </tr>
    <tr>
        <td class="form-table-key">Voltage<?php echo T_Units::V; ?></td>
        <td class="form-table-value"><input type="text" class="number" id="cvoltage-%i" name="custom[%i][voltage]" value="12.0" pattern="\d+(.\d+)?" required /></td>
    </tr>
    <tr>
        <td class="form-table-key">Price<?php echo T_Units::CFA; ?></td>
        <td class="form-table-value"><input type="text" class="number" id="cprice-%i" name="custom[%i][price]" value="0.0" pattern="\d+(.\d+)?" required /></td>
    </tr>
    <tr>
        <td class="form-table-key">Stock</td>
        <td class="form-table-value"><input type="number" class="number" id="cstock-%i" name="custom[%i][stock]" value="1" pattern="\d+" required onchange="checkLoadStock(this)" /></td>
    </tr>
    <tr>
        <td class="form-table-key">Save</td>
        <td class="form-table-value number"><input type="checkbox" id="csave-%i" name="custom[%i][save]" checked /></td>
    </tr>
</table>

<!---------------------- MAIN FORM -------------------------->

<form action="project_config.php" method="post" id="loadForm">

<h2>General information</h2>

<table>
  <tr>
    <td class="form-table-key">Sunlight<?php echo T_Units::H; ?></td>
    <td class="form-table-value"><input type="number" class="number" name="sunhours" value="5" min="0" pattern="\d+" required /></td>
  </tr>
</table>

<h2>Load information</h2>

<table cellspacing=0 cellpadding=0 class="project-load" id="products">
  <tr class="project-load-head">
    <td>Product</td>
    <td>Amount</td>
    <td>Day time<?php echo T_Units::H; ?></td>
    <td>Night time<?php echo T_Units::H; ?></td>
    <td>Sold</td>
    <td width=100%></td> <!-- dummy column for scaling -->
  </tr>

  <!---------------------- ADD LOAD SELECTBOX -------------------------->

  <tr class="project-load-item">
    <td>
      <select onchange="addLoadProduct()" id="pselector"> <!-- Has no name such that it's not sent as form data. -->
      <option value='choose'>Choose...</option>
      <?php echo $loadProducts; ?>
      <option value='custom'>Custom</option>
      </select>
    </td>
    <td colspan=5>&nbsp;</td>
  </tr>
  <tr>
    <td class='form-table-action' colspan='6'>
      <button type="submit">Search configurations >></buttom> <!-- Has no name such that it's not sent as form data. -->
    </td>
  </tr>
</table>


</form>

<?php t_end(); ?>
