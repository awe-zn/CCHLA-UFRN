<?php

/**
 * Card de Resultado - Páginas
 */

$search_term = get_search_query();
?>

<article class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
            <i class="fa-solid fa-file text-xl text-gray-600"></i>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 text-xs text-gray-500 mb-2">
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-50 text-gray-700 rounded font-medium">
                    <i class="fa-solid fa-file"></i>
                    Página
                </span>
            </div>

            <h3 class="text-xl font-bold text-gray-900 mb-2">
                <a href="<?php the_permalink(); ?>" class="hover:text-blue-600 transition-colors">
                    <?php echo cchla_highlight_search_term(get_the_title(), $search_term); ?>
                </a>
            </h3>

            <p class="text-gray-600 mb-3 line-clamp-2">
                <?php echo cchla_highlight_search_term(wp_trim_words(get_the_content(), 30), $search_term); ?>
            </p>

            <a href="<?php the_permalink(); ?>"
                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium text-sm">
                <?php esc_html_e('Ver página', 'cchla-ufrn'); ?>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</article>