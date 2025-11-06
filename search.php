<?php

/**
 * Template para Resultados de Busca
 * 
 * Busca em todos os tipos de conteúdo:
 * - Posts (Notícias)
 * - Páginas
 * - Departamentos
 * - Cursos
 * - Publicações
 * - Especiais
 * - Serviços
 * - Acesso Rápido
 *
 * @package CCHLA_UFRN
 * @since 2.0.0
 */

get_header();

// Termo de busca
$search_query = get_search_query();
$search_query_display = !empty($search_query) ? $search_query : __('todos os termos', 'cchla-ufrn');

// Estatísticas da busca
global $wp_query;
$total_results = $wp_query->found_posts;
$current_page = max(1, get_query_var('paged'));
?>

<!-- Breadcrumb -->
<?php cchla_breadcrumb(); ?>

<!-- Container Principal -->
<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Cabeçalho da Busca -->
        <header class="mb-8">
            <div class="bg-white rounded-lg shadow-sm p-8 border border-gray-200">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-search text-3xl text-blue-600"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            <?php
                            if ($total_results > 0) {
                                printf(
                                    esc_html(_n('%s resultado encontrado', '%s resultados encontrados', $total_results, 'cchla-ufrn')),
                                    '<span class="text-blue-600">' . number_format_i18n($total_results) . '</span>'
                                );
                            } else {
                                esc_html_e('Nenhum resultado encontrado', 'cchla-ufrn');
                            }
                            ?>
                        </h1>
                        <p class="text-gray-600 text-lg">
                            <?php
                            printf(
                                esc_html__('Busca por: %s', 'cchla-ufrn'),
                                '<strong class="text-gray-900">"' . esc_html($search_query_display) . '"</strong>'
                            );
                            ?>
                        </p>
                    </div>
                </div>

                <!-- Formulário de Busca -->
                <div class="mt-6">
                    <?php get_search_form(); ?>
                </div>
            </div>
        </header>

        <div class="grid lg:grid-cols-4 gap-8">

            <!-- Sidebar de Filtros -->
            <aside class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-filter text-blue-600"></i>
                        <?php esc_html_e('Filtrar Resultados', 'cchla-ufrn'); ?>
                    </h2>

                    <?php
                    // Conta resultados por tipo
                    $counts_by_type = cchla_get_search_results_count_by_type($search_query);
                    $current_post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
                    ?>

                    <nav class="space-y-2">
                        <!-- Todos -->
                        <a href="<?php echo esc_url(add_query_arg('s', $search_query, home_url('/'))); ?>"
                            class="flex items-center justify-between p-3 rounded-lg transition-all duration-200 <?php echo empty($current_post_type) ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50 text-gray-700'; ?>">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-globe w-5"></i>
                                <?php esc_html_e('Todos', 'cchla-ufrn'); ?>
                            </span>
                            <span class="text-sm font-semibold"><?php echo number_format_i18n($total_results); ?></span>
                        </a>

                        <!-- Por Tipo de Conteúdo -->
                        <?php
                        $post_type_filters = array(
                            'post' => array(
                                'label' => __('Notícias', 'cchla-ufrn'),
                                'icon' => 'fa-newspaper',
                                'color' => 'blue',
                            ),
                            'page' => array(
                                'label' => __('Páginas', 'cchla-ufrn'),
                                'icon' => 'fa-file',
                                'color' => 'gray',
                            ),
                            'departamentos' => array(
                                'label' => __('Departamentos', 'cchla-ufrn'),
                                'icon' => 'fa-building',
                                'color' => 'indigo',
                            ),
                            'cursos' => array(
                                'label' => __('Cursos', 'cchla-ufrn'),
                                'icon' => 'fa-graduation-cap',
                                'color' => 'green',
                            ),
                            'publicacoes' => array(
                                'label' => __('Publicações', 'cchla-ufrn'),
                                'icon' => 'fa-book',
                                'color' => 'purple',
                            ),
                            'especiais' => array(
                                'label' => __('Especiais', 'cchla-ufrn'),
                                'icon' => 'fa-video',
                                'color' => 'red',
                            ),
                            'servicos' => array(
                                'label' => __('Serviços', 'cchla-ufrn'),
                                'icon' => 'fa-hand-holding-heart',
                                'color' => 'yellow',
                            ),
                            'acesso_rapido' => array(
                                'label' => __('Sistemas', 'cchla-ufrn'),
                                'icon' => 'fa-link',
                                'color' => 'pink',
                            ),
                        );

                        foreach ($post_type_filters as $type => $config) {
                            $count = isset($counts_by_type[$type]) ? $counts_by_type[$type] : 0;

                            if ($count > 0) {
                                $is_active = ($current_post_type === $type);
                                $filter_url = add_query_arg(array('s' => $search_query, 'post_type' => $type), home_url('/'));
                        ?>
                                <a href="<?php echo esc_url($filter_url); ?>"
                                    class="flex items-center justify-between p-3 rounded-lg transition-all duration-200 <?php echo $is_active ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50 text-gray-700'; ?>">
                                    <span class="flex items-center gap-2">
                                        <i class="fa-solid <?php echo esc_attr($config['icon']); ?> w-5"></i>
                                        <?php echo esc_html($config['label']); ?>
                                    </span>
                                    <span class="text-sm font-semibold"><?php echo number_format_i18n($count); ?></span>
                                </a>
                        <?php
                            }
                        }
                        ?>
                    </nav>

                    <!-- Dica -->
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-100">
                        <p class="text-sm text-blue-800">
                            <i class="fa-solid fa-lightbulb mr-1"></i>
                            <strong><?php _e('Dica:', 'cchla-ufrn'); ?></strong>
                            <?php _e('Use aspas para buscar frases exatas. Ex: "inteligência artificial"', 'cchla-ufrn'); ?>
                        </p>
                    </div>
                </div>
            </aside>

            <!-- Resultados -->
            <main class="lg:col-span-3">
                <?php if (have_posts()) : ?>

                    <!-- Info da Paginação -->
                    <div class="mb-6 flex items-center justify-between text-sm text-gray-600">
                        <span>
                            <?php
                            $results_per_page = get_option('posts_per_page', 10);
                            $showing_start = (($current_page - 1) * $results_per_page) + 1;
                            $showing_end = min($total_results, $current_page * $results_per_page);

                            printf(
                                esc_html__('Mostrando %s - %s de %s', 'cchla-ufrn'),
                                '<strong>' . number_format_i18n($showing_start) . '</strong>',
                                '<strong>' . number_format_i18n($showing_end) . '</strong>',
                                '<strong>' . number_format_i18n($total_results) . '</strong>'
                            );
                            ?>
                        </span>
                    </div>

                    <!-- Lista de Resultados -->
                    <div class="space-y-4">
                        <?php
                        while (have_posts()) : the_post();
                            // Tenta carregar template específico, se não existir, usa fallback
                            $template = locate_template('template-parts/search/result-' . get_post_type() . '.php');

                            if ($template) {
                                get_template_part('template-parts/search/result', get_post_type());
                            } else {
                                // Fallback: template genérico
                                cchla_display_search_result_fallback();
                            }
                        endwhile;
                        ?>
                    </div>

                    <!-- Paginação -->
                    <div class="mt-12">
                        <?php
                        the_posts_pagination(array(
                            'mid_size' => 2,
                            'prev_text' => '<i class="fa-solid fa-chevron-left mr-2"></i>' . __('Anterior', 'cchla-ufrn'),
                            'next_text' => __('Próxima', 'cchla-ufrn') . '<i class="fa-solid fa-chevron-right ml-2"></i>',
                            'class' => 'flex justify-center gap-2',
                        ));
                        ?>
                    </div>

                <?php else : ?>

                    <!-- Nenhum Resultado -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fa-solid fa-magnifying-glass text-4xl text-gray-400"></i>
                        </div>

                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            <?php esc_html_e('Nenhum resultado encontrado', 'cchla-ufrn'); ?>
                        </h2>

                        <p class="text-gray-600 mb-8 max-w-md mx-auto">
                            <?php
                            printf(
                                esc_html__('Não encontramos nenhum resultado para "%s". Tente usar outros termos ou verifique a ortografia.', 'cchla-ufrn'),
                                '<strong>' . esc_html($search_query) . '</strong>'
                            );
                            ?>
                        </p>

                        <!-- Sugestões -->
                        <div class="bg-blue-50 rounded-lg p-6 max-w-2xl mx-auto text-left">
                            <h3 class="font-semibold text-gray-900 mb-3">
                                <?php esc_html_e('Sugestões:', 'cchla-ufrn'); ?>
                            </h3>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-check text-blue-600 mt-0.5"></i>
                                    <span><?php esc_html_e('Verifique a ortografia dos termos de busca', 'cchla-ufrn'); ?></span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-check text-blue-600 mt-0.5"></i>
                                    <span><?php esc_html_e('Tente usar palavras-chave diferentes', 'cchla-ufrn'); ?></span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-check text-blue-600 mt-0.5"></i>
                                    <span><?php esc_html_e('Use termos mais genéricos', 'cchla-ufrn'); ?></span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-check text-blue-600 mt-0.5"></i>
                                    <span><?php esc_html_e('Reduza o número de palavras', 'cchla-ufrn'); ?></span>
                                </li>
                            </ul>
                        </div>

                        <!-- Links Rápidos -->
                        <div class="mt-8 pt-8 border-t border-gray-200">
                            <p class="text-sm text-gray-600 mb-4">
                                <?php esc_html_e('Ou navegue pelas seções:', 'cchla-ufrn'); ?>
                            </p>
                            <div class="flex flex-wrap justify-center gap-3">
                                <a href="<?php echo esc_url(get_post_type_archive_link('departamentos')); ?>"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fa-solid fa-building"></i>
                                    <?php esc_html_e('Departamentos', 'cchla-ufrn'); ?>
                                </a>
                                <a href="<?php echo esc_url(get_post_type_archive_link('cursos')); ?>"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                    <?php esc_html_e('Cursos', 'cchla-ufrn'); ?>
                                </a>
                                <a href="<?php echo esc_url(get_post_type_archive_link('publicacoes')); ?>"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fa-solid fa-book"></i>
                                    <?php esc_html_e('Publicações', 'cchla-ufrn'); ?>
                                </a>
                                <a href="<?php echo esc_url(get_post_type_archive_link('servicos')); ?>"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fa-solid fa-hand-holding-heart"></i>
                                    <?php esc_html_e('Serviços', 'cchla-ufrn'); ?>
                                </a>
                                <a href="<?php echo esc_url(home_url('/')); ?>"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fa-solid fa-house"></i>
                                    <?php esc_html_e('Voltar ao Início', 'cchla-ufrn'); ?>
                                </a>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>
            </main>

        </div>

    </div>
</div>

<?php get_footer(); ?>