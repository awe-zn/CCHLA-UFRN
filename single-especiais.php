<?php

/**
 * Single Template - Especial
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

get_header();
?>

<?php while (have_posts()) : the_post();
    $video_tipo = get_post_meta(get_the_ID(), '_especial_video_tipo', true);
    $video_url = get_post_meta(get_the_ID(), '_especial_video_url', true);
    $video_embed = get_post_meta(get_the_ID(), '_especial_video_embed', true);
    $video_arquivo_id = get_post_meta(get_the_ID(), '_especial_video_arquivo_id', true);
    $link_projeto = get_post_meta(get_the_ID(), '_especial_link_projeto', true);
    $categorias = get_the_terms(get_the_ID(), 'categoria_especial');

    $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'large');
?>

    <!-- Breadcrumb -->
    <?php cchla_breadcrumb();     ?>

    <main class="bg-white">

        <!-- Hero Section -->
        <section class="bg-gradient-to-r from-[#183AB3] to-[#1E47C7] text-white py-16 bg-[url('<?php echo get_template_directory_uri(); ?>/assets/img/bg-textura.png')] bg-cover bg-center">
            <div class=" container mx-auto px-4 max-w-5xl">

                <?php if ($categorias && !is_wp_error($categorias)) : ?>
                    <div class="mb-4">
                        <a href="<?php echo esc_url(get_term_link($categorias[0])); ?>"
                            class="inline-block px-3 py-1 bg-white/20 text-white text-sm font-medium rounded-full hover:bg-white/30 transition-colors">
                            <?php echo esc_html($categorias[0]->name); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">
                    <?php the_title(); ?>
                </h1>

                <?php if (has_excerpt()) : ?>
                    <p class="text-xl text-blue-100 leading-relaxed">
                        <?php the_excerpt(); ?>
                    </p>
                <?php endif; ?>

            </div>
        </section>

        <!-- Vídeo -->
        <section class="py-12">
            <div class="container mx-auto px-4 max-w-5xl">

                <div class="mb-12">
                    <?php if ($video_tipo === 'url' && $video_url) : ?>
                        <div class="video-wrapper aspect-video rounded-lg overflow-hidden shadow-2xl">
                            <?php echo wp_oembed_get($video_url, array('width' => 1200)); ?>
                        </div>

                    <?php elseif ($video_tipo === 'embed' && $video_embed) : ?>
                        <div class="video-wrapper aspect-video rounded-lg overflow-hidden shadow-2xl">
                            <?php echo wp_kses_post($video_embed); ?>
                        </div>

                    <?php elseif ($video_tipo === 'arquivo' && $video_arquivo_id) : ?>
                        <video
                            src="<?php echo esc_url(wp_get_attachment_url($video_arquivo_id)); ?>"
                            poster="<?php echo esc_url($thumbnail); ?>"
                            class="w-full rounded-lg shadow-2xl"
                            controls>
                            <?php esc_html_e('Seu navegador não suporta a tag de vídeo.', 'cchla-ufrn'); ?>
                        </video>

                    <?php elseif ($thumbnail) : ?>
                        <img src="<?php echo esc_url($thumbnail); ?>"
                            alt="<?php the_title_attribute(); ?>"
                            class="w-full rounded-lg shadow-2xl">
                    <?php endif; ?>
                </div>

                <!-- Conteúdo -->
                <div class="prose prose-lg max-w-none mb-12">
                    <?php the_content(); ?>
                </div>

                <!-- Link do Projeto -->
                <?php if ($link_projeto) : ?>
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-8 mb-12">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">
                            <?php esc_html_e('Acesse o Projeto', 'cchla-ufrn'); ?>
                        </h3>
                        <p class="text-gray-700 mb-6">
                            <?php esc_html_e('Visite o site oficial do projeto para mais informações e recursos.', 'cchla-ufrn'); ?>
                        </p>
                        <a href="<?php echo esc_url($link_projeto); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fa-solid fa-external-link-alt"></i>
                            <?php esc_html_e('Visitar Site do Projeto', 'cchla-ufrn'); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Compartilhamento -->
                <div class="border-t border-gray-200 pt-8 no-print">
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
        </section>

        <!-- Outros Especiais -->
        <?php
        if ($categorias && !is_wp_error($categorias)) {
            $related_args = array(
                'post_type' => 'especiais',
                'posts_per_page' => 3,
                'post__not_in' => array(get_the_ID()),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'categoria_especial',
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
                            <?php esc_html_e('Outros Especiais', 'cchla-ufrn'); ?>
                        </h2>

                        <div class="grid gap-8 md:grid-cols-3">
                            <?php while ($related_query->have_posts()) : $related_query->the_post();
                                $rel_link_projeto = get_post_meta(get_the_ID(), '_especial_link_projeto', true);
                                $rel_link = $rel_link_projeto ? $rel_link_projeto : get_permalink();
                            ?>
                                <article class="bg-white rounded-lg overflow-hidden shadow hover:shadow-lg transition-shadow">
                                    <a href="<?php echo esc_url($rel_link); ?>">
                                        <div class="aspect-video overflow-hidden">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <?php the_post_thumbnail('medium', array('class' => 'w-full h-full object-cover hover:scale-105 transition-transform duration-300')); ?>
                                            <?php else : ?>
                                                <div class="w-full h-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center">
                                                    <i class="fa-solid fa-video text-4xl text-white opacity-50"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="p-5">
                                            <h3 class="font-semibold text-gray-900 mb-2">
                                                <?php the_title(); ?>
                                            </h3>

                                            <span class="text-blue-600 text-sm font-medium">
                                                <?php esc_html_e('Assistir →', 'cchla-ufrn'); ?>
                                            </span>
                                        </div>
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

<style>
    .video-wrapper {
        position: relative;
        padding-bottom: 56.25%;
        height: 0;
        overflow: hidden;
    }

    .video-wrapper iframe,
    .video-wrapper video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .prose {
        max-width: none;
    }

    .prose h2 {
        font-size: 1.875rem;
        font-weight: 700;
        color: #1e40af;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }

    .prose p {
        margin-bottom: 1rem;
        line-height: 1.8;
    }
</style>

<?php
get_footer();
