<?php
/*
 * This template is loading order's convo
 * if user/customer is logged
 * used when shortcode is used
 */


global $wooconvo;

if (!isset($_REQUEST['order']) || !$wooconvo -> order_valid):

if(!$wooconvo -> order_valid)
	echo '<span class="wooconvo-error">'.__('No messages found or you provided wrong information', $wooconvo->plugin_meta['shortname']).'</span>';

?>
<div id="wooconvo-load-order">
	<form method="get">
	<label><?php _e('Type Your Order Number', $wooconvo->plugin_meta['shortname'])?>
		<input type="text" name="order" value="" />
	</label>
	<label><?php _e('Type Your Email', $wooconvo->plugin_meta['shortname'])?>
		<input type="email" name="email" value="" />
	</label>
	
	<br>		
	<input type="submit" value="<?php _e('Load detail', $wooconvo->plugin_meta['shortname'])?>">
	</form>
</div>
<?php else:

	$wooconvo -> render_wooconvo_frontend();

endif;