=== Plugin Name ===
Contributors: Bountin, 25th-floor
Donate link: http://25th-floor.com
Tags: sugarcrm, soap, code
Requires at least: 2.9
Tested up to: 3.1
Stable tag: 0.1

Library plugin to access SugarCRM via its SOAP interface.

== Description ==

This plugin enables you to access SugarCRM (any flavor) using its SOAP interface.
It has been tested with SugarCRM 6.0 and above. Currently the following calls have
been implemeted:

* login
* logout
* get_entry_list
* set_relationship
* get_relationships

This plugin includes nusoap 0.9.5 (http://sourceforge.net/projects/nusoap/).

To use this plugin include 'wp-sugarcrm-api-soap/sugarsoapclient.php' in your code
and create an instance of sugarSOAPclient.

See 'Examples' and 'API' for more details.

Note: Development was kindly sponsored by bor!sgloger @ http://borisgloger.com

== Installation ==

1. Upload `wp-sugarcrm-api-soap` to the `/wp-content/plugins/` directory.
1. Build your own plugins which are using the `sugarSOAPclient` class.

== Changelog ==

= 0.1 =

* initial release

== API ==

* __construct( $url )
	Returns a new sugarSOAPclient object instance. The $url parameter should point to 
the soap.php of your SugarCRM installation.

* login( $user, $password_hash, $admin_check = true )
	Login to SugarCRM with the given username `$user` and the password md5-hashed 
`$pasword_hash`. With `$admin_check` the clients checks if the user has admin rights. 
If he does not, the function will return false.

* logout()
	Closes the connection to SugarCRM.

* getEntryList( $module, $query = '', $order_by = '', $offset = 0, $select_fields = '', $max_results = 0, $deleted = false )
	Retrieves entries from SugarCRM. You have to specify the entries' module `$module` and a 
database query `$query`. You can optionally set an `$order_by` field and an `$offset`. 
`$select_fields` can be used to set the returned fields (default is all fields). 
`$max_results` limits the amount of data and with `$deleted` you can choose to select 
deleted entries.

* setEntry( $module, $data )
	Changes or creates an entry. If you set an id in `$data`, the accordant entry will be updated.
Otherwise a new entry will be created. 

* setRelationship( $module1, $module1_id, $module2, $module2_id )
	Adds a relationship between the two entries.
	
* getRelationships( $module_name, $module_id, $related_module )
	Retrieves relationship data.

== Examples ==

The following code example echo's Contacts with the first name "Martin". 

`<?php
	include_once WP_PLUGIN_DIR . '/wp-sugarcrm-api-soap/sugarsoapclient.php';
	
	// Create a new soapClient 
	$soapClient = new sugarSOAPclient( 'http://crm.example.org/soap.php' );
	
	// Login with your user data
	$soapClient->login( 'FooUser', md5( 'BarPassword' ) );
	
	// Retrieve Array of Contacts
	$contacts = $soapClient->getEntryList( 'Contacts', 'contacts.first_name="Martin"' );
	foreach ( $contacts['entry_list'] AS $contact ) {
		// Convert SugarCRM's name_value_list to an associative array
		$contact = convertNVLToArray( $contact['name_value_list'] );
		echo $contact['first_name'] . ' ' . $contact['last_name'] . "<br />";
	}
	
	// Say "Good Bye"
	$soapClient->logout();
?>`
