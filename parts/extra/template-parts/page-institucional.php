<?php

/**
 * Template Name: Institucional (com Sidebar)
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

get_header();
?>

<?php while (have_posts()) : the_post(); ?>

    <!-- Breadcrumb -->
    <?php get_template_part('template-parts/breadcrumb'); ?>

    <main class="bg-white text-gray-800">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

                <!-- Sidebar -->
                <aside class="lg:col-span-1">
                    <nav class="bg-gray-50 rounded-lg p-4 sticky top-4">
                        <h2 class="font-bold text-lg mb-4 text-blue-700">
                            <?php esc_html_e('Navegação', 'cchla-ufrn'); ?>
                        </h2>
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'sidebar-menu',
                            'container' => false,
                            'menu_class' => 'space-y-2',
                            'fallback_cb' => false,
                        ));
                        ?>
                    </nav>
                </aside>

                <!-- Conteúdo -->
                <div class="lg:col-span-3">
                    <?php if (cchla_should_show_title()) : ?>
                        <header class="mb-8">
                            <h1 class="text-4xl font-bold text-blue-700">
                                <?php the_title(); ?>
                            </h1>
                        </header>
                    <?php endif; ?>

                    <article class="prose prose-blue max-w-none">
                        <?php the_content(); ?>
                    </article>
                </div>

            </div>
        </div>
    </main>

<?php endwhile; ?>

<?php get_footer(); ?>