<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package Natural Herbs Lite
 */
?>
<div id="sidebar">    
    <?php if ( ! dynamic_sidebar( 'sidebar-1' ) ) : ?>
        <h3 class="widget-title"><?php esc_html_e( 'Category', 'natural-herbs-lite' ); ?></h3>
        <aside id="categories" class="widget">           
            <ul>
                <?php wp_list_categories('title_li=');  ?>
            </ul>
        </aside>
       <h3 class="widget-title"><?php esc_html_e( 'Archives', 'natural-herbs-lite' ); ?></h3>
        <aside id="archives" class="widget">           
            <ul>
                <?php wp_get_archives( array( 'type' => 'monthly' ) ); ?>
            </ul>
        </aside>
         <h3 class="widget-title"><?php esc_html_e( 'Meta', 'natural-herbs-lite' ); ?></h3>
         <aside id="meta" class="widget">           
            <ul>
                <?php wp_register(); ?>
                <li><?php wp_loginout(); ?></li>
                <?php wp_meta(); ?>
            </ul>
        </aside>
    <?php endif; // end sidebar widget area ?>	
</div><!-- sidebar -->