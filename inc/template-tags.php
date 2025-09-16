<?php
/**
 * Template Tags (2025) para el tema Elyxir.
 *
 * Todas las funciones están en global namespace y prefijadas con `elyxir_`
 * para evitar colisiones y facilitar su uso desde plantillas sin namespaces.
 *
 * @package Elyxir
 */

defined('ABSPATH') || exit;

/**
 * =============================
 * Helpers genéricos / utilidades
 * =============================
 */

/**
 * Determina si un attachment tiene caption.
 */
if (!function_exists('elyxir_has_caption')) {
    function elyxir_has_caption($attachment_id) {
        $post = get_post($attachment_id);
        return $post && !empty($post->post_excerpt);
    }
}

/**
 * Devuelve un SVG inline desde /assets/icons/{$name}.svg
 * (Opcional: útil si manejas un set de íconos inline)
 * Asegúrate de tener los SVGs sanitizados previamente.
 */
if (!function_exists('elyxir_get_svg_icon')) {
    function elyxir_get_svg_icon($name, $attrs = []) {
        $name = sanitize_file_name($name);
        if (!$name) return '';

        $path = get_template_directory() . '/assets/icons/' . $name . '.svg';
        if (!file_exists($path)) {
            return '';
        }

        $svg = file_get_contents($path); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        if (!$svg) return '';

        // Atributos inline adicionales (clase, aria-hidden, etc.)
        $attr_str = '';
        foreach ((array)$attrs as $k => $v) {
            $attr_str .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"';
        }

        // Inserta atributos en la primera etiqueta <svg ...>
        $svg = preg_replace('/<svg\b(.*?)>/', '<svg$1' . $attr_str . '>', $svg, 1);

        // Sanitiza: permite solo tags/attrs seguros para SVG (lista básica)
        $allowed = [
            'svg'   => ['class' => true, 'aria-hidden' => true, 'role' => true, 'width' => true, 'height' => true, 'viewBox' => true, 'xmlns' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true],
            'path'  => ['d' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'fill-rule' => true, 'clip-rule' => true],
            'g'     => ['fill' => true, 'stroke' => true, 'stroke-width' => true],
            'title' => [],
        ];
        return wp_kses($svg, $allowed);
    }
}

/**
 * =============================
 * Salida de contenidos del post
 * =============================
 */

/**
 * Thumbnail accesible:
 * - En single: <figure> sin enlace
 * - En listados: <a> envolviendo la imagen
 * - Usa 'post-thumbnail' por defecto (filtrable)
 */
if (!function_exists('elyxir_post_thumbnail')) {
    function elyxir_post_thumbnail() {
        if (post_password_required() || is_attachment() || !has_post_thumbnail()) {
            return;
        }

        $img_id = get_post_thumbnail_id();
        $alt    = get_post_meta($img_id, '_wp_attachment_image_alt', true);
        $alt    = $alt ? $alt : get_the_title();
        $size   = apply_filters('elyxir/thumbnail_size', 'post-thumbnail');

        if (is_singular()) {
            echo '<figure class="post-thumbnail">';
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo get_the_post_thumbnail(
                null,
                $size,
                [
                    'alt'      => esc_attr($alt),
                    'loading'  => 'eager',
                    'decoding' => 'async',
                ]
            );
            if (elyxir_has_caption($img_id)) {
                $caption = wp_kses_post(get_post($img_id)->post_excerpt);
                if ($caption) {
                    echo '<figcaption class="wp-caption-text">' . $caption . '</figcaption>';
                }
            }
            echo '</figure>';
        } else {
            echo '<a class="post-thumbnail" href="' . esc_url(get_permalink()) . '" aria-label="' . esc_attr(get_the_title()) . '">';
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo get_the_post_thumbnail(
                null,
                $size,
                [
                    'alt'      => esc_attr($alt),
                    'loading'  => 'lazy',
                    'decoding' => 'async',
                ]
            );
            echo '</a>';
        }
    }
}

