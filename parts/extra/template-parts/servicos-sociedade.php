<?php

/**
 * Template Part - Serviços à Sociedade
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

$args = array(
    'post_type' => 'servicos',
    'posts_per_page' => 4,
    'orderby' => 'menu_order',
    'order' => 'ASC',
    'post_status' => 'publish',
);

$servicos_query = new WP_Query($args);

if (!$servicos_query->have_posts()) {
    return;
}
?>

<section class="max-w-screen-xl mx-auto px-4 pt-10 pb-20">
    <!-- Cabeçalho da seção -->
    <header class="mb-10">
        <p class="text-sm uppercase tracking-wide text-blue-700 border-b border-blue-300 inline-block pb-1">
            <?php esc_html_e('Extensão', 'cchla-ufrn'); ?>
        </p>
        <h2 class="text-3xl md:text-4xl font-semibold mt-4">
            <?php esc_html_e('Serviços à sociedade', 'cchla-ufrn'); ?>
        </h2>
        <p class="text-zinc-600 mt-2">
            <?php esc_html_e('Tenha acesso aos serviços ofertados pelo nosso departamento.', 'cchla-ufrn'); ?>
        </p>
    </header>

    <!-- Grid responsivo -->
    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">

        <?php while ($servicos_query->have_posts()) : $servicos_query->the_post();
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
            <a href="<?php echo esc_url($link_url); ?>"
                target="<?php echo esc_attr($link_target); ?>"
                <?php if ($link_rel) echo 'rel="' . esc_attr($link_rel) . '"'; ?>
                class="block p-6 border border-blue-200 rounded-lg hover:shadow-md hover:-translate-y-1 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400">

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

        <?php endwhile; ?>

    </div>

    <!-- Link para ver todos (se houver mais de 4) -->
    <?php
    $total_servicos = wp_count_posts('servicos')->publish;
    if ($total_servicos > 4) :
    ?>
        <div class="mt-10 text-right">
            <a href="<?php echo esc_url(get_post_type_archive_link('servicos')); ?>"
                class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400">
                <?php esc_html_e('Ver todos os serviços', 'cchla-ufrn'); ?>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    <?php endif; ?>

</section>

<?php
wp_reset_postdata();
