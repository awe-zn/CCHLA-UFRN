<?php

/**
 * Archive Template - Acesso Rápido
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

get_header();
?>

<!-- Breadcrumb -->
<?php get_template_part('template-parts/breadcrumb'); ?>

<main class="bg-white text-gray-800">

    <!-- Cabeçalho -->
    <section class="bg-gray-50 py-12">
        <div class="container mx-auto px-4 max-w-screen-xl">
            <p class="text-gray-600 text-sm font-light pb-2 border-b border-blue-200 w-fit mb-4">
                <?php esc_html_e('ACESSO RÁPIDO', 'cchla-ufrn'); ?>
            </p>
            <h1 class="text-4xl font-bold text-blue-700 mb-3">
                <?php esc_html_e('Todos os Sistemas', 'cchla-ufrn'); ?>
            </h1>
            <p class="text-lg text-gray-600">
                <?php esc_html_e('Acesse os principais sistemas da UFRN e do CCHLA', 'cchla-ufrn'); ?>
            </p>
        </div>
    </section>

    <!-- Filtros por Categoria -->
    <?php
    $terms = get_terms(array(
        'taxonomy' => 'categoria_acesso',
        'hide_empty' => true,
    ));

    if ($terms && !is_wp_error($terms)) :
    ?>
        <section class="bg-white border-b border-gray-200 py-6">
            <div class="container mx-auto px-4 max-w-screen-xl">
                <div class="flex flex-wrap gap-3 items-center">
                    <span class="text-sm font-semibold text-gray-700">
                        <?php esc_html_e('Filtrar por:', 'cchla-ufrn'); ?>
                    </span>

                    <a href="<?php echo esc_url(get_post_type_archive_link('acesso_rapido')); ?>"
                        class="px-4 py-2 text-sm rounded-full border <?php echo !is_tax() ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:border-blue-400'; ?> transition-colors">
                        <?php esc_html_e('Todos', 'cchla-ufrn'); ?>
                    </a>

                    <?php foreach ($terms as $term) :
                        $is_current = is_tax('categoria_acesso', $term->slug);
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

    <!-- Listagem de Sistemas -->
    <section class="py-12">
        <div class="container mx-auto px-4 max-w-screen-xl">

            <?php if (have_posts()) : ?>

                <div class="grid grid-cols-3 gap-8 max-lg:grid-cols-2 max-md:grid-cols-1">
                    <?php while (have_posts()) : the_post();
                        $descricao = get_post_meta(get_the_ID(), '_acesso_descricao', true);
                        $link_externo = get_post_meta(get_the_ID(), '_acesso_link_externo', true);
                        $abertura = get_post_meta(get_the_ID(), '_acesso_abertura', true);
                        $tipo_icone = get_post_meta(get_the_ID(), '_acesso_tipo_icone', true);
                        $icone_url = get_post_meta(get_the_ID(), '_acesso_icone_url', true);
                        $icone_classe = get_post_meta(get_the_ID(), '_acesso_icone_classe', true);

                        $abertura = $abertura ? $abertura : '_blank';
                        $rel = ($abertura === '_blank') ? 'noopener noreferrer' : '';

                        // Pega as categorias
                        $categorias = get_the_terms(get_the_ID(), 'categoria_acesso');
                    ?>

                        <!-- ITEM -->
                        <article class="group flex flex-col border border-blue-200 rounded-sm hover:bg-blue-50 hover:border-blue-400 transition-all duration-200">
                            <a href="<?php echo esc_url($link_externo); ?>"
                                target="<?php echo esc_attr($abertura); ?>"
                                <?php if ($rel) echo 'rel="' . esc_attr($rel) . '"'; ?>
                                class="flex flex-col gap-2 p-5 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-300">

                                <?php if ($tipo_icone === 'classe' && $icone_classe) : ?>
                                    <div class="mb-2">
                                        <i class="<?php echo esc_attr($icone_classe); ?>"
                                            style="font-size: 34px; color: #1e40af;"
                                            aria-hidden="true"></i>
                                    </div>
                                <?php elseif ($tipo_icone === 'imagem' && $icone_url) : ?>
                                    <div class="mb-2">
                                        <img src="<?php echo esc_url($icone_url); ?>"
                                            alt="<?php echo esc_attr(get_the_title()); ?>"
                                            class="w-[34px] h-[34px] object-contain">
                                    </div>
                                <?php endif; ?>

                                <strong class="text-blue-900 group-hover:text-blue-700 transition-colors text-lg">
                                    <?php the_title(); ?>
                                </strong>

                                <?php if ($descricao) : ?>
                                    <p class="text-sm text-gray-600 flex-grow">
                                        <?php echo esc_html($descricao); ?>
                                    </p>
                                <?php endif; ?>
                            </a>

                            <?php if ($categorias && !is_wp_error($categorias)) : ?>
                                <div class="px-5 pb-4 pt-2 border-t border-gray-100">
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach ($categorias as $categoria) : ?>
                                            <a href="<?php echo esc_url(get_term_link($categoria)); ?>"
                                                class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors">
                                                <?php echo esc_html($categoria->name); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </article>

                    <?php endwhile; ?>
                </div>

                <!-- Paginação -->
                <?php
                $pagination = paginate_links(array(
                    'prev_text' => '<i class="fa-solid fa-chevron-left"></i>',
                    'next_text' => '<i class="fa-solid fa-chevron-right"></i>',
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
                    <i class="fa-solid fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-semibold text-gray-700 mb-2">
                        <?php esc_html_e('Nenhum sistema encontrado', 'cchla-ufrn'); ?>
                    </h3>
                    <p class="text-gray-500">
                        <?php esc_html_e('Tente filtrar por outra categoria', 'cchla-ufrn'); ?>
                    </p>
                </div>

            <?php endif; ?>

        </div>
    </section>

</main>

<?php
get_footer();
