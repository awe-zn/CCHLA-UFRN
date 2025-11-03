<?php

/**
 * Template Part - Especial CCHLA
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

// Busca especial em destaque
$destaque_args = array(
    'post_type' => 'especiais',
    'posts_per_page' => 1,
    'meta_query' => array(
        array(
            'key' => '_especial_destaque_home',
            'value' => '1',
        ),
    ),
    'orderby' => 'menu_order',
    'order' => 'ASC',
);

$destaque_query = new WP_Query($destaque_args);

// Busca outros especiais para o carrossel
$carrossel_args = array(
    'post_type' => 'especiais',
    'posts_per_page' => 8,
    'meta_query' => array(
        array(
            'key' => '_especial_destaque_home',
            'compare' => 'NOT EXISTS',
        ),
    ),
    'orderby' => 'menu_order',
    'order' => 'ASC',
);

$carrossel_query = new WP_Query($carrossel_args);

if (!$destaque_query->have_posts() && !$carrossel_query->have_posts()) {
    return;
}
?>

<section class="bg-[#183AB3] text-[#DFEBF0]" id="section-especial">
    <!-- BLOCO SUPERIOR -->
    <div class="relative bg-[url('<?php echo get_template_directory_uri(); ?>/assets/img/bg-textura.png')] bg-cover bg-center">
        <div class="max-w-screen-xl mx-auto px-4 md:px-10 xl:px-0 py-16 space-y-10">
            <header class="space-y-2">
                <h2 class="text-3xl md:text-4xl font-light">
                    <?php esc_html_e('Especial CCHLA', 'cchla-ufrn'); ?>
                </h2>
                <p class="text-[#A1CBFF] text-base leading-relaxed">
                    <?php esc_html_e('Conheça projetos do CCHLA que fazem a diferença na sociedade.', 'cchla-ufrn'); ?>
                </p>
            </header>

            <!-- Destaque principal -->
            <?php if ($destaque_query->have_posts()) : ?>
                <?php while ($destaque_query->have_posts()) : $destaque_query->the_post();
                    $video_tipo = get_post_meta(get_the_ID(), '_especial_video_tipo', true);
                    $video_url = get_post_meta(get_the_ID(), '_especial_video_url', true);
                    $video_embed = get_post_meta(get_the_ID(), '_especial_video_embed', true);
                    $video_arquivo_id = get_post_meta(get_the_ID(), '_especial_video_arquivo_id', true);
                    $link_projeto = get_post_meta(get_the_ID(), '_especial_link_projeto', true);

                    $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'large');
                ?>

                    <article class="grid gap-10 lg:grid-cols-2 items-start">
                        <figure class="relative group">
                            <?php if ($video_tipo === 'url' && $video_url) : ?>
                                <div class="video-wrapper" style="position: relative; padding-bottom: 56.25%; height: 0;">
                                    <?php echo wp_oembed_get($video_url, array('width' => 800)); ?>
                                </div>

                            <?php elseif ($video_tipo === 'embed' && $video_embed) : ?>
                                <div class="video-wrapper">
                                    <?php echo wp_kses_post($video_embed); ?>
                                </div>

                            <?php elseif ($video_tipo === 'arquivo' && $video_arquivo_id) : ?>
                                <video
                                    src="<?php echo esc_url(wp_get_attachment_url($video_arquivo_id)); ?>"
                                    poster="<?php echo esc_url($thumbnail); ?>"
                                    class="w-full h-auto rounded-lg border border-blue-300"
                                    controls>
                                    <?php esc_html_e('Seu navegador não suporta a tag de vídeo.', 'cchla-ufrn'); ?>
                                </video>

                            <?php elseif ($thumbnail) : ?>
                                <img src="<?php echo esc_url($thumbnail); ?>"
                                    alt="<?php the_title_attribute(); ?>"
                                    class="w-full h-auto rounded-lg border border-blue-300">
                            <?php endif; ?>
                        </figure>

                        <div class="flex flex-col gap-4">
                            <h3 class="text-2xl font-semibold text-white">
                                <?php the_title(); ?>
                            </h3>
                            <div class="space-y-4 text-[#DFEBF0] text-base leading-relaxed">
                                <?php the_content(); ?>
                            </div>

                            <?php if ($link_projeto) : ?>
                                <a href="<?php echo esc_url($link_projeto); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center gap-2 text-white font-semibold hover:text-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-400 rounded transition">
                                    <?php esc_html_e('Acesse o link do projeto', 'cchla-ufrn'); ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </a>
                            <?php else : ?>
                                <a href="<?php the_permalink(); ?>"
                                    class="flex items-center gap-2 text-white font-semibold hover:text-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-400 rounded transition">
                                    <?php esc_html_e('Saiba mais sobre o projeto', 'cchla-ufrn'); ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>

                <?php endwhile; ?>
            <?php endif; ?>

            <?php wp_reset_postdata(); ?>
        </div>
    </div>

    <!-- BLOCO INFERIOR -->
    <?php if ($carrossel_query->have_posts()) : ?>
        <div class="bg-gradient-to-b from-[#183AB3] to-[#162556] text-[#B2C8FF] py-16 px-4 md:px-10 xl:px-0">
            <div class="max-w-screen-xl mx-auto flex flex-col gap-12">

                <!-- Carrossel -->
                <div class="relative">
                    <div id="carrossel-especiais"
                        class="overflow-x-auto scroll-smooth snap-x snap-mandatory flex gap-8 pb-4 xl:overflow-visible">

                        <?php while ($carrossel_query->have_posts()) : $carrossel_query->the_post();
                            $link_projeto = get_post_meta(get_the_ID(), '_especial_link_projeto', true);
                            $link_url = $link_projeto ? $link_projeto : get_permalink();
                        ?>

                            <a href="<?php echo esc_url($link_url); ?>"
                                class="min-w-[260px] snap-start group"
                                <?php if ($link_projeto) echo 'target="_blank" rel="noopener noreferrer"'; ?>>
                                <figure class="flex flex-col gap-4 font-semibold">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('medium', array(
                                            'class' => 'rounded-lg transition duration-300 group-hover:ring-4 group-hover:ring-blue-400',
                                            'alt' => get_the_title()
                                        )); ?>
                                    <?php else : ?>
                                        <div class="w-full h-48 bg-blue-800 rounded-lg flex items-center justify-center">
                                            <i class="fa-solid fa-video text-4xl text-blue-400"></i>
                                        </div>
                                    <?php endif; ?>

                                    <figcaption class="group-hover:text-[#E5EDFF] transition duration-300">
                                        <?php the_title(); ?>
                                    </figcaption>
                                </figure>
                            </a>

                        <?php endwhile; ?>

                    </div>

                    <!-- Botões -->
                    <div class="flex justify-between items-center mt-10">
                        <a href="<?php echo esc_url(get_post_type_archive_link('especiais')); ?>"
                            class="inline-flex items-center gap-3 border border-[#ACBCE6] px-6 py-3 rounded-md text-sm text-[#E5EDFF] font-semibold hover:bg-white hover:text-[#183AB3] transition focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <?php esc_html_e('Ver todos os especiais', 'cchla-ufrn'); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>

                        <div class="flex gap-4 text-[#EFEFF0] lg:hidden">
                            <button id="prev-especial" aria-label="<?php esc_attr_e('Anterior', 'cchla-ufrn'); ?>"
                                class="flex items-center justify-center w-10 h-10 border border-[#EFEFF0] rounded-full hover:bg-white hover:text-[#193CB8] transition focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <button id="next-especial" aria-label="<?php esc_attr_e('Próximo', 'cchla-ufrn'); ?>"
                                class="flex items-center justify-center w-10 h-10 border border-[#EFEFF0] rounded-full hover:bg-white hover:text-[#193CB8] transition focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>
</section>

<!-- SCRIPT DO CARROSSEL -->
<script>
    (function() {
        const carrossel = document.getElementById("carrossel-especiais");
        const prevBtn = document.getElementById("prev-especial");
        const nextBtn = document.getElementById("next-especial");

        if (!carrossel || !prevBtn || !nextBtn) return;

        const scrollAmount = 300;

        prevBtn.addEventListener("click", () => {
            carrossel.scrollBy({
                left: -scrollAmount,
                behavior: "smooth"
            });
        });

        nextBtn.addEventListener("click", () => {
            carrossel.scrollBy({
                left: scrollAmount,
                behavior: "smooth"
            });
        });

        // Acessibilidade: setas controláveis por teclado
        document.addEventListener("keydown", (e) => {
            if (e.key === "ArrowLeft") {
                carrossel.scrollBy({
                    left: -scrollAmount,
                    behavior: "smooth"
                });
            }
            if (e.key === "ArrowRight") {
                carrossel.scrollBy({
                    left: scrollAmount,
                    behavior: "smooth"
                });
            }
        });
    })();
</script>

<style>
    /* Responsividade do vídeo embed */
    .video-wrapper iframe,
    .video-wrapper video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 0.5rem;
    }

    .video-wrapper {
        position: relative;
        padding-bottom: 56.25%;
        height: 0;
        overflow: hidden;
    }

    .video-wrapper.no-padding {
        padding-bottom: 0;
        height: auto;
    }
</style>