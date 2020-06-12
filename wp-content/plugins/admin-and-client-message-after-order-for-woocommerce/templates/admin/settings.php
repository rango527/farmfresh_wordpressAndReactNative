<?php 
/*
 * this file rendering admin options for the plugin
* options are defined in admin/admin-options.php
*/

$this -> load_template('admin/options.php');

$vendor_template_url = get_bloginfo('url').'/wp-admin/admin.php?page=wc-settings&tab=email&section=wooemail_vendor_msg';
$sendUpdate = array();
	$active = 'active';
?>

<style type="text/css">
	.wooconvo-setting label{
		display: block;
	}
</style>

<div class="wooconvo_refresh_loader"></div>
<div id="wooconvo-hole-code" style="display:none">
	<h2><?php echo $this->plugin_meta['name']?></h2>
	<div  class="naccs  wooconvo-setting">
		<div class="row">
			<div class="gc col-md-3 col-sm-3 col-xs-5">
				<div class="menu">
				<?php foreach($this -> the_options as $id => $option){ ?>
	     			<div class="<?php echo $active;?>">
	     				<span class="light"></span>
	     				<span><?php echo $option['name']?></span>
	     			</div>
				<?php $active = '';} ?>
				</div>
			</div>
			<div class="gc col-md-9 col-sm-9 col-xs-7">
				<ul class="nacc">
				<?php $active = 'active'; foreach($this -> the_options as $id => $option){ ?>
		      		<li  class="<?php echo $active;?> plugin-field-set">	
		      		<div>
					<?php
						$desc = isset($option['desc']) ? stripslashes($option['desc']) : '';
						foreach($option['meat'] as $key => $data){
							
								
								$field_type  = isset($data['type']) ? $data['type'] : '';
								$field_name  = isset($data['id']) ? $data['id'] : '';
								$field_label = isset($data['label']) ? $data['label'] : '';
								$field_desc  = isset($data['desc']) ? $data['desc'] : '';
								$field_help  = isset($data['help']) ? $data['help'] : '';
								$link        = isset($data['link']) ? $data['link'] : '';
								
								$sendUpdate[$field_name] = array('type'	=> $field_type);
								
						
								$field_value = isset($this->plugin_settings[$field_name]) ? $this->plugin_settings[$field_name] : '';
								$field_value = $field_type == 'checkbox' ? $field_value : stripcslashes($field_value);
								
					?>
				
					<?php switch($field_type){
						
						case 'text':
					?>
							<div>
								<label for="<?php echo $field_name?>"><?php echo $field_label?></label>
								<h4><?php echo $field_desc?> </h4>
								<input type="text" name="<?php echo $field_name?>" id="<?php echo $field_name?>" value="<?php echo $field_value?>" class="regular-text">
								<em><?php echo $field_help?> </em> 
							</div>
							
					<?php 
						break;
						case 'textarea':
							$ta_val = $field_value;
					?>
							<div>
								<label for="<?php echo $field_name?>"><?php echo $field_label?></label>
								<h4><?php echo $field_desc?> </h4>
								<textarea cols="45" rows="6" name="<?php echo $field_name?>" id="<?php echo $field_name?>"><?php echo $ta_val?></textarea>
								<br />
								<em><?php echo $field_help?> </em> 
								
							</div>
								
					<?php 
						break;
						case 'checkbox':
					?>
								<div>
									<label for="<?php echo $field_name?>"><?php echo $field_label?></label>
									<h4><?php echo $field_desc?> </h4>
									<?php foreach($data['options'] as $k => $label){?>
									
										<label style="font-weight: unset; margin-top:0px; margin-bottom:0px"for="<?php echo $field_name.'-'.$k?>"> 
										<input type="checkbox" name="<?php echo $field_name?>" id="<?php echo $field_name.'-'.$k?>" value="<?php echo $k?>"> <?php echo $label?>
										</label></br>
									<?php }?>

									<em><?php echo $field_help?> </em> 
									<?php if(! empty($link) ) { ?>
									<a target="_blank" href="<?php echo $vendor_template_url; ?>"><?php echo $link; ?></a>
									<?php } ?>
									
								
									<!-- setting value -->
									<script>
									setChecked('<?php echo $field_name?>', '<?php echo json_encode($field_value)?>');
									</script>
								
									
								</div>		
									
					<?php 
						break;
						case 'radio':
					?>
							<div>
								<label for="<?php echo $field_name?>"><?php echo $field_label?></label>
								<h4><?php echo $field_desc?> </h4>
									<?php foreach($data['options'] as $k => $label){?>
										<label style="font-weight: unset;margin-top:0px; margin-bottom:0px" for="<?php echo $field_name.'-'.$k?>"> <input type="radio" name="<?php echo $field_name?>" id="<?php echo $field_name.'-'.$k?>" value="<?php echo $k?>"> <?php echo $label?>
										</label></br>
								
								<?php }?>
								
								<script>
								setCheckedRadio('<?php echo $field_name?>', '<?php echo $field_value?>');
								</script>
							</div>
							
					<?php 
						break;
						case 'select':
					?>
					
							<div>
								<label for="<?php echo $field_name?>"><?php echo $field_label?></label>									 
								<h4><?php echo $field_desc?> </h4>
								
									<select name="<?php echo $field_name?>" id="<?php echo $field_name?>">
									
										
										<?php foreach($data['options'] as $k => $label){
											
												$selected = ($k == $this ->plugin_settings[ $data['id'] ]) ? 'selected = "selected"' : '';
												
												echo '<option value="'.$k.'" '.$selected.'>'.$label.'</option>';
										}
											?>
										
									</select> 
									</span>
								
								<br />
								<em><?php echo $field_help?> </em>
							</div>
								
									
					<?php 
						break;
						case 'para':
					?>
								<div>
									
									<h4><?php echo $field_desc?> </h4>
									
									<br />
									<em><?php echo $field_help?> </em>
								</div>
							
							
												
					<?php 
						break;
						case 'file':
					?>
				
							
								<?php 
								$file = $this->plugin_meta['path'] .'/templates/admin/'.$data['id'];
								if(file_exists($file))
									include $file;
								else 	
									echo 'file not exists '.$file;
								?> 
							
														
					<?php 
						break;
					} }
				 $active = '';
				?>
			</div>
			</li>
		<?php $active="";} ?>
		</ul>
		</div>
		<p class="woo_setting_btn" ><button style="margin-top:70px" class="button button-primary" onclick=updateOptions('<?php echo json_encode($sendUpdate)?>')><?php _e('Save settings', 'wooconvo')?></button></p>
	</div>
</div>