/**
 * Encabezado de entrada:
 * - En single: <h1>
 * - En listados: <h2><a>
 */
if (!function_exists('elyxir_entry_header')) {
    function elyxir_entry_header() {
        if (is_singular()) {
            echo '<h1 class="entry-title">' . esc_html(get_the_title()) . '</h1>';
        } else {
            echo '<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '">';
            echo esc_html(get_the_title());
            echo '</a></h2>';
        }
    }
}

/**
 * Meta "Publicado el" con fecha de publicación y actualización si aplica.
 */
if (!function_exists('elyxir_posted_on')) {
    function elyxir_posted_on() {
        $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

        $published = get_the_date(DATE_W3C);
        $display_published = get_the_date();

        $modified = get_the_modified_date(DATE_W3C);
        $display_modified = get_the_modified_date();

        if ($published !== $modified) {
            $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>' .
                           ' <time class="updated" datetime="%3$s">%4$s</time>';
        }

        $time_string = sprintf(
            $time_string,
            esc_attr($published),
            esc_html($display_published),
            esc_attr($modified),
            esc_html($display_modified)
        );

        $posted_on = sprintf(
            /* translators: %s: post date */
            esc_html_x('Posted on %s', 'post date', 'elyxir'),
            $time_string
        );

        echo '<span class="posted-on">' . $posted_on . '</span>';
    }
}

/**
 * Meta "por Autor".
 */
if (!function_exists('elyxir_posted_by')) {
    function elyxir_posted_by() {
        $byline = sprintf(
            /* translators: %s: author link */
            esc_html_x('by %s', 'post author', 'elyxir'),
            '<span class="author vcard"><a class="url fn n" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html(get_the_author()) . '</a></span>'
        );
        echo '<span class="byline"> ' . $byline . '</span>';
    }
}

/**
 * Meta agrupada (fecha + autor).
 */
if (!function_exists('elyxir_entry_meta_bar')) {
    function elyxir_entry_meta_bar() {
        echo '<div class="entry-meta">';
        elyxir_posted_on();
        echo ' ';
        elyxir_posted_by();
        echo '</div>';
    }
}

/**
 * Footer de entrada (categorías, tags, comments link, edit link).
 */
if (!function_exists('elyxir_entry_footer')) {
    function elyxir_entry_footer() {
        echo '<footer class="entry-footer">';

        // Categorías
        $categories_list = get_the_category_list(esc_html__(', ', 'elyxir'));
        if ($categories_list) {
            printf(
                '<span class="cat-links">' . esc_html__('Posted in %s', 'elyxir') . '</span>',
                wp_kses_post($categories_list)
            );
        }

        // Tags
        $tags_list = get_the_tag_list('', esc_html__(', ', 'elyxir'));
        if ($tags_list) {
            printf(
                ' <span class="tags-links">' . esc_html__('Tagged %s', 'elyxir') . '</span>',
                wp_kses_post($tags_list)
            );
        }

        // Enlace a comentarios (si aplica)
        if (!is_single() && !post_password_required() && (comments_open() || get_comments_number())) {
            echo ' <span class="comments-link">';
            comments_popup_link(
                esc_html__('Leave a comment', 'elyxir'),
                esc_html__('1 Comment', 'elyxir'),
                esc_html__('% Comments', 'elyxir')
            );
            echo '</span>';
        }

        // Enlace de edición
        edit_post_link(
            sprintf(
                /* translators: %s: post title */
                esc_html__('Edit %s', 'elyxir'),
                '<span class="screen-reader-text">' . esc_html(get_the_title()) . '</span>'
            ),
            ' <span class="edit-link">',
            '</span>'
        );

        echo '</footer>';
    }
}

/**
 * Excerpt seguro (con longitud controlable).
 * $length es número de palabras (default 24).
 */
