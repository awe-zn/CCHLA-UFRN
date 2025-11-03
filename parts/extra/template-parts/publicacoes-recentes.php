<?php

/**
 * Template Part - Publicações Recentes
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

$args = array(
    'post_type' => 'publicacoes',
    'posts_per_page' => 6,
    'orderby' => 'date',
    'order' => 'DESC',
    'post_status' => 'publish',
);

$publicacoes_query = new WP_Query($args);

if (!$publicacoes_query->have_posts()) {
    return;
}
?>

<section class="bg-[#F4F6F9] pb-16 md:px-0 xl:px-0">
    <div class="bg-[#EFF2FB] py-10 border-b border-b-[#BAC6ED]">
        <header class="max-w-screen-xl mx-auto space-y-4 px-4 md:px-10">
            <p class="text-xs text-gray-500 uppercase tracking-wider border-b border-gray-300 pb-1 inline-block">
                <?php esc_html_e('Produção no CCHLA', 'cchla-ufrn'); ?>
            </p>
            <h2 class="text-4xl font-light text-gray-900 max-sm:text-2xl">
                <?php esc_html_e('Publicações recentes do CCHLA', 'cchla-ufrn'); ?>
            </h2>
            <p class="text-gray-600 text-base">
                <?php esc_html_e('Acompanhe as últimas publicações realizadas no nosso departamento.', 'cchla-ufrn'); ?>
            </p>
        </header>
    </div>

    <div class="max-w-screen-xl mx-auto mt-16 px-4 md:px-10">

        <!-- GRID FLUIDO -->
        <div class="grid grid-cols-1 sm:grid-cols-1 lg:grid-cols-3 gap-10 sm:gap-6 md:gap-8">

            <?php while ($publicacoes_query->have_posts()) : $publicacoes_query->the_post();
                $autores = get_post_meta(get_the_ID(), '_publicacao_autores', true);
                $isbn = get_post_meta(get_the_ID(), '_publicacao_isbn', true);
                $link_externo = get_post_meta(get_the_ID(), '_publicacao_link_externo', true);
                $tipos = get_the_terms(get_the_ID(), 'tipo_publicacao');
                $tipo_nome = ($tipos && !is_wp_error($tipos)) ? $tipos[0]->name : 'Livro';

                // Link: externo se existir, senão permalink
                $link_url = $link_externo ? $link_externo : get_permalink();
                $link_target = $link_externo ? '_blank' : '_self';
                $link_rel = $link_externo ? 'noopener noreferrer' : '';
            ?>

                <!-- CARD -->
                <a href="<?php echo esc_url($link_url); ?>"
                    target="<?php echo esc_attr($link_target); ?>"
                    <?php if ($link_rel) echo 'rel="' . esc_attr($link_rel) . '"'; ?>
                    class="group flex flex-col justify-between bg-white rounded-md p-6 hover:shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400">

                    <div class="space-y-2">
                        <p class="text-xs uppercase text-gray-600 font-medium tracking-wide">
                            <?php echo esc_html($tipo_nome); ?>
                        </p>

                        <h3 class="font-semibold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">
                            <?php the_title(); ?>
                        </h3>

                        <?php if ($autores) : ?>
                            <p class="text-sm text-gray-600">
                                <?php
                                // Formata autores
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
                                <?php the_post_thumbnail('publicacao-thumb', array(
                                    'class' => 'w-24 h-32 object-cover rounded-md shadow-sm group-hover:scale-105 transition-transform duration-300',
                                    'alt' => get_the_title()
                                )); ?>
                            </figure>
                        <?php else : ?>
                            <div class="max-md:hidden w-24 h-32 bg-gray-200 rounded-md flex items-center justify-center">
                                <i class="fa-solid fa-book text-gray-400 text-2xl"></i>
                            </div>
                        <?php endif; ?>

                        <span class="flex items-center gap-1 text-blue-600 text-sm font-medium group-hover:underline <?php echo !has_post_thumbnail() ? 'ml-auto' : ''; ?>">
                            <?php esc_html_e('Leia mais', 'cchla-ufrn'); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </span>
                    </div>
                </a>

            <?php endwhile; ?>

        </div>

        <!-- LINK FINAL -->
        <div class="mt-12 flex justify-end">
            <a href="<?php echo esc_url(get_post_type_archive_link('publicacoes')); ?>"
                class="flex items-center gap-2 text-blue-700 font-semibold hover:underline focus:outline-none focus:ring-2 focus:ring-blue-300 rounded">
                <?php esc_html_e('Acesse todas as publicações', 'cchla-ufrn'); ?>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>

    </div>
</section>

<?php
wp_reset_postdata();
