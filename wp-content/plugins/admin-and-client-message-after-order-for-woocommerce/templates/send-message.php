<?php
/*
 * This template is use to send/reply convo
 */


global $wooconvo;

//echo 'the pass var is '.$user_name
wp_nonce_field('doing_wooconvo','wooconvo_nonce');
wp_enqueue_style( 'dashicons' );
wp_enqueue_style( 'jquery-blockui' );

$file_attachment = $wooconvo->get_option('_file_attachment');
$file_attach = false;
if (isset($file_attachment[0]) && $file_attachment[0] == 'yes') {
    $file_attach = true;
}


// Labels
$button_label = $wooconvo->get_option('_button_text');
$button_label = $button_label != '' ? $button_label : 'Send';
$placehoder_label = $wooconvo->get_option('_message_placeholder');
$placehoder_label = $placehoder_label != '' ? $placehoder_label : 'Type Message';

$textarea_class = 'wooconvo-textarea';
if( !$file_attach ) {
    $textarea_class .= ' wooconvo-nofile';
}

$class="";
$textbox = '';
$enable_revision = 'no';
if(class_exists('WOOCONVO_REV')){
    $enable_revision = $wooconvo->get_option('_enable_revision') != '' ? $wooconvo->get_option('_enable_revision')[0] : 'no' ;  
    $no_revision = $wooconvo->get_option('_no_revision');  
    $no_revision = $no_revision != '' ? $no_revision : 3;  

    $rev_button_text = $wooconvo->get_option('_rev_button_text');  
    $rev_button_text = $rev_button_text != '' ? $rev_button_text : 'Revision';

    $app_button_text = $wooconvo->get_option('_app_button_text');  
    $app_button_text = $app_button_text != '' ? $app_button_text : 'Approve'; 

    $cancel_button_text = $wooconvo->get_option('_cancel_button_text');  
    $cancel_button_text = $cancel_button_text != '' ? $cancel_button_text : 'Cancel Revision';

    
    if($enable_revision == 'yes' && !is_admin()){
        $textbox = 'woo-cus-box-hide';
        $class="cus-upload";
    }              
}

?>
<div id="wooconvo-send" class="wooconvo-send">
<input type="hidden" name="order_id" value="<?php echo $wooconvo->order_id; ?>" />

<?php if ($convo_order_admin == 'yes'){?>
<input type="hidden" name="is_admin" value="yes" />
<?php }?>

        <div class="bottom-bottons">
            <textarea class="<?php echo esc_attr($textarea_class); echo ' '.$textbox;?>" name="message" placeholder="<?php printf(__('%s', 'wooconvo'), $placehoder_label)?>"></textarea>
            <input type="submit" name="nm-wooconvo-send"
                class="nm-wooconvo-send <?php echo $textbox; ?>"
                value="<?php printf(__('%s', 'wooconvo'), $button_label)?>"
                onclick="return send_order_message()">
            
            <?php
            
            if( $file_attach ) :
            
                $name = 'wooconvo_file';
                $upload_label = $wooconvo->get_option('_upload_text');
                $upload_label = $upload_label != '' ? $upload_label : 'Select file';
                $label_select     = sprintf(__('%s', 'wooconvo'), $upload_label);
                $files_allowed    = ($this->get_option('_files_allowed') ? 5   : $this->get_option('_files_allowed'));
                $file_types       = ($this->get_option('_types_allowed') == '' ? 'jpg,png,gif' : $this->get_option('_types_allowed'));
                $file_size        = ($this->get_option('_size_limit') == '' ? '5mb' : $this->get_option('_size_limit'));
                $chunk_size       = '1mb';
                
                $args = array(  
                    'name'                  => $name,
                    'id'                    => $name,
                    'data-type'             => 'file',
                    'button-label-select'   => $label_select,
                    'button-label-upload'   => $label_select,
                    'files-allowed'         => $files_allowed,
                    'file-types'            => $file_types,
                    'file-size'             => $file_size,
                    'chunk-size'            => $chunk_size
                );
                    
                 echo '<div id="nm-uploader-area-'. $name.'" class="nm-uploader-area '.$class.'"  style="diaplay:none;">';
                $wooconvo -> inputs['file'] -> render_input($args);
                echo '</div>';

                if(!is_admin() && $enable_revision == 'yes'){ ?>
                <div class="wooconvo-cus-btn">
                   <input type="submit" name="nm-wooconvo-send"
                    class="woo-approve-btn btn"
                    data-val = 'Approve'
                    value="<?php _e($app_button_text, 'wooconvo'); ?>"
                    onclick="return send_order_message_onfrontend()">
                <?php   $rev_count = get_post_meta($wooconvo->order_id, 'revision_msg', true);
                if(empty($rev_count)) $rev_count = 0;
               
                if($rev_count < $no_revision){?>
                    <Button class="woo-revise-btn"><?php _e($rev_button_text, 'wooconvo');?></Button>     
                </div>
                    <Button class="woo-cancel-revise-btn"><?php _e($cancel_button_text, 'wooconvo');?></Button>     
                
                 <div class="woo-header">
                    <h4><?php _e('REVISION ('.$rev_count.' of '.$no_revision.' Max)', 'wooconvo'); ?></h4>
                </div>
                
            <?php } }?>
                
                <input type="hidden" id="_order_file_name" name="_order_file_name">
                <span id="sending-order-message"></span>
                
            <?php endif; ?>
        </div>
</div>