if (!function_exists('elyxir_the_excerpt')) {
    function elyxir_the_excerpt($length = 24) {
        $length = max(1, (int)$length);
        $text = has_excerpt() ? get_the_excerpt() : wp_strip_all_tags(get_the_content(''));
        $text = wp_trim_words($text, $length, '&hellip;');
        echo '<p class="entry-excerpt">' . esc_html($text) . '</p>';
    }
}

/**
 * "Leer más" accesible.
 */
if (!function_exists('elyxir_read_more_link')) {
    function elyxir_read_more_link() {
        $label = esc_html__('Continue reading', 'elyxir');
        $title = get_the_title();
        $screen_reader = '<span class="screen-reader-text"> ' . esc_html($title) . '</span>';
        echo '<a class="read-more" href="' . esc_url(get_permalink()) . '">' . $label . $screen_reader . '</a>';
    }
}

/**
 * =============================
 * Navegación / Paginación
 * =============================
 */

/**
 * Paginación para listados (archives, blog).
 * Usa the_posts_pagination con clases personalizadas.
 */
if (!function_exists('elyxir_posts_pagination')) {
    function elyxir_posts_pagination() {
        $args = [
            'mid_size'  => 2,
            'prev_text' => esc_html__('Previous', 'elyxir'),
            'next_text' => esc_html__('Next', 'elyxir'),
            'screen_reader_text' => esc_html__('Posts navigation', 'elyxir'),
        ];
        echo '<nav class="navigation posts-navigation" role="navigation" aria-label="' . esc_attr__('Posts', 'elyxir') . '">';
        the_posts_pagination($args);
        echo '</nav>';
    }
}

/**
 * Navegación entre posts en single.
 */
if (!function_exists('elyxir_post_nav')) {
    function elyxir_post_nav() {
        $prev = get_previous_post_link('%link', esc_html__('Previous post', 'elyxir'));
        $next = get_next_post_link('%link', esc_html__('Next post', 'elyxir'));

        if (!$prev && !$next) return;

        echo '<nav class="navigation post-navigation" role="navigation" aria-label="' . esc_attr__('Post', 'elyxir') . '">';
        echo '<h2 class="screen-reader-text">' . esc_html__('Post navigation', 'elyxir') . '</h2>';
        echo '<div class="nav-links">';
        if ($prev) echo '<div class="nav-previous">' . $prev . '</div>';
        if ($next) echo '<div class="nav-next">' . $next . '</div>';
        echo '</div></nav>';
    }
}

/**
 * Breadcrumbs simples (Home > Parent > Actual).
 * Nota: Para breadcrumbs SEO-rich considera un plugin especializado.
 */
