<?php

function t_menulink($trg, $name) {
    echo "<a class='menulink " . (basename($_SERVER['SCRIPT_NAME']) == $trg ? ' selected':'') . "' href='{$trg}'>${name}</a>";
}
?>

<div id="header">

  <h1 id="pagetitle">Solar Installation Calculation Tool</h1>
  <?php
    t_menulink('load.php',              'LOAD');
    t_menulink('panel.php',             'PANEL');
    t_menulink('battery.php',           'BATTERY');
    t_menulink('hardware.php',          'HARDWARE');
    t_menulink('project_overview.php',  'PROJECT');
  ?>
  <hr class="menuline">

</div>
