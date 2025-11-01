<?php

/**
 * Page Template
 *
 * Template padrão para páginas estáticas
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

get_header();
?>

<?php while (have_posts()) : the_post(); ?>

    <!-- Breadcrumb -->
    <nav class="bg-gray-100 border-b border-gray-300" aria-label="breadcrumb">
        <div class="max-w-screen-xl mx-auto px-4 py-2 text-sm text-gray-500">
            <ol class="flex flex-wrap items-center space-x-1 sm:space-x-2">
                <li>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="hover:text-blue-600 transition-colors">
                        <?php esc_html_e('Início', 'cchla-ufrn'); ?>
                    </a>
                </li>

                <?php
                // Breadcrumb para páginas filhas
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
                ?>

                <li>
                    <span class="text-gray-400">›</span>
                </li>
                <li aria-current="page" class="text-gray-700 font-medium">
                    <?php the_title(); ?>
                </li>
            </ol>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="bg-white text-gray-800 page-institucional">

        <!-- Conteúdo principal -->
        <section class="py-8 sm:py-8 lg:py-8">
            <div class="container mx-auto px-4 max-w-4xl">

                <!-- Título -->
                <header class="mb-10">
                    <h1 class="text-4xl sm:text-5xl font-bold text-blue-700 mb-3">
                        <?php the_title(); ?>
                    </h1>

                    <?php if (has_excerpt()) : ?>
                        <p class="text-xl text-gray-600 leading-relaxed">
                            <?php the_excerpt(); ?>
                        </p>
                    <?php endif; ?>
                </header>

                <!-- Conteúdo -->
                <article class="prose prose-blue max-w-none">
                    <?php
                    the_content();

                    wp_link_pages(array(
                        'before' => '<div class="page-links mt-8 pt-6 border-t border-gray-200"><span class="font-semibold">' . __('Páginas:', 'cchla-ufrn') . '</span>',
                        'after'  => '</div>',
                        'link_before' => '<span class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition mx-1">',
                        'link_after' => '</span>',
                    ));
                    ?>
                </article>

            </div>
        </section>

    </main>

<?php endwhile; ?>

<?php
get_footer();
