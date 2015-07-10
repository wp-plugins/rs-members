<?php
/**
 * RS-members is wordpress most powerful membership plugin many many features are include there.
 *
 * @link       http://www.themexpo.net
 * @since      1.0.1
 *
 * @package    rs-members
 */
class RsMembersUninstall
{
	/**
	 * 
	 */
	public static function uninstall()
	{
		/*
		* - Check if the $_REQUEST content actually is the plugin name
		* - Run an admin referrer check to make sure it goes through authentication
		* - Verify the output of $_GET makes sense
		* - Repeat with other user roles. Best directly by using the links/query string parameters.
		* - Repeat things for multisite. Once for a single site in the network, once sitewide.
		*/
	}
}	//End Class

!defined('WP_UNINSTALL_PLUGIN') || die;

// EOF