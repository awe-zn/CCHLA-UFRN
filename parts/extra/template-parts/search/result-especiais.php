<?php

/**
 * Card de Resultado - Especiais
 */

$search_term = get_search_query();
$categorias = get_the_terms(get_the_ID(), 'categoria_especial');
?>

<article class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
            <i class="fa-solid fa-video text-xl text-red-600"></i>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 text-xs text-gray-500 mb-2">
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-50 text-red-700 rounded font-medium">
                    <i class="fa-solid fa-video"></i>
                    Especial
                </span>
                <?php if ($categorias && !is_wp_error($categorias)) : ?>
                    <span>•</span>
                    <span><?php echo esc_html($categorias[0]->name); ?></span>
                <?php endif; ?>
            </div>

            <h3 class="text-xl font-bold text-gray-900 mb-2">
                <a href="<?php the_permalink(); ?>" class="hover:text-blue-600 transition-colors">
                    <?php echo cchla_highlight_search_term(get_the_title(), $search_term); ?>
                </a>
            </h3>

            <p class="text-gray-600 mb-3 line-clamp-2">
                <?php echo cchla_highlight_search_term(get_the_excerpt(), $search_term); ?>
            </p>

            <a href="<?php the_permalink(); ?>"
                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium text-sm">
                <i class="fa-solid fa-play mr-1"></i>
                <?php esc_html_e('Assistir', 'cchla-ufrn'); ?>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>

        <?php if (has_post_thumbnail()) : ?>
            <div class="flex-shrink-0 hidden sm:block">
                <a href="<?php the_permalink(); ?>" class="relative group">
                    <?php the_post_thumbnail('thumbnail', array('class' => 'w-32 h-20 object-cover rounded shadow-sm')); ?>
                    <div class="absolute inset-0 bg-black bg-opacity-30 rounded flex items-center justify-center group-hover:bg-opacity-40 transition-all">
                        <i class="fa-solid fa-play text-white text-2xl"></i>
                    </div>
                </a>
            </div>
        <?php endif; ?>
    </div>
</article>