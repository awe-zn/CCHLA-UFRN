<?php

/**
 * Template Part - Informações de Contato
 * Uso: get_template_part('template-parts/contato-info');
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

$telefone = cchla_get_contato_info('telefone_principal');
$email = cchla_get_contato_info('email_principal');
$endereco = cchla_get_contato_info('endereco');
$horario = cchla_get_contato_info('horario');
$google_maps = cchla_get_contato_info('google_maps');
?>

<div class="contato-info-grid grid gap-6 md:grid-cols-2 lg:grid-cols-3">

    <?php if ($telefone) : ?>
        <div class="contato-item flex items-start gap-4 p-6 bg-white rounded-lg shadow-sm">
            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fa-solid fa-phone text-blue-600 text-xl"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 mb-1">
                    <?php esc_html_e('Telefone', 'cchla-ufrn'); ?>
                </h4>
                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $telefone)); ?>"
                    class="text-gray-600 hover:text-blue-600 transition-colors">
                    <?php echo esc_html($telefone); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($email) : ?>
        <div class="contato-item flex items-start gap-4 p-6 bg-white rounded-lg shadow-sm">
            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fa-solid fa-envelope text-blue-600 text-xl"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 mb-1">
                    <?php esc_html_e('Email', 'cchla-ufrn'); ?>
                </h4>
                <a href="mailto:<?php echo esc_attr($email); ?>"
                    class="text-gray-600 hover:text-blue-600 transition-colors break-all">
                    <?php echo esc_html($email); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($endereco) : ?>
        <div class="contato-item flex items-start gap-4 p-6 bg-white rounded-lg shadow-sm">
            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fa-solid fa-location-dot text-blue-600 text-xl"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 mb-1">
                    <?php esc_html_e('Endereço', 'cchla-ufrn'); ?>
                </h4>
                <?php if ($google_maps) : ?>
                    <a href="<?php echo esc_url($google_maps); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-gray-600 hover:text-blue-600 transition-colors">
                        <?php echo esc_html($endereco); ?>
                    </a>
                <?php else : ?>
                    <address class="text-gray-600 not-italic">
                        <?php echo esc_html($endereco); ?>
                    </address>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($horario) : ?>
        <div class="contato-item flex items-start gap-4 p-6 bg-white rounded-lg shadow-sm">
            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fa-solid fa-clock text-blue-600 text-xl"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 mb-1">
                    <?php esc_html_e('Horário', 'cchla-ufrn'); ?>
                </h4>
                <p class="text-gray-600">
                    <?php echo esc_html($horario); ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

</div>