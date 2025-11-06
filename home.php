<?php

/**
 * Blog Archive Template - Posts/Notícias
 * 
 * Template unificado para blog, categorias, tags e autor
 * Segue o padrão visual dos outros archives do CCHLA
 * 
 * @package CCHLA_UFRN
 * @since 2.0.0
 */

get_header();

// Detecta contexto atual
$is_category = is_category();
$is_tag = is_tag();
$is_author = is_author();
$is_date = is_date();
$is_home = is_home();

// Informações do contexto
$queried_object = get_queried_object();
$context_title = '';
$context_description = '';
$context_label = '';
$context_icon = 'fa-newspaper';
$context_color = 'blue'; // blue, purple, green

if ($is_category) {
    $context_label = __('Categoria', 'cchla-ufrn');
    $context_title = single_cat_title('', false);
    $context_description = category_description();
    $context_icon = 'fa-folder';
    $context_color = 'blue';
} elseif ($is_tag) {
    $context_label = __('Tag', 'cchla-ufrn');
    $context_title = single_tag_title('', false);
    $context_description = tag_description();
    $context_icon = 'fa-tag';
    $context_color = 'purple';
} elseif ($is_author) {
    $context_label = __('Autor', 'cchla-ufrn');
    $author = $queried_object;
    $context_title = $author->display_name;
    $context_description = get_the_author_meta('description', $author->ID);
    $context_icon = 'fa-user-pen';
    $context_color = 'green';
} elseif ($is_date) {
    $context_label = __('Arquivo', 'cchla-ufrn');
    if (is_day()) {
        $context_title = get_the_date();
    } elseif (is_month()) {
        $context_title = get_the_date('F \d\e Y');
    } elseif (is_year()) {
        $context_title = get_the_date('Y');
    }
    $context_icon = 'fa-calendar';
    $context_color = 'blue';
} else {
    $context_label = __('Blog', 'cchla-ufrn');
    $context_title = get_the_title(get_option('page_for_posts')) ?: __('Notícias', 'cchla-ufrn');
    $context_description = __('Acompanhe as últimas notícias e atualizações do CCHLA', 'cchla-ufrn');
    $context_icon = 'fa-newspaper';
    $context_color = 'blue';
}

// Pega contagem
global $wp_query;
$total_posts = $wp_query->found_posts;
?>

<!-- Breadcrumb -->
<?php cchla_breadcrumb(); ?>

