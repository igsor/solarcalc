<?php

/**
 *
 * Parameters:
 *  $moduleName : Name of the database to be modified.
 *  $fields     : Array of field names to modify in the queries and form data (i.e. db column names).
 *  $editId     : Id of the item to edit or delete.
 *  $db         : MySQLi database object
 *  $post       : Assumed to be the POST variable (passed instead of global access to make it visible).
 * 
 * Returns:
 *                The possibly modified editId.
 * 
 * Assumptions:
 * - All form data is passed in the POST variable.
 * - The POST values have the same name as the database fields.
 * - The action is identified by presence of doEdit, doAdd and doDelete.
 * - All fields are required.
 * 
 * Input is checked for existence and escaped but no type check is executed.
 * 
 */
function handleModuleAction($moduleName, $fields, $optionals, $db, $post)
{
    if (isset($post['doEdit']) or isset($post['doDelete'])) {
        if (!isset($post['id'])) {
            t_argumentError();
        }

        $editId = $db->escape_string($post['id']);
    }

    if (isset($post['doEdit']) or isset($post['doAdd'])) {
        // Handle form data.
        foreach($fields as $fieldName) {
            if (!isset($post[$fieldName])) {
                if (!in_array($fieldName, $optionals)) {
                    t_argumentError();
                } else {
                    $data[$fieldName] = '';
                }
            } else {
                $data[$fieldName] = $db->escape_string($post[$fieldName]);
            }
        }

        // Edit response.
        if (isset($post['doEdit'])) {
            // Build the field-dependent parts of the UPDATE query.
            $update_fields = array();
            foreach($fields as $fieldName) {
                $update_fields[] =  "`$fieldName` = '{$data[$fieldName]}'";
            }

            // Update the database.
            $db->query("
                UPDATE
                    `{$moduleName}`
                SET
                    " . join(', ', $update_fields) . "
                WHERE
                    `id` = '{$editId}'
            ") or die(mysqli_error($db));

            // FIXME: Action?
            return $editId;
        }

        // Add response.
        if (isset($post['doAdd'])) {
            // Build the field-dependent parts of the INSERT query.
            $insert_fields = array();
            $insert_data = array();
            foreach($fields as $fieldName) {
                array_push($insert_fields, "`{$fieldName}`");
                array_push($insert_data, "'${data[$fieldName]}'");
            }

            // Update the database.
            $db->query("
                INSERT
                INTO `{$moduleName}` (
                    " . join(',', $insert_fields) . "
                ) VALUES (
                    " . join(',', $insert_data). "
                )
            ") or die(mysqli_error($db));

            // Go to edit mode.
            return $db->insert_id;
        }

    } else if (isset($post['doDelete'])) {
        $db->query("
            DELETE FROM
                `${moduleName}`
            WHERE
                `id` = '{$editId}'
        ") or die(mysqli_error($db));

        // FIXME: Action?
        return '';

    }

    return -1;
}

// EOF //
