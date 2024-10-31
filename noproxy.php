<?php
/*
Plugin Name: Prospress Noproxy
Plugin URI: http://prospress.org
Description: Remove Proxy bidding from Prospress auctions.  
Author: Anthony Yin-Xiong Khoo
Version: 1.0
Author URI: http://prospress.org/
*/

load_plugin_textdomain( 'noproxy', PP_CP_PLUGIN_DIR. '/languages', dirname( plugin_basename( __FILE__ ) ). '/languages' );
/**
 * Load NoProxy only if Prospress plugin is active
 *
 * @package Noproxy
 * @since 1.0
 */
add_action('plugins_loaded', 'ppnp_load');
function ppnp_load() {
	if( class_exists( 'PP_Market_System' ) ) {

		add_filter( 'bid_form', 'ppnp_bid_form', 1 );

		add_filter( 'increment_bid_value' , 'ppnp_bid_increment' , 1 , 1 );
		add_filter( 'bid_pre_db_insert','ppnp_validate_bid' , 1 , 1 );
		add_filter( 'bid_message','ppnp_validate_post' , 1 , 2 );
		add_action( 'new_winning_bid', 'ppnp_winning_bid_value' );
	} else {
		add_action( 'admin_notices', 'ppnp_activation_error' );
	}
}

/**
 * Error message when mother plugins Prospress is not activated
 *
 * @package Prospress-Cubepoints
 * @since 1.0
 */
function ppnp_activation_error(){
	echo "<div id='ppnp_activation_error' class='error fade'><p><strong>".__('Warning.', 'noproxy')."</strong> ";
	echo __( "Noproxy requires Prospress plugin be active.", 'noproxy')."</div>";
}
/**
 * Check whether the plugin is active by checking the active_plugins list.
 *
 *
 * @package Prospress Noproxy
 * @since 1.0
 *
 * @param string $plugin Base plugin path from plugins directory.
 * @return bool True, if in the active plugins list. False, not in the list.
 */
function ppnp_is_plugin_active( $plugin ) {
	return	in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
}


/**
 * 
 * filter bid form output to semantically reflect noproxy behaviour
 *
 * @package Prospress Noproxy
 * @since 1.0
 *
 */
function ppnp_bid_form( $form ) {
	return str_replace('Enter max bid: ', __( 'Enter bid: ', 'noproxy' ), $form);
}
/**
 * 
 * filter bid increment value to increment by 100% ( no proxy style increments )
 *
 * @package Prospress Noproxy
 * @since 1.0
 *
 */
function ppnp_bid_increment( $eqn ) {
	$equ[increment] = $equ[bid_value];
	return $eqn;
}
/**
 * 
 * Update wining bid value in postmeta
 *
 * @package Prospress Noproxy
 * @since 1.0
 *
 * @uses get_winning_bid
 *
 */
function ppnp_winning_bid_value( $bid ) {

	if (!function_exists( 'get_winning_bid') )
		return;
	$new_winning_bid_id = get_winning_bid( $bid[ 'post_id' ] )->ID;
	update_post_meta( $new_winning_bid_id, 'winning_bid_value', $bid['bid_value'] );
}
/**
* Hooks into validate_bid to alter bid validation case 4:
*   - Current user still winning & new bid greater than last.
* 
*
* @package Prospress Noproxy
* @since 1.0
*
*/
function ppnp_validate_bid( $bid ) {
	global $market_systems;
	//re-route max bid case to standard winning bid case.
	if ( 4 == $bid['message_id'] ){
		$bid['message_id'] = 'noproxy';
		$market_systems[ 'auctions' ]->message_id = 'noproxy';
		}
	return $bid;
}
/**
 * Prints appropriate bid message for noproxy max bid increase
 *
 * @package Prospress Noproxy
 * @since 1.0
 *
 */
function ppnp_validate_post( $message, $message_id ) {
	if ( 'noproxy' == (string)$message_id )
		return __( 'Congratulations, you are the winning bidder.', 'noproxy' );
	else
		return $message;
}
?>