<?php

/**
 * The main template file (Failback)
 *
 * Este é um template de failback usado apenas quando nenhum template mais específico
 * corresponde à requisição. A página principal é controlada por front-page.php
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

get_header();
?>

<div class="site-content">
    <div class="container">

        <div class="failback-message">
            <div class="failback-message__content">
                <i class="fa-solid fa-circle-info failback-message__icon" aria-hidden="true"></i>

                <h1 class="failback-message__title">
                    <?php esc_html_e('Página em construção', 'cchla-ufrn'); ?>
                </h1>

                <p class="failback-message__text">
                    <?php esc_html_e('Este conteúdo está sendo preparado. Por favor, volte em breve.', 'cchla-ufrn'); ?>
                </p>

                <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn--primary">
                    <i class="fa-solid fa-house" aria-hidden="true"></i>
                    <?php esc_html_e('Voltar para a página inicial', 'cchla-ufrn'); ?>
                </a>
            </div>
        </div>

    </div><!-- .container -->
</div><!-- .site-content -->

<?php
get_footer();
