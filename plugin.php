<?php 
/**
 * Base plugin
 * @package WDH
 * @subpackage Plugin
 *
 */

require_once(dirname(__FILE__) . '/constants.php');
require_once(WDH_LIB_DIR . '/settings.php');
require_once(WDH_LIB_DIR . '/options.php');
require_once(WDH_LIB_DIR . '/post-type.php');
require_once(WDH_LIB_DIR . '/taxonomy.php');

/**
 * Base plugin to inherit froms
 * @author ericjuden <eric@ericjuden.com>
 *
 */
class WDH_Plugin {
	/**
	 * Constructor
	 */
	function __construct(){
		
	}
}
?>