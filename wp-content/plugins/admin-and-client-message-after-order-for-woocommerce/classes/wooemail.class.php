<?php
/*
 * Wooconvo vendor email class
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Wooemail_Vendor_Msg extends WC_Email {
    
  
    public function __construct() {

        // set ID, this simply needs to be a unique name
        $this->id   = 'wooconvo_chat';

        // this is the title in WooCommerce Email settings
        $this->title = 'Vendor Customer Msg';

        // this is the description in WooCommerce email settings
        $this->description = 'Email notifications are sent to choosen Recipient when any message is created.';

        // these are the default heading and subject lines that can be overridden using the settings
        $this->heading = 'New Order Message Receive';
        $this->subject = 'New Order Message Receive';
        
        
        $this->template_html  = 'wooconvo-email-msg.php';
        
        
        // Call parent constructor to load any other defaults not explicity defined here
        parent::__construct();

        // this sets the recipient to the settings defined below in init_form_fields()
        $this->recipient = $this->get_option( 'recipient' );
        
    }
    
   
    public function trigger( $order_id,$to,$wooconvo_message,$order_detail_url ) {
        

        if ( ! $order_id )
            return;
            
        // var_dump($this->recipient);
        // exit;
            
        if(!empty($this->recipient) )
            array_push($to, $this->recipient);
            
     
        if ( ! $this->is_enabled())
            return;
            
        $order = wc_get_order( $order_id );
        
        $order_number = $order_id;
        if(!empty($order->get_order_number()) && class_exists('WC_Seq_Order_Number')){
            $order_number = $order->get_order_number();
            
        }
            

            
        $subject = $this->get_subject().' Order no  #'.$order_number;
        
    
        $send_email = $this->send( $to, $subject, $this->get_content_temp($wooconvo_message), $this->get_headers(), '' );
        
        if($send_email){
            return true;
        }
        return false;
        
    }
    
    
    
    public function get_content_temp($wooconvo_message) {
        ob_start();
        $this->load_templates( $this->template_html, array(
            'message'         => $wooconvo_message,
            'order'           => $this->object,
            'email_heading'   => $this->get_heading()
        ) );
        return ob_get_clean();
    }
    
    function load_templates($file_name, $variables=array('')){

		extract($variables);
		

		$file_path = WOOCONVO_PATH . "/templates/vendor/{$file_name}";
	
		if (file_exists($file_path))
			require($file_path);
		else
			die('Could not load file '.$file_path);
	}
    

    
    
    public function init_form_fields() {

        $this->form_fields = array(
            'enabled'    => array(
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable this email notification',
                'default' => 'yes'
            ),
            'recipient'  => array(
                'title'       => 'Recipient(s)',
                'type'        => 'text',
                'description' => sprintf( 'Enter recipients (comma separated) for this email.', '' ),
                'placeholder' => '',
                'default'     => ''
            ),
            'subject'    => array(
                'title'       => 'Subject',
                'type'        => 'text',
                'description' => sprintf( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
                'placeholder' => '',
                'default'     => ''
            ),
            'heading'    => array(
                'title'       => 'Email Heading',
                'type'        => 'text',
                'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.' ), $this->heading ),
                'placeholder' => '',
                'default'     => ''
            ),
            'email_type' => array(
                'title'       => 'Email type',
                'type'        => 'select',
                'description' => 'Choose which format of email to send.',
                'default'     => 'html',
                'class'       => 'email_type',
                'options'     => array(
                'html'      => 'HTML',
                )
            )
        );
    }
    
}