<?php

/**
 * Template part - Other Highlights Section (Outros Destaques com imagens)
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

// Busca 6 posts da categoria "Outros Destaques" (3 principais + 3 secundários)
$other_highlights_args = array(
    'post_type' => 'post',
    'posts_per_page' => 6,
    'post_status' => 'publish',
    'category_name' => 'outros-destaques',
    'orderby' => 'date',
    'order' => 'DESC',
);

$other_highlights_query = new WP_Query($other_highlights_args);
?>

<?php if ($other_highlights_query->have_posts()) : ?>
    <section class="bg-zinc-100 py-16 px-4 md:px-10 xl:px-24">
        <div class="max-w-screen-xl mx-auto space-y-10">

            <!-- Cabeçalho -->
            <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <p class="text-blue-700 text-sm font-medium border-b-2 border-blue-500 inline-block pb-1">
                        <?php esc_html_e('Fique por dentro', 'cchla-ufrn'); ?>
                    </p>
                    <h2 class="text-3xl md:text-4xl font-light text-zinc-800 mt-3">
                        <?php esc_html_e('Notícias no CCHLA', 'cchla-ufrn'); ?>
                    </h2>
                </div>
                <a href="<?php echo esc_url(get_category_link(get_cat_ID('Outros Destaques'))); ?>"
                    class="flex items-center gap-2 text-blue-700 font-semibold hover:underline focus:outline-none focus:ring-2 focus:ring-blue-400 rounded">
                    <span><?php esc_html_e('Acesse mais notícias', 'cchla-ufrn'); ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </header>

            <?php
            $post_count = 0;
            $main_posts = array();
            $secondary_posts = array();

            // Separa os 3 primeiros posts (bloco 1) dos 3 últimos (bloco 2)
            while ($other_highlights_query->have_posts()) : $other_highlights_query->the_post();
                $post_count++;
                if ($post_count <= 3) {
                    $main_posts[] = get_post();
                } else {
                    $secondary_posts[] = get_post();
                }
            endwhile;
            wp_reset_postdata();
            ?>

            <!-- Bloco 1: notícias em destaque (3 cards grandes) -->
            <div class="grid gap-6 md:grid-cols-3">
                <?php
                foreach ($main_posts as $post) :
                    setup_postdata($post);
                ?>
                    <article class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition">
                        <a href="<?php echo esc_url(get_permalink($post->ID)); ?>"
                            class="block focus:outline-none focus:ring-2 focus:ring-blue-400 rounded group">
                            <figure class="space-y-3">
                                <?php if (has_post_thumbnail($post->ID)) : ?>
                                    <?php
                                    echo cchla_get_custom_srcset_image(
                                        get_post_thumbnail_id($post->ID),
                                        array('cchla-thumbnail', 'cchla-post-card', 'cchla-highlight-large'),
                                        'cchla-highlight-large',
                                        array(
                                            'class' => 'rounded-lg w-full object-cover transition-colors duration-200 group-hover:ring-4 group-hover:ring-blue-500',
                                            'alt' => get_the_title($post->ID),
                                            'sizes_attr' => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw'
                                        )
                                    );
                                    ?>
                                <?php endif; ?>

                                <figcaption class="font-semibold text-zinc-800 text-lg leading-snug">
                                    <?php echo get_the_title($post->ID); ?>
                                </figcaption>

                                <time class="block text-sm text-zinc-500" datetime="<?php echo esc_attr(get_the_date('c', $post->ID)); ?>">
                                    <?php
                                    // Verifica se é hoje
                                    $post_date = get_the_date('Y-m-d', $post->ID);
                                    $today = date('Y-m-d');

                                    if ($post_date === $today) {
                                        echo esc_html('Hoje, ' . get_the_time('H:i', $post->ID));
                                    } else {
                                        echo esc_html(get_the_date('d \d\e M. Y', $post->ID));
                                    }
                                    ?>
                                </time>
                            </figure>
                        </a>
                    </article>
                <?php
                endforeach;
                wp_reset_postdata();
                ?>
            </div>

            <!-- Bloco 2: notícias secundárias (3 cards menores) -->
            <?php if (!empty($secondary_posts)) : ?>
                <div class="grid gap-6 sm:grid-cols-1 xl:grid-cols-3">
                    <?php
                    foreach ($secondary_posts as $post) :
                        setup_postdata($post);

                        // Pega a primeira categoria que não seja "Outros Destaques"
                        $categories = get_the_category($post->ID);
                        $category_name = '';
                        if (!empty($categories)) {
                            foreach ($categories as $cat) {
                                if ($cat->slug !== 'outros-destaques') {
                                    $category_name = $cat->name;
                                    break;
                                }
                            }
                        }
                    ?>
                        <article class="flex items-start gap-3 bg-white p-3 rounded-xl shadow-sm hover:shadow-md transition">
                            <?php if (has_post_thumbnail($post->ID)) : ?>
                                <?php
                                echo cchla_get_custom_srcset_image(
                                    get_post_thumbnail_id($post->ID),
                                    array('cchla-highlight-small', 'cchla-thumbnail'),
                                    'cchla-highlight-small',
                                    array(
                                        'class' => 'w-24 h-24 object-cover rounded transition-all duration-200 hover:ring-4 hover:ring-blue-500',
                                        'alt' => get_the_title($post->ID),
                                        'sizes_attr' => '150px'
                                    )
                                );
                                ?>
                            <?php endif; ?>

                            <div class="text-sm">
                                <?php if ($category_name) : ?>
                                    <p class="uppercase text-zinc-500 font-medium">
                                        <?php echo esc_html($category_name); ?>
                                    </p>
                                <?php endif; ?>

                                <a href="<?php echo esc_url(get_permalink($post->ID)); ?>"
                                    class="text-zinc-800 font-semibold hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 rounded">
                                    <?php echo get_the_title($post->ID); ?>
                                </a>
                            </div>
                        </article>
                    <?php
                    endforeach;
                    wp_reset_postdata();
                    ?>
                </div>
            <?php endif; ?>

        </div>
    </section>
<?php endif; ?>