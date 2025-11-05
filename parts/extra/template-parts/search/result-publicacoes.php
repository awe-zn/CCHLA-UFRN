<?php

/**
 * Card de Resultado - Publicações
 */

$search_term = get_search_query();
$tipos = get_the_terms(get_the_ID(), 'tipo_publicacao');
$autores = get_post_meta(get_the_ID(), '_publicacao_autores', true);
?>

<article class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <i class="fa-solid fa-book text-xl text-green-600"></i>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 text-xs text-gray-500 mb-2">
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-50 text-green-700 rounded font-medium">
                    <i class="fa-solid fa-book"></i>
                    Publicação
                </span>
                <?php if ($tipos && !is_wp_error($tipos)) : ?>
                    <span>•</span>
                    <span><?php echo esc_html($tipos[0]->name); ?></span>
                <?php endif; ?>
            </div>

            <h3 class="text-xl font-bold text-gray-900 mb-2">
                <a href="<?php the_permalink(); ?>" class="hover:text-blue-600 transition-colors">
                    <?php echo cchla_highlight_search_term(get_the_title(), $search_term); ?>
                </a>
            </h3>

            <?php if ($autores) : ?>
                <p class="text-sm text-gray-600 mb-2">
                    <i class="fa-solid fa-user text-xs mr-1"></i>
                    <?php echo esc_html($autores); ?>
                </p>
            <?php endif; ?>

            <p class="text-gray-600 mb-3 line-clamp-2">
                <?php echo cchla_highlight_search_term(get_the_excerpt(), $search_term); ?>
            </p>

            <a href="<?php the_permalink(); ?>"
                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium text-sm">
                <?php esc_html_e('Ver publicação', 'cchla-ufrn'); ?>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>

        <?php if (has_post_thumbnail()) : ?>
            <div class="flex-shrink-0 hidden sm:block">
                <a href="<?php the_permalink(); ?>">
                    <?php the_post_thumbnail('thumbnail', array('class' => 'w-20 h-28 object-cover rounded shadow-sm')); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</article>