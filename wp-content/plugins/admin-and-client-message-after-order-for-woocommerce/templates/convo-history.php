<?php
/*
 * this template is loading all conversations
 * against one order
 * used for: Front-end & Admin
 */
global $wooconvo;

$convos = $wooconvo -> get_order_convos();

echo '<div class="wooconvo-send">';
echo '<div class="chat-container">';


// If not chat found
if( !$convos ) {
    
    $default_message = apply_filters('wooconvo_default_message', __("Type message below",'wooconvo'));
    echo '<ol class="chat">';
    echo '<li class="wooconvo-first-message">'.$default_message.'</li>';
    echo '</ol>';
    
    echo '</div>';  // .wooconvo-send
    echo '</div>';  // .chat-container
    return;
}
    
    

echo '<input type="hidden" name="existing_convo_id" value="'.$wooconvo->order_id.'" />';


$thread = json_decode($convos -> convo_thread);

// showing last message on top
if( apply_filters('wooconvo_show_latest_on_top', false) ) {
    $thread = array_reverse($thread);
}

$hide_gravatar = $wooconvo->get_option('_hide_gravatar');

$gravatar = true;

if (isset($hide_gravatar[0]) && $hide_gravatar[0] == 'hide') {
    $gravatar = false;
}

// $wooconvo -> pa($thread);

$vendor_emails = wooconvo_get_order_admin_email($wooconvo->order_id);
// Since it retuns an array, we will pickup first
$vendor_email = $vendor_emails[0];

?>


<ol class="chat">
<?php foreach ($thread as $msg) {
        // ppom_pa($msg);
        if ( $convo_order_admin == 'yes' ) {
            $css_class = ($msg->sent_by == wooconvo_get_vendor_name($wooconvo->order_id, $convo_order_admin, $vendor_emails)) ? 'self' : 'other' ;
        } else {
            $css_class = ($msg->sent_by == wooconvo_get_vendor_name($wooconvo->order_id)) ? 'other' : 'self' ;
        }
    ?>
    <li class="<?php echo $css_class; ?>">
        <span class="avatar">
            <?php
            
                if ($msg->user == $vendor_email) {
                    if ($gravatar) {
                         echo get_avatar( $vendor_email, 128 );   
                    }
                } else {
                    if ($gravatar) {
					 echo get_avatar( $msg->user, 128 );
                    }
				}
			?>
        </span>
        <span class="msg">
            <p><strong><?php echo $msg->sent_by; ?></strong></p>
            <p>
                <?php echo stripslashes($msg->message); ?>
				<?php if ($msg->files != '') {
					$wooconvo -> render_attachments($msg->files);
				} ?>                        
            </p>
            <time><span class="dashicons dashicons-clock"></span> <?php echo $wooconvo->time_difference($msg->senton); ?></time>
        </span>
    </li>        	
<?php } ?>
</ol>

</div>	<!--wooconvo-send-->
</div>  <!--chat-container-->