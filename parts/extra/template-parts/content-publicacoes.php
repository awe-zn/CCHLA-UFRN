<?php

/**
 * Template part - Content Publicações
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

$autores = get_post_meta(get_the_ID(), '_publicacao_autores', true);
$isbn = get_post_meta(get_the_ID(), '_publicacao_isbn', true);
$link_externo = get_post_meta(get_the_ID(), '_publicacao_link_externo', true);
$tipos = get_the_terms(get_the_ID(), 'tipo_publicacao');
$tipo_nome = ($tipos && !is_wp_error($tipos)) ? $tipos[0]->name : 'Livro';

$link_url = $link_externo ? $link_externo : get_permalink();
$link_target = $link_externo ? '_blank' : '_self';
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('group flex flex-col justify-between bg-white rounded-md p-6 hover:shadow-lg transition-all duration-300'); ?>>

    <div class="space-y-2">
        <p class="text-xs uppercase text-gray-600 font-medium tracking-wide">
            <?php echo esc_html($tipo_nome); ?>
        </p>

        <h3 class="font-semibold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">
            <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>">
                <?php the_title(); ?>
            </a>
        </h3>

        <?php if ($autores) : ?>
            <p class="text-sm text-gray-600">
                <?php echo esc_html($autores); ?>
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
                <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>">
                    <?php the_post_thumbnail('publicacao-thumb', array(
                        'class' => 'w-24 h-32 object-cover rounded-md shadow-sm group-hover:scale-105 transition-transform duration-300'
                    )); ?>
                </a>
            </figure>
        <?php endif; ?>

        <a href="<?php echo esc_url($link_url); ?>"
            target="<?php echo esc_attr($link_target); ?>"
            class="flex items-center gap-1 text-blue-600 text-sm font-medium group-hover:underline">
            <?php esc_html_e('Leia mais', 'cchla-ufrn'); ?>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
        </a>
    </div>

</article>