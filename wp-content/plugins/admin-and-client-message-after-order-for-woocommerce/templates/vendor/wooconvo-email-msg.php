<?php
/**
 * Vendor Customer Msg
 *
**/ 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


do_action( 'woocommerce_email_header', $email_heading, $email); ?>

<?php  echo $message;


?>



<?php


do_action( 'woocommerce_email_footer', $email);