<?php
/*
Plugin Name: wp-sugarcrm-api-soap
Plugin URI: http://25th-floor.com
Description: Library plugin to access SugarCRM via its SOAP interface.
Version: 0.1
Author: 25th-floor
Author URI: http://25th-floor.com
License: GPL2
*/

include_once( WP_PLUGIN_DIR . "/wp-sugarcrm-api-soap/nusoap-0.9.5/lib/nusoap.php" );

/**
 * Converts an Array to a SugarCRM-SOAP compatible name_value_list
 * 
 * @param Array $data
 * @return Array
 */
function convertArrayToNVL( $data ){
	$return = array();
	foreach ( $data AS $key => $value )
		$return[] = array( 'name' => $key, 'value' => $value );
	return $return;
}

/**
 * Converts a SugarCRM-SOAP compatible name_value_list to an Array
 * 
 * @param Array $data
 * @return Array
 */
function convertNVLToArray ( $data ){
	$return = array();
	foreach ( $data AS $row ){
    	$return[$row['name']] = $row['value'];
    }
    return $return;
}

/**
 * @author 25th-floor
 * @version 0.2
 */
class sugarSOAPclient {
	/**
	 * NuSOAP object
	 *
	 * @var string
	 */
	protected $soapclient;

	/**
	 * SugarCRM Session ID
	 *
	 * @var string
	 */
	protected $sid = NULL;

	/**
	 * @param string $url Url to sugar's soap.php
	 * @return boolean
	 */
	public function __construct( $url ){
		if ( !class_exists( 'nusoap_client' ) )
			return false;
		$this->soapclient = new nusoap_client( $url, false );
		$this->soapclient->soap_defencoding = "UTF-8";
		$this->soapclient->decode_utf8 = false;
		
		return true;
	}

	/**
	 * Login with user credentials
	 *
	 * @param string $user
	 * @param string $password_hash
	 * @param boolean $admin_check
	 * @return boolean
	 */
	public function login( $user, $password_hash, $admin_check = true ){
		$login_params = array(
			'user_name' => $user,
			'password'  => $password_hash,
			'version'   => ''
		);

		$result = $this->soapclient->call( 'login', array(
			'user_auth' => $login_params,
			'application_name' => 'wp_sugarcrm'
		));

		if ( $result && $result['error']['number'] == 0 ){
			$this->sid = $result['id'];
		} else {
			return false;
		}

		if ( !$admin_check )
			return true;

		$result = $this->soapclient->call( 'is_user_admin' , array(
			'session' => $this->sid,
		));
		if ( $result == 1 ) {
			return true;
		} else {
			$this->sid = null;
			return false;
		}
	}

	/**
	 * Logout
	 */
	public function logout(){
		$result = $this->soapclient->call('logout', array(
			'session'	=> $this->sid,
		));

		$this->sid = null;
	}

	/**
	 * Retrieves a list of entries
	 *
	 * @param string $module
	 * @param query $query
	 * @param string $order_by
	 * @param integer $offset
	 * @param array $select_fields
	 * @param integer $max_results
	 * @param boolean $deleted
	 * @return array
	 */
	public function getEntryList( $module, $query = '', $order_by = '', $offset = 0, $select_fields = '', $max_results = 0, $deleted = false ){
		if ( !$this->sid )
			return false;

		$result = $this->soapclient->call('get_entry_list', array(
			'session'		=> $this->sid,
			'module_name'	=> $module,
			'query'			=> $query,
			'order_by'		=> $order_by,
			'offset'		=> $offset,
			'select_fields'	=> $select_fields,
			'max_results'	=> $max_results,
			'deleted'		=> $deleted,
		));

		if ( $result['result_count'] > 0 ){
			return $result;
		} else {
			return FALSE;
		}
	}

	/**
	 * Adds or changes an entry
	 *
	 * @param string $module
	 * @param array $data
	 * @return array
	 */
	public function setEntry( $module, $data ){
		if ( !$this->sid )
			return false;

    	$result = $this->soapclient->call( 'set_entry' , array(
    		'session' 			=> $this->sid,
    		'module_name'		=> $module,
    		'name_value_list'	=> convertArrayToNVL( $data ),
    	));

    	return $result;
	}

    /**
     * Creates a new relationship-entry
     *
     * @param string $module1
     * @param string $module1_id
     * @param string $module2
     * @param string $module2_id
     * @return array
     */
    public function setRelationship( $module1, $module1_id, $module2, $module2_id ){
		if ( !$this->sid )
			return false;

    	$data = array(
    		'module1' 	=> $module1,
    		'module1_id'=> $module1_id,
    		'module2'	=> $module2,
    		'module2_id'=> $module2_id,
    	);

    	$result = $this->soapclient->call( 'set_relationship', array(
    		'session' => $this->sid,
    		'set_relationship_value' => $data
    	));

		return $result;
	}

    /**
     * Retrieves relationship data
     *
     * @param string $module_name
     * @param string $module_id
     * @param string $related_module
     * @return array
     */
    public function getRelationships( $module_name, $module_id, $related_module ){
    	$result = $this->soapclient->call( 'get_relationships', array(
    		'session' => $this->sid,
    		'module_name' => $module_name,
    		'module_id'	=> $module_id,
    		'related_module' => $related_module,
    	));

    	if ( $result['error']['number'] == 0 ){
    		return $result;
    	}else{
    		return FALSE;
    	}
    }
}
?>
