<?php

$meatGeneral = array('general-settings'	=> array(	'name'		=> __('General Settings', 'wooconvo'),
													'type'	=> 'tab',
													'meat'	=> array('button-text' =>  array('label'	=> __('Button Label', 'wooconvo'),
            																					'desc'		=> __('Send Button Label', 'wooconvo'),
															            						'id'		=> $this->plugin_meta['shortname'].'_button_text',
															            						'type'		=> 'text',
															            						'default'	=> __('Send', 'wooconvo'),
															            						'help'		=> __('', 'wooconvo')
															            					),
													            		'placehoder-text' =>  array('label'	=> __('Placeholder Label', 'wooconvo'),
													            							'desc'		    => __('A placeholder label for message box', 'wooconvo'),
													            							'id'			=> $this->plugin_meta['shortname'].'_message_placeholder',
													            							'type'			=> 'text',
													            							'default'		=> __('Type Message', 'wooconvo'),
													            							'help'			=> __('', 'wooconvo')
													            							),
												),
											),
										);
					
$general_options = apply_filters('wooconvo_pro_options', $meatGeneral);
					

$meatDialog = array(
		'message-sent'	=> array(	'label'		=> __('Message Sent message', 'wooconvo'),
		'desc'		=> __('This message will be shown when message is sent', 'wooconvo'),
		'id'			=> $this->plugin_meta['shortname'].'_message_sent',
		'type'			=> 'textarea',
		'default'		=> '',
		'help'			=> ''),
		
		'email-message'	=> array(	'label'		=> __('Email', 'wooconvo'),
		'desc'		=> __('This will be sent as email text.', 'wooconvo'),
		'id'			=> $this->plugin_meta['shortname'].'_email_message',
		'type'			=> 'textarea',
		'default'		=> '',
		'help'			=> 'Shortcodes:<br>
		Sender Name: %sender_name%<br>
		Sender Email: %sender_email%'),
		
		'filetype-error'	=> array('label'		=> __('File type not supported message', 'wooconvo'),
		'desc'		=> __('This message will be shown invalid file type is selected', 'wooconvo'),
		'id'			=> $this->plugin_meta['shortname'].'_filetype_error',
		'type'			=> 'textarea',
		'default'		=> '',
		'help'			=> ''),);


// $option_tabs = array('file-settings'	=> array(	'name'		=> __('General Settings', 'wooconvo'),
// 														'type'	=> 'tab',
// 														'meat'	=> $general_options,
														
// 													),
// 													array(	'name'		=> __('Email Settings', 'wooconvo'),
// 														'type'	=> 'tab',
// 														'meat'	=> $general_options,
														
// 													),
// 													array(	'name'		=> __('File Settings', 'wooconvo'),
// 														'type'	=> 'tab',
// 														'meat'	=> $general_options,
														
// 													),
// 												);
$this -> the_options = apply_filters('wooconvo_options_tabs', $general_options);