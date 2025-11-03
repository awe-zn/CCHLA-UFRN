<?php

/**
 * Archive Template - Serviços
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

get_header();
?>

<!-- Breadcrumb -->
<?php get_template_part('parts/extra/template-parts/breadcrumb'); ?>

<main class="bg-white">

    <!-- Cabeçalho -->
    <section class="bg-gray-50 py-12 border-b border-gray-200">
        <div class="container mx-auto px-4 max-w-screen-xl">
            <h1 class="text-4xl font-bold text-gray-900 mb-3">
                <?php
                if (is_tax('categoria_servico')) {
                    single_term_title(__('Serviços: ', 'cchla-ufrn'));
                } else {
                    esc_html_e('Todos os serviços', 'cchla-ufrn');
                }
                ?>
            </h1>
            <p class="text-gray-600 text-base">
                <?php
                if (is_tax('categoria_servico')) {
                    $term = get_queried_object();
                    echo $term->description ? esc_html($term->description) : esc_html__('Confira os serviços desta categoria', 'cchla-ufrn');
                } else {
                    esc_html_e('Conheça todos os serviços oferecidos pelo CCHLA à comunidade', 'cchla-ufrn');
                }
                ?>
            </p>
        </div>
    </section>

    <!-- Filtros por Categoria -->
    <?php
    $terms = get_terms(array(
        'taxonomy' => 'categoria_servico',
        'hide_empty' => true,
    ));

    if ($terms && !is_wp_error($terms) && false) :
    ?>
        <section class="bg-white border-b border-gray-200 py-6">
            <div class="container mx-auto px-4 max-w-screen-xl">
                <div class="flex flex-wrap gap-3 items-center">
                    <span class="text-sm font-semibold text-gray-700">
                        <?php esc_html_e('Filtrar por categoria:', 'cchla-ufrn'); ?>
                    </span>

                    <a href="<?php echo esc_url(get_post_type_archive_link('servicos')); ?>"
                        class="px-4 py-2 text-sm rounded-full border <?php echo !is_tax() ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:border-blue-400'; ?> transition-colors">
                        <?php esc_html_e('Todas', 'cchla-ufrn'); ?>
                    </a>

                    <?php foreach ($terms as $term) :
                        $is_current = is_tax('categoria_servico', $term->slug);
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

    <!-- Listagem de Serviços -->
    <section class="py-12">
        <div class="container mx-auto px-4 max-w-screen-xl">

            <?php if (have_posts()) : ?>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                    <?php while (have_posts()) : the_post();
                        $icone_tipo = get_post_meta(get_the_ID(), '_servico_icone_tipo', true);
                        $icone_classe = get_post_meta(get_the_ID(), '_servico_icone_classe', true);
                        $icone_svg = get_post_meta(get_the_ID(), '_servico_icone_svg', true);
                        $link_externo = get_post_meta(get_the_ID(), '_servico_link_externo', true);
                        $link_botao_texto = get_post_meta(get_the_ID(), '_servico_link_botao_texto', true);

                        $link_url = $link_externo ? $link_externo : get_permalink();
                        $link_target = $link_externo ? '_blank' : '_self';
                        $link_rel = $link_externo ? 'noopener noreferrer' : '';
                        $botao_texto = $link_botao_texto ? $link_botao_texto : __('Leia mais', 'cchla-ufrn');
                    ?>

                        <!-- Card -->
                        <article class="block p-6 border border-blue-200 rounded-lg hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                            <a href="<?php echo esc_url($link_url); ?>"
                                target="<?php echo esc_attr($link_target); ?>"
                                <?php if ($link_rel) echo 'rel="' . esc_attr($link_rel) . '"'; ?>
                                class="block focus:outline-none focus:ring-2 focus:ring-blue-400 rounded">

                                <!-- Ícone -->
                                <div class="text-blue-600 mb-3" style="width: 32px; height: 32px;">
                                    <?php if ($icone_tipo === 'svg' && $icone_svg) : ?>
                                        <?php echo wp_kses_post($icone_svg); ?>
                                    <?php elseif ($icone_classe) : ?>
                                        <i class="<?php echo esc_attr($icone_classe); ?>" style="font-size: 32px;" aria-hidden="true"></i>
                                    <?php else : ?>
                                        <i class="fa-solid fa-circle-info" style="font-size: 32px;" aria-hidden="true"></i>
                                    <?php endif; ?>
                                </div>

                                <!-- Título -->
                                <h3 class="font-semibold mb-2">
                                    <?php the_title(); ?>
                                </h3>

                                <!-- Descrição -->
                                <p class="text-sm text-zinc-600 mb-3">
                                    <?php echo has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 20); ?>
                                </p>

                                <!-- Link -->
                                <span class="text-blue-600 font-medium inline-flex items-center gap-1">
                                    <?php echo esc_html($botao_texto); ?>
                                    <span aria-hidden="true">→</span>
                                </span>
                            </a>
                        </article>

                    <?php endwhile; ?>

                </div>

                <!-- Paginação -->
                <?php
                $pagination = paginate_links(array(
                    'prev_text' => '<i class="fa-solid fa-chevron-left"></i> ' . __('Anterior', 'cchla-ufrn'),
                    'next_text' => __('Próxima', 'cchla-ufrn') . ' <i class="fa-solid fa-chevron-right"></i>',
                    'type' => 'array',
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
                    <i class="fa-solid fa-heart text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-semibold text-gray-700 mb-2">
                        <?php esc_html_e('Nenhum serviço encontrado', 'cchla-ufrn'); ?>
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
