<?php

if ( is_page_template( 'template-transparent-header.php' ) ) {
	get_header( 'transparent' );
} else {
	get_header();
}

if ( 'page' == get_option( 'show_on_front' ) ) {
?>

	<div id="primary" class="content-area full-width">
		<main id="main" class="site-main" role="main">

		<?php
		while ( have_posts() ) : the_post();

			get_template_part( 'content', 'front-page' );

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

		endwhile; // End of the loop.

		?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>

<?php
} else {

	get_template_part( 'home' );

}
?>