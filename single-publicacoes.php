<?php

/**
 * Single Template - Publicação
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

get_header();
?>

<?php while (have_posts()) : the_post();
    $autores = get_post_meta(get_the_ID(), '_publicacao_autores', true);
    $isbn = get_post_meta(get_the_ID(), '_publicacao_isbn', true);
    $paginas = get_post_meta(get_the_ID(), '_publicacao_paginas', true);
    $link_externo = get_post_meta(get_the_ID(), '_publicacao_link_externo', true);
    $ano = get_post_meta(get_the_ID(), '_publicacao_ano', true);
    $editora = get_post_meta(get_the_ID(), '_publicacao_editora', true);
    $tipos = get_the_terms(get_the_ID(), 'tipo_publicacao');
?>

    <!-- Breadcrumb -->
    <?php get_template_part('parts/extra/template-parts/breadcrumb'); ?>

    <main class="bg-white">

        <article class="py-12">
            <div class="container mx-auto px-4 max-w-5xl">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

                    <!-- Coluna da Capa -->
                    <aside class="lg:col-span-1">
                        <?php if (has_post_thumbnail()) : ?>
                            <figure class="sticky top-8">
                                <?php the_post_thumbnail('publicacao-capa', array(
                                    'class' => 'w-full rounded-lg shadow-xl',
                                    'alt' => get_the_title()
                                )); ?>
                            </figure>
                        <?php else : ?>
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-xl p-12 flex items-center justify-center aspect-[2/3]">
                                <i class="fa-solid fa-book text-blue-300 text-6xl"></i>
                            </div>
                        <?php endif; ?>

                        <?php if ($link_externo) : ?>
                            <a href="<?php echo esc_url($link_externo); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-6 w-full flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <i class="fa-solid fa-external-link-alt"></i>
                                <?php esc_html_e('Acessar Publicação', 'cchla-ufrn'); ?>
                            </a>
                        <?php endif; ?>
                    </aside>

                    <!-- Coluna do Conteúdo -->
                    <div class="lg:col-span-2">

                        <!-- Tipo -->
                        <?php if ($tipos && !is_wp_error($tipos)) : ?>
                            <div class="mb-4">
                                <a href="<?php echo esc_url(get_term_link($tipos[0])); ?>"
                                    class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full hover:bg-blue-200 transition-colors">
                                    <?php echo esc_html($tipos[0]->name); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Título -->
                        <h1 class="text-4xl font-bold text-gray-900 mb-6 leading-tight">
                            <?php the_title(); ?>
                        </h1>

                        <!-- Metadados -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-8 space-y-3">
                            <?php if ($autores) : ?>
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-user-pen text-blue-600 mt-1"></i>
                                    <div>
                                        <strong class="block text-sm text-gray-600 mb-1">
                                            <?php
                                            echo strpos($autores, ',') !== false
                                                ? esc_html__('Autores/Organizadores', 'cchla-ufrn')
                                                : esc_html__('Autor', 'cchla-ufrn');
                                            ?>
                                        </strong>
                                        <p class="text-gray-900"><?php echo esc_html($autores); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($editora) : ?>
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-building text-blue-600 mt-1"></i>
                                    <div>
                                        <strong class="block text-sm text-gray-600 mb-1">
                                            <?php esc_html_e('Editora', 'cchla-ufrn'); ?>
                                        </strong>
                                        <p class="text-gray-900"><?php echo esc_html($editora); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($ano) : ?>
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-calendar text-blue-600 mt-1"></i>
                                    <div>
                                        <strong class="block text-sm text-gray-600 mb-1">
                                            <?php esc_html_e('Ano de Publicação', 'cchla-ufrn'); ?>
                                        </strong>
                                        <p class="text-gray-900"><?php echo esc_html($ano); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($isbn) : ?>
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-barcode text-blue-600 mt-1"></i>
                                    <div>
                                        <strong class="block text-sm text-gray-600 mb-1">
                                            <?php esc_html_e('ISBN / ISSN', 'cchla-ufrn'); ?>
                                        </strong>
                                        <p class="text-gray-900 font-mono text-sm"><?php echo esc_html($isbn); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($paginas) : ?>
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-file-lines text-blue-600 mt-1"></i>
                                    <div>
                                        <strong class="block text-sm text-gray-600 mb-1">
                                            <?php esc_html_e('Número de Páginas', 'cchla-ufrn'); ?>
                                        </strong>
                                        <p class="text-gray-900"><?php echo esc_html($paginas); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Descrição -->
                        <?php if (has_excerpt() || get_the_content()) : ?>
                            <div class="prose prose-lg max-w-none mb-8">
                                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                                    <?php esc_html_e('Sobre esta publicação', 'cchla-ufrn'); ?>
                                </h2>

                                <?php if (has_excerpt()) : ?>
                                    <div class="text-lg text-gray-700 leading-relaxed mb-4">
                                        <?php the_excerpt(); ?>
                                    </div>
                                <?php endif; ?>

                                <?php the_content(); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Botões de Compartilhamento -->
                        <div class="border-t border-gray-200 pt-6 mt-8">
                            <h3 class="text-sm font-semibold text-gray-700 mb-4">
                                <?php esc_html_e('Compartilhar:', 'cchla-ufrn'); ?>
                            </h3>
                            <div class="flex gap-3">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center justify-center w-10 h-10 bg-[#1877F2] text-white rounded-full hover:bg-[#145dbf] transition-colors"
                                    aria-label="<?php esc_attr_e('Compartilhar no Facebook', 'cchla-ufrn'); ?>">
                                    <i class="fa-brands fa-facebook-f"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center justify-center w-10 h-10 bg-[#1DA1F2] text-white rounded-full hover:bg-[#1a8cd8] transition-colors"
                                    aria-label="<?php esc_attr_e('Compartilhar no Twitter', 'cchla-ufrn'); ?>">
                                    <i class="fa-brands fa-twitter"></i>
                                </a>
                                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode(get_the_title() . ' - ' . get_permalink()); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center justify-center w-10 h-10 bg-[#25D366] text-white rounded-full hover:bg-[#1da851] transition-colors"
                                    aria-label="<?php esc_attr_e('Compartilhar no WhatsApp', 'cchla-ufrn'); ?>">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </a>
                                <button onclick="navigator.clipboard.writeText('<?php echo esc_js(get_permalink()); ?>'); alert('Link copiado!');"
                                    class="flex items-center justify-center w-10 h-10 bg-gray-600 text-white rounded-full hover:bg-gray-700 transition-colors"
                                    aria-label="<?php esc_attr_e('Copiar link', 'cchla-ufrn'); ?>">
                                    <i class="fa-solid fa-link"></i>
                                </button>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </article>

        <!-- Publicações Relacionadas -->
        <?php
        if ($tipos && !is_wp_error($tipos)) {
            $related_args = array(
                'post_type' => 'publicacoes',
                'posts_per_page' => 3,
                'post__not_in' => array(get_the_ID()),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'tipo_publicacao',
                        'field' => 'term_id',
                        'terms' => $tipos[0]->term_id,
                    ),
                ),
            );

            $related_query = new WP_Query($related_args);

            if ($related_query->have_posts()) :
        ?>
                <section class="bg-gray-50 py-12">
                    <div class="container mx-auto px-4 max-w-screen-xl">
                        <h2 class="text-2xl font-bold text-gray-900 mb-8">
                            <?php esc_html_e('Publicações Relacionadas', 'cchla-ufrn'); ?>
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <?php while ($related_query->have_posts()) : $related_query->the_post();
                                $rel_link = get_post_meta(get_the_ID(), '_publicacao_link_externo', true);
                                $rel_link = $rel_link ? $rel_link : get_permalink();
                            ?>
                                <article class="bg-white rounded-lg p-5 shadow hover:shadow-lg transition-shadow">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <a href="<?php echo esc_url($rel_link); ?>">
                                            <?php the_post_thumbnail('publicacao-thumb', array(
                                                'class' => 'w-full h-48 object-cover rounded mb-4'
                                            )); ?>
                                        </a>
                                    <?php endif; ?>

                                    <h3 class="font-semibold text-gray-900 mb-2">
                                        <a href="<?php echo esc_url($rel_link); ?>" class="hover:text-blue-600">
                                            <?php the_title(); ?>
                                        </a>
                                    </h3>

                                    <a href="<?php echo esc_url($rel_link); ?>"
                                        class="text-blue-600 text-sm font-medium hover:underline">
                                        <?php esc_html_e('Ver mais', 'cchla-ufrn'); ?> →
                                    </a>
                                </article>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </section>
        <?php
            endif;
            wp_reset_postdata();
        }
        ?>

    </main>

<?php endwhile; ?>

<?php
get_footer();
