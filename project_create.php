<?php

require_once('init.php');

$db = mysql_connect($DB_HOST, $DB_USER, $DB_PASS) or die(mysql_error());
mysql_select_db($DB_NAME, $db) or die(mysql_error());

// Start the layout.
t_start();

// Create the form.
?>

<h2>Project summary</h2>

<h3>Loads</h3>
<table cellspacing=0 cellpadding=0 class="loadtable">
 <tr class="tablehead">
  <td>Product</td>
  <td>Amount</td>
  <td>Day time <div class="unit" title="Hour">[H]</div></td>
  <td>Night time <div class="unit" title="Hour">[H]</div></td>
  <td>Power <div class="unit" title="Watt">[W]</div><td>
 </tr>
 <tr class="tablerow">
  <td>Raspberry Pi</td>
  <td>1</td>
  <td>5</td>
  <td>4</td>
  <td>10</td>
 </tr>
 <tr class="tablerow">
  <td>Raspberry Pi</td>
  <td>1</td>
  <td>5</td>
  <td>4</td>
  <td>10</td>
 </tr>
 <tr class="tablerow">
  <td>Raspberry Pi</td>
  <td>1</td>
  <td>5</td>
  <td>4</td>
  <td>10</td>
 </tr>
</table>

<h3>Panels</h3>

<table cellspacing=0 cellpadding=0 class="loadtable">
 <tr class="tablehead">
  <td>Product</td>
  <td>Amount</td>
  <td>Voltage <div class="unit" title="Volt">[V]</div></td>
  <td>Power <div class="unit" title="Watt">[W]</div></td>
 </tr>
 <tr class="tablerow">
  <td>Panel 1</td>
  <td>1</td>
  <td>12</td>
  <td>200</td>
 </tr>
 <tr class="tablerow">
  <td>Panel 2</td>
  <td>2</td>
  <td>12</td>
  <td>120</td>
 </tr>
</table>

<!--

Panels
* Amount, Type, Characteristics
* Network

Batteries
* Amount, Type, Characteristics
* Total capacity

Extra hardware
-->

<h3>Batteries</h3>

<table cellspacing=0 cellpadding=0 class="loadtable">
 <tr class="tablehead">
  <td>Product</td>
  <td>Amount</td>
  <td>Voltage <div class="unit" title="Volt">[V]</div></td>
  <td>Capacity <div class="unit" title="Ampere hour">[Ah]</div></td>
  <td>Usable capacity <div class="unit" title="Ampere hour">[Ah]</div></td>
 </tr>
 <tr class="tablerow">
  <td>Battery 1</td>
  <td>1</td>
  <td>12</td>
  <td>200</td>
  <td>100</td>
 </tr>
 <tr class="tablerow">
  <td>Battery 2</td>
  <td>2</td>
  <td>12</td>
  <td>100</td>
  <td>80</td>
 </tr>
</table>

<h3>Extra hardware</h3>

<table cellspacing=0 cellpadding=0 class="loadtable">
 <tr class="tablehead">
  <td>Product</td>
  <td>Amount</td>
 </tr>
 <tr class="tablerow">
  <td>Controller A</td>
  <td>1</td>
 </tr>
 <tr class="tablerow">
  <td>Inverter X</td>
  <td>1</td>
 </tr>
</table>

<h3>Budget</h3>

<table cellspacing=0 cellpadding=0 class="loadtable">
 <tr class="tablehead">
  <td>Product</td>
  <td>Amount</td>
  <td>Price <div class="unit" title="Central African Franc">[CFA]</div></td>
 </tr>
 <tr class="tablerow">
  <td>Panel 1</td>
  <td>1</td>
  <td>50</td>
 </tr>
 <tr class="tablerow">
  <td>Panel 2</td>
  <td>2</td>
  <td>100</td>
 </tr>
 <tr class="tablerow">
  <td>Battery 1</td>
  <td>2</td>
  <td>200</td>
 </tr>
 <tr class="tablerow">
  <td>Battery 2</td>
  <td>1</td>
  <td>50</td>
 </tr>
 <tr class="tablerow">
  <td>Controller A</td>
  <td>1</td>
  <td>5</td>
 </tr>
 <tr class="tablerow">
  <td>Inverter X</td>
  <td>1</td>
  <td>500</td>
 </tr>
 <tr class="tablerow calcresult">
  <td></td>
  <td></td>
  <td>1205</td>
 </tr>
</table>

<h2>Project Metadata</h2>
<form>
<table cellspacing=0 cellpadding=0 class="projecttable">
  <tr>
    <td class="tbl_key">Name</td>
    <td class="tbl_value"><input type="text" name="project_name" value="" required /></td>
  </tr>
  <tr>
    <td class="tbl_key">Description</td>
    <td class="tbl_value"><textarea cols=60 rows=5 name="description"></textarea></td>
  </tr>
  <tr>
    <td class="tbl_key">Location</td>
    <td class="tbl_value"><input type="text" name="location" value="" required /></td>
  </tr>
  <tr>
    <td class="tbl_key">Client name</td>
    <td class="tbl_value"><input type="text" name="client_name" value="" required /></td>
  </tr>
  <tr>
    <td class="tbl_key">Client phone</td>
    <td class="tbl_value"><input type="phone" name="client_phone" value="" /></td>
  </tr>
  <tr>
    <td class="tbl_key">Responsible person</td>
    <td class="tbl_value"><input type="text" name="responsible_name" value="" required /></td>
  </tr>
  <tr>
    <td class="tbl_key">Responsible phone</td>
    <td class="tbl_value"><input type="phone" name="responsible_phone" value="" /></td>
  </tr>
  <tr>
    <td class="tbl_key">Delivery date</td>
    <td class="tbl_value"><input type="date" name="delivery" value="" /></td>
  </tr>
  <tr>
    <td class="tbl_key">Comments</td>
    <td class="tbl_value"><textarea cols=60 rows=5 name="comment"></textarea></td>
  </tr>
  <tr class="buttonrow">
    <td colspan=2><input type="submit" name="doCreateProject" value="Create project"></td>
  </tr>
</table>
</form>

<?php t_end(); ?>
