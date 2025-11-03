<footer class="bg-[#2E3CB9] text-white pt-16 pb-0">
    <!-- GRID SUPERIOR -->
    <div class="max-w-screen-xl mx-auto flex flex-col gap-12 px-6 lg:flex-row lg:justify-between">
        <!-- BLOCO INSTITUCIONAL -->
        <div class="flex flex-col gap-6 text-left md:text-center lg:text-left">
            <div class="flex flex-col gap-6 text-left md:text-center lg:text-left">
                <h4 class="text-xl font-semibold">
                    <?php echo esc_html(get_theme_mod('cchla_sigla', 'CCHLA')); ?>
                </h4>
                <p class="text-sm mt-2 md:text-center">
                    <?php
                    $rodape_texto = get_theme_mod('cchla_rodape_texto', 'Centro de Ciências Humanas,<br>Letras e Artes');
                    echo wp_kses_post($rodape_texto);
                    ?>
                </p>
            </div>

            <!-- REDES SOCIAIS -->
            <nav aria-label="Redes sociais" class="flex gap-4 justify-start md:justify-center lg:justify-start">
                <?php
                $redes = cchla_get_redes_sociais();

                // Se não houver redes configuradas, mostra placeholders
                if (empty($redes)) {
                    $redes_padrao = array(
                        'twitter' => array('url' => '#', 'icon' => 'fa-brands fa-twitter', 'label' => 'Twitter'),
                        'instagram' => array('url' => '#', 'icon' => 'fa-brands fa-instagram', 'label' => 'Instagram'),
                        'youtube' => array('url' => '#', 'icon' => 'fa-brands fa-youtube', 'label' => 'YouTube'),
                    );
                    $redes = $redes_padrao;
                }

                foreach ($redes as $key => $rede) {
                    $url = $rede['url'];

                    // WhatsApp usa formato especial
                    if ($key === 'whatsapp' && !empty($url)) {
                        $numero = preg_replace('/[^0-9]/', '', $url);
                        $url = 'https://wa.me/' . $numero;
                    }

                    printf(
                        '<a href="%s" aria-label="%s" %s class="flex items-center justify-center w-8 h-8 bg-white text-blue-700 rounded-full transition-all duration-200 hover:bg-blue-700 hover:text-white focus:bg-blue-700 focus:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"><i class="%s text-[14px]" aria-hidden="true"></i></a>',
                        esc_url($url),
                        esc_attr($rede['label']),
                        ($url !== '#') ? 'target="_blank" rel="noopener noreferrer"' : '',
                        esc_attr($rede['icon'])
                    );
                }
                ?>
            </nav>
        </div>

        <!-- LINKS PRINCIPAIS -->
        <nav aria-label="Mapa do site" class="grid grid-cols-2 gap-10 md:grid-cols-3 text-sm md:text-center lg:text-left text-[16px]">
            <!-- COLUNA 1 -->
            <ul class="flex flex-col gap-2">
                <li><span class="font-bold uppercase tracking-wide"><?php esc_html_e('Institucional', 'cchla-ufrn'); ?></span></li>
                <?php
                if (has_nav_menu('footer-institucional')) {
                    wp_nav_menu(array(
                        'theme_location' => 'footer-institucional',
                        'container' => false,
                        'items_wrap' => '%3$s',
                        'link_before' => '',
                        'link_after' => '',
                        'fallback_cb' => false,
                    ));
                } else {
                    // Links padrão caso o menu não esteja configurado
                ?>
                    <li><a href="#" class="hover:underline focus:underline"><?php esc_html_e('Administração', 'cchla-ufrn'); ?></a></li>
                    <li><a href="#" class="hover:underline focus:underline"><?php esc_html_e('Documentos', 'cchla-ufrn'); ?></a></li>
                    <li><a href="#" class="hover:underline focus:underline"><?php esc_html_e('CONSEC', 'cchla-ufrn'); ?></a></li>
                    <li><a href="#" class="hover:underline focus:underline"><?php esc_html_e('Departamentos', 'cchla-ufrn'); ?></a></li>
                <?php
                }
                ?>
            </ul>

            <!-- COLUNA 2 -->
            <ul class="flex flex-col gap-2">
                <li><span class="font-bold uppercase tracking-wide"><?php esc_html_e('Acadêmico', 'cchla-ufrn'); ?></span></li>
                <?php
                if (has_nav_menu('footer-academico')) {
                    wp_nav_menu(array(
                        'theme_location' => 'footer-academico',
                        'container' => false,
                        'items_wrap' => '%3$s',
                        'fallback_cb' => false,
                    ));
                } else {
                ?>
                    <li><a href="#" class="hover:underline focus:underline"><?php esc_html_e('Ensino', 'cchla-ufrn'); ?></a></li>
                    <li><a href="#" class="hover:underline focus:underline"><?php esc_html_e('Pesquisa', 'cchla-ufrn'); ?></a></li>
                    <li><a href="#" class="hover:underline focus:underline"><?php esc_html_e('Extensão', 'cchla-ufrn'); ?></a></li>
                    <li><a href="#" class="hover:underline focus:underline"><?php esc_html_e('Publicações', 'cchla-ufrn'); ?></a></li>
                <?php
                }
                ?>
            </ul>

            <!-- COLUNA 3 -->
            <ul class="flex flex-col gap-2">
                <li><span class="font-bold uppercase tracking-wide"><?php esc_html_e('Imprensa', 'cchla-ufrn'); ?></span></li>
                <?php
                if (has_nav_menu('footer-imprensa')) {
                    wp_nav_menu(array(
                        'theme_location' => 'footer-imprensa',
                        'container' => false,
                        'items_wrap' => '%3$s',
                        'fallback_cb' => false,
                    ));
                } else {
                ?>
                    <li><a href="#" class="hover:underline focus:underline"><?php esc_html_e('Eventos', 'cchla-ufrn'); ?></a></li>
                    <li><a href="#" class="hover:underline focus:underline"><?php esc_html_e('Orçamento', 'cchla-ufrn'); ?></a></li>
                    <li><a href="#" class="hover:underline focus:underline"><?php esc_html_e('Notícias', 'cchla-ufrn'); ?></a></li>
                    <li><a href="#" class="hover:underline focus:underline"><?php esc_html_e('Especiais', 'cchla-ufrn'); ?></a></li>
                <?php
                }
                ?>
            </ul>
        </nav>
    </div>

    <!-- LINHA DIVISÓRIA -->
    <div class="border-t-1 border-[#3457CB] my-10 mx-6 lg:mx-auto max-w-screen-xl"></div>

    <!-- BLOCO INFERIOR -->
    <div class="text-center text-sm leading-relaxed max-w-screen-md mx-auto px-6 pt-4 pb-14 text-[16px]">
        <a id="linkMapa">
            <h3 class="font-bold uppercase">
                <?php echo esc_html(get_theme_mod('cchla_nome_completo', 'Universidade Federal do Rio Grande do Norte')); ?>
            </h3>
            <p class="mt-1">
                <?php echo esc_html(get_theme_mod('cchla_rodape_texto', 'Centro de Ciências Humanas, Letras e Artes')); ?>
            </p>
            <p class="mt-4">
                <?php echo esc_html(cchla_get_contato_info('endereco')); ?>
            </p>
        </a>

        <script>
            const link = document.getElementById("linkMapa");
            const destino = "<?php echo esc_js(get_theme_mod('cchla_nome_completo', 'CCHLA - CENTRO DE CIÊNCIAS HUMANAS, LETRAS E ARTES - UFRN, Natal, RN')); ?>";

            // link web padrão (funciona em todos)
            const urlWeb = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(destino)}`;
            // link mobile (abre o app direto)
            const urlMobile = `geo:0,0?q=${encodeURIComponent(destino)}`;

            // Verifica se é mobile
            const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

            // Define comportamento no clique
            link.addEventListener("click", function(e) {
                e.preventDefault();
                if (isMobile) {
                    // tenta abrir o app do Maps
                    window.location.href = urlMobile;
                    // fallback pro link web se o app não abrir em 800ms
                    setTimeout(() => {
                        window.open(urlWeb, "_blank");
                    }, 800);
                } else {
                    // desktop: abre o maps na nova aba
                    window.open(urlWeb, "_blank");
                }
            });
        </script>

        <p class="mt-1">
            <?php esc_html_e('Telefone:', 'cchla-ufrn'); ?>
            <strong>
                <?php
                $tel_principal = cchla_get_contato_info('telefone_principal');
                $tel_secundario = cchla_get_contato_info('telefone_secundario');

                echo esc_html($tel_principal);
                if ($tel_secundario) {
                    echo ' / ' . esc_html($tel_secundario);
                }
                ?>
            </strong>
            |
            <?php esc_html_e('E-mail:', 'cchla-ufrn'); ?>
            <a href="mailto:<?php echo esc_attr(cchla_get_contato_info('email_principal')); ?>" class="font-bold hover:underline focus:underline">
                <?php echo esc_html(cchla_get_contato_info('email_principal')); ?>
            </a>
        </p>
    </div>

    <!-- RODAPÉ FINAL -->
    <div class="bg-[#1D2E7A] py-6 text-center">
        <?php
        $creditos_link = get_theme_mod('cchla_creditos_link', 'https://agenciaweb.ifrn.edu.br');
        $creditos_texto = get_theme_mod('cchla_creditos', 'Desenvolvido pela Agência Web do IFRN');
        ?>
        <a href="<?php echo esc_url($creditos_link); ?>"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="<?php echo esc_attr($creditos_texto); ?>"
            class="inline-block hover:opacity-90 focus:opacity-90 transition-opacity">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo-awe.svg"
                alt="<?php echo esc_attr($creditos_texto); ?>"
                class="mx-auto w-8 h-auto">
        </a>
    </div>
</footer>

<?php wp_footer(); ?>
</body>

</html>