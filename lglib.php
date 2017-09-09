<?php
/**
*   Table names and other global configuration values.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2012-2017 Lee Garner <lee@leegarner.com>
*   @package    lglib
*   @version    1.0.5
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*               GNU Public License v2 or later
*   @filesource
*/

// Static configuration items
global $_LGLIB_CONF;
$_LGLIB_CONF['pi_version'] = '1.0.5';
$_LGLIB_CONF['pi_name'] = 'lglib';
$_LGLIB_CONF['gl_version'] = '1.6.0';
$_LGLIB_CONF['pi_url'] = 'http://www.leegarner.com';
$_LGLIB_CONF['pi_display_name'] = 'lgLib';

global $_TABLES, $_DB_table_prefix;
$_DB_prefix = $_DB_table_prefix . 'lglib_';
$_TABLES['lglib_messages'] = $_DB_prefix . 'messages';
$_TABLES['lglib_jobqueue'] = $_DB_prefix . 'jobqueue';

?>
