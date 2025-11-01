<?php

/**
 * Template part - Latest News Section (Demais notícias por ordem cronológica)
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

// Busca posts excluindo as categorias Destaque e Outros Destaques
$latest_news_args = array(
    'post_type' => 'post',
    'posts_per_page' => 6,
    'post_status' => 'publish',
    'category__not_in' => array(
        get_cat_ID('Destaque'),
        get_cat_ID('Outros Destaques')
    ),
    'orderby' => 'date',
    'order' => 'DESC',
);

$latest_news_query = new WP_Query($latest_news_args);
?>

<?php if ($latest_news_query->have_posts()) : ?>
    <section class="bg-white py-16 px-4 md:px-10 xl:px-24">
        <div class="max-w-screen-xl mx-auto space-y-10">

            <!-- Cabeçalho -->
            <header>
                <h2 class="text-blue-700 uppercase text-sm font-semibold tracking-wide border-b-2 border-blue-500 inline-block pb-1">
                    <?php esc_html_e('Mais notícias', 'cchla-ufrn'); ?>
                </h2>
            </header>

            <!-- Lista de notícias -->
            <div class="grid gap-10 sm:grid-cols-2 xl:grid-cols-3">
                <?php while ($latest_news_query->have_posts()) : $latest_news_query->the_post(); ?>

                    <!-- Card de notícia -->
                    <article class="space-y-2">
                        <?php
                        // Pega a primeira categoria
                        $categories = get_the_category();
                        $category_name = '';
                        if (!empty($categories)) {
                            $category_name = $categories[0]->name;
                        }

                        // Se não tiver categoria, tenta pegar o autor
                        if (empty($category_name)) {
                            $category_name = get_the_author();
                        }
                        ?>

                        <p class="text-sm text-zinc-500 uppercase">
                            <?php echo esc_html($category_name); ?>
                        </p>

                        <a href="<?php the_permalink(); ?>"
                            class="block font-semibold text-lg text-zinc-900 hover:text-blue-600 transition-colors duration-150">
                            <?php the_title(); ?>
                        </a>

                        <time class="block text-sm text-zinc-500" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                            <?php
                            printf(
                                esc_html__('Publicada em %s', 'cchla-ufrn'),
                                esc_html(get_the_date('d/m/Y'))
                            );
                            ?>
                        </time>
                    </article>

                <?php endwhile; ?>
            </div>

            <!-- Botão -->
            <div class="flex justify-start mt-4">
                <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <?php esc_html_e('Ver todas as notícias', 'cchla-ufrn'); ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>

        </div>
    </section>
<?php
    wp_reset_postdata();
endif;
?>