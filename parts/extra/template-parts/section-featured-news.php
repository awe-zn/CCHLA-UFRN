<?php

/**
 * Template part - Featured News Section (5 notícias em destaque)
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

// Busca 5 posts da categoria "Destaque"
$featured_args = array(
    'post_type' => 'post',
    'posts_per_page' => 5,
    'post_status' => 'publish',
    'category_name' => 'destaque',
    'orderby' => 'date',
    'order' => 'DESC',
);

$featured_query = new WP_Query($featured_args);
?>

<?php if ($featured_query->have_posts()) : ?>
    <section class="max-w-screen-xl mx-auto px-4 grid gap-10 lg:grid-cols-2 items-start my-16">

        <?php
        $post_count = 0;
        $main_post = null;
        $other_posts = array();

        // Separa o post principal dos demais
        while ($featured_query->have_posts()) : $featured_query->the_post();
            $post_count++;
            if ($post_count === 1) {
                $main_post = get_post();
            } else {
                $other_posts[] = get_post();
            }
        endwhile;
        wp_reset_postdata();
        ?>

        <?php if ($main_post) :
            $post = $main_post;
            setup_postdata($post);
        ?>
            <!-- Notícia principal -->
            <article class="space-y-4">
                <?php if (has_post_thumbnail($post->ID)) : ?>
                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" class="block" style="height: 360px; overflow-y: hidden;">
                        <?php
                        echo cchla_get_custom_srcset_image(
                            get_post_thumbnail_id($post->ID),
                            array('cchla-thumbnail', 'cchla-featured-side', 'cchla-featured-main', 'cchla-featured-main-2x'),
                            'cchla-featured-main',
                            array(
                                'class' => 'w-full rounded-lg',
                                'alt' => get_the_title($post->ID),
                                'loading' => 'eager', // Primeira imagem não usa lazy
                                'sizes_attr' => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 800px'
                            )
                        );
                        ?>
                    </a>
                <?php endif; ?>

                <div>
                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" class="block">
                        <h2 class="text-blue-700 font-bold text-xl">
                            <?php echo get_the_title($post->ID); ?>
                        </h2>
                    </a>
                    <p class="text-blue-950">
                        <?php echo wp_trim_words(get_the_excerpt($post->ID), 25, '...'); ?>
                    </p>
                </div>
            </article>
        <?php
            wp_reset_postdata();
        endif;
        ?>

        <!-- Demais notícias -->
        <div class="grid gap-4">
            <?php
            foreach ($other_posts as $post) :
                setup_postdata($post);

                // Pega a primeira categoria que não seja "Destaque"
                $categories = get_the_category($post->ID);
                $category_name = '';
                if (!empty($categories)) {
                    foreach ($categories as $cat) {
                        if ($cat->slug !== 'destaque') {
                            $category_name = strtoupper($cat->name);
                            break;
                        }
                    }
                }
            ?>

                <!-- Card -->
                <article class="grid grid-cols-12 rounded gap-3 items-start" style="height: 110px; overflow-y: hidden;">
                    <?php if (has_post_thumbnail($post->ID)) : ?>
                        <img src="<?php echo esc_url(get_the_post_thumbnail_url($post->ID, 'cchla-featured-side')); ?>"
                            srcset="<?php echo esc_attr(wp_get_attachment_image_srcset(get_post_thumbnail_id($post->ID), 'cchla-featured-side')); ?>"
                            sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 400px"
                            alt="<?php echo esc_attr(get_the_title($post->ID)); ?>"
                            class="col-span-4 object-cover w-full h-full"
                            loading="lazy">
                    <?php endif; ?>

                    <div class="col-span-8 text-sm flex flex-col justify-center text-[12px]">
                        <p class="text-gray-500 uppercase">
                            <?php echo esc_html($category_name); ?> –
                            <span class="italic"><?php echo esc_html(get_the_date('d \d\e M. Y', $post->ID)); ?></span>
                        </p>
                        <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" class="text-blue-700 font-semibold hover:underline text-[16px]">
                            <?php echo get_the_title($post->ID); ?>
                        </a>
                    </div>
                </article>

            <?php
            endforeach;
            wp_reset_postdata();

            ?>
        </div>

    </section>
<?php endif; ?>