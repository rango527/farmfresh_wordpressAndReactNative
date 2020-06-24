<?php

echo'	<div class="wrap">
			<div><img style="float:left;" src="'.MOV_LOGO_URL.'"></div>
			<div class="otp-header">
				'.mo_("OTP Verification").'
				<a class="add-new-h2" id="accountButton" href="'.$profile_url.'">'.mo_("Account").'</a>
                <a class="add-new-h2" id="LicensingPlanButton" style="background-color:orange;color:black;" href="'.$license_url.'">'.mo_("Licensing Plans").'</a>
				<a class="add-new-h2" id="faqButton" href="'.$help_url.'" target="_blank">'.mo_("FAQs").'</a>
				<a class="mo-otp-feedback add-new-h2" id="feedbackButton" href="#">'.mo_("Feedback").'</a>
	            <div class="mo-otp-help-button static" style="margin-right:60px;z-index:10">
                    <a id="show_prem_addons_button" class="button button-primary button-large" style="background:orange;color:black">
                        <span class="dashicons dashicons-admin-tools" style="margin:5% 0 0 0;"></span>
                            '.mo_("Premium Addons").'
                    </a>
                    <a class="button button-primary button-large" id="restart_tour_button">
                        <span class="dashicons dashicons-controls-repeat" style="margin:5% 0 0 0;"></span>
                            '.mo_("Restart Tour").'
                    </a>
                </div>
            </div>
		</div>';

echo'	<div id="tab">
			<h2 class="nav-tab-wrapper">';

        
        foreach ($tabDetails->_tabDetails as $tabs)
        {
            if($tabs->_showInNav) {
                echo '<a  class="nav-tab 
                        ' . ($active_tab === $tabs->_menuSlug ? 'nav-tab-active' : '') . '" 
                        href="' . $tabs->_url . '"
                        style="'. $tabs->_css .'"
                        id="' . $tabs->_id . '">
                        ' . $tabs->_tabName . '
                    </a>';
            }
        }

        echo '</h2>';

        if(!$registered) {
            echo '<div  style="background-color:rgba(255,5,0,0.29);font-size:0.9em;" 
                        class="notice notice-error">
                        <h2>' .$registerMsg.'</h2>
                  </div>';
        }else if(!$activated) {
            echo '<div  style="background-color:rgba(255,5,0,0.29);font-size:0.9em;" 
                        class="notice notice-error">
                        <h2>' .$activationMsg.'</h2>
                  </div>';
        }