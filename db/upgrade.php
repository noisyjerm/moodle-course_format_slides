<?php

// This file keeps track of upgrades to
// the slides course format
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

function xmldb_format_slides_upgrade($oldversion) {
    global $CFG, $DB;
    
    $dbman = $DB->get_manager();

    if ($oldversion < 2011043000) {

    /// Add Background positon-x and position-y fields
        $table = new xmldb_table('format_slides');
        $field1 = new xmldb_field('image_pos_x', XMLDB_TYPE_TEXT);
        $field2 = new xmldb_field('image_pos_y', XMLDB_TYPE_TEXT);

        // Conditionally change fields
        if ($dbman->field_exists($table, $field1)) {
            rename_field($table, $field1, 'bg_position');
        }
        
       if ($dbman->field_exists($table, $field2)) {
            rename_field($table, $field2, 'height');
        }

        // slides savepoint reached
        upgrade_plugin_savepoint(true, 2011043000, 'format_slides');
    }

   

    return true;
}


