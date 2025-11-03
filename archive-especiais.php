<?php

/**
 * Archive Template - Especiais
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

get_header();
?>

<!-- Breadcrumb -->
<?php get_template_part('template-parts/breadcrumb'); ?>

<main class="bg-white">

    <!-- Cabeçalho -->
    <section class="bg-[#183AB3] text-white py-12">
        <div class="container mx-auto px-4 max-w-screen-xl">
            <h1 class="text-4xl font-bold mb-3">
                <?php
                if (is_tax('categoria_especial')) {
                    single_term_title(__('Especiais: ', 'cchla-ufrn'));
                } else {
                    esc_html_e('Todos os Especiais CCHLA', 'cchla-ufrn');
                }
                ?>
            </h1>
            <p class="text-blue-200 text-base">
                <?php
                if (is_tax('categoria_especial')) {
                    $term = get_queried_object();
                    echo $term->description ? esc_html($term->description) : esc_html__('Confira os especiais desta categoria', 'cchla-ufrn');
                } else {
                    esc_html_e('Conheça todos os projetos especiais que fazem a diferença na sociedade', 'cchla-ufrn');
                }
                ?>
            </p>
        </div>
    </section>

    <!-- Filtros por Categoria -->
    <?php
    $terms = get_terms(array(
        'taxonomy' => 'categoria_especial',
        'hide_empty' => true,
    ));

    if ($terms && !is_wp_error($terms)) :
    ?>
        <section class="bg-gray-50 border-b border-gray-200 py-6">
            <div class="container mx-auto px-4 max-w-screen-xl">
                <div class="flex flex-wrap gap-3 items-center">
                    <span class="text-sm font-semibold text-gray-700">
                        <?php esc_html_e('Filtrar por categoria:', 'cchla-ufrn'); ?>
                    </span>

                    <a href="<?php echo esc_url(get_post_type_archive_link('especiais')); ?>"
                        class="px-4 py-2 text-sm rounded-full border <?php echo !is_tax() ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:border-blue-400'; ?> transition-colors">
                        <?php esc_html_e('Todas', 'cchla-ufrn'); ?>
                    </a>

                    <?php foreach ($terms as $term) :
                        $is_current = is_tax('categoria_especial', $term->slug);
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

    <!-- Listagem de Especiais -->
    <section class="py-12">
        <div class="container mx-auto px-4 max-w-screen-xl">

            <?php if (have_posts()) : ?>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">

                    <?php while (have_posts()) : the_post();
                        $link_projeto = get_post_meta(get_the_ID(), '_especial_link_projeto', true);
                        $link_url = $link_projeto ? $link_projeto : get_permalink();
                        $link_target = $link_projeto ? '_blank' : '_self';
                    ?>

                        <!-- Card -->
                        <article class="group bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition-shadow">
                            <a href="<?php echo esc_url($link_url); ?>"
                                target="<?php echo esc_attr($link_target); ?>"
                                <?php if ($link_projeto) echo 'rel="noopener noreferrer"'; ?>>

                                <!-- Thumbnail -->
                                <div class="relative overflow-hidden aspect-video">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('large', array(
                                            'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300'
                                        )); ?>
                                    <?php else : ?>
                                        <div class="w-full h-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center">
                                            <i class="fa-solid fa-video text-6xl text-white opacity-50"></i>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Badge de vídeo -->
                                    <div class="absolute top-3 left-3 bg-black/70 text-white px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1">
                                        <i class="fa-solid fa-play"></i>
                                        <?php esc_html_e('Vídeo', 'cchla-ufrn'); ?>
                                    </div>
                                </div>

                                <!-- Conteúdo -->
                                <div class="p-6">
                                    <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors">
                                        <?php the_title(); ?>
                                    </h3>

                                    <?php if (has_excerpt()) : ?>
                                        <p class="text-gray-600 text-sm mb-4">
                                            <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                                        </p>
                                    <?php endif; ?>

                                    <span class="text-blue-600 font-medium inline-flex items-center gap-1">
                                        <?php echo $link_projeto ? esc_html__('Acessar projeto', 'cchla-ufrn') : esc_html__('Saiba mais', 'cchla-ufrn'); ?>
                                        <i class="fa-solid fa-arrow-right text-xs"></i>
                                    </span>
                                </div>

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
                    <i class="fa-solid fa-video text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-semibold text-gray-700 mb-2">
                        <?php esc_html_e('Nenhum especial encontrado', 'cchla-ufrn'); ?>
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
