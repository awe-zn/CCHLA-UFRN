<?php

/**
 * Card de Resultado - Posts (Notícias)
 */

$search_term = get_search_query();
$categories = get_the_category();
?>

<article class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-start gap-4">
        <!-- Ícone do Tipo -->
        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <i class="fa-solid fa-newspaper text-xl text-blue-600"></i>
        </div>

        <!-- Conteúdo -->
        <div class="flex-1 min-w-0">
            <!-- Meta -->
            <div class="flex items-center gap-3 text-xs text-gray-500 mb-2">
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded font-medium">
                    <i class="fa-solid fa-newspaper"></i>
                    Notícia
                </span>
                <?php if ($categories) : ?>
                    <span>•</span>
                    <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>"
                        class="hover:text-blue-600">
                        <?php echo esc_html($categories[0]->name); ?>
                    </a>
                <?php endif; ?>
                <span>•</span>
                <time datetime="<?php echo get_the_date('c'); ?>">
                    <?php echo get_the_date(); ?>
                </time>
            </div>

            <!-- Título -->
            <h3 class="text-xl font-bold text-gray-900 mb-2">
                <a href="<?php the_permalink(); ?>" class="hover:text-blue-600 transition-colors">
                    <?php echo cchla_highlight_search_term(get_the_title(), $search_term); ?>
                </a>
            </h3>

            <!-- Excerpt -->
            <p class="text-gray-600 mb-3 line-clamp-2">
                <?php echo cchla_highlight_search_term(get_the_excerpt(), $search_term); ?>
            </p>

            <!-- Link -->
            <a href="<?php the_permalink(); ?>"
                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium text-sm">
                <?php esc_html_e('Ler mais', 'cchla-ufrn'); ?>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</article>