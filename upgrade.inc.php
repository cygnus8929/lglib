<?php
/**
*   Upgrade routines for the lgLib plugin
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2013 Lee Garner <lee@leegarner.com>
*   @package    lglib
*   @version    0.0.2
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/

// Required to get the config values
global $_CONF, $_LGLIB_CONF, $_DB_dbms;

/** Include the default configuration values */
require_once LGLIB_PI_PATH . '/install_defaults.php';

/** Include the table creation strings */
require_once LGLIB_PI_PATH . "/sql/{$_DB_dbms}_install.php";

/**
*   Perform the upgrade starting at the current version.
*
*   @param  string  $current_ver    Current installed version to be upgraded
*   @return integer                 Error code, 0 for success
*/
function LGLIB_do_upgrade($current_ver)
{
    global $_LGLIB_DEFAULTS, $_LGLIB_CONF, $_PLUGIN_INFO;

    if (isset($_PLUGIN_INFO[$_CONF_LGLIB['pi_name']])) {
        if (is_array($_PLUGIN_INFO[$_CONF_LGLIB['pi_name']])) {
            // glFusion > 1.6.5
            $current_ver = $_PLUGIN_INFO[$_CONF_LGLIB['pi_name']]['pi_version'];
        } else {
            // legacy
            $current_ver = $_PLUGIN_INFO[$_CONF_LGLIB['pi_name']];
        }
    } else {
        return false;
    }

    // Get the config object
    $c = config::get_instance();

    if (!COM_checkVersion($current_ver, '0.0.2')) {
        // upgrade from 0.0.1 to 0.0.2
        $current_ver = '0.0.2';
        COM_errorLog("Updating Plugin to $current_ver");
        if (!LGLIB_do_upgrade_sql($current_ver)) return false;
        if (!LGLIB_do_set_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '0.0.4')) {
        // upgrade from 0.0.3 to 0.0.4
        $current_ver = '0.0.4';
        COM_errorLog("Updating Plugin to $current_ver");
        $c->add('img_disp_relpath', $_LGLIB_DEFAULTS['img_disp_relpath'],
                'text', 0, 0, 15, 20, true, $_LGLIB_CONF['pi_name']);
        if (!LGLIB_do_set_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '0.0.5')) {
        // upgrade from 0.0.4 to 0.0.5
        $current_ver = '0.0.4';
        COM_errorLog("Updating Plugin to $current_ver");
        $c = config::get_instance();
        $c->add('cron_schedule_interval', $_LGLIB_DEFAULTS['cron_schedule_interval'],
                'text', 0, 0, 15, 30, true, $_LGLIB_CONF['pi_name']);
        $c->add('cron_key', $_LGLIB_DEFAULTS['cron_key'],
                'text', 0, 0, 15, 40, true, $_LGLIB_CONF['pi_name']);
        if (!LGLIB_do_set_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '0.0.6')) {
        // upgrade from 0.0.5 to 0.0.6
        $current_ver = '0.0.6';
        COM_errorLog("Updating Plugin to $current_ver");
        $c = config::get_instance();
        $c->add('img_cache_interval', $_LGLIB_DEFAULTS['img_cache_interval'],
                'text', 0, 0, 15, 50, true, $_LGLIB_CONF['pi_name']);
        $c->add('img_cache_maxage', $_LGLIB_DEFAULTS['img_cache_maxage'],
                'text', 0, 0, 15, 60, true, $_LGLIB_CONF['pi_name']);
        if (!LGLIB_do_set_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '0.0.7')) {
        // upgrade from 0.0.6 to 0.0.7
        $current_ver = '0.0.7';
        COM_errorLog("Updating Plugin to $current_ver");
        $c->add('slimbox_autoactivation', $_LGLIB_DEFAULTS['slimbox_autoactivation'],
                'select', 0, 0, 3, 70, true, $_LGLIB_CONF['pi_name']);
        if (!LGLIB_do_upgrade_sql($current_ver)) return false;
        if (!LGLIB_do_set_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '1.0.1')) {
        // upgrade to 1.0.1
        $current_ver = '1.0.1';
        COM_errorLog("Updating Plugin to $current_ver");
        $c->add('use_lglib_messages', $_LGLIB_DEFAULTS['use_lglib_messages'],
                'select', 0, 0, 3, 80, true, $_LGLIB_CONF['pi_name']);
        if (!LGLIB_do_set_version($current_ver)) return false;
    }

    if (!COM_checkVersion($current_ver, '1.0.5')) {
        // upgrade to 1.0.5
        $current_ver = '1.0.5';
        COM_errorLog("Updating Plugin to $current_ver");
        $c->add('enable_smartresizer', $_LGLIB_DEFAULTS['enable_smartresizer'],
                'select', 0, 0, 3, 90, true, $_LGLIB_CONF['pi_name']);
        if (!LGLIB_do_set_version($current_ver)) return false;
    }

    // Final version setting in case there was no upgrade process for
    // this version
    if (!LGLIB_do_set_version($_LGLIB_CONF['pi_version'])) return false;
    return true;
}


/**
*   Actually perform any sql updates.
*
*   @param  string  $version    Version being upgraded TO
*   @return boolean         True on success, False on failure
*/
function LGLIB_do_upgrade_sql($version)
{
    global $_TABLES, $_LGLIB_CONF, $_UPGRADE_SQL;

    // If no sql statements passed in, return success
    if (!isset($_UPGRADE_SQL[$version]) ||
            !is_array($_UPGRADE_SQL[$version]))
        return true;

    // Execute SQL now to perform the upgrade
    COM_errorLOG("--Updating lgLib to version $version");
    foreach ($_UPGRADE_SQL[$version] as $q) {
        COM_errorLOG("lgLib Plugin $version update: Executing SQL => $q");
        DB_query($q, '1');
        if (DB_error()) {
            COM_errorLog("SQL Error during lgLib plugin update: $q",1);
            return false;
        }
    }
    return true;
}


/**
*   Update the plugin version number in the database.
*   Called at each version upgrade to keep up to date with
*   successful upgrades.
*
*   @param  string  $ver    New version to set
*   @return boolean         True on success, False on failure
*/
function LGLIB_do_set_version($ver)
{
    global $_TABLES, $_CONF_LGLIB;

    // now update the current version number.
    $sql = "UPDATE {$_TABLES['plugins']} SET
            pi_version = '{$_CONF_LGLIB['pi_version']}',
            pi_gl_version = '{$_CONF_LGLIB['gl_version']}',
            pi_homepage = '{$_CONF_LGLIB['pi_url']}'
        WHERE pi_name = '{$_CONF_LGLIB['pi_name']}'";

    $res = DB_query($sql, 1);
    if (DB_error()) {
        COM_errorLog("Error updating the {$_CONF_LGLIB['pi_display_name']} Plugin version",1);
        return false;
    } else {
        return true;
    }
}

?>
