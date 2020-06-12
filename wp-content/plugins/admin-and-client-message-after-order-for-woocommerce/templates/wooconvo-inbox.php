<?php
/*
 * wooconvo form template file
 */
global $wooconvo;

?>
<div id="compose"><a title="New Convo" href="#" onclick="doShowCompose();return false;">Compose</a> </div>
          
          <!-- compose new convo starts -->
<div id="compose-convo" style="display:none">
<form id="frm-new-wooconvo" method="post" onSubmit="return validateCompose()">
    <table width="100%" border="0">
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
        </table>
</form>
</div>
          <!-- compose new convo ends -->
          
<div id="wooconvo-login">
<form id="frm-wooconvo-login" method="post" onSubmit="return validateLogin()">
    <table width="100%" border="0">
          <tr>
            <td>Email:</td>
            <td>&nbsp;</td>
            <td><input type="email" name="loginemail" id="loginemail" placeholder="Email"><br>
            <span class="error" id="loginemail_err">Required</span></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td><input type="submit" name="nm-wooconvo-login" value="Login"></td>
            <td>&nbsp;</td>
          </tr>
        </table>
</form>
</div>

<?php if(isset($_REQUEST['user_email'])){?>   
<div id="wooconvo-inbox">
<?php } else {?>
<div id="wooconvo-inbox" style="display:none">
<?php } ?>
<?php
		extract($_REQUEST);
echo $user_email;
//if(isset($_REQUEST['user_email']));
$arrConvo = $wooconvo -> get_user_convos();
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
	<table width="100%" border="0">
		<tr>
			<td><input type="checkbox" name="convos[]" value="<?php echo $convo-> convo_id?>"/></td>
            <td><?php echo $unread_class?> <?php echo $parties?></td>
            <td><a href="javascript:loadConvoHistory(<?php echo $convo-> convo_id?>);"><?php echo $title?></a></td>
            <td><?php echo date('M-d,y i:s', strtotime($convo -> sent_on))?></td>
		</tr>
	</table>
    <!-- convo detail -->
   	<div id="convo-history-panel-<?php echo $convo-> convo_id?>" style="display:none">
    <table width="100%" border="0">
		<tr>
        	<td>
  				<h2 id="history-heading"></h2>
    			<p><a href="javascript:loadConvoCurrentPage(<?php echo $convo-> convo_id?>)">&laquo; <?php _e('Back to Conversations')?></a></p>
    			<p id="convo-detail-container">
   				 <img src="<?php echo plugins_url('images/loading.gif', __FILE__)?>" alt="Wait..." />
   				</p>
				<?php 
				$arrSelectedConvo = json_decode($convo -> convo_thread);
				foreach($arrSelectedConvo as $c):
				$selectedConvoTitle = $c -> username . __(' wrote on ').date('M-d,Y', $c->senton);
    			echo stripslashes($selectedConvoTitle).' ';
        		echo stripslashes($c -> message).' ';
        
				if($c -> files)
				{
					$files = explode(',', $c -> files);
					echo '<strong>['.count($files).'] Files Attachment:</strong><br />';
					foreach($files as $f):
						$file_path = $path_folder . $c -> username .'/'.$f;
						echo '<a href="'.$file_path.'" target="_blank">'.$f.'</a>';
						echo "<br />";
					endforeach;
				}
				endforeach;
				?>  
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
  			<!-- convo detail -->
            </td>
		</tr>
	</table>
	</div>
<?php
}
?>
</div>