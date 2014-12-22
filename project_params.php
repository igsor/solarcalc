<?php

require_once('init.php');

// List of loads.
$db = mysql_connect($DB_HOST, $DB_USER, $DB_PASS) or die(mysql_error());
mysql_select_db($DB_NAME, $db) or die(mysql_error());
$result = mysql_query("SELECT `id`, `name` FROM `load`", $db) or die(mysql_error());
$loadProducts = '';
while($row = mysql_fetch_assoc($result)) {
    $loadProducts .= "<option value='{$row['id']}'>{$row['name']}</option>\n";
}

// Start the layout.
t_start();

// Create the form.
?>

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
  <tr class="tablerow" style="display:none" id="tpl_product">
    <td>

      <!------------------------------ LOAD SELECT ------------------------------>

      <select class="selectinput" name="load[%i][product]" onchange="changeLoadProduct(this, this.parentNode.parentNode)" id="selector">
      <option value='remove'>&lt;Remove&gt;</option>
      <?php echo $loadProducts; ?>
      <option value='custom'>Custom</option>
      </select>

      <!---------------------- CUSTOM LOAD DEFINITION -------------------------->

      <table cellspacing=0 cellpadding=0 class="customload" style="display: none">
      <tr>
          <td class="tbl_key">Name</td>
          <td class="tbl_value"><input type="text" class="textinput" name="custom[%i][name]" value=" " /></td>
        </tr>
        <tr>
          <td class="tbl_key">Type</td>
          <td class="tbl_value">
            <select class="selectinput" style="width: 50px" name="custom[%i][type]">
              <option>AC</option>
              <option selected>DC</option>
            </select>
          </td>
        </tr>
        <tr>
          <td class="tbl_key">Power<?php echo T_Units::W; ?></td>
          <td class="tbl_value"><input type="text" class="resetDefault textinput" name="custom[%i][power]" value="0.0" pattern="[\d.]*" /></td>
        </tr>
        <tr>
          <td class="tbl_key">Voltage<?php echo T_Units::V; ?></td>
          <td class="tbl_value"><input type="text" class="textinput" name="custom[%i][voltage]" value="12.0" pattern="[\d.]*" /></td>
        </tr>
        <tr>
          <td class="tbl_key">Price<?php echo T_Units::CFA; ?></td>
          <td class="tbl_value"><input type="text" class="textinput resetDefault" name="custom[%i][price]" value="0.0" pattern="[\d.]*" /></td>
        </tr>
        <tr>
          <td class="tbl_key">Stock</td>
          <td class="tbl_value"><input type="checkbox" name="custom[%i][stock]" /></td>
        </tr>
        <tr>
          <td class="tbl_key">Save</td>
          <td class="tbl_value"><input type="checkbox" name="custom[%i][save]" checked /></td>
        </tr>
      </table>
    </td>

    <!---------------------- LOAD SELECTION PARAMETERS -------------------------->

    <td><input type="number" class="textinput" name="load[%i][amount]"     value="1" pattern="\d*" min="1" /></td>
    <td><input type="number" class="textinput" name="load[%i][dayhours]"   value=""  pattern="\d*" min="0" /></td>
    <td><input type="number" class="textinput" name="load[%i][nighthours]" value=""  pattern="\d*" min="0" /></td>
    <td><input type="checkbox" name="load[%i][sell]" /></td>
  </tr>

  <!---------------------- ADD LOAD SELECTBOX -------------------------->

  <tr class="tablerow">
    <td>
      <select class="selectinput" onchange="addLoadProduct(this)" id="pselector">
      <option value='choose'>Choose...</option>
      <?php echo $loadProducts; ?>
      <option value='custom'>Custom</option>
      </select>
    </td>
    <td colspan=4>&nbsp;</td>
  </tr>
</table>

<input type="submit" class="acceptbutton" value="Search configurations >>" />

</form>

<?php t_end(); ?>
