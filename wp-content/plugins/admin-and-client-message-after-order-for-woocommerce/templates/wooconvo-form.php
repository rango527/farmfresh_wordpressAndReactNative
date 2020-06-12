<?php
/*
 * wooconvo form template file
 */
global $wooconvo;

$arrConvo = $wooconvo -> get_user_convos();

?>
<div id="wooconvo-inbox">
<?php
$convo_row_count = 0;
	foreach($arrConvo as $convo)
	{
		$convo_row_count++;
		$parties = $wooconvo -> convo_parties( $convo -> convo_thread, $current_user->user_login);
		$title = $wooconvo -> convo_title($convo -> subject, $convo -> convo_thread);
		$page_number = ceil($convo_row_count / 10);//nmMemberConvo::$convo_per_page);
		$unread_class = '';
		if($convo -> last_sent_by != $current_user->ID and $convo -> read_by != $current_user->ID)
			$unread_class = 'unread';
	
		//$page_next = nmMemberConvo::getNextPage(
?>
   <!-- Inbox structure starts -->
   
        <li>
        	<ul id="convo-<?php echo $convo-> convo_id?>">
            	<li class="check"><input type="checkbox" name="convos[]" value="<?php echo $convo-> convo_id?>"/></li>
                <li class="buddies <?php echo $unread_class?>" onclick="loadConvoHistory(<?php echo $convo-> convo_id?>)"><?php echo $parties?></li>
                <a href="javascript:loadConvoHistory(<?php echo $convo-> convo_id?>);"><?php echo $title?></a>
                <li class="time" onclick="loadConvoHistory(<?php echo $convo-> convo_id?>)"><?php echo date('M-d,y i:s', strtotime($convo -> sent_on))?></li>
            </ul>
        </li>
   <!-- convo detail -->
    <div id="convo-history-panel-<?php echo $convo-> convo_id?>" style="display:none">
  	<h2 id="history-heading"></h2>
    <p><a class="back-to-convo" href="javascript:loadConvoCurrentPage(<?php echo $convo-> convo_id?>)">&laquo; <?php _e('Back to Conversations')?></a></p>
    <p id="convo-detail-container">
    <img src="<?php echo plugins_url('images/loading.gif', __FILE__)?>" alt="Wait..." />
    </p>
       
    <ul class="nm-convo-detail">
	<?php 
	//$convoDetail = $wooconvo -> getConvoDetail($convo-> convo_id);
	//print_r($convoDetail);

	$arrSelectedConvo = json_decode($convo -> convo_thread);
	foreach($arrSelectedConvo as $c):
	$selectedConvoTitle = $c -> username . __(' wrote on ').date('M-d,Y', $c->senton);
	
	?>
    	<li class="convo-head"><?php echo stripslashes($selectedConvoTitle)?></li>
        <li class="convo-text"><?php echo stripslashes($c -> message)?></li>
        
        
        <?php
		if($c -> files)
		{
			$files = explode(',', $c -> files);
			echo '<li class="convo-attachment">
				<strong>['.count($files).'] Files Attachment:</strong><br />';
			foreach($files as $f):
				$file_path = $path_folder . $c -> username .'/'.$f;
				echo '<a href="'.$file_path.'" target="_blank">'.$f.'</a>';
				echo "<br />";
			endforeach;
			echo '</li>';
		}
		?>
        	
	<?php 
	endforeach;
	?>  
	</ul> 
    <p>    
    <h3><?php _e('Reply')?>:</h3>
    <form id="frm-reply-convo-<?php echo $convo-> convo_id?>" onSubmit="return validateReply(<?php echo $convo-> convo_id?>)" method="post">
    <?php wp_nonce_field('nm-convo-nonce-reply');?>
    <input type="hidden" name="reply-c-id" id="reply-c-id" value="<?php echo $convo-> convo_id?>" />
    <textarea name="nm-reply-<?php echo $convo-> convo_id?>" id="nm-reply-<?php echo $convo-> convo_id?>" rows="4" cols="60"></textarea><br />
    <span class="error" id="reply_err"><?php _e('Required')?></span><br />
    
	<?php
    if(get_option('nmconvo_allow_attachment'))
	{
	?>
    <input id="file_upload_reply-<?php echo $convo-> convo_id?>" name="file_upload_reply-<?php echo $convo-> convo_id?>" type="file" />
   	<input type="hidden" name="file-name-reply-<?php echo $convo-> convo_id?>" id="file-name-reply-<?php echo $convo-> convo_id?>">
    <span id="upload-response-reply"><?php echo $max_file_size?></span><br />

	
    <?php		
	}
	?>

	<input type="submit" value="<?php echo 'Send';?>" name="reply-convo-<?php echo $convo-> convo_id?>"/>
    </form>
    </p>
  </div>
  <!-- convo detail -->

        <?php
		}
	 	?>
</div>

<form id="frm-new-wooconvo" method="post" onSubmit="return validateCompose()">
    <table width="100%" border="0">
          <tbody>
          <tr>
            <td>Order No:</td>
            <td>&nbsp;</td>
            <td><input type="text" name="orderno" id="orderno" placeholder="Order No."><br>
            <span class="error" id="orderno_err">Required</span></td>
          </tr>
          <tr>
            <td>Email:</td>
            <td>&nbsp;</td>
            <td><input type="email" name="customeremail" id="customeremail" placeholder="Email"><br>
            <span class="error" id="customeremail_err">Required</span></td>
          </tr>
          <tr>
            <td>Message:</td>
            <td>&nbsp;</td>
            <td><textarea name="message" id="message" cols="45" rows="5"></textarea><br>
				<span class="error" id="message_err">Required</span>
                </td>
          </tr>
          <!-- file attachement -->
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td><input type="submit" name="nm-new-convo" value="Send"></td>
            <td>&nbsp;</td>
          </tr>
        </tbody></table>
</form>