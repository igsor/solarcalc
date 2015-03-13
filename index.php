<?php

require_once('init.php');

/** PARAMETERS **/

// Edit parameter.
$editId = '';
if (key_exists('edit', $_GET)) {
    $editId = $_GET['edit'];
}

// Database connection.
$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or fatal_error(mysqli_connect_error());

// Handle actions.
$fields = array('name', 'description', 'power', 'type', 'voltage', 'price', 'stock');
$optionals = array('description');
if (($newId = handleModuleAction('load', $fields, $optionals, $db, $_POST)) != -1) {
    $editId = $newId;
}

/** PAGE CONTENT **/

// Layout start.
t_start();


?>

<table id="startpagebody">
    <tr>
        <td colspan=2>
            <h2 id="introductiontitle">Welcome to SOLARCALC</h2>
            <p>
                The solarcalc is a tool to help with the calculation of solar installations.
                Please, read carefully through the introduction below before you start with your first project.<br/>
                You can find an explanation about how to create new projects and everything that is connected to it.

            </p>
        </td>
    </tr>
    <tr>
        <td class="startpage-coloumn">
            <ul>
                <li> Load
                    <p>
                        Click on the <em>LOAD</em> button to see a list of all existing loads.<br/>
                        The loads are connected to the solar circuit and consume power.
                        You can easily add new loads, that you have in stock or know that you will use often.
                    </p>
                <li> Panel
                    <p>
                        Click on the <em>PANEL</em> button to see a list of all existing loads.</br>
                        The panels you define in this database will be used for the calculations of the project. Only create panels, that you wish to use in the projects.
                    </p>
                <li> Battery
                    <p>
                        Click on the <em>BATTERY</em> button to see a list of all existing batteries.<br/>
                        The batteries you define and add in this database will be used for the calculation of the project.
                        Only create batteries, that you wish to use in the project.
                    </p>
            </ul>
        </td>
        <td class="startpage-coloumn">

                <li> Hardware
                    <p>
                        Click on the <em>HARDWARE</em> button to see a list of the controllers and the inverters.
                        You can change between controllers and inverters by choosing them in the drop-down menu on the top of the page.
                    </p>
                <li> Project
                    <p> Click here to see an overview of all existing projects.
                        Click on any of the projects to see the details and edit anything you like.
                    </p>
            </ul>
        </td>
        </tr>
        <tr>
        <td class="startpage-coloumn">
            <h3>How to create new elements and change or delete existing elements</h3>
            <p>
            To create entries for either LOAD, PANEL, BATTERY or HARDWARE navigate to the respective page.
            Click on the <em>Add</em> button at the bottom of the page.
            You will then see a form where you can enter all details about the new element that you wish to add to the database.
            Confirm the creation of a new element by pressing the <em>OK</em> button at the bottom of the form.
            If you hit the <em>Cancel</em> button, all entries of this new form are deleted.<br/>
            You can have a look at the details of any element if you click on its name.
            The same form that is needed to add new elements will appear, but with all the existing data of this element.
            If you choose to change some of the entries, then click on the <em>OK</em> button after you have applied the changes.
            If you hit <em>Cancel</em>, all changes that are not saved will be undone.
            If you wish to delete a load, click on the <em>DEL</em> button on the right side and confirm your decision in the appearing window.
            </p>

        </td>
        <td class="startpage-coloumn">
            <h3>How can the SOLARCALC do my calculations?</h3>
            <p>
                To be able to use the amazing powers of the fantastic Solar Installation Calculation Tool go to the <em>PROJECT</em> site. Scroll down until you see the <em>Add</em> button. If you click on this button a new button appears, the <em>by Load Definition Wizard</em>. If you click on this button, you will be guided to another page where you can define your new project.
                <ol>
                <li> First you need to give some general information about your project.
                    <p>
                        The hours of sunlight are not the time between sunrise and sunset. Here you need to enter the time per day the sun shines with 100% power. Usually this is significantly lower than the time the sun is up. This is because in the early morning and the late afternoon, the sun does not have all of its power. To find out about the amount of sunhours for your project-site consult a weather-station. There are many good pages in the internet, which can provide these nubers for almost every place on the world.
                    </p>
                <li> Now choose all the loads that are needed for your project.
                    <p>
                        Either you can select the loads from the drop-down menu which is connected to the load-database. Or, if your desired load is not yet available, you can choose the <em>custom</em> load. It is the last element in the drop-down menu. The custom element will provide you with the same form as on the load page. You can specify your own personal load. If you want, you can save this new load directly to the load database.
                    </p>
                <li> For every element choose the Amount, Daytime, Nighttime and if you sell this product directly.
                    <p>
                        <b>Amount</b>: How many pieces of this product do you need.<br/>
                        <b>Day time</b>: How long is this load used during the day. This means if it is not twilight or dark outside.<br/>
                        <b>Night time</b>: How long is load used during the night or early in the morning and late in the evening. This is the same thing as autonomie in hours.<br/>
                        <b>Sold</b>: If you sell this load to the customer, the box should be ticked. Its price will be added to the budget in the end.<br/>
                        If you are done with this, click on the <em>Search configurations</em> button. The solarcalc will now search for all possible sets of panels and batteries that can support your choice of loads.
                    </p>
                <li> Choose the configuration you like.
                    <p>
                        At the top you see a short summary of the load, that you have chosen. Below are a selection of possible configurations of batteries and panels. For every configuration you see the number and type of panels, number and type of batteries and controller and, if needed, inverter. Below there is a quick summary of the detail of this choice. This includes to total price, the price per kilo Watt hour, the total usable battery capacity and the total panel power. In the last row are the expected lifetime of this configurationm usually it is limited by the battery lifetime. And at last you can see if all components of this configuration are in stock.<br/>
                        If you click on one of the blue titles of these configurations, a larger summary with more details popps up. The price of all individual components are listed and the remaining battery capacity as well as the panel power are listed.<br/>
                        Choose any of the configurations and if you are happy with your choice click on the button that appears next to the price details.
                    </p>
                <li> Read through the project summary and fill in any of the remaining data
                    <p>
                        You see a summary of all data concerning your project. The loads, the batteries, the panels, the controller and inverter and a detailed budget over all parts that are sold.
                    </p>
                <li> Click on <em>Project create</em>
                    <p>
                        Congratulation, you have created your own project. Go to the project page to have anoter look at it and change anything you like.
                    </p>
                </ol>
            </p>
        </td>
    </tr>
</table>

<?php


// Layout end.
$db->close();
t_end();

?>
