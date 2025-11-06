<?php

/**
 * Single Template - Serviço
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

get_header();
?>

<?php while (have_posts()) : the_post();
    $icone_tipo = get_post_meta(get_the_ID(), '_servico_icone_tipo', true);
    $icone_classe = get_post_meta(get_the_ID(), '_servico_icone_classe', true);
    $icone_svg = get_post_meta(get_the_ID(), '_servico_icone_svg', true);
    $link_externo = get_post_meta(get_the_ID(), '_servico_link_externo', true);
    $responsavel = get_post_meta(get_the_ID(), '_servico_responsavel', true);
    $contato = get_post_meta(get_the_ID(), '_servico_contato', true);
    $horario = get_post_meta(get_the_ID(), '_servico_horario', true);
    $localizacao = get_post_meta(get_the_ID(), '_servico_localizacao', true);
    $categorias = get_the_terms(get_the_ID(), 'categoria_servico');
?>

    <!-- Breadcrumb -->
    <?php cchla_breadcrumb();     ?>

    <main class="bg-white" style="min-height: 50vh;">

        <article class="py-12">
            <div class="container mx-auto px-4 max-w-4xl">

                <!-- Cabeçalho -->
                <header class="mb-8">

                    <!-- Categoria -->
                    <?php if ($categorias && !is_wp_error($categorias)) : ?>
                        <div class="mb-4">
                            <a href="<?php echo esc_url(get_term_link($categorias[0])); ?>"
                                class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full hover:bg-blue-200 transition-colors">
                                <?php echo esc_html($categorias[0]->name); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Ícone e Título -->
                    <div class="flex items-start gap-6 mb-6">
                        <div class="text-blue-600 flex-shrink-0" style="width: 64px; height: 64px;">
                            <?php if ($icone_tipo === 'svg' && $icone_svg) : ?>
                                <?php echo wp_kses_post($icone_svg); ?>
                            <?php elseif ($icone_classe) : ?>
                                <i class="<?php echo esc_attr($icone_classe); ?>" style="font-size: 64px;" aria-hidden="true"></i>
                            <?php else : ?>
                                <i class="fa-solid fa-circle-info" style="font-size: 64px;" aria-hidden="true"></i>
                            <?php endif; ?>
                        </div>

                        <div class="flex-1">
                            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                                <?php the_title(); ?>
                            </h1>

                            <?php if (has_excerpt()) : ?>
                                <p class="text-xl text-gray-600 leading-relaxed">
                                    <?php the_excerpt(); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Link Externo -->
                    <?php if ($link_externo) : ?>
                        <div class="mb-6">
                            <a href="<?php echo esc_url($link_externo); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <i class="fa-solid fa-external-link-alt"></i>
                                <?php esc_html_e('Acessar Serviço', 'cchla-ufrn'); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                </header>

                <!-- Informações do Serviço -->
                <?php if ($responsavel || $contato || $horario || $localizacao) : ?>
                    <aside class="bg-gray-50 rounded-lg p-6 mb-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">
                            <?php esc_html_e('Informações', 'cchla-ufrn'); ?>
                        </h2>

                        <div class="space-y-4">
                            <?php if ($responsavel) : ?>
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-user text-blue-600 mt-1"></i>
                                    <div>
                                        <strong class="block text-sm text-gray-600 mb-1">
                                            <?php esc_html_e('Responsável', 'cchla-ufrn'); ?>
                                        </strong>
                                        <p class="text-gray-900"><?php echo esc_html($responsavel); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($contato) : ?>
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-phone text-blue-600 mt-1"></i>
                                    <div>
                                        <strong class="block text-sm text-gray-600 mb-1">
                                            <?php esc_html_e('Contato', 'cchla-ufrn'); ?>
                                        </strong>
                                        <p class="text-gray-900"><?php echo esc_html($contato); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($horario) : ?>
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-clock text-blue-600 mt-1"></i>
                                    <div>
                                        <strong class="block text-sm text-gray-600 mb-1">
                                            <?php esc_html_e('Horário de Funcionamento', 'cchla-ufrn'); ?>
                                        </strong>
                                        <p class="text-gray-900"><?php echo esc_html($horario); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($localizacao) : ?>
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-location-dot text-blue-600 mt-1"></i>
                                    <div>
                                        <strong class="block text-sm text-gray-600 mb-1">
                                            <?php esc_html_e('Localização', 'cchla-ufrn'); ?>
                                        </strong>
                                        <p class="text-gray-900"><?php echo esc_html($localizacao); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </aside>
                <?php endif; ?>

                <!-- Conteúdo -->
                <div class="prose prose-lg max-w-none">
                    <?php the_content(); ?>
                </div>

                <!-- Imagem destacada -->
                <?php if (has_post_thumbnail()) : ?>
                    <figure class="mt-8">
                        <?php the_post_thumbnail('large', array(
                            'class' => 'w-full rounded-lg shadow-lg'
                        )); ?>
                    </figure>
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
        </article>

        <!-- Serviços Relacionados -->
        <?php
        if ($categorias && !is_wp_error($categorias)) {
            $related_args = array(
                'post_type' => 'servicos',
                'posts_per_page' => 3,
                'post__not_in' => array(get_the_ID()),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'categoria_servico',
                        'field' => 'term_id',
                        'terms' => $categorias[0]->term_id,
                    ),
                ),
            );

            $related_query = new WP_Query($related_args);

            if ($related_query->have_posts()) :
        ?>
                <section class="bg-gray-50 py-12">
                    <div class="container mx-auto px-4 max-w-screen-xl">
                        <h2 class="text-2xl font-bold text-gray-900 mb-8">
                            <?php esc_html_e('Outros Serviços', 'cchla-ufrn'); ?>
                        </h2>

                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <?php while ($related_query->have_posts()) : $related_query->the_post();
                                $rel_icone_tipo = get_post_meta(get_the_ID(), '_servico_icone_tipo', true);
                                $rel_icone_classe = get_post_meta(get_the_ID(), '_servico_icone_classe', true);
                                $rel_icone_svg = get_post_meta(get_the_ID(), '_servico_icone_svg', true);
                                $rel_link_externo = get_post_meta(get_the_ID(), '_servico_link_externo', true);

                                $rel_link = $rel_link_externo ? $rel_link_externo : get_permalink();
                            ?>
                                <article class="bg-white rounded-lg p-5 border border-blue-200 hover:shadow-md transition-shadow">
                                    <a href="<?php echo esc_url($rel_link); ?>" class="block">
                                        <div class="text-blue-600 mb-3" style="width: 32px; height: 32px;">
                                            <?php if ($rel_icone_tipo === 'svg' && $rel_icone_svg) : ?>
                                                <?php echo wp_kses_post($rel_icone_svg); ?>
                                            <?php elseif ($rel_icone_classe) : ?>
                                                <i class="<?php echo esc_attr($rel_icone_classe); ?>" style="font-size: 32px;"></i>
                                            <?php endif; ?>
                                        </div>

                                        <h3 class="font-semibold text-gray-900 mb-2">
                                            <?php the_title(); ?>
                                        </h3>

                                        <p class="text-sm text-gray-600 mb-3">
                                            <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                                        </p>

                                        <span class="text-blue-600 text-sm font-medium hover:underline">
                                            <?php esc_html_e('Ver mais', 'cchla-ufrn'); ?> →
                                        </span>
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
