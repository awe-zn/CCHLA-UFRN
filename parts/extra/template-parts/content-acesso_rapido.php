<?php

/**
 * Template part - Content Acesso Rápido
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

$descricao = get_post_meta(get_the_ID(), '_acesso_descricao', true);
$link_externo = get_post_meta(get_the_ID(), '_acesso_link_externo', true);
$abertura = get_post_meta(get_the_ID(), '_acesso_abertura', true);
$tipo_icone = get_post_meta(get_the_ID(), '_acesso_tipo_icone', true);
$icone_url = get_post_meta(get_the_ID(), '_acesso_icone_url', true);
$icone_classe = get_post_meta(get_the_ID(), '_acesso_icone_classe', true);

$abertura = $abertura ? $abertura : '_blank';
$rel = ($abertura === '_blank') ? 'noopener noreferrer' : '';
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('group flex flex-col gap-2 p-5 border border-blue-200 rounded-sm hover:bg-blue-50 hover:border-blue-400 transition-all duration-200'); ?>>

    <a href="<?php echo esc_url($link_externo); ?>"
        target="<?php echo esc_attr($abertura); ?>"
        <?php if ($rel) echo 'rel="' . esc_attr($rel) . '"'; ?>
        class="flex flex-col gap-2 focus:outline-none focus:ring-2 focus:ring-blue-300">

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

</article>