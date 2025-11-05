<?php

/**
 * Card de Resultado - Acesso Rápido (Sistemas)
 */

$search_term = get_search_query();
$categorias = get_the_terms(get_the_ID(), 'categoria_acesso');
$link_externo = get_post_meta(get_the_ID(), '_acesso_link_externo', true);
$descricao = get_post_meta(get_the_ID(), '_acesso_descricao', true);
?>

<article class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
            <i class="fa-solid fa-link text-xl text-purple-600"></i>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 text-xs text-gray-500 mb-2">
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-50 text-purple-700 rounded font-medium">
                    <i class="fa-solid fa-link"></i>
                    Sistema
                </span>
                <?php if ($categorias && !is_wp_error($categorias)) : ?>
                    <span>•</span>
                    <span><?php echo esc_html($categorias[0]->name); ?></span>
                <?php endif; ?>
                <?php if ($link_externo) : ?>
                    <span>•</span>
                    <span class="inline-flex items-center gap-1">
                        <i class="fa-solid fa-external-link-alt text-xs"></i>
                        Externo
                    </span>
                <?php endif; ?>
            </div>

            <h3 class="text-xl font-bold text-gray-900 mb-2">
                <a href="<?php echo $link_externo ? esc_url($link_externo) : get_permalink(); ?>"
                    <?php echo $link_externo ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
                    class="hover:text-blue-600 transition-colors">
                    <?php echo cchla_highlight_search_term(get_the_title(), $search_term); ?>
                </a>
            </h3>

            <?php if ($descricao) : ?>
                <p class="text-gray-600 mb-3 line-clamp-2">
                    <?php echo cchla_highlight_search_term($descricao, $search_term); ?>
                </p>
            <?php endif; ?>

            <a href="<?php echo $link_externo ? esc_url($link_externo) : get_permalink(); ?>"
                <?php echo $link_externo ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium text-sm">
                <?php esc_html_e('Acessar sistema', 'cchla-ufrn'); ?>
                <i class="fa-solid <?php echo $link_externo ? 'fa-external-link-alt' : 'fa-arrow-right'; ?> text-xs"></i>
            </a>
        </div>
    </div>
</article>