<?php

/**
 * Archive Template - Publicações
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

get_header();
?>


<!-- Breadcrumb -->
<?php cchla_breadcrumb(); ?>

<main class="bg-[#F4F6F9]">

    <!-- Cabeçalho -->
    <section class="bg-[#EFF2FB] py-12 border-b border-b-[#BAC6ED]">
        <div class="container mx-auto px-4 max-w-screen-xl">
            <p class="text-xs text-gray-500 uppercase tracking-wider border-b border-gray-300 pb-1 inline-block mb-4">
                <?php esc_html_e('PRODUÇÃO NO CCHLA', 'cchla-ufrn'); ?>
            </p>
            <h1 class="text-4xl font-bold text-gray-900 mb-3 max-sm:text-2xl">
                <?php
                if (is_tax('tipo_publicacao')) {
                    single_term_title(__('Publicações: ', 'cchla-ufrn'));
                } else {
                    esc_html_e('Todas as Publicações', 'cchla-ufrn');
                }
                ?>
            </h1>
            <p class="text-gray-600 text-base">
                <?php
                if (is_tax('tipo_publicacao')) {
                    $term = get_queried_object();
                    echo $term->description ? esc_html($term->description) : esc_html__('Confira as publicações desta categoria', 'cchla-ufrn');
                } else {
                    esc_html_e('Explore toda a produção acadêmica do CCHLA', 'cchla-ufrn');
                }
                ?>
            </p>
        </div>
    </section>

    <!-- Filtros por Tipo -->
    <?php
    $terms = get_terms(array(
        'taxonomy' => 'tipo_publicacao',
        'hide_empty' => true,
    ));

    if ($terms && !is_wp_error($terms)) :
    ?>
        <section class="bg-white border-b border-gray-200 py-6">
            <div class="container mx-auto px-4 max-w-screen-xl">
                <div class="flex flex-wrap gap-3 items-center">
                    <span class="text-sm font-semibold text-gray-700">
                        <?php esc_html_e('Filtrar por tipo:', 'cchla-ufrn'); ?>
                    </span>

                    <a href="<?php echo esc_url(get_post_type_archive_link('publicacoes')); ?>"
                        class="px-4 py-2 text-sm rounded-full border <?php echo !is_tax() ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:border-blue-400'; ?> transition-colors">
                        <?php esc_html_e('Todas', 'cchla-ufrn'); ?>
                    </a>

                    <?php foreach ($terms as $term) :
                        $is_current = is_tax('tipo_publicacao', $term->slug);
                    ?>
                        <a href="<?php echo esc_url(get_term_link($term)); ?>"
                            class="px-4 py-2 text-sm rounded-full border <?php echo $is_current ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:border-blue-400'; ?> transition-colors">
                            <?php echo esc_html($term->name); ?>
                            <span class="ml-1 text-xs opacity-75">(<?php echo $term->count; ?>)</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Barra de Busca e Ordenação -->
    <section class="bg-white border-b border-gray-200 py-4">
        <div class="container mx-auto px-4 max-w-screen-xl">
            <div class="flex flex-col md:flex-row gap-4 justify-between items-center">

                <!-- Busca -->
                <form method="get" action="<?php echo esc_url(get_post_type_archive_link('publicacoes')); ?>" class="flex-1 max-w-md">
                    <div class="relative">
                        <input
                            type="text"
                            name="s"
                            value="<?php echo get_search_query(); ?>"
                            placeholder="<?php esc_attr_e('Buscar publicações...', 'cchla-ufrn'); ?>"
                            class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <input type="hidden" name="post_type" value="publicacoes">
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
                            <i class="fa-solid fa-search"></i>
                        </button>
                    </div>
                </form>

                <!-- Ordenação -->
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600">
                        <?php esc_html_e('Ordenar:', 'cchla-ufrn'); ?>
                    </span>
                    <select id="publicacoes-order" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="date-desc" <?php selected(get_query_var('orderby'), ''); ?>>
                            <?php esc_html_e('Mais recentes', 'cchla-ufrn'); ?>
                        </option>
                        <option value="date-asc">
                            <?php esc_html_e('Mais antigas', 'cchla-ufrn'); ?>
                        </option>
                        <option value="title-asc">
                            <?php esc_html_e('Título (A-Z)', 'cchla-ufrn'); ?>
                        </option>
                        <option value="title-desc">
                            <?php esc_html_e('Título (Z-A)', 'cchla-ufrn'); ?>
                        </option>
                    </select>
                </div>

            </div>
        </div>
    </section>

    <script>
        document.getElementById('publicacoes-order')?.addEventListener('change', function() {
            const value = this.value.split('-');
            const url = new URL(window.location.href);
            url.searchParams.set('orderby', value[0]);
            url.searchParams.set('order', value[1]);
            window.location.href = url.toString();
        });
    </script>

    <!-- Listagem de Publicações -->
    <section class="py-12">
        <div class="container mx-auto px-4 max-w-screen-xl">

            <?php if (have_posts()) : ?>

                <div class="grid grid-cols-1 sm:grid-cols-1 lg:grid-cols-3 gap-10 sm:gap-6 md:gap-8">

                    <?php while (have_posts()) : the_post();
                        $autores = get_post_meta(get_the_ID(), '_publicacao_autores', true);
                        $isbn = get_post_meta(get_the_ID(), '_publicacao_isbn', true);
                        $link_externo = get_post_meta(get_the_ID(), '_publicacao_link_externo', true);
                        $ano = get_post_meta(get_the_ID(), '_publicacao_ano', true);
                        $tipos = get_the_terms(get_the_ID(), 'tipo_publicacao');
                        $tipo_nome = ($tipos && !is_wp_error($tipos)) ? $tipos[0]->name : 'Livro';

                        $link_url = $link_externo ? $link_externo : get_permalink();
                        $link_target = $link_externo ? '_blank' : '_self';
                        $link_rel = $link_externo ? 'noopener noreferrer' : '';
                    ?>

                        <!-- CARD -->
                        <article class="group flex flex-col justify-between bg-white rounded-md p-6 hover:shadow-lg transition-all duration-300">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs uppercase text-gray-600 font-medium tracking-wide">
                                        <?php echo esc_html($tipo_nome); ?>
                                    </p>
                                    <?php if ($ano) : ?>
                                        <span class="text-xs text-gray-500 font-semibold">
                                            <?php echo esc_html($ano); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <h3 class="font-semibold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">
                                    <a href="<?php echo esc_url($link_url); ?>"
                                        target="<?php echo esc_attr($link_target); ?>"
                                        <?php if ($link_rel) echo 'rel="' . esc_attr($link_rel) . '"'; ?>>
                                        <?php the_title(); ?>
                                    </a>
                                </h3>

                                <?php if ($autores) : ?>
                                    <p class="text-sm text-gray-600">
                                        <?php
                                        if (strpos($autores, ',') !== false) {
                                            echo esc_html__('Organizadores: ', 'cchla-ufrn') . esc_html($autores);
                                        } else {
                                            echo esc_html__('Autor: ', 'cchla-ufrn') . esc_html($autores);
                                        }
                                        ?>
                                    </p>
                                <?php endif; ?>

                                <?php if ($isbn) : ?>
                                    <p class="text-sm text-gray-500">
                                        <?php echo esc_html($isbn); ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="flex justify-between items-end mt-4">
                                <?php if (has_post_thumbnail()) : ?>
                                    <figure class="max-md:hidden">
                                        <a href="<?php echo esc_url($link_url); ?>"
                                            target="<?php echo esc_attr($link_target); ?>"
                                            <?php if ($link_rel) echo 'rel="' . esc_attr($link_rel) . '"'; ?>>
                                            <?php the_post_thumbnail('publicacao-thumb', array(
                                                'class' => 'w-24 h-32 object-cover rounded-md shadow-sm group-hover:scale-105 transition-transform duration-300',
                                                'alt' => get_the_title()
                                            )); ?>
                                        </a>
                                    </figure>
                                <?php else : ?>
                                    <div class="max-md:hidden w-24 h-32 bg-gray-200 rounded-md flex items-center justify-center">
                                        <i class="fa-solid fa-book text-gray-400 text-2xl"></i>
                                    </div>
                                <?php endif; ?>

                                <a href="<?php echo esc_url($link_url); ?>"
                                    target="<?php echo esc_attr($link_target); ?>"
                                    <?php if ($link_rel) echo 'rel="' . esc_attr($link_rel) . '"'; ?>
                                    class="flex items-center gap-1 text-blue-600 text-sm font-medium hover:underline focus:outline-none focus:ring-2 focus:ring-blue-300 rounded <?php echo !has_post_thumbnail() ? 'ml-auto' : ''; ?>">
                                    <?php esc_html_e('Leia mais', 'cchla-ufrn'); ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </a>
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

                    <style>
                        .pagination {
                            display: flex;
                            gap: 0.5rem;
                        }

                        .pagination .page-numbers {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            min-width: 2.5rem;
                            height: 2.5rem;
                            padding: 0 0.75rem;
                            border: 1px solid #d1d5db;
                            border-radius: 0.375rem;
                            background: white;
                            color: #374151;
                            font-size: 0.875rem;
                            transition: all 0.2s;
                        }

                        .pagination .page-numbers:hover {
                            border-color: #3b82f6;
                            color: #3b82f6;
                            background: #eff6ff;
                        }

                        .pagination .page-numbers.current {
                            background: #3b82f6;
                            color: white;
                            border-color: #3b82f6;
                        }

                        .pagination .page-numbers.dots {
                            border: none;
                        }
                    </style>
                <?php endif; ?>

            <?php else : ?>

                <!-- Nenhum resultado -->
                <div class="text-center py-16">
                    <i class="fa-solid fa-book-open text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-semibold text-gray-700 mb-2">
                        <?php esc_html_e('Nenhuma publicação encontrada', 'cchla-ufrn'); ?>
                    </h3>
                    <p class="text-gray-500 mb-6">
                        <?php
                        if (get_search_query()) {
                            printf(
                                esc_html__('Nenhum resultado para "%s"', 'cchla-ufrn'),
                                '<strong>' . esc_html(get_search_query()) . '</strong>'
                            );
                        } else {
                            esc_html_e('Tente filtrar por outro tipo ou ajustar sua busca', 'cchla-ufrn');
                        }
                        ?>
                    </p>
                    <a href="<?php echo esc_url(get_post_type_archive_link('publicacoes')); ?>"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                        <i class="fa-solid fa-arrow-left"></i>
                        <?php esc_html_e('Ver todas as publicações', 'cchla-ufrn'); ?>
                    </a>
                </div>

            <?php endif; ?>

        </div>
    </section>

</main>

<?php
get_footer();
