<?php 

/* == Direct access not allowed ==*/
if( ! defined('ABSPATH' ) ){ exit; }

?>

<div id="nm-inquiry-tab" class="nm-enquiry-plugin">
	<form id="send-inquiry">
		<input type="hidden" name="wooh_product_id" value="<?php echo esc_attr($product_id);?>">
		<input type="hidden" name="action" value="wooh_send_inquiry">
		<div id="nm-send-enquiry">
			<div class="nm-plugin-box first-text">
			  <label for="nm-sender"><?php _e( 'Name:', 'nm-woostore' ); ?></label>
			  <input type="text" name="wooh_sender" required>
			</div>
			<div class="nm-plugin-box">
			  <label for="sender-email"><?php _e( 'Email:', 'nm-woostore' ); ?></label>
			  <input type="email" name="wooh_sender_email" required>
			</div>
			<p class="clearfix"></p>
			
			<label for="enquiry-text"><?php _e( 'Enquiry:', 'nm-woostore' ); ?></label>
			 <textarea name="wooh_enquiry_text" rows="3" required></textarea>
			 <br>
			 <br>
			<input type="submit">
			<div class="nm-progress">
				<span></span>
			</div>
		 </div>
	</form>
</div>