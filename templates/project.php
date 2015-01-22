<?php

function project_module($name, $table_fu)
{
    ?>
        <div class="detailButton"> 
        <a class="unitbutton" onclick="toggleVisibility(document.getElementById('detail<?php echo $name; ?>'))"><?php echo $name; ?></a>
        </div>
        <div class="detail" id="detail<?php echo $name; ?>">
        <?php $table_fu(); ?>
        </div>
        <br/>
    <?php
}

