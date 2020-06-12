<?php

/* == Direct access not allowed ==*/
if( ! defined('ABSPATH' ) ){ exit; }


$existing_tabs = get_option('wooh_tabs');

echo '<div id="container">';

  $label_counter = 1;
    if(is_array($existing_tabs)){
        foreach ($existing_tabs as $order => $contents) {
            render_the_row($contents, $label_counter);   
            $label_counter++;
        }
    }else{
        render_the_default();
    }
echo '</div>';
?>

<br>
<button id="save-all" class="button button-primary"><?php _e('Save tabs','wooh') ?></button>
<button id="reset-all" class="button button-secondary"><?php _e('Reset tabs','wooh') ?></button><br>
<span id="saving-tabs"></span>