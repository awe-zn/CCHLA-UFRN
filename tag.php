<?php

/**
 * Tag Archive Template
 * 
 * Template específico para arquivos de tags
 * Exibe posts filtrados por tag com busca e filtros
 * 
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

get_header();

// Informações da tag atual
$current_tag = get_queried_object();
$tag_name = $current_tag->name;
$tag_slug = $current_tag->slug;
$tag_description = $current_tag->description;
$tag_count = $current_tag->count;
$tag_id = $current_tag->term_id;
?>

<!-- Breadcrumb -->
<?php cchla_breadcrumb(); ?>

<main class="bg-white">

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-purple-900 to-purple-700 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Voltar -->
            <div class="mb-6">
                <a href="<?php echo esc_url(get_post_type_archive_link('post')); ?>"
                    class="inline-flex items-center gap-2 text-white/80 hover:text-white transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                    <?php _e('Voltar para Notícias', 'cchla-ufrn'); ?>
                </a>
            </div>

            <!-- Badge da Tag -->
            <div class="mb-4">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm font-semibold">
                    <i class="fa-solid fa-tag"></i>
                    <?php _e('Tag', 'cchla-ufrn'); ?>
                </span>
            </div>

            <!-- Título -->
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                #<?php echo esc_html($tag_name); ?>
            </h1>

            <!-- Descrição -->
            <?php if ($tag_description) : ?>
                <div class="text-xl text-white/90 max-w-3xl mb-6">
                    <?php echo wpautop($tag_description); ?>
                </div>
            <?php endif; ?>

            <!-- Contador -->
            <div class="flex items-center gap-6 text-white/80">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-newspaper text-2xl"></i>
                    <div>
                        <div class="text-3xl font-bold text-white">
                            <?php echo number_format_i18n($tag_count); ?>
                        </div>
                        <div class="text-sm">
                            <?php echo _n('publicação', 'publicações', $tag_count, 'cchla-ufrn'); ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Barra de Busca e Filtros -->
    <section class="bg-white border-b border-gray-200 py-6 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">

                <!-- Busca dentro da Tag -->
                <form method="get" class="flex-1 max-w-md">
                    <div class="relative">
                        <input type="text"
                            name="s"
                            value="<?php echo get_search_query(); ?>"
                            placeholder="<?php printf(esc_attr__('Buscar em %s...', 'cchla-ufrn'), $tag_name); ?>"
                            class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <input type="hidden" name="tag" value="<?php echo esc_attr($tag_slug); ?>">
                        <button type="submit"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-purple-600 transition-colors">
                            <i class="fa-solid fa-search"></i>
                        </button>
                    </div>
                </form>

                <!-- Filtros -->
                <div class="flex flex-wrap gap-3 items-center">

                    <!-- Filtro por Categoria -->
                    <?php
                    $categories = get_categories(array(
                        'hide_empty' => true,
                        'orderby' => 'count',
                        'order' => 'DESC'
                    ));

                    if ($categories) :
                    ?>
                        <div class="relative">
                            <select id="filter-category"
                                class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white appearance-none pr-10">
                                <option value=""><?php _e('Todas as categorias', 'cchla-ufrn'); ?></option>
                                <?php foreach ($categories as $category) : ?>
                                    <option value="<?php echo esc_attr($category->term_id); ?>"
                                        <?php selected(isset($_GET['cat']) && $_GET['cat'] == $category->term_id); ?>>
                                        <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    <?php endif; ?>

                    <!-- Filtro por Ano -->
                    <?php
                    global $wpdb;
                    $years = $wpdb->get_col("
                        SELECT DISTINCT YEAR(p.post_date) 
                        FROM $wpdb->posts p
                        INNER JOIN $wpdb->term_relationships tr ON p.ID = tr.object_id
                        INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                        WHERE tt.term_id = $tag_id
                        AND p.post_status = 'publish'
                        AND p.post_type = 'post'
                        ORDER BY p.post_date DESC
                    ");

                    if ($years) :
                    ?>
                        <div class="relative">
                            <select id="filter-year"
                                class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white appearance-none pr-10">
                                <option value=""><?php _e('Todos os anos', 'cchla-ufrn'); ?></option>
                                <?php foreach ($years as $year) : ?>
                                    <option value="<?php echo esc_attr($year); ?>"
                                        <?php selected(isset($_GET['year']) && $_GET['year'] == $year); ?>>
                                        <?php echo esc_html($year); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    <?php endif; ?>

                    <!-- Ordenação -->
                    <div class="relative">
                        <select id="filter-order"
                            class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white appearance-none pr-10">
                            <option value="date-desc"><?php _e('Mais recentes', 'cchla-ufrn'); ?></option>
                            <option value="date-asc"><?php _e('Mais antigas', 'cchla-ufrn'); ?></option>
                            <option value="title-asc"><?php _e('Título (A-Z)', 'cchla-ufrn'); ?></option>
                            <option value="title-desc"><?php _e('Título (Z-A)', 'cchla-ufrn'); ?></option>
                            <option value="comment_count-desc"><?php _e('Mais comentadas', 'cchla-ufrn'); ?></option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>

                    <!-- Limpar Filtros -->
                    <?php if (get_search_query() || isset($_GET['cat']) || isset($_GET['year'])) : ?>
                        <a href="<?php echo get_tag_link($tag_id); ?>"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 hover:text-purple-600 transition-colors">
                            <i class="fa-solid fa-times-circle"></i>
                            <?php _e('Limpar filtros', 'cchla-ufrn'); ?>
                        </a>
                    <?php endif; ?>

                </div>

            </div>
        </div>
    </section>

    <!-- Filtros Ativos (Pills) -->
    <?php
    $active_filters = array();

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

    if (isset($_GET['year']) && !empty($_GET['year'])) {
        $active_filters[] = array(
            'label' => __('Ano:', 'cchla-ufrn') . ' ' . $_GET['year'],
            'url' => remove_query_arg('year'),
            'color' => 'green'
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
        <section class="bg-purple-50 py-4 border-b border-purple-100">
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

                    <a href="<?php echo get_tag_link($tag_id); ?>"
                        class="text-sm text-purple-600 hover:text-purple-800 transition-colors ml-2 font-medium">
                        <?php _e('Limpar todos', 'cchla-ufrn'); ?>
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Tags Relacionadas -->
    <?php
    // Busca outras tags populares
    $related_tags = get_tags(array(
        'exclude' => $tag_id,
        'orderby' => 'count',
        'order' => 'DESC',
        'number' => 10,
        'hide_empty' => true
    ));

    if ($related_tags) :
    ?>
        <section class="bg-gray-50 py-6 border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-tags"></i>
                    <?php _e('Tags relacionadas:', 'cchla-ufrn'); ?>
                </h2>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($related_tags as $related_tag) : ?>
                        <a href="<?php echo get_tag_link($related_tag->term_id); ?>"
                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 hover:border-purple-500 hover:bg-purple-50 text-gray-700 hover:text-purple-700 rounded-full text-sm transition-all">
                            <span>#<?php echo esc_html($related_tag->name); ?></span>
                            <span class="text-xs text-gray-500">(<?php echo $related_tag->count; ?>)</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Listagem de Posts -->
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <?php if (have_posts()) : ?>

                <!-- Info de Resultados -->
                <div class="mb-8 text-sm text-gray-600">
                    <?php
                    global $wp_query;
                    $total = $wp_query->found_posts;
                    $current_page = max(1, get_query_var('paged'));
                    $per_page = get_query_var('posts_per_page');
                    $showing_from = (($current_page - 1) * $per_page) + 1;
                    $showing_to = min($current_page * $per_page, $total);

                    printf(
                        __('Exibindo %s-%s de %s publicações', 'cchla-ufrn'),
                        '<strong>' . number_format_i18n($showing_from) . '</strong>',
                        '<strong>' . number_format_i18n($showing_to) . '</strong>',
                        '<strong>' . number_format_i18n($total) . '</strong>'
                    );
                    ?>
                </div>

                <!-- Grid de Posts -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

                    <?php while (have_posts()) : the_post(); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class('group bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-xl transition-all duration-300 border border-gray-100'); ?>>

                            <!-- Imagem -->
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>" class="block aspect-video overflow-hidden bg-gray-100">
                                    <?php the_post_thumbnail('medium_large', array(
                                        'class' => 'w-full h-full object-cover group-hover:scale-110 transition-transform duration-500',
                                        'loading' => 'lazy'
                                    )); ?>
                                </a>
                            <?php endif; ?>

                            <div class="p-6">

                                <!-- Meta -->
                                <div class="flex items-center gap-3 text-xs text-gray-500 mb-3">
                                    <time datetime="<?php echo get_the_date('c'); ?>" class="flex items-center gap-1">
                                        <i class="fa-solid fa-calendar-alt"></i>
                                        <?php echo get_the_date(); ?>
                                    </time>

                                    <?php
                                    $categories = get_the_category();
                                    if ($categories) :
                                    ?>
                                        <span class="flex items-center gap-1">
                                            <i class="fa-solid fa-folder"></i>
                                            <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>"
                                                class="hover:text-purple-600">
                                                <?php echo esc_html($categories[0]->name); ?>
                                            </a>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (get_comments_number() > 0) : ?>
                                        <span class="flex items-center gap-1">
                                            <i class="fa-solid fa-comment"></i>
                                            <?php comments_number('0', '1', '%'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Título -->
                                <h2 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-purple-600 transition-colors">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>

                                <!-- Excerpt -->
                                <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                    <?php echo get_the_excerpt(); ?>
                                </p>

                                <!-- Footer -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <a href="<?php the_permalink(); ?>"
                                        class="inline-flex items-center gap-2 text-purple-600 hover:text-purple-800 font-medium text-sm group/link">
                                        <?php _e('Ler mais', 'cchla-ufrn'); ?>
                                        <i class="fa-solid fa-arrow-right text-xs group-hover/link:translate-x-1 transition-transform"></i>
                                    </a>

                                    <?php
                                    $author_id = get_the_author_meta('ID');
                                    $author_avatar = get_avatar_url($author_id, array('size' => 32));
                                    ?>
                                    <a href="<?php echo get_author_posts_url($author_id); ?>"
                                        class="flex items-center gap-2 text-xs text-gray-600 hover:text-purple-600 transition-colors"
                                        title="<?php printf(__('Ver todos os posts de %s', 'cchla-ufrn'), get_the_author()); ?>">
                                        <img src="<?php echo esc_url($author_avatar); ?>"
                                            alt="<?php echo esc_attr(get_the_author()); ?>"
                                            class="w-6 h-6 rounded-full"
                                            loading="lazy">
                                        <span><?php the_author(); ?></span>
                                    </a>
                                </div>

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
                    <nav class="mt-12" aria-label="<?php esc_attr_e('Paginação', 'cchla-ufrn'); ?>">
                        <ul class="flex justify-center items-center gap-2 flex-wrap">
                            <?php foreach ($pagination as $page) : ?>
                                <li><?php echo $page; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else : ?>

                <!-- Nenhum resultado -->
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-purple-100 rounded-full mb-6">
                        <i class="fa-solid fa-tag text-4xl text-purple-600"></i>
                    </div>

                    <h3 class="text-2xl font-bold text-gray-900 mb-3">
                        <?php _e('Nenhuma publicação encontrada', 'cchla-ufrn'); ?>
                    </h3>

                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        <?php
                        if (get_search_query() || isset($_GET['cat']) || isset($_GET['year'])) {
                            _e('Tente ajustar os filtros ou fazer uma nova busca.', 'cchla-ufrn');
                        } else {
                            printf(
                                __('Ainda não há publicações com a tag "%s".', 'cchla-ufrn'),
                                '<strong>' . esc_html($tag_name) . '</strong>'
                            );
                        }
                        ?>
                    </p>

                    <div class="flex gap-3 justify-center">
                        <?php if (get_search_query() || isset($_GET['cat']) || isset($_GET['year'])) : ?>
                            <a href="<?php echo get_tag_link($tag_id); ?>"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                <i class="fa-solid fa-times-circle"></i>
                                <?php _e('Limpar filtros', 'cchla-ufrn'); ?>
                            </a>
                        <?php endif; ?>

                        <a href="<?php echo get_post_type_archive_link('post'); ?>"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                            <i class="fa-solid fa-newspaper"></i>
                            <?php _e('Ver todas as notícias', 'cchla-ufrn'); ?>
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
        const filterYear = document.getElementById('filter-year');
        const filterOrder = document.getElementById('filter-order');

        function buildUrl() {
            const baseUrl = '<?php echo esc_url(get_tag_link($tag_id)); ?>';
            const params = new URLSearchParams(window.location.search);

            // Mantém a busca se existir
            const searchQuery = '<?php echo esc_js(get_search_query()); ?>';
            if (searchQuery) {
                params.set('s', searchQuery);
            } else {
                params.delete('s');
            }

            // Categoria
            if (filterCategory && filterCategory.value) {
                params.set('cat', filterCategory.value);
            } else {
                params.delete('cat');
            }

            // Ano
            if (filterYear && filterYear.value) {
                params.set('year', filterYear.value);
            } else {
                params.delete('year');
            }

            // Ordenação
            if (filterOrder && filterOrder.value) {
                const orderValue = filterOrder.value.split('-');
                if (orderValue.length === 2) {
                    params.set('orderby', orderValue[0]);
                    params.set('order', orderValue[1].toUpperCase());
                }
            } else {
                params.delete('orderby');
                params.delete('order');
            }

            // Remove paginação
            params.delete('paged');

            // Constrói URL final
            const queryString = params.toString();
            const finalUrl = queryString ? baseUrl + '?' + queryString : baseUrl;

            window.location.href = finalUrl;
        }

        // Event listeners
        if (filterCategory) {
            filterCategory.addEventListener('change', buildUrl);
        }

        if (filterYear) {
            filterYear.addEventListener('change', buildUrl);
        }

        if (filterOrder) {
            filterOrder.addEventListener('change', buildUrl);
        }

    })();
</script>

<!-- Estilos Customizados -->
<style>
    /* Paginação */
    .pagination {
        display: flex;
        gap: 0.5rem;
    }

    .pagination a,
    .pagination span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        height: 2.5rem;
        padding: 0 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .pagination a {
        background: white;
        border: 1px solid #e5e7eb;
        color: #6b7280;
    }

    .pagination a:hover {
        background: #f3f4f6;
        border-color: #9333ea;
        color: #9333ea;
    }

    .pagination .current {
        background: #9333ea;
        border: 1px solid #9333ea;
        color: white;
    }

    .pagination .dots {
        border: none;
        background: none;
        color: #9ca3af;
    }

    /* Line clamp fallback */
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<?php get_footer(); ?>