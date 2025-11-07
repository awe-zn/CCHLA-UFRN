<?php

/**
 * Footer Template - CCHLA UFRN
 * Design baseado no layout oficial
 * 
 * @package CCHLA_UFRN
 * @since 2.0.0
 */
?>

<footer class="bg-[#2E3CB9] text-white pt-16 pb-0" role="contentinfo">
    <div class="max-w-screen-xl mx-auto px-6 pb-8">

        <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-12 lg:gap-16 mb-12 justify-between">

            <div class="flex flex-col gap-6">

                <!-- Logo/Sigla -->
                <div>
                    <h2 class="text-[28px] font-bold leading-tight mb-3">
                        <?php echo esc_html(get_theme_mod('cchla_sigla', 'CCHLA')); ?>
                    </h2>
                    <p class="text-[15px] leading-relaxed text-white/90">
                        <?php
                        $rodape_texto = get_theme_mod('cchla_rodape_texto', 'Centro de Ciências Humanas,<br>Letras e Artes');
                        echo wp_kses($rodape_texto, array('br' => array()));
                        ?>
                    </p>
                </div>

                <!-- Redes Sociais -->
                <?php
                $redes = cchla_get_redes_sociais();

                // Filtra apenas redes com URL válida
                $redes_validas = array_filter($redes, function ($rede) {
                    return !empty($rede['url']) && $rede['url'] !== '#';
                });

                if (!empty($redes_validas)) :
                ?>
                    <nav aria-label="<?php esc_attr_e('Redes sociais do CCHLA', 'cchla-ufrn'); ?>">
                        <ul class="flex gap-3">
                            <?php
                            foreach ($redes_validas as $key => $rede) {
                                $url = $rede['url'];

                                // WhatsApp: formato especial
                                if ($key === 'whatsapp') {
                                    $numero = preg_replace('/[^0-9]/', '', $url);
                                    $url = 'https://wa.me/' . $numero;
                                }

                                printf(
                                    '<li>
                                    <a href="%s" 
                                       aria-label="%s" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="flex text-[18px] items-center justify-center w-10 h-10 bg-white text-[#3f47cc] rounded-full transition-all duration-400 hover:border-white/90 hover:text-white/90 hover:bg-[#3f47cc] hover:scale-105 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-[#3f47cc]">
                                        <i class="%s" aria-hidden="true"></i>
                                        <span class="sr-only">%s</span>
                                    </a>
                                </li>',
                                    esc_url($url),
                                    esc_attr($rede['label']),
                                    esc_attr($rede['icon']),
                                    esc_html($rede['label'])
                                );
                            }
                            ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

            <?php
            // Localizações de menu do footer
            $footer_menus = array(
                'footer-institucional' => __('INSTITUCIONAL', 'cchla-ufrn'),
                'footer-academico'     => __('ACADÊMICO', 'cchla-ufrn'),
                'footer-imprensa'      => __('IMPRENSA', 'cchla-ufrn'),
            );

            // Verifica quantos menus estão configurados
            $menus_configurados = array();
            foreach ($footer_menus as $location => $title) {
                if (has_nav_menu($location)) {
                    $menu_items = wp_get_nav_menu_items(get_nav_menu_locations()[$location]);
                    if ($menu_items) {
                        $menus_configurados[$location] = array(
                            'title' => $title,
                            'items' => cchla_build_menu_tree($menu_items)
                        );
                    }
                }
            }

            // Só mostra a grid de menus se houver pelo menos 1 menu configurado
            if (!empty($menus_configurados)) :
            ?>
                <nav aria-label="<?php esc_attr_e('Navegação do rodapé', 'cchla-ufrn'); ?>"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-10">

                    <?php foreach ($menus_configurados as $location => $menu_data) : ?>
                        <?php foreach ($menu_data['items'] as $parent_item) : ?>

                            <!-- Coluna de Menu -->
                            <div>
                                <!-- Título do Menu (Pai) -->
                                <h3 class="text-[16px] font-bold uppercase tracking-[0.08em] mb-4 text-white">
                                    <?php echo esc_html($parent_item->title); ?>
                                </h3>

                                <!-- Submenu -->
                                <?php if (!empty($parent_item->children)) : ?>
                                    <ul class="space-y-2.5">
                                        <?php foreach ($parent_item->children as $child_item) : ?>
                                            <li>
                                                <a href="<?php echo esc_url($child_item->url); ?>"
                                                    class="text-[15px] text-white/90 hover:text-white hover:underline focus:text-white focus:underline focus:outline-none transition-colors inline-block leading-relaxed"
                                                    <?php echo ($child_item->target === '_blank') ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                                                    <?php echo esc_html($child_item->title); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>

                        <?php endforeach; ?>
                    <?php endforeach; ?>

                </nav>
            <?php endif; ?>
        </div>

        <div class="border-t border-white/20 mb-10" role="presentation"></div>

        <div class="text-center text-sm leading-relaxed max-w-screen-md mx-auto px-6 pt-4 pb-12 text-[16px]">

            <h3 class="text-[16px] font-bold uppercase tracking-wide leading-tight">
                <?php echo esc_html(get_theme_mod('cchla_nome_completo', 'UNIVERSIDADE FEDERAL DO RIO GRANDE DO NORTE')); ?>
            </h3>

            <p class="mt-1 text-[16px] text-white/90 leading-relaxed">
                <?php echo esc_html(get_theme_mod('cchla_subtitulo_footer', 'Centro de Ciências Humanas, Letras e Artes')); ?>
            </p>

            <?php
            $endereco = cchla_get_contato_info('endereco');
            if ($endereco) :
            ?>
                <p class="mt-4 text-base text-white/90 leading-relaxed">
                    <a href="#" id="link-mapa-footer" class="hover:text-white hover:underline focus:text-white focus:underline focus:outline-none transition-colors" aria-label="<?php esc_attr_e('Abrir localização no Google Maps', 'cchla-ufrn'); ?>" title="<?php esc_attr_e('Abrir localização no Google Maps', 'cchla-ufrn'); ?>">
                        <?php echo esc_html($endereco); ?>
                    </a>
                </p>
            <?php endif; ?>


            <div class="mt-1 text-base text-white/90 leading-relaxed">
                <?php
                $tel_principal = cchla_get_contato_info('telefone_principal');
                $tel_secundario = cchla_get_contato_info('telefone_secundario');
                $email_principal = cchla_get_contato_info('email_principal');
                ?>

                <?php if ($tel_principal || $tel_secundario) : ?>
                    <span class="inline-block">
                        <span class="font-normal"><?php _e('Telefone:', 'cchla-ufrn'); ?></span>

                        <?php if ($tel_principal) : ?>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $tel_principal)); ?>"
                                class="font-bold hover:text-white hover:underline focus:text-white focus:underline transition-colors">
                                <?php echo esc_html($tel_principal); ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($tel_secundario) : ?>
                            <span class="font-normal"> / </span>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $tel_secundario)); ?>"
                                class="font-bold hover:text-white hover:underline focus:text-white focus:underline transition-colors">
                                <?php echo esc_html($tel_secundario); ?>
                            </a>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>

                <?php if (($tel_principal || $tel_secundario) && $email_principal) : ?>
                    <span class="inline-block mx-2">|</span>
                <?php endif; ?>

                <?php if ($email_principal) : ?>
                    <span class="inline-block">
                        <span class="font-normal"><?php _e('E-mail:', 'cchla-ufrn'); ?></span>
                        <a href="mailto:<?php echo esc_attr($email_principal); ?>"
                            class="font-bold hover:text-white hover:underline focus:text-white focus:underline transition-colors">
                            <?php echo esc_html($email_principal); ?>
                        </a>
                    </span>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div class="bg-[#1D2E7A] py-6 text-center">
        <div class="max-w-[1200px] mx-auto px-6 text-center">
            <?php
            $creditos_link = get_theme_mod('cchla_creditos_link', 'https://agenciaweb.ifrn.edu.br');
            $creditos_texto = get_theme_mod('cchla_creditos', 'Desenvolvido pela Agência Web do IFRN');
            $creditos_logo = get_theme_mod('cchla_creditos_logo', get_template_directory_uri() . '/assets/img/logo-awe.svg');
            ?>
            <a href="<?php echo esc_url($creditos_link); ?>"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="<?php echo esc_attr($creditos_texto); ?>"
                class="inline-block hover:opacity-90 focus:opacity-90 transition-opacity">
                <img src="<?php echo esc_url($creditos_logo); ?>" alt="<?php echo esc_attr($creditos_texto); ?>" class="mx-auto w-8 h-auto" loading="lazy">
            </a>
        </div>
    </div>

    <script>
        (function() {
            'use strict';

            const linkMapa = document.getElementById('link-mapa-footer');
            if (!linkMapa) return;

            const destino = <?php echo json_encode(get_theme_mod('cchla_nome_completo', 'CCHLA - UFRN, Natal, RN')); ?>;
            const urlWeb = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(destino)}`;
            const urlMobile = `geo:0,0?q=${encodeURIComponent(destino)}`;
            const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

            linkMapa.addEventListener('click', function(e) {
                e.preventDefault();

                if (isMobile) {
                    window.location.href = urlMobile;
                    setTimeout(() => {
                        window.open(urlWeb, '_blank', 'noopener,noreferrer');
                    }, 800);
                } else {
                    window.open(urlWeb, '_blank', 'noopener,noreferrer');
                }
            });
        })();
    </script>
</footer>

<?php wp_footer(); ?>
</body>

</html>