<?php

/**
 * Formulário de Busca
 *
 * @package CCHLA_UFRN
 */

$search_query = get_search_query();
$placeholder = __('Buscar em Notícias, Publicações, Especiais...', 'cchla-ufrn');
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <div class="flex gap-2">
        <div class="relative flex-1">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                <i class="fa-solid fa-search"></i>
            </span>
            <input
                type="search"
                name="s"
                value="<?php echo esc_attr($search_query); ?>"
                placeholder="<?php echo esc_attr($placeholder); ?>"
                class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all"
                required
                aria-label="<?php esc_attr_e('Campo de busca', 'cchla-ufrn'); ?>">
        </div>
        <button
            type="submit"
            class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition-all flex items-center gap-2">
            <i class="fa-solid fa-search"></i>
            <span class="hidden sm:inline"><?php esc_html_e('Buscar', 'cchla-ufrn'); ?></span>
        </button>
    </div>
</form>