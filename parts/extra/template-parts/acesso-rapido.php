<?php

/**
 * Template Part - Acesso Rápido
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */


$args = array(
    'post_type' => 'acesso_rapido',
    'posts_per_page' => 6,
    'orderby' => 'meta_value_num',
    'meta_key' => '_acesso_ordem',
    'order' => 'ASC',
    'post_status' => 'publish',
);

$acessos_query = new WP_Query($args);

if (!$acessos_query->have_posts()) {
    return;
}

// Verifica se há mais de 6 itens
$total_acessos = wp_count_posts('acesso_rapido')->publish;
$has_more = $total_acessos > 6;
?>

<section class="mx-auto max-w-screen-xl px-4 py-16">
    <header class="mb-10 max-md:px-4">
        <p class="text-gray-600 text-sm font-light pb-2 border-b border-blue-200 w-fit">
            <?php esc_html_e('ACESSO RÁPIDO', 'cchla-ufrn'); ?>
        </p>
        <h2 class="text-4xl font-light mt-2">
            <?php esc_html_e('Sistemas', 'cchla-ufrn'); ?>
        </h2>
    </header>

    <div class="grid grid-cols-3 gap-8 max-lg:grid-cols-2 max-md:grid-cols-1">
        <?php while ($acessos_query->have_posts()) : $acessos_query->the_post();
            $descricao = get_post_meta(get_the_ID(), '_acesso_descricao', true);
            $link_externo = get_post_meta(get_the_ID(), '_acesso_link_externo', true);
            $abertura = get_post_meta(get_the_ID(), '_acesso_abertura', true);
            $tipo_icone = get_post_meta(get_the_ID(), '_acesso_tipo_icone', true);
            $icone_url = get_post_meta(get_the_ID(), '_acesso_icone_url', true);
            $icone_classe = get_post_meta(get_the_ID(), '_acesso_icone_classe', true);

            $abertura = $abertura ? $abertura : '_blank';
            $rel = ($abertura === '_blank') ? 'noopener noreferrer' : '';
        ?>

            <!-- ITEM -->
            <a href="<?php echo esc_url($link_externo); ?>"
                target="<?php echo esc_attr($abertura); ?>"
                <?php if ($rel) echo 'rel="' . esc_attr($rel) . '"'; ?>
                class="group flex flex-col gap-2 p-5 border border-blue-200 rounded-sm hover:bg-blue-50 hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300 transition-all duration-200">

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

                <strong class="text-blue-900 group-hover:text-blue-700 transition-colors">
                    <?php the_title(); ?>
                </strong>

                <?php if ($descricao) : ?>
                    <p class="text-sm text-gray-600">
                        <?php echo esc_html($descricao); ?>
                    </p>
                <?php endif; ?>
            </a>

        <?php endwhile; ?>
    </div>

    <?php if ($has_more) : ?>
        <div class="text-center mt-10">
            <a href="<?php echo esc_url(get_post_type_archive_link('acesso_rapido')); ?>"
                class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400">
                <?php esc_html_e('Ver todos os sistemas', 'cchla-ufrn'); ?>
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    <?php endif; ?>

</section>

<?php
wp_reset_postdata();
