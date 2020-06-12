<?php
//about theme info
add_action( 'admin_menu', 'natural_herbs_lite_abouttheme' );
function natural_herbs_lite_abouttheme() {    	
	add_theme_page( esc_html__('About Theme', 'natural-herbs-lite'), esc_html__('About Theme', 'natural-herbs-lite'), 'edit_theme_options', 'natural_herbs_lite_guide', 'natural_herbs_lite_mostrar_guide');   
} 
//guidline for about theme
function natural_herbs_lite_mostrar_guide() { 
	//custom function about theme customizer
	$return = add_query_arg( array()) ;
?>
<div class="wrapper-info">
	<div class="col-left">
   		   <div class="col-left-area">
			  <?php esc_attr_e('Theme Information', 'natural-herbs-lite'); ?>
		   </div>
          <p><?php esc_attr_e('Natural Herbs Lite is a herbal WordPress theme and template which is responsive and can be used for fresh, organic, nature, eco friendly, solar, green energy, vegetative, farming, agriculture, spices based. Ayurveda, Yoga, Naturopathy, Medical and Medicines, Spa, stress relief and conservation websites can also use it. It can also be used for multipurpose and is Gutenberg and other page builder like Elementor compatible and is also WooCommerce compatible for online shop and store.','natural-herbs-lite'); ?></p>
		  <a href="<?php echo esc_url(NATURAL_HERBS_LITE_SKTTHEMES_PRO_THEME_URL); ?>"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/free-vs-pro.png" alt="" /></a>
	</div><!-- .col-left -->
	<div class="col-right">			
			<div class="centerbold">
				<hr />
				<a href="<?php echo esc_url(NATURAL_HERBS_LITE_SKTTHEMES_LIVE_DEMO); ?>" target="_blank"><?php esc_attr_e('Live Demo', 'natural-herbs-lite'); ?></a> | 
				<a href="<?php echo esc_url(NATURAL_HERBS_LITE_SKTTHEMES_PRO_THEME_URL); ?>"><?php esc_attr_e('Buy Pro', 'natural-herbs-lite'); ?></a> | 
				<a href="<?php echo esc_url(NATURAL_HERBS_LITE_SKTTHEMES_THEME_DOC); ?>" target="_blank"><?php esc_attr_e('Documentation', 'natural-herbs-lite'); ?></a>
                <div class="space5"></div>
				<hr />                
                <a href="<?php echo esc_url(NATURAL_HERBS_LITE_SKTTHEMES_THEMES); ?>" target="_blank"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/sktskill.jpg" alt="" /></a>
			</div>		
	</div><!-- .col-right -->
</div><!-- .wrapper-info -->
<?php } ?>