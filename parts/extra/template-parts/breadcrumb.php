<?php

/**
 * Template part - Breadcrumb
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

if (is_front_page()) {
    return; // Não exibe breadcrumb na home
}
?>

<nav class="bg-gray-100 border-b border-gray-300" aria-label="breadcrumb">
    <div class="max-w-screen-xl mx-auto px-4 py-2 text-sm text-gray-500">
        <ol class="flex flex-wrap items-center space-x-1 sm:space-x-2">
            <li>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="hover:text-blue-600 transition-colors">
                    <?php esc_html_e('Início', 'cchla-ufrn'); ?>
                </a>
            </li>

            <?php
            if (is_page()) {
                // Breadcrumb para páginas
                if (wp_get_post_parent_id(get_the_ID())) {
                    $parent_id = wp_get_post_parent_id(get_the_ID());
                    $breadcrumbs = array();

                    while ($parent_id) {
                        $page = get_post($parent_id);
                        $breadcrumbs[] = array(
                            'title' => get_the_title($page->ID),
                            'url' => get_permalink($page->ID)
                        );
                        $parent_id = $page->post_parent;
                    }

                    $breadcrumbs = array_reverse($breadcrumbs);

                    foreach ($breadcrumbs as $crumb) {
                        echo '<li><span class="text-gray-400">›</span></li>';
                        echo '<li><a href="' . esc_url($crumb['url']) . '" class="hover:text-blue-600 transition-colors">' . esc_html($crumb['title']) . '</a></li>';
                    }
                }
            } elseif (is_single()) {
                // Breadcrumb para posts
                $categories = get_the_category();
                if ($categories) {
                    $category = $categories[0];
                    echo '<li><span class="text-gray-400">›</span></li>';
                    echo '<li><a href="' . esc_url(get_category_link($category->term_id)) . '" class="hover:text-blue-600 transition-colors">' . esc_html($category->name) . '</a></li>';
                }
            } elseif (is_category()) {
                // Já está na categoria
            }
            ?>

            <li>
                <span class="text-gray-400">›</span>
            </li>
            <li aria-current="page" class="text-gray-700 font-medium">
                <?php
                if (is_category()) {
                    single_cat_title();
                } else {
                    echo wp_trim_words(get_the_title(), 5, '...');
                }
                ?>
            </li>
        </ol>
    </div>
</nav>