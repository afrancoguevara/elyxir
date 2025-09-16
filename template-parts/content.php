<?php
/**
 * Template Part: Content
 *
 * Muestra el contenido de una entrada en single y en listados/archives.
 * Usa helpers elyxir_* si existen (post_thumbnail, entry_header, meta, excerpt, read_more, footer).
 *
 * @package Elyxir
 */

defined('ABSPATH') || exit;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php
	// Etiqueta "destacado" para sticky posts en el blog.
	if ( is_sticky() && is_home() && ! is_paged() ) : ?>
		<span class="sticky-post"><?php esc_html_e( 'Featured', 'elyxir' ); ?></span>
	<?php endif; ?>

	<header class="entry-header">
		<?php
		// Thumbnail (helper -> fallback).
		if ( function_exists( 'elyxir_post_thumbnail' ) ) {
			elyxir_post_thumbnail();
		} elseif ( has_post_thumbnail() ) {
			if ( is_singular() ) {
				echo '<figure class="post-thumbnail">';
				the_post_thumbnail( 'post-thumbnail', ['loading' => 'eager', 'decoding' => 'async'] );
				echo '</figure>';
			} else {
				echo '<a class="post-thumbnail" href="' . esc_url( get_permalink() ) . '">';
				the_post_thumbnail( 'post-thumbnail', ['loading' => 'lazy', 'decoding' => 'async'] );
				echo '</a>';
			}
		}

		// Título (helper -> fallback).
		if ( function_exists( 'elyxir_entry_header' ) ) {
			elyxir_entry_header();
		} else {
			if ( is_singular() ) {
				echo '<h1 class="entry-title">' . esc_html( get_the_title() ) . '</h1>';
			} else {
				echo '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">'
					. esc_html( get_the_title() ) . '</a></h2>';
			}
		}

		// Meta (fecha/autor) solo en posts.
		if ( 'post' === get_post_type() ) {
			if ( function_exists( 'elyxir_entry_meta_bar' ) ) {
				elyxir_entry_meta_bar();
			} else {
				echo '<div class="entry-meta">';
				echo '<span class="posted-on">' . esc_html( get_the_date() ) . '</span> ';
				echo '<span class="byline">' . esc_html( get_the_author() ) . '</span>';
				echo '</div>';
			}
		}
		?>
	</header><!-- .entry-header -->

	<?php if ( is_singular() ) : ?>

		<div class="entry-content">
			<?php
			the_content();

			// Navegación para posts paginados con <!--nextpage-->
			wp_link_pages( [
				'before'      => '<div class="page-links">' . esc_html__( 'Pages:', 'elyxir' ),
				'after'       => '</div>',
				'link_before' => '<span class="page-number">',
				'link_after'  => '</span>',
			] );
			?>
		</div><!-- .entry-content -->

	<?php else : ?>

		<div class="entry-summary">
			<?php
			// Resumen (helper -> fallback).
			if ( function_exists( 'elyxir_the_excerpt' ) ) {
				elyxir_the_excerpt( 28 ); // cambia 28 si quieres más/menos palabras
			} else {
				the_excerpt();
			}
			?>
		</div><!-- .entry-summary -->

		<div class="entry-actions">
			<?php
			// Leer más (helper -> fallback).
			if ( function_exists( 'elyxir_read_more_link' ) ) {
				elyxir_read_more_link();
			} else {
				echo '<a class="read-more" href="' . esc_url( get_permalink() ) . '">'
					. esc_html__( 'Continue reading', 'elyxir' )
					. ' <span class="screen-reader-text">' . esc_html( get_the_title() ) . '</span></a>';
			}
			?>
		</div>

	<?php endif; ?>

	<footer class="entry-footer">
		<?php
		// Footer (cats/tags/comments/edit) (helper -> fallback simple).
		if ( function_exists( 'elyxir_entry_footer' ) ) {
			elyxir_entry_footer();
		} else {
			// Categorías / Tags mínimos como fallback.
			$categories_list = get_the_category_list( esc_html__( ', ', 'elyxir' ) );
			if ( $categories_list ) {
				printf(
					'<span class="cat-links">' . esc_html__( 'Posted in %s', 'elyxir' ) . '</span>',
					wp_kses_post( $categories_list )
				);
			}
			$tags_list = get_the_tag_list( '', esc_html__( ', ', 'elyxir' ) );
			if ( $tags_list ) {
				printf(
					' <span class="tags-links">' . esc_html__( 'Tagged %s', 'elyxir' ) . '</span>',
					wp_kses_post( $tags_list )
				);
			}
			edit_post_link(
				sprintf(
					esc_html__( 'Edit %s', 'elyxir' ),
					'<span class="screen-reader-text">' . esc_html( get_the_title() ) . '</span>'
				),
				' <span class="edit-link">',
				'</span>'
			);
		}
		?>
	</footer><!-- .entry-footer -->

</article><!-- #post-<?php the_ID(); ?> -->
