<?php
/*
Plugin Name: WP E-Commerce - (!USA)/International Weight Table
Plugin URI: http://getshopped.org/resources/docs/get-involved/writing-a-new-shipping-module/
Description: Custom Shipping Module For Shipping to USA
Version: 0.9
Author: Graphic Agenda
Author URI: http://www.graphicagenda.com

Original Author: Lee Willis
Original Author URI: http://www.leewillis.co.uk/
Original Plugin URI: http://getshopped.org/resources/docs/get-involved/writing-a-new-shipping-module/
 Version: 0.9(all) - Fixed Wrong Number of Arguments 01/04/11
 Version: 0.8.2(USA) - Testing $country rule 04/14/10
 Version: 0.8.1(aus) - getQuote to spit the Australia array
 Version: 0.5.1(aus) - General fixes 03/31/10 
 This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 2.
 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
 If there are general errors on your server with this plugin installed, hopefully you can uncomment the next line
*/
// error_reporting(0); // Turn off all error reporting

class full_intl_shipping {

	var $internal_name;
	var $name;
	var $is_external;

	function full_intl_shipping () {

		// An internal reference to the method - must be unique!
		$this->internal_name = "full_intl_shipping";
		
		// $this->name is how the method will appear to end users
		$this->name = "International Shipping";

		// Set to FALSE - doesn't really do anything :)
		$this->is_external = FALSE;

		return true;
	}
	
	/* You must always supply this */
	function getName() {
		return $this->name;
	}
	
	/* You must always supply this */
	function getInternalName() {
		return $this->internal_name;
	}
	
	
	/* Use this function to return HTML for setting any configuration options for your shipping method
	 * This will appear in the WP E-Commerce admin area under Products > Settings > Shipping
         *
	 * Whatever you output here will be wrapped inside the right <form> tags, and also
	 * a <table> </table> block */

	function getForm() {
	//	$output ="<table>";
		$output.="<tr><th>".TXT_WPSC_TOTAL_WEIGHT_IN_POUNDS."</th><th>".TXT_WPSC_SHIPPING_PRICE."</th></tr>";
		$layers = get_option("full_intl_shipping_options");
		if ($layers != '') {
			foreach($layers as $key => $shipping) {
				$output.="<tr class='rate_row'><td >";
				$output .="<i style='color: grey;'>".__('If weight is ', 'wpsc')."</i><input type='text' value='$key' name='weight_layer[]'size='4'><i style='color: grey;'>".__(' and above', 'wpsc')."</i></td><td>".wpsc_get_currency_symbol()."<input type='text' value='{$shipping}' name='weight_shipping[]' size='4'>&nbsp;&nbsp;<a href='#' class='delete_button' >".__('Delete', 'wpsc')."</a></td></tr>";
			}
		}
		$output.="<input type='hidden' name='checkpage' value='weight_full_intl'>";
		$output.="<tr class='addlayer'><td colspan='2'>Layers: <a style='cursor:pointer;' id='addweightlayer' >Add Layer</a></td></tr>";
	//	$output .="</table>";		
		

		return $output;
		
	}

	/* Use this function to store the settings submitted by the form above
	 * Submitted form data is in $_POST */

	function submit_form() {
		$layers = (array)$_POST['weight_layer'];
		$shippings = (array)$_POST['weight_shipping'];
		if ($shippings != ''){
			foreach($shippings as $key => $price) {
				if ($price == '') {
					unset($shippings[$key]);
					unset($layers[$key]);
				} else {
					$new_layer[$layers[$key]] = $price;
				}
			}
		}
		if ($_POST['checkpage'] == 'weight_full_intl') {
			update_option('full_intl_shipping_options',$new_layer);
		}
		return true;

	}
	
	/* If there is a per-item shipping charge that applies irrespective of the chosen shipping method
         * then it should be calculated and returned here. The value returned from this function is used
         * as-is on the product pages. It is also included in the final cart & checkout figure along
         * with the results from GetQuote (below) */




	/* This function returns an Array of possible shipping choices, and associated costs.
         * This is for the cart in general, per item charges (As returned from get_item_shipping (above))
         * will be added on as well. */

	function getQuote() {

		global $wpdb, $wpsc_cart;

		// This code is let here to show how you can access delivery address info
		// We don't use it for this skeleton shipping method

		if (isset($_POST['country'])) {

			$country = $_POST['country'];
			$_SESSION['wpsc_delivery_country'] = $country;

		} else {

			$country = $_SESSION['wpsc_delivery_country'];

		}
		
		// Retrieve the options set by submit_form() above
		$weight = wpsc_cart_weight_total();
		$layers = get_option('full_intl_shipping_options');
		if ($layers != '' && $country != 'US') {
			krsort($layers);
			foreach ($layers as $key => $shipping) {
				if ($weight >= (float)$key) {
					return array("International Shipping Rate"=>$shipping);
				}
			}
		// Return an array of options for the user to choose
		// The first option is the default
		
		//return array ("Australia Shipping" => (float) $aus_shipping_rates['charge'] );
		return array("INTL"=>array_shift($layers));
		}
		else {
		return array();
		}
	}
	
/*Warning: Missing argument 2 for usa_shipping::get_item_shipping(), called in 
		/var/www/vhosts/edwardsharpeandthemagneticzeros.com/httpdocs/merch/ _
		svn/wp-content/plugins/wp-e-commerce/wpsc-includes/cart.class.php _
		on line 1838 and defined in _
		/var/www/vhosts/edwardsharpeandthemagneticzeros.com/httpdocs/merch/svn/wp-content/plugins/Selective/usa_weight_table.php on line 153*/
	/*	function get_item_shipping($unit_price, $quantity, $weight, $product_id) {
	return 0;
	}	*/
	function get_item_shipping($unit_price) {
		return 0;
	}
	
	function get_cart_shipping($total_price, $weight) {
		$layers = get_option('full_intl_shipping_options');
		if ($layers != '') {
			krsort($layers);
			foreach ($layers as $key => $shipping) {
				if ($weight >= (float)$key) {
					$output = $shipping;
				}
			}
		}
	  return $output;
	}
} 

function full_intl_shipping_add($wpsc_shipping_modules) {

	global $full_intl_shipping;
	
	$full_intl_shipping = new full_intl_shipping();
	$wpsc_shipping_modules[$full_intl_shipping->getInternalName()] = $full_intl_shipping;

	return $wpsc_shipping_modules;
}

add_filter('wpsc_shipping_modules', 'full_intl_shipping_add');
?>