if (!function_exists('elyxir_breadcrumbs')) {
    function elyxir_breadcrumbs() {
        if (is_front_page()) return;

        echo '<nav class="breadcrumbs" aria-label="' . esc_attr__('Breadcrumbs', 'elyxir') . '">';
        echo '<ol>';
        // Home
        echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'elyxir') . '</a></li>';

        if (is_home()) {
            echo '<li>' . esc_html(get_the_title(get_option('page_for_posts', true))) . '</li>';
        } elseif (is_category()) {
            $cat = get_queried_object();
            if ($cat && $cat->parent) {
                $parents = array_reverse(get_ancestors($cat->term_id, 'category'));
                foreach ($parents as $parent_id) {
                    $p = get_category($parent_id);
                    echo '<li><a href="' . esc_url(get_category_link($p)) . '">' . esc_html($p->name) . '</a></li>';
                }
            }
            echo '<li>' . esc_html(single_cat_title('', false)) . '</li>';
        } elseif (is_singular()) {
            $post = get_post();
            if ($post && $post->post_type === 'page') {
                $parents = array_reverse(get_post_ancestors($post->ID));
                foreach ($parents as $pid) {
                    echo '<li><a href="' . esc_url(get_permalink($pid)) . '">' . esc_html(get_the_title($pid)) . '</a></li>';
                }
                echo '<li aria-current="page">' . esc_html(get_the_title()) . '</li>';
            } elseif ($post && $post->post_type === 'post') {
                $cats = get_the_category();
                if (!empty($cats)) {
                    $primary = $cats[0];
                    echo '<li><a href="' . esc_url(get_category_link($primary)) . '">' . esc_html($primary->name) . '</a></li>';
                }
                echo '<li aria-current="page">' . esc_html(get_the_title()) . '</li>';
            } else {
                $pt = get_post_type_object(get_post_type());
                if ($pt && !is_post_type_hierarchical($pt->name)) {
                    echo '<li><a href="' . esc_url(get_post_type_archive_link($pt->name)) . '">' . esc_html($pt->labels->name) . '</a></li>';
                }
                echo '<li aria-current="page">' . esc_html(get_the_title()) . '</li>';
            }
        } elseif (is_search()) {
            printf('<li>' . esc_html__('Search results for "%s"', 'elyxir') . '</li>', esc_html(get_search_query()));
        } elseif (is_tag()) {
            echo '<li>' . esc_html(single_tag_title('', false)) . '</li>';
        } elseif (is_author()) {
            echo '<li>' . esc_html(get_the_author()) . '</li>';
        } elseif (is_404()) {
            echo '<li>' . esc_html__('Not Found', 'elyxir') . '</li>';
        }

        echo '</ol></nav>';
    }
}

/**
 * =============================
 * Comentarios
 * =============================
 */

/**
 * Navegación de comentarios (prev/next páginas de comments).
 */
if (!function_exists('elyxir_comment_nav')) {
    function elyxir_comment_nav() {
        if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
            <nav class="comment-navigation" role="navigation" aria-label="<?php echo esc_attr__('Comments', 'elyxir'); ?>">
                <h2 class="screen-reader-text"><?php esc_html_e('Comment navigation', 'elyxir'); ?></h2>
                <div class="nav-links">
                    <div class="nav-previous"><?php previous_comments_link(esc_html__('Older Comments', 'elyxir')); ?></div>
                    <div class="nav-next"><?php next_comments_link(esc_html__('Newer Comments', 'elyxir')); ?></div>
                </div>
            </nav>
        <?php
        endif;
    }
}

/**
 * Callback opcional para wp_list_comments (markup básico y accesible).
 */
if (!function_exists('elyxir_comment_callback')) {
    function elyxir_comment_callback($comment, $args, $depth) {
        $tag = ($args['style'] === 'div') ? 'div' : 'li';
        ?>
        <<?php echo $tag; ?> <?php comment_class(empty($args['has_children']) ? '' : 'parent'); ?> id="comment-<?php comment_ID(); ?>">
            <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
                <footer class="comment-meta">
                    <div class="comment-author vcard">
                        <?php echo get_avatar($comment, 48); ?>
                        <b class="fn"><?php comment_author_link(); ?></b>
                        <span class="says"><?php esc_html_e('says:', 'elyxir'); ?></span>
                    </div>
                    <div class="comment-metadata">
                        <a href="<?php echo esc_url(get_comment_link($comment->comment_ID)); ?>">
                            <time datetime="<?php comment_time('c'); ?>">
                                <?php printf(esc_html__('%1$s at %2$s', 'elyxir'), get_comment_date(), get_comment_time()); ?>
                            </time>
                        </a>
                        <?php edit_comment_link(esc_html__('Edit', 'elyxir'), ' <span class="edit-link">', '</span>'); ?>
                    </div>
                </footer>

                <div class="comment-content">
                    <?php comment_text(); ?>
                </div>

                <div class="reply">
                    <?php
                    comment_reply_link(array_merge($args, [
                        'add_below' => 'div-comment',
                        'depth'     => $depth,
                        'max_depth' => $args['max_depth'],
                    ]));
                    ?>
                </div>
            </article>
        <?php
    }
}
