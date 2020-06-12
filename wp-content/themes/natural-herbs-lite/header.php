<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div class="container">
 *
 * @package Natural Herbs Lite
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div class="header">
<div class="headerinformation">
<div class="container">
<?php
$contact_no = get_theme_mod('contact_no');
$contact_mail = get_theme_mod('contact_mail');
?>
<?php if ($contact_no || $contact_mail) { ?>
<div class="header-left">
    <?php  if (!empty($contact_no)) { ?>
    <div class="phone-top-left"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/icon-phone.png" /><?php echo esc_html($contact_no); ?></div>
    <?php } 
            if(!empty($contact_mail)){ 
        ?>
    <div class="email-top-left"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/icon-email.png" /><a href="mailto:<?php echo esc_attr( antispambot(sanitize_email( $contact_mail ) )); ?>"><?php echo esc_html( antispambot( $contact_mail ) ); ?></a></div>
    <?php } ?>
    <div class="clear"></div>
</div>
<?php } ?>
<div class="header-right">
    <?php
            $fb_link = get_theme_mod('fb_link'); 
            $twitt_link = get_theme_mod('twitt_link');
            $gplus_link = get_theme_mod('gplus_link');
            $linked_link = get_theme_mod('linked_link');
       
       if(!empty($fb_link) || !empty($twitt_link)  || !empty($gplus_link)  || !empty($linked_link)){?>	
      <div class="header-social">
      <div class="header-social-icons">
                <?php 
                if (!empty($fb_link)) { ?>
                <a title="<?php esc_attr__('facebook','natural-herbs-lite'); ?>" class="fb" target="_blank" href="<?php echo esc_url($fb_link); ?>"></a> 
                <?php }						
                if (!empty($twitt_link)) { ?>
                <a title="<?php esc_attr__('twitter','natural-herbs-lite'); ?>" class="tw" target="_blank" href="<?php echo esc_url($twitt_link); ?>"></a>
                <?php } 
                if (!empty($gplus_link)) { ?>
                <a title="<?php esc_attr__('google-plus','natural-herbs-lite'); ?>" class="gp" target="_blank" href="<?php echo esc_url($gplus_link); ?>"></a>
                <?php } 						
                 if (!empty($linked_link)) { ?> 
                <a title="<?php esc_attr__('linkedin','natural-herbs-lite'); ?>" class="in" target="_blank" href="<?php echo esc_url($linked_link); ?>"></a>
                <?php } ?>                   
              </div>  
               </div>
        <?php } ?>
</div>
<div class="clear"></div>
</div>
     </div>	
<div class="container">
    <div class="logomenuarea">
        <div class="logo">
    <?php natural_herbs_lite_the_custom_logo(); ?>
    <div class="clear"></div>
    <?php
    $description = get_bloginfo( 'description', 'display' );
    ?>
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
    <h2 class="site-title"><?php bloginfo('name'); ?></h2>
    <?php if ( $description || is_customize_preview() ) :?>
    <p class="site-description"><?php echo esc_html($description); ?></p>                          
    <?php endif; ?>
    </a>
</div>
<div class="navarea">
    <div class="toggle"><a class="toggleMenu" href="#" style="display:none;"><?php esc_attr_e('Menu','natural-herbs-lite'); ?></a></div> 
    <div class="sitenav">
      <?php wp_nav_menu( array('theme_location' => 'primary') ); ?>         
    </div><!-- .sitenav--> 
</div>
    <div class="clear"></div> 
    </div>	
</div> <!-- container -->
</div><!--.header -->