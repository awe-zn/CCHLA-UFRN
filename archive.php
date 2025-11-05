<?php

/**
 * Archive Template - Universal
 *
 * Template para todos os tipos de arquivos (categorias, tags, autores, CPTs)
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

get_header();

// Detecta o tipo de arquivo
$is_cpt_archive = is_post_type_archive();
$post_type = get_post_type();
$queried_object = get_queried_object();
?>

<h1>Arquivo - archive.php</h1>

<!-- Breadcrumb -->
<?php cchla_breadcrumb(); ?>

<main class="bg-white">

    <!-- Cabeçalho do Arquivo -->
    <section class="bg-gray-50 py-12 border-b border-gray-200">
        <div class="container mx-auto px-4 max-w-screen-xl">

            <?php
            // Define título e descrição baseado no tipo de arquivo
            $archive_title = '';
            $archive_description = '';
            $archive_label = '';

            if (is_category()) {
                $archive_label = __('Categoria', 'cchla-ufrn');
                $archive_title = single_cat_title('', false);
                $archive_description = category_description();
            } elseif (is_tag()) {
                $archive_label = __('Tag', 'cchla-ufrn');
                $archive_title = single_tag_title('', false);
                $archive_description = tag_description();
            } elseif (is_author()) {
                $archive_label = __('Autor', 'cchla-ufrn');
                $author = get_queried_object();
                $archive_title = $author->display_name;
                $archive_description = get_the_author_meta('description', $author->ID);
            } elseif (is_date()) {
                $archive_label = __('Arquivo', 'cchla-ufrn');
                if (is_day()) {
                    $archive_title = get_the_date();
                } elseif (is_month()) {
                    $archive_title = get_the_date('F Y');
                } elseif (is_year()) {
                    $archive_title = get_the_date('Y');
                }
            } elseif (is_tax()) {
                // Taxonomia customizada
                $term = get_queried_object();
                $tax_obj = get_taxonomy($term->taxonomy);
                $archive_label = $tax_obj->labels->singular_name;
                $archive_title = $term->name;
                $archive_description = $term->description;
            } elseif (is_post_type_archive()) {
                // Arquivo de Custom Post Type
                $post_type_obj = get_post_type_object(get_query_var('post_type'));
                $archive_label = __('Arquivo', 'cchla-ufrn');
                $archive_title = $post_type_obj->labels->name;
                $archive_description = $post_type_obj->description;
            }
            ?>

            <?php if ($archive_label) : ?>
                <p class="text-xs text-gray-500 uppercase tracking-wider border-b border-gray-300 pb-1 inline-block mb-4">
                    <?php echo esc_html($archive_label); ?>
                </p>
            <?php endif; ?>

            <h1 class="text-4xl font-bold text-blue-700 mb-3 max-sm:text-2xl">
                <?php echo esc_html($archive_title); ?>
            </h1>

            <?php if ($archive_description) : ?>
                <div class="text-gray-600 text-base max-w-3xl">
                    <?php echo wp_kses_post(wpautop($archive_description)); ?>
                </div>
            <?php endif; ?>

            <!-- Contador de resultados -->
            <div class="mt-6 text-sm text-gray-500">
                <?php
                global $wp_query;
                if ($wp_query->found_posts > 0) {
                    printf(
                        _n('%s resultado encontrado', '%s resultados encontrados', $wp_query->found_posts, 'cchla-ufrn'),
                        '<strong>' . number_format_i18n($wp_query->found_posts) . '</strong>'
                    );
                }
                ?>
            </div>

        </div>
    </section>

    <!-- Filtros e Busca -->
    <section class="bg-white border-b border-gray-200 py-6 sticky top-0 z-10 shadow-sm">
        <div class="container mx-auto px-4 max-w-screen-xl">

            <?php
            // Determina quais filtros exibir baseado no contexto
            $show_category_filter = !is_post_type_archive() || get_post_type() === 'post';
            $show_tag_filter = !is_post_type_archive() || get_post_type() === 'post';
            $show_author_filter = !is_post_type_archive() || get_post_type() === 'post';
            $show_date_filter = true;
            $show_custom_tax_filter = false;
            $custom_taxonomy = '';

            // Detecta taxonomias customizadas para CPTs
            if (is_post_type_archive() || is_tax()) {
                $current_post_type = is_tax() ? get_taxonomy($queried_object->taxonomy)->object_type[0] : get_query_var('post_type');

                // Mapeia CPTs para suas taxonomias
                $cpt_taxonomies = array(
                    'publicacoes' => 'tipo_publicacao',
                    'acesso_rapido' => 'categoria_acesso',
                );

                if (isset($cpt_taxonomies[$current_post_type])) {
                    $show_custom_tax_filter = true;
                    $custom_taxonomy = $cpt_taxonomies[$current_post_type];
                    $show_category_filter = false;
                    $show_tag_filter = false;
                    $show_author_filter = false;
                }
            }
            ?>

            <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">

                <!-- Busca -->
                <form method="get" action="<?php echo esc_url(home_url('/')); ?>" class="flex-1 max-w-md">
                    <div class="relative">
                        <input
                            type="text"
                            name="s"
                            value="<?php echo get_search_query(); ?>"
                            placeholder="<?php esc_attr_e('Buscar...', 'cchla-ufrn'); ?>"
                            class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">

                        <?php if (is_post_type_archive()) : ?>
                            <input type="hidden" name="post_type" value="<?php echo esc_attr(get_query_var('post_type')); ?>">
                        <?php endif; ?>

                        <?php if (is_category()) : ?>
                            <input type="hidden" name="cat" value="<?php echo get_queried_object_id(); ?>">
                        <?php endif; ?>

                        <?php if (is_tag()) : ?>
                            <input type="hidden" name="tag" value="<?php echo get_queried_object()->slug; ?>">
                        <?php endif; ?>

                        <?php if (is_author()) : ?>
                            <input type="hidden" name="author" value="<?php echo get_queried_object_id(); ?>">
                        <?php endif; ?>

                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
                            <i class="fa-solid fa-search"></i>
                        </button>
                    </div>
                </form>

                <!-- Filtros -->
                <div class="flex flex-wrap gap-3 items-center">

                    <!-- Filtro de Categorias (Posts padrão) -->
                    <?php if ($show_category_filter && !is_category()) :
                        $categories = get_categories(array('hide_empty' => true));
                        if ($categories) :
                    ?>
                            <div class="relative">
                                <select id="filter-category" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                                    <option value=""><?php esc_html_e('Todas as categorias', 'cchla-ufrn'); ?></option>
                                    <?php foreach ($categories as $category) : ?>
                                        <option value="<?php echo esc_attr($category->term_id); ?>" <?php selected(is_category($category->term_id)); ?>>
                                            <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                    <?php endif;
                    endif; ?>

                    <!-- Filtro de Tags (Posts padrão) -->
                    <?php if ($show_tag_filter && !is_tag()) :
                        $tags = get_tags(array('hide_empty' => true, 'number' => 20));
                        if ($tags) :
                    ?>
                            <div class="relative">
                                <select id="filter-tag" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                                    <option value=""><?php esc_html_e('Todas as tags', 'cchla-ufrn'); ?></option>
                                    <?php foreach ($tags as $tag) : ?>
                                        <option value="<?php echo esc_attr($tag->slug); ?>" <?php selected(is_tag($tag->slug)); ?>>
                                            <?php echo esc_html($tag->name); ?> (<?php echo $tag->count; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                    <?php endif;
                    endif; ?>

                    <!-- Filtro de Taxonomias Customizadas (CPTs) -->
                    <?php if ($show_custom_tax_filter && $custom_taxonomy) :
                        $terms = get_terms(array(
                            'taxonomy' => $custom_taxonomy,
                            'hide_empty' => true,
                        ));
                        if ($terms && !is_wp_error($terms)) :
                            $tax_obj = get_taxonomy($custom_taxonomy);
                    ?>
                            <div class="relative">
                                <select id="filter-custom-tax" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                                    <option value=""><?php printf(esc_html__('Todos %s', 'cchla-ufrn'), esc_html($tax_obj->labels->name)); ?></option>
                                    <?php foreach ($terms as $term) : ?>
                                        <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(is_tax($custom_taxonomy, $term->slug)); ?>>
                                            <?php echo esc_html($term->name); ?> (<?php echo $term->count; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                    <?php endif;
                    endif; ?>

                    <!-- Filtro de Ano -->
                    <?php if ($show_date_filter && !is_date()) :
                        $years = $wpdb->get_col("
                            SELECT DISTINCT YEAR(post_date) 
                            FROM $wpdb->posts 
                            WHERE post_status = 'publish' 
                            AND post_type " . (is_post_type_archive() ? "= '" . esc_sql(get_query_var('post_type')) . "'" : "= 'post'") . "
                            ORDER BY post_date DESC
                        ");
                        if ($years) :
                    ?>
                            <div class="relative">
                                <select id="filter-year" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                                    <option value=""><?php esc_html_e('Todos os anos', 'cchla-ufrn'); ?></option>
                                    <?php foreach ($years as $year) : ?>
                                        <option value="<?php echo esc_attr($year); ?>" <?php selected(is_year() && get_query_var('year') == $year); ?>>
                                            <?php echo esc_html($year); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                    <?php endif;
                    endif; ?>

                    <!-- Ordenação -->
                    <div class="relative">
                        <select id="filter-order" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                            <option value="date-desc" <?php selected(get_query_var('orderby'), ''); ?>>
                                <?php esc_html_e('Mais recentes', 'cchla-ufrn'); ?>
                            </option>
                            <option value="date-asc" <?php selected(get_query_var('order'), 'ASC'); ?>>
                                <?php esc_html_e('Mais antigas', 'cchla-ufrn'); ?>
                            </option>
                            <option value="title-asc">
                                <?php esc_html_e('Título (A-Z)', 'cchla-ufrn'); ?>
                            </option>
                            <option value="title-desc">
                                <?php esc_html_e('Título (Z-A)', 'cchla-ufrn'); ?>
                            </option>
                            <option value="comment_count-desc">
                                <?php esc_html_e('Mais comentados', 'cchla-ufrn'); ?>
                            </option>
                        </select>
                    </div>

                    <!-- Botão Limpar Filtros -->
                    <?php if (get_search_query() || is_filtered()) : ?>
                        <a href="<?php echo esc_url(get_post_type_archive_link(get_post_type())); ?>"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-blue-600 transition-colors">
                            <i class="fa-solid fa-times-circle"></i>
                            <?php esc_html_e('Limpar filtros', 'cchla-ufrn'); ?>
                        </a>
                    <?php endif; ?>

                </div>

            </div>

        </div>
    </section>

    <!-- JavaScript para Filtros -->
    <script>
        (function() {
            const filterCategory = document.getElementById('filter-category');
            const filterTag = document.getElementById('filter-tag');
            const filterCustomTax = document.getElementById('filter-custom-tax');
            const filterYear = document.getElementById('filter-year');
            const filterOrder = document.getElementById('filter-order');

            function buildUrl() {
                const url = new URL(window.location.href);
                const params = new URLSearchParams(url.search);

                // Categoria
                if (filterCategory) {
                    const catValue = filterCategory.value;
                    if (catValue) {
                        params.set('cat', catValue);
                    } else {
                        params.delete('cat');
                    }
                }

                // Tag
                if (filterTag) {
                    const tagValue = filterTag.value;
                    if (tagValue) {
                        params.set('tag', tagValue);
                    } else {
                        params.delete('tag');
                    }
                }

                // Taxonomia Customizada
                if (filterCustomTax) {
                    const taxonomy = filterCustomTax.dataset.taxonomy || '<?php echo esc_js($custom_taxonomy); ?>';
                    const termValue = filterCustomTax.value;
                    if (termValue) {
                        params.set('tax', taxonomy);
                        params.set('term', termValue);
                    } else {
                        params.delete('tax');
                        params.delete('term');
                    }
                }

                // Ano
                if (filterYear) {
                    const yearValue = filterYear.value;
                    if (yearValue) {
                        params.set('year', yearValue);
                    } else {
                        params.delete('year');
                    }
                }

                // Ordenação
                if (filterOrder) {
                    const orderValue = filterOrder.value.split('-');
                    if (orderValue[0] && orderValue[1]) {
                        params.set('orderby', orderValue[0]);
                        params.set('order', orderValue[1]);
                    } else {
                        params.delete('orderby');
                        params.delete('order');
                    }
                }

                // Remove página se existir
                params.delete('paged');

                url.search = params.toString();
                window.location.href = url.toString();
            }

            // Event Listeners
            if (filterCategory) filterCategory.addEventListener('change', buildUrl);
            if (filterTag) filterTag.addEventListener('change', buildUrl);
            if (filterCustomTax) filterCustomTax.addEventListener('change', buildUrl);
            if (filterYear) filterYear.addEventListener('change', buildUrl);
            if (filterOrder) filterOrder.addEventListener('change', buildUrl);
        })();
    </script>

    <!-- Filtros Ativos (Pills) -->
    <?php
    $active_filters = array();

    if (is_category()) {
        $active_filters[] = array(
            'label' => __('Categoria:', 'cchla-ufrn') . ' ' . single_cat_title('', false),
            'url' => remove_query_arg('cat'),
        );
    }

    if (is_tag()) {
        $active_filters[] = array(
            'label' => __('Tag:', 'cchla-ufrn') . ' ' . single_tag_title('', false),
            'url' => remove_query_arg('tag'),
        );
    }

    if (is_tax()) {
        $term = get_queried_object();
        $active_filters[] = array(
            'label' => get_taxonomy($term->taxonomy)->labels->singular_name . ': ' . $term->name,
            'url' => get_post_type_archive_link(get_post_type()),
        );
    }

    if (get_query_var('year')) {
        $active_filters[] = array(
            'label' => __('Ano:', 'cchla-ufrn') . ' ' . get_query_var('year'),
            'url' => remove_query_arg('year'),
        );
    }

    if (get_search_query()) {
        $active_filters[] = array(
            'label' => __('Busca:', 'cchla-ufrn') . ' "' . get_search_query() . '"',
            'url' => remove_query_arg('s'),
        );
    }

    if (!empty($active_filters)) :
    ?>
        <section class="bg-gray-50 py-4 border-b border-gray-200">
            <div class="container mx-auto px-4 max-w-screen-xl">
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-sm font-semibold text-gray-700">
                        <?php esc_html_e('Filtros ativos:', 'cchla-ufrn'); ?>
                    </span>

                    <?php foreach ($active_filters as $filter) : ?>
                        <a href="<?php echo esc_url($filter['url']); ?>"
                            class="inline-flex items-center gap-2 px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm hover:bg-blue-200 transition-colors">
                            <?php echo esc_html($filter['label']); ?>
                            <i class="fa-solid fa-times text-xs"></i>
                        </a>
                    <?php endforeach; ?>

                    <a href="<?php echo esc_url(get_post_type_archive_link(get_post_type()) ?: home_url('/')); ?>"
                        class="text-sm text-gray-600 hover:text-blue-600 transition-colors ml-2">
                        <?php esc_html_e('Limpar todos', 'cchla-ufrn'); ?>
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Listagem de Posts -->
    <section class="py-12">
        <div class="container mx-auto px-4 max-w-screen-xl">

            <?php if (have_posts()) : ?>

                <!-- Grid de Posts -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                    <?php while (have_posts()) : the_post(); ?>

                        <?php
                        // Usa template part específico do post type se existir
                        $template_slug = 'parts/extra/template-parts/content';
                        $template_name = get_post_type();

                        if (locate_template($template_slug . '-' . $template_name . '.php')) {
                            get_template_part($template_slug, $template_name);
                        } else {
                            get_template_part($template_slug, 'archive');
                        }
                        ?>

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
                    <i class="fa-solid fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-semibold text-gray-700 mb-2">
                        <?php esc_html_e('Nenhum resultado encontrado', 'cchla-ufrn'); ?>
                    </h3>
                    <p class="text-gray-500 mb-6">
                        <?php
                        if (get_search_query()) {
                            printf(
                                esc_html__('Sua busca por "%s" não retornou resultados.', 'cchla-ufrn'),
                                '<strong>' . esc_html(get_search_query()) . '</strong>'
                            );
                        } else {
                            esc_html_e('Tente ajustar os filtros ou fazer uma nova busca.', 'cchla-ufrn');
                        }
                        ?>
                    </p>
                    <a href="<?php echo esc_url(home_url('/')); ?>"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                        <i class="fa-solid fa-home"></i>
                        <?php esc_html_e('Voltar para home', 'cchla-ufrn'); ?>
                    </a>
                </div>

            <?php endif; ?>

        </div>
    </section>

</main>

<?php
get_footer();