<main class="bg-white">

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-<?php echo $context_color; ?>-900 to-<?php echo $context_color; ?>-700 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <?php if ($is_category || $is_tag || $is_author) : ?>
                <!-- Voltar -->
                <div class="mb-6">
                    <a href="<?php echo esc_url(get_post_type_archive_link('post')); ?>"
                        class="inline-flex items-center gap-2 text-white/80 hover:text-white transition-colors">
                        <i class="fa-solid fa-arrow-left"></i>
                        <?php _e('Voltar para Notícias', 'cchla-ufrn'); ?>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Badge do Contexto -->
            <div class="mb-4">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm font-semibold">
                    <i class="fa-solid <?php echo esc_attr($context_icon); ?>"></i>
                    <?php echo esc_html($context_label); ?>
                </span>
            </div>

            <!-- Título -->
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <?php echo esc_html($context_title); ?>
            </h1>

            <!-- Descrição -->
            <?php if ($context_description) : ?>
                <div class="text-xl text-white/90 max-w-3xl mb-6">
                    <?php echo wp_kses_post(wpautop($context_description)); ?>
                </div>
            <?php endif; ?>

            <!-- Contador -->
            <div class="flex items-center gap-6 text-white/80">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-newspaper text-2xl"></i>
                    <div>
                        <div class="text-3xl font-bold text-white">
                            <?php echo number_format_i18n($total_posts); ?>
                        </div>
                        <div class="text-sm">
                            <?php echo _n('notícia', 'notícias', $total_posts, 'cchla-ufrn'); ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Barra de Busca e Filtros com ABAS -->
    <section class="bg-white border-b border-gray-200 py-4 lg:py-6 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Título + Abas -->
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">
                    <?php _e('Filtre ou Pesquise', 'cchla-ufrn'); ?>
                </h2>

                <!-- Toggle de Abas -->
                <div class="inline-flex bg-gray-100 rounded-lg p-1" role="tablist" aria-label="<?php esc_attr_e('Alternar entre pesquisa e filtros', 'cchla-ufrn'); ?>">
                    <button type="button"
                        id="tab-search-btn"
                        role="tab"
                        aria-selected="false"
                        aria-controls="tab-search-panel"
                        class="tab-button px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fa-solid fa-search mr-2"></i>
                        <?php _e('Pesquisar', 'cchla-ufrn'); ?>
                    </button>
                    <button type="button"
                        id="tab-filters-btn"
                        role="tab"
                        aria-selected="true"
                        aria-controls="tab-filters-panel"
                        class="tab-button active px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fa-solid fa-sliders mr-2"></i>
                        <?php _e('Filtros', 'cchla-ufrn'); ?>
                        <?php
                        // Contador de filtros ativos
                        $active_filters = 0;
                        if (isset($_GET['cat'])) $active_filters++;
                        if (isset($_GET['tag'])) $active_filters++;
                        if (isset($_GET['year'])) $active_filters++;
                        if (isset($_GET['orderby'])) $active_filters++;

                        if ($active_filters > 0) :
                        ?>
                            <span class="inline-flex items-center justify-center ml-1.5 w-5 h-5 bg-blue-600 text-white text-xs font-bold rounded-full">
                                <?php echo $active_filters; ?>
                            </span>
                        <?php endif; ?>
                    </button>
                </div>
            </div>

            <!-- ==========================================
             PAINEL DE PESQUISA
             ========================================== -->
            <div id="tab-search-panel"
                role="tabpanel"
                aria-labelledby="tab-search-btn"
                class="tab-panel hidden">

                <form method="get" action="<?php echo esc_url(home_url('/')); ?>" class="max-w-3xl">
                    <div class="relative">
                        <label for="search-input" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php _e('Digite o que você procura:', 'cchla-ufrn'); ?>
                        </label>

                        <div class="relative">
                            <input type="text"
                                id="search-input"
                                name="s"
                                value="<?php echo esc_attr(get_search_query()); ?>"
                                placeholder="<?php esc_attr_e('Ex: inteligência artificial, história medieval, linguística...', 'cchla-ufrn'); ?>"
                                autocomplete="off"
                                class="w-full pl-12 pr-32 py-4 text-lg border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">

                            <!-- Ícone de busca -->
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fa-solid fa-search text-xl"></i>
                            </div>

                            <?php if ($is_category) : ?>
                                <input type="hidden" name="cat" value="<?php echo get_queried_object_id(); ?>">
                            <?php endif; ?>

                            <?php if ($is_tag) : ?>
                                <input type="hidden" name="tag" value="<?php echo get_queried_object()->slug; ?>">
                            <?php endif; ?>

                            <?php if ($is_author) : ?>
                                <input type="hidden" name="author" value="<?php echo get_queried_object_id(); ?>">
                            <?php endif; ?>

                            <!-- Botões -->
                            <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2">
                                <?php if (get_search_query()) : ?>
                                    <button type="button"
                                        id="clear-search"
                                        class="p-2 text-gray-400 hover:text-red-600 transition-colors"
                                        aria-label="<?php esc_attr_e('Limpar busca', 'cchla-ufrn'); ?>">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                <?php endif; ?>

                                <button type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                                    <?php _e('Buscar', 'cchla-ufrn'); ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Dicas de Busca -->
                    <div class="mt-3 flex flex-wrap gap-2 text-sm">
                        <span class="text-gray-600">
                            <i class="fa-solid fa-lightbulb text-yellow-500 mr-1"></i>
                            <?php _e('Dica:', 'cchla-ufrn'); ?>
                        </span>
                        <span class="text-gray-500">
                            <?php _e('Use aspas para buscar frases exatas:', 'cchla-ufrn'); ?>
                        </span>
                        <code class="px-2 py-0.5 bg-gray-100 text-blue-600 rounded">"inteligência artificial"</code>
                    </div>

                    <!-- Busca ativa - Info -->
                    <?php if (get_search_query()) : ?>
                        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-start gap-3">
                            <i class="fa-solid fa-info-circle text-blue-600 mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm text-blue-900">
                                    <?php
                                    printf(
                                        __('Buscando por: %s', 'cchla-ufrn'),
                                        '<strong>"' . esc_html(get_search_query()) . '"</strong>'
                                    );
                                    ?>
                                </p>
                            </div>
                            <a href="<?php echo esc_url(remove_query_arg('s')); ?>"
                                class="text-sm text-blue-700 hover:text-blue-900 font-medium">
                                <?php _e('Limpar', 'cchla-ufrn'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- ==========================================
             PAINEL DE FILTROS
             ========================================== -->
            <div id="tab-filters-panel"
                role="tabpanel"
                aria-labelledby="tab-filters-btn"
                class="tab-panel">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">

                    <!-- Filtro de Categorias -->
                    <?php if (!$is_category) :
                        $categories = get_categories(array(
                            'hide_empty' => true,
                            'orderby' => 'count',
                            'order' => 'DESC',
                            'number' => 15
                        ));

                        if ($categories) :
                    ?>
                            <div>
                                <label for="filter-category" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">
                                    <i class="fa-solid fa-folder mr-1"></i>
                                    <?php _e('Categoria', 'cchla-ufrn'); ?>
                                </label>
                                <div class="relative">
                                    <select id="filter-category"
                                        class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white appearance-none transition-shadow">
                                        <option value=""><?php _e('Todas', 'cchla-ufrn'); ?></option>
                                        <?php foreach ($categories as $category) : ?>
                                            <option value="<?php echo esc_attr($category->term_id); ?>"
                                                <?php selected(isset($_GET['cat']) && $_GET['cat'] == $category->term_id); ?>>
                                                <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                                </div>
                            </div>
                    <?php endif;
                    endif; ?>

                    <!-- Filtro de Tags -->
                    <?php if (!$is_tag) :
                        $tags = get_tags(array(
                            'hide_empty' => true,
                            'orderby' => 'count',
                            'order' => 'DESC',
                            'number' => 20
                        ));

                        if ($tags) :
                    ?>
                            <div>
                                <label for="filter-tag" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">
                                    <i class="fa-solid fa-tag mr-1"></i>
                                    <?php _e('Tag', 'cchla-ufrn'); ?>
                                </label>
                                <div class="relative">
                                    <select id="filter-tag"
                                        class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white appearance-none transition-shadow">
                                        <option value=""><?php _e('Todas', 'cchla-ufrn'); ?></option>
                                        <?php foreach ($tags as $tag) : ?>
                                            <option value="<?php echo esc_attr($tag->slug); ?>"
                                                <?php selected(isset($_GET['tag']) && $_GET['tag'] == $tag->slug); ?>>
                                                #<?php echo esc_html($tag->name); ?> (<?php echo $tag->count; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                                </div>
                            </div>
                    <?php endif;
                    endif; ?>

                    <!-- Filtro de Ano -->
                    <?php
                    global $wpdb;
                    $years = $wpdb->get_col("
                    SELECT DISTINCT YEAR(post_date) 
                    FROM $wpdb->posts 
                    WHERE post_status = 'publish' 
                    AND post_type = 'post'
                    ORDER BY post_date DESC
                ");

                    if ($years && count($years) > 1) :
                    ?>
                        <div>
                            <label for="filter-year" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">
                                <i class="fa-solid fa-calendar mr-1"></i>
                                <?php _e('Ano', 'cchla-ufrn'); ?>
                            </label>
                            <div class="relative">
                                <select id="filter-year"
                                    class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white appearance-none transition-shadow">
                                    <option value=""><?php _e('Todos', 'cchla-ufrn'); ?></option>
                                    <?php foreach ($years as $year) : ?>
                                        <option value="<?php echo esc_attr($year); ?>"
                                            <?php selected(isset($_GET['year']) && $_GET['year'] == $year); ?>>
                                            <?php echo esc_html($year); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Ordenação -->
                    <div>
                        <label for="filter-order" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wider">
                            <i class="fa-solid fa-sort mr-1"></i>
                            <?php _e('Ordenar', 'cchla-ufrn'); ?>
                        </label>
                        <div class="relative">
                            <select id="filter-order"
                                class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white appearance-none transition-shadow">
                                <option value="date-desc" <?php selected((!isset($_GET['orderby']) || $_GET['orderby'] == 'date') && (!isset($_GET['order']) || $_GET['order'] == 'DESC')); ?>>
                                    <?php _e('Mais recentes', 'cchla-ufrn'); ?>
                                </option>
                                <option value="date-asc" <?php selected(isset($_GET['orderby']) && $_GET['orderby'] == 'date' && isset($_GET['order']) && $_GET['order'] == 'ASC'); ?>>
                                    <?php _e('Mais antigas', 'cchla-ufrn'); ?>
                                </option>
                                <option value="title-asc" <?php selected(isset($_GET['orderby']) && $_GET['orderby'] == 'title' && isset($_GET['order']) && $_GET['order'] == 'ASC'); ?>>
                                    <?php _e('Título (A-Z)', 'cchla-ufrn'); ?>
                                </option>
                                <option value="title-desc" <?php selected(isset($_GET['orderby']) && $_GET['orderby'] == 'title' && isset($_GET['order']) && $_GET['order'] == 'DESC'); ?>>
                                    <?php _e('Título (Z-A)', 'cchla-ufrn'); ?>
                                </option>
                                <option value="comment_count-desc" <?php selected(isset($_GET['orderby']) && $_GET['orderby'] == 'comment_count'); ?>>
                                    <?php _e('Mais comentadas', 'cchla-ufrn'); ?>
                                </option>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>

                    <!-- Botão Limpar (Grid Item) -->
                    <?php if (get_search_query() || isset($_GET['cat']) || isset($_GET['tag']) || isset($_GET['year']) || isset($_GET['orderby'])) : ?>
                        <div class="flex items-end">
                            <a href="<?php echo esc_url(get_post_type_archive_link('post') ?: home_url('/')); ?>"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 rounded-lg transition-colors font-medium text-sm">
                                <i class="fa-solid fa-times-circle"></i>
                                <?php _e('Limpar', 'cchla-ufrn'); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </section>

    <script>
        (function() {
            'use strict';

            // ==========================================
            // MARCA ITEM ATIVO
            // ==========================================
            const currentPageItem = document.querySelector('.pagination-item .current');
            if (currentPageItem) {
                const parentLi = currentPageItem.closest('.pagination-item');
                if (parentLi) {
                    parentLi.classList.add('active-item');
                }
            }

            // ==========================================
            // SCROLL SUAVE AO CLICAR
            // ==========================================
            const paginationLinks = document.querySelectorAll('.pagination-item a');

            paginationLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Scroll suave para o topo da página
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });

                    // Feedback visual
                    const nav = document.querySelector('nav[aria-label*="Paginação"]');
                    if (nav) {
                        nav.classList.add('pagination-loading');
                    }
                });
            });

            // ==========================================
            // ATALHOS DE TECLADO
            // ==========================================
            document.addEventListener('keydown', function(e) {
                // Shift + Seta Esquerda = Página Anterior
                if (e.shiftKey && e.key === 'ArrowLeft') {
                    e.preventDefault();
                    const prevLink = document.querySelector('.pagination-item a[rel="prev"]');
                    if (prevLink) {
                        prevLink.click();
                    }
                }

                // Shift + Seta Direita = Próxima Página
                if (e.shiftKey && e.key === 'ArrowRight') {
                    e.preventDefault();
                    const nextLink = document.querySelector('.pagination-item a[rel="next"]');
                    if (nextLink) {
                        nextLink.click();
                    }
                }
            });

            // ==========================================
            // TOOLTIP PARA NÚMEROS DE PÁGINA
            // ==========================================
            paginationLinks.forEach(link => {
                // Adiciona título descritivo
                const pageNumber = link.textContent.trim();
                if (!isNaN(pageNumber)) {
                    link.setAttribute('title', 'Ir para a página ' + pageNumber);
                }
            });

        })();
    </script>

    <!-- JavaScript: Sistema de Abas + Filtros -->
    <script>
        (function() {
            'use strict';

            // ==========================================
            // SISTEMA DE ABAS
            // ==========================================
            const tabSearchBtn = document.getElementById('tab-search-btn');
            const tabFiltersBtn = document.getElementById('tab-filters-btn');
            const tabSearchPanel = document.getElementById('tab-search-panel');
            const tabFiltersPanel = document.getElementById('tab-filters-panel');

            function switchToTab(tabName) {
                if (tabName === 'search') {
                    // Ativa pesquisa
                    tabSearchBtn.classList.add('active');
                    tabSearchBtn.setAttribute('aria-selected', 'true');
                    tabFiltersBtn.classList.remove('active');
                    tabFiltersBtn.setAttribute('aria-selected', 'false');

                    tabSearchPanel.classList.remove('hidden');
                    tabFiltersPanel.classList.add('hidden');

                    // Foca no input de busca
                    setTimeout(() => {
                        document.getElementById('search-input')?.focus();
                    }, 100);

                } else {
                    // Ativa filtros
                    tabFiltersBtn.classList.add('active');
                    tabFiltersBtn.setAttribute('aria-selected', 'true');
                    tabSearchBtn.classList.remove('active');
                    tabSearchBtn.setAttribute('aria-selected', 'false');

                    tabFiltersPanel.classList.remove('hidden');
                    tabSearchPanel.classList.add('hidden');
                }
            }

            // Event listeners das abas
            if (tabSearchBtn) {
                tabSearchBtn.addEventListener('click', () => switchToTab('search'));
            }

            if (tabFiltersBtn) {
                tabFiltersBtn.addEventListener('click', () => switchToTab('filters'));
            }

            // Define aba inicial baseado em se há busca ativa
            const hasSearchQuery = '<?php echo esc_js(get_search_query()); ?>';
            if (hasSearchQuery) {
                switchToTab('search');
            }

            // ==========================================
            // LIMPAR BUSCA
            // ==========================================
            const clearSearchBtn = document.getElementById('clear-search');
            const searchInput = document.getElementById('search-input');

            if (clearSearchBtn && searchInput) {
                clearSearchBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    searchInput.focus();
                });
            }

            // ==========================================
            // LÓGICA DOS FILTROS
            // ==========================================
            const filterCategory = document.getElementById('filter-category');
            const filterTag = document.getElementById('filter-tag');
            const filterYear = document.getElementById('filter-year');
            const filterOrder = document.getElementById('filter-order');

            function buildUrl() {
                let baseUrl = window.location.origin + window.location.pathname;
                if (baseUrl.endsWith('/') && baseUrl.length > 1) {
                    baseUrl = baseUrl.slice(0, -1);
                }

                const params = new URLSearchParams();

                // Mantém contexto
                <?php if ($is_category && !isset($_GET['cat'])) : ?>
                    baseUrl = '<?php echo esc_url(get_category_link(get_queried_object_id())); ?>';
                <?php endif; ?>

                <?php if ($is_tag && !isset($_GET['tag'])) : ?>
                    baseUrl = '<?php echo esc_url(get_tag_link(get_queried_object_id())); ?>';
                <?php endif; ?>

                <?php if ($is_author) : ?>
                    baseUrl = '<?php echo esc_url(get_author_posts_url(get_queried_object_id())); ?>';
                <?php endif; ?>

                // Busca
                const searchQuery = searchInput?.value || '<?php echo esc_js(get_search_query()); ?>';
                if (searchQuery) {
                    params.set('s', searchQuery);
                }

                // Categoria
                if (filterCategory && filterCategory.value) {
                    <?php if ($is_category && !isset($_GET['cat'])) : ?>
                        baseUrl = '<?php echo esc_url(get_post_type_archive_link('post') ?: home_url('/')); ?>';
                    <?php endif; ?>
                    params.set('cat', filterCategory.value);
                }

                // Tag
                if (filterTag && filterTag.value) {
                    <?php if ($is_tag && !isset($_GET['tag'])) : ?>
                        baseUrl = '<?php echo esc_url(get_post_type_archive_link('post') ?: home_url('/')); ?>';
                    <?php endif; ?>
                    params.set('tag', filterTag.value);
                }

                // Ano
                if (filterYear && filterYear.value) {
                    params.set('year', filterYear.value);
                }

                // Ordenação
                if (filterOrder && filterOrder.value) {
                    const [orderby, order] = filterOrder.value.split('-');
                    if (orderby && order) {
                        params.set('orderby', orderby);
                        params.set('order', order.toUpperCase());
                    }
                }

                params.delete('paged');
                params.delete('page');

                const queryString = params.toString();
                const finalUrl = queryString ? baseUrl + '?' + queryString : baseUrl;

                // Feedback visual
                document.body.style.opacity = '0.7';

                window.location.href = finalUrl;
            }

            // Event listeners dos filtros
            if (filterCategory) filterCategory.addEventListener('change', buildUrl);
            if (filterTag) filterTag.addEventListener('change', buildUrl);
            if (filterYear) filterYear.addEventListener('change', buildUrl);
            if (filterOrder) filterOrder.addEventListener('change', buildUrl);

            // ==========================================
            // ATALHOS DE TECLADO
            // ==========================================
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + K = Abre busca
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    switchToTab('search');
                }

                // Ctrl/Cmd + F = Abre filtros
                if ((e.ctrlKey || e.metaKey) && e.key === 'f' && !e.shiftKey) {
                    e.preventDefault();
                    switchToTab('filters');
                }
            });

        })();
    </script>


    <!-- Estilos das Abas -->
    <style>
        /* Botões de aba */
        .tab-button {
            color: #6b7280;
            background: transparent;
        }

        .tab-button.active {
            color: #1f2937;
            background: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .tab-button:hover:not(.active) {
            color: #374151;
            background: rgba(255, 255, 255, 0.5);
        }

        /* Painéis */
        .tab-panel {
            animation: fadeInUp 0.3s ease-out;
        }

        .tab-panel.hidden {
            display: none;
        }

        /* Animação de entrada */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Previne zoom no iOS */
        @media (max-width: 640px) {

            select,
            input[type="text"] {
                font-size: 16px !important;
            }
        }

        /* Sticky aprimorado */
        .sticky {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.98);
        }
    </style>

    <!-- Filtros Ativos (Pills) -->
    <?php
    $active_filters = array();

    if ($is_category && !isset($_GET['cat'])) {
        $active_filters[] = array(
            'label' => __('Categoria:', 'cchla-ufrn') . ' ' . single_cat_title('', false),
            'url' => get_post_type_archive_link('post'),
            'color' => 'blue'
        );
    }

    if (isset($_GET['cat']) && !empty($_GET['cat'])) {
        $cat = get_category($_GET['cat']);
        if ($cat) {
            $active_filters[] = array(
                'label' => __('Categoria:', 'cchla-ufrn') . ' ' . $cat->name,
                'url' => remove_query_arg('cat'),
                'color' => 'blue'
            );
        }
    }

    if ($is_tag && !isset($_GET['tag'])) {
        $active_filters[] = array(
            'label' => __('Tag:', 'cchla-ufrn') . ' #' . single_tag_title('', false),
            'url' => get_post_type_archive_link('post'),
            'color' => 'purple'
        );
    }

    if (isset($_GET['tag']) && !empty($_GET['tag'])) {
        $tag = get_term_by('slug', $_GET['tag'], 'post_tag');
        if ($tag) {
            $active_filters[] = array(
                'label' => __('Tag:', 'cchla-ufrn') . ' #' . $tag->name,
                'url' => remove_query_arg('tag'),
                'color' => 'purple'
            );
        }
    }

    if ($is_author && !isset($_GET['author'])) {
        $author = get_queried_object();
        $active_filters[] = array(
            'label' => __('Autor:', 'cchla-ufrn') . ' ' . $author->display_name,
            'url' => get_post_type_archive_link('post'),
            'color' => 'green'
        );
    }

    if (isset($_GET['year']) && !empty($_GET['year'])) {
        $active_filters[] = array(
            'label' => __('Ano:', 'cchla-ufrn') . ' ' . $_GET['year'],
            'url' => remove_query_arg('year'),
            'color' => 'indigo'
        );
    }

    if (get_search_query()) {
        $active_filters[] = array(
            'label' => __('Busca:', 'cchla-ufrn') . ' "' . get_search_query() . '"',
            'url' => remove_query_arg('s'),
            'color' => 'orange'
        );
    }

    if (!empty($active_filters)) :
    ?>
        <section class="bg-gray-50 py-4 border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fa-solid fa-filter"></i>
                        <?php _e('Filtros ativos:', 'cchla-ufrn'); ?>
                    </span>

                    <?php foreach ($active_filters as $filter) : ?>
                        <a href="<?php echo esc_url($filter['url']); ?>"
                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-<?php echo $filter['color']; ?>-100 text-<?php echo $filter['color']; ?>-700 rounded-full text-sm hover:bg-<?php echo $filter['color']; ?>-200 transition-colors">
                            <?php echo esc_html($filter['label']); ?>
                            <i class="fa-solid fa-times text-xs"></i>
                        </a>
                    <?php endforeach; ?>

                    <a href="<?php echo get_post_type_archive_link('post'); ?>"
                        class="text-sm text-gray-600 hover:text-blue-600 transition-colors ml-2 font-medium">
                        <?php _e('Limpar todos', 'cchla-ufrn'); ?>
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Listagem de Posts -->
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <?php if (have_posts()) : ?>

                <!-- Info de Resultados -->
                <div class="mb-8 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <?php
                        $current_page = max(1, get_query_var('paged'));
                        $per_page = get_query_var('posts_per_page');
                        $showing_from = (($current_page - 1) * $per_page) + 1;
                        $showing_to = min($current_page * $per_page, $total_posts);

                        printf(
                            __('Exibindo %s-%s de %s notícias', 'cchla-ufrn'),
                            '<strong>' . number_format_i18n($showing_from) . '</strong>',
                            '<strong>' . number_format_i18n($showing_to) . '</strong>',
                            '<strong>' . number_format_i18n($total_posts) . '</strong>'
                        );
                        ?>
                    </div>

                    <!-- View Mode Toggle (Opcional) -->
                    <div class="hidden sm:flex items-center gap-2">
                        <span class="text-xs text-gray-500 mr-2"><?php _e('Visualização:', 'cchla-ufrn'); ?></span>
                        <button type="button"
                            id="view-grid"
                            class="p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded transition-colors"
                            aria-label="<?php esc_attr_e('Visualização em grade', 'cchla-ufrn'); ?>">
                            <i class="fa-solid fa-grid text-sm"></i>
                        </button>
                        <button type="button"
                            id="view-list"
                            class="p-2 text-blue-600 bg-blue-50 rounded transition-colors"
                            aria-label="<?php esc_attr_e('Visualização em lista', 'cchla-ufrn'); ?>">
                            <i class="fa-solid fa-list text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Grid de Posts (SEM IMAGENS) -->
                <div id="posts-container" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <?php while (have_posts()) : the_post(); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class('group bg-white border border-gray-200 rounded-lg p-6 hover:border-blue-500 hover:shadow-lg transition-all duration-300'); ?>>

                            <!-- Meta -->
                            <div class="flex items-center gap-3 text-xs text-gray-500 mb-4">
                                <time datetime="<?php echo get_the_date('c'); ?>" class="flex items-center gap-1">
                                    <i class="fa-solid fa-calendar-alt"></i>
                                    <?php echo get_the_date('d.m.Y'); ?>
                                </time>

                                <?php
                                $categories = get_the_category();
                                if ($categories && !$is_category) :
                                ?>
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-folder"></i>
                                        <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>"
                                            class="hover:text-blue-600 transition-colors">
                                            <?php echo esc_html($categories[0]->name); ?>
                                        </a>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Título -->
                            <h2 class="text-lg font-bold text-gray-900 mb-3 line-clamp-3 group-hover:text-blue-600 transition-colors leading-tight">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h2>

                            <!-- Excerpt -->
                            <p class="text-sm text-gray-600 mb-4 line-clamp-4 leading-relaxed">
                                <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                            </p>

                            <!-- Footer -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <a href="<?php the_permalink(); ?>"
                                    class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium text-sm group/link">
                                    <?php _e('Continue lendo', 'cchla-ufrn'); ?>
                                    <i class="fa-solid fa-arrow-right text-xs group-hover/link:translate-x-1 transition-transform"></i>
                                </a>

                                <?php if (get_comments_number() > 0) : ?>
                                    <span class="flex items-center gap-1 text-xs text-gray-500">
                                        <i class="fa-solid fa-comment"></i>
                                        <?php comments_number('0', '1', '%'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                        </article>

                    <?php endwhile; ?>

                </div>

                <!-- Paginação -->
                <?php
                $pagination = paginate_links(array(
                    'prev_text' => '<i class="fa-solid fa-chevron-left"></i> ' . __('Anterior', 'cchla-ufrn'),
                    'next_text' => __('Próxima', 'cchla-ufrn') . ' <i class="fa-solid fa-chevron-right"></i>',
                    'type' => 'array',
                    'before_page_number' => '<span class="sr-only">' . __('Página', 'cchla-ufrn') . ' </span>',
                ));

                if ($pagination) :
                ?>
                    <!-- Paginação Otimizada -->
                    <?php
                    $pagination = paginate_links(array(
                        'prev_text' => '<i class="fa-solid fa-chevron-left"></i><span class="ml-2 hidden sm:inline">' . __('Anterior', 'cchla-ufrn') . '</span>',
                        'next_text' => '<span class="mr-2 hidden sm:inline">' . __('Próxima', 'cchla-ufrn') . '</span><i class="fa-solid fa-chevron-right"></i>',
                        'type' => 'array',
                        'mid_size' => 2,
                        'end_size' => 1,
                        'before_page_number' => '<span class="sr-only">' . __('Página', 'cchla-ufrn') . ' </span>',
                        'add_fragment' => '',
                    ));

                    if ($pagination) :
                    ?>
                        <nav class="mt-12" aria-label="<?php esc_attr_e('Paginação de notícias', 'cchla-ufrn'); ?>">
                            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">

                                <!-- Info de Página Atual (Mobile) -->
                                <div class="sm:hidden text-center mb-4">
                                    <p class="text-sm text-gray-600">
                                        <?php
                                        global $wp_query;
                                        $current = max(1, get_query_var('paged'));
                                        $total = $wp_query->max_num_pages;
                                        printf(
                                            __('Página %s de %s', 'cchla-ufrn'),
                                            '<strong class="text-cchla-blue">' . number_format_i18n($current) . '</strong>',
                                            '<strong>' . number_format_i18n($total) . '</strong>'
                                        );
                                        ?>
                                    </p>
                                </div>

                                <!-- Links de Paginação -->
                                <ul class="flex justify-center items-center gap-2 flex-wrap">
                                    <?php foreach ($pagination as $page) : ?>
                                        <li class="pagination-item">
                                            <?php echo $page; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                                <!-- Info Adicional (Desktop) -->
                                <div class="hidden sm:block text-center mt-4 pt-4 border-t border-gray-100">
                                    <p class="text-xs text-gray-500">
                                        <?php
                                        $current = max(1, get_query_var('paged'));
                                        $total = $wp_query->max_num_pages;
                                        $total_posts = $wp_query->found_posts;

                                        printf(
                                            __('Página %s de %s • Total: %s notícias', 'cchla-ufrn'),
                                            '<strong>' . number_format_i18n($current) . '</strong>',
                                            '<strong>' . number_format_i18n($total) . '</strong>',
                                            '<strong>' . number_format_i18n($total_posts) . '</strong>'
                                        );
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>

            <?php else : ?>

                <!-- Nenhum resultado -->
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-6">
                        <i class="fa-solid fa-newspaper text-4xl text-blue-600"></i>
                    </div>

                    <h3 class="text-2xl font-bold text-gray-900 mb-3">
                        <?php _e('Nenhuma notícia encontrada', 'cchla-ufrn'); ?>
                    </h3>

                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        <?php
                        if (get_search_query()) {
                            printf(
                                __('Sua busca por "%s" não retornou resultados.', 'cchla-ufrn'),
                                '<strong>' . esc_html(get_search_query()) . '</strong>'
                            );
                        } else {
                            _e('Tente ajustar os filtros ou fazer uma nova busca.', 'cchla-ufrn');
                        }
                        ?>
                    </p>

                    <div class="flex gap-3 justify-center">
                        <?php if (cchla_is_filtered()) : ?>
                            <a href="<?php echo get_post_type_archive_link('post'); ?>"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fa-solid fa-times-circle"></i>
                                <?php _e('Limpar filtros', 'cchla-ufrn'); ?>
                            </a>
                        <?php endif; ?>

                        <a href="<?php echo esc_url(home_url('/')); ?>"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                            <i class="fa-solid fa-home"></i>
                            <?php _e('Voltar para home', 'cchla-ufrn'); ?>
                        </a>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </section>

</main>

<!-- JavaScript para Filtros -->
<script>
    (function() {
        'use strict';

        const filterCategory = document.getElementById('filter-category');
        const filterTag = document.getElementById('filter-tag');
        const filterYear = document.getElementById('filter-year');
        const filterOrder = document.getElementById('filter-order');

        /**
         * Constrói URL com filtros aplicados
         */
        function buildUrl() {
            // Pega a URL atual como base
            const currentUrl = new URL(window.location.href);
            const params = new URLSearchParams();

            // Determina a URL base
            let baseUrl = window.location.origin + window.location.pathname;

            // Remove trailing slash se existir
            if (baseUrl.endsWith('/') && baseUrl.length > 1) {
                baseUrl = baseUrl.slice(0, -1);
            }

            // ==========================================
            // MANTÉM CONTEXTO ATUAL
            // ==========================================

            <?php if ($is_category && !isset($_GET['cat'])) : ?>
                // Se estamos em uma página de categoria direta (/categoria/slug/)
                // não adicionamos parâmetro cat, já está na URL
            <?php elseif (isset($_GET['cat'])) : ?>
                // Se categoria veio de parâmetro GET, mantém
                params.set('cat', '<?php echo intval($_GET['cat']); ?>');
            <?php endif; ?>

            <?php if ($is_tag && !isset($_GET['tag'])) : ?>
                // Se estamos em uma página de tag direta (/tag/slug/)
                // não adicionamos parâmetro tag, já está na URL
            <?php elseif (isset($_GET['tag'])) : ?>
                // Se tag veio de parâmetro GET, mantém
                params.set('tag', '<?php echo esc_js($_GET['tag']); ?>');
            <?php endif; ?>

            <?php if ($is_author && !isset($_GET['author'])) : ?>
                // Se estamos em página de autor
            <?php elseif (isset($_GET['author'])) : ?>
                params.set('author', '<?php echo intval($_GET['author']); ?>');
            <?php endif; ?>

            // ==========================================
            // BUSCA
            // ==========================================
            const searchQuery = '<?php echo esc_js(get_search_query()); ?>';
            if (searchQuery) {
                params.set('s', searchQuery);
            }

            // ==========================================
            // CATEGORIA (Select)
            // ==========================================
            if (filterCategory && filterCategory.value) {
                // Remove contexto de categoria anterior se houver
                <?php if ($is_category && !isset($_GET['cat'])) : ?>
                    baseUrl = '<?php echo esc_url(get_post_type_archive_link('post') ?: home_url('/')); ?>';
                <?php endif; ?>

                params.set('cat', filterCategory.value);
            }

            // ==========================================
            // TAG (Select)
            // ==========================================
            if (filterTag && filterTag.value) {
                // Remove contexto de tag anterior se houver
                <?php if ($is_tag && !isset($_GET['tag'])) : ?>
                    baseUrl = '<?php echo esc_url(get_post_type_archive_link('post') ?: home_url('/')); ?>';
                <?php endif; ?>

                params.set('tag', filterTag.value);
            }

            // ==========================================
            // ANO
            // ==========================================
            if (filterYear && filterYear.value) {
                params.set('year', filterYear.value);
            }

            // ==========================================
            // ORDENAÇÃO
            // ==========================================
            if (filterOrder && filterOrder.value) {
                const orderValue = filterOrder.value.split('-');
                if (orderValue.length === 2) {
                    params.set('orderby', orderValue[0]);
                    params.set('order', orderValue[1].toUpperCase());
                }
            }

            // Remove paginação ao aplicar filtros
            params.delete('paged');
            params.delete('page');

            // ==========================================
            // CONSTRÓI URL FINAL
            // ==========================================
            const queryString = params.toString();
            const finalUrl = queryString ? baseUrl + '?' + queryString : baseUrl;

            // Debug (remova em produção)
            console.log('Base URL:', baseUrl);
            console.log('Params:', queryString);
            console.log('Final URL:', finalUrl);

            // Redireciona
            window.location.href = finalUrl;
        }

        // ==========================================
        // EVENT LISTENERS
        // ==========================================
        if (filterCategory) {
            filterCategory.addEventListener('change', function() {
                if (this.value) {
                    buildUrl();
                }
            });
        }

        if (filterTag) {
            filterTag.addEventListener('change', function() {
                if (this.value) {
                    buildUrl();
                }
            });
        }

        if (filterYear) {
            filterYear.addEventListener('change', function() {
                buildUrl();
            });
        }

        if (filterOrder) {
            filterOrder.addEventListener('change', function() {
                buildUrl();
            });
        }

        // ==========================================
        // RESTAURA VALORES DOS SELECTS
        // ==========================================
        function restoreFilters() {
            const urlParams = new URLSearchParams(window.location.search);

            // Categoria
            if (filterCategory) {
                const catParam = urlParams.get('cat');
                if (catParam) {
                    filterCategory.value = catParam;
                }
            }

            // Tag
            if (filterTag) {
                const tagParam = urlParams.get('tag');
                if (tagParam) {
                    filterTag.value = tagParam;
                }
            }

            // Ano
            if (filterYear) {
                const yearParam = urlParams.get('year');
                if (yearParam) {
                    filterYear.value = yearParam;
                }
            }

            // Ordenação
            if (filterOrder) {
                const orderby = urlParams.get('orderby');
                const order = urlParams.get('order');

                if (orderby && order) {
                    filterOrder.value = orderby + '-' + order.toLowerCase();
                }
            }
        }

        // Restaura filtros ao carregar a página
        restoreFilters();

        // ==========================================
        // VIEW MODE TOGGLE (Grid/List)
        // ==========================================
        const viewGrid = document.getElementById('view-grid');
        const viewList = document.getElementById('view-list');
        const postsContainer = document.getElementById('posts-container');

        if (viewGrid && viewList && postsContainer) {
            // Carrega preferência salva
            const savedView = localStorage.getItem('cchla_posts_view') || 'grid';

            if (savedView === 'list') {
                switchToListView();
            }

            viewGrid.addEventListener('click', function() {
                switchToGridView();
                localStorage.setItem('cchla_posts_view', 'grid');
            });

            viewList.addEventListener('click', function() {
                switchToListView();
                localStorage.setItem('cchla_posts_view', 'list');
            });

            function switchToGridView() {
                postsContainer.className = 'grid md:grid-cols-2 lg:grid-cols-3 gap-6';
                viewGrid.className = 'p-2 text-blue-600 bg-blue-50 rounded transition-colors';
                viewList.className = 'p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded transition-colors';

                // Remove classe list-view dos artigos
                const articles = postsContainer.querySelectorAll('article');
                articles.forEach(article => {
                    article.classList.remove('list-view');
                });
            }

            function switchToListView() {
                postsContainer.className = 'space-y-6';
                viewGrid.className = 'p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded transition-colors';
                viewList.className = 'p-2 text-blue-600 bg-blue-50 rounded transition-colors';

                // Adiciona classe list-view aos artigos
                const articles = postsContainer.querySelectorAll('article');
                articles.forEach(article => {
                    article.classList.add('list-view');
                });
            }
        }

    })();
</script>


<?php get_footer(); ?>