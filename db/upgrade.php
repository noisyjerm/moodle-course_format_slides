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

    if ($oldversion < 2011040800) {

    /// Add Background positon-x and position-y fields
        $table = new xmldb_table('format_slides');
        $field = new xmldb_field('layout_columns', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, "2", 'image_pos_y');

        // Conditionally add fields
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // slides savepoint reached
        upgrade_plugin_savepoint(true, 2011040800, 'format_slides');
    }

   

    return true;
}


