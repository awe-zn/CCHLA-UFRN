<?php

/**
 * Template part for displaying posts in archive
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-lg transition-shadow'); ?>>

    <?php if (has_post_thumbnail()) : ?>
        <a href="<?php the_permalink(); ?>" class="block aspect-video overflow-hidden bg-gray-100">
            <?php the_post_thumbnail('medium_large', array('class' => 'w-full h-full object-cover hover:scale-105 transition-transform duration-300')); ?>
        </a>
    <?php endif; ?>

    <div class="p-6">
        <!-- Meta -->
        <div class="flex items-center gap-3 text-sm text-gray-500 mb-3">
            <time datetime="<?php echo get_the_date('c'); ?>">
                <i class="fa-solid fa-calendar mr-1"></i>
                <?php echo get_the_date(); ?>
            </time>

            <?php if (get_post_type() === 'post') : ?>
                <?php $categories = get_the_category(); ?>
                <?php if ($categories) : ?>
                    <span>
                        <i class="fa-solid fa-folder mr-1"></i>
                        <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>" class="hover:text-blue-600">
                            <?php echo esc_html($categories[0]->name); ?>
                        </a>
                    </span>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Título -->
        <h2 class="text-xl font-bold text-gray-900 mb-3 hover:text-blue-600 transition-colors">
            <a href="<?php the_permalink(); ?>">
                <?php the_title(); ?>
            </a>
        </h2>

        <!-- Excerpt -->
        <p class="text-gray-600 mb-4 line-clamp-3">
            <?php echo get_the_excerpt(); ?>
        </p>

        <!-- Link -->
        <a href="<?php the_permalink(); ?>" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium">
            <?php _e('Ler mais', 'cchla-ufrn'); ?>
            <i class="fa-solid fa-arrow-right text-sm"></i>
        </a>
    </div>

</article>