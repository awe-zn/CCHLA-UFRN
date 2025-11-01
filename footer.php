<?php

$site_assets = get_template_directory_uri();

?>

<!-- footer -->
<footer class="bg-[#2E3CB9] text-white pt-16 pb-0">
    <!-- GRID SUPERIOR -->
    <div class="max-w-screen-xl mx-auto flex flex-col gap-12 px-6 lg:flex-row lg:justify-between">
        <!-- BLOCO INSTITUCIONAL -->
        <div class="flex flex-col gap-6 text-left md:text-center lg:text-left">
            <div class="flex flex-col gap-6 text-left md:text-center lg:text-left">
                <h4 class="text-xl font-semibold">CCHLA</h4>
                <p class="text-sm mt-2 md:text-center ">Centro de Ciências Humanas,<br>Letras e Artes</p>
            </div>

            <!-- REDES SOCIAIS -->
            <nav aria-label="Redes sociais" class="flex gap-4 justify-start md:justify-center lg:justify-start">
                <a href="#" aria-label="Twitter"
                    class="flex items-center justify-center w-8 h-8 bg-white text-blue-700 rounded-full transition-all duration-200 hover:bg-blue-700 hover:text-white focus:bg-blue-700 focus:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fa-brands fa-twitter text-[14px]" aria-hidden="true"></i>
                </a>

                <a href="#" aria-label="Instagram"
                    class="flex items-center justify-center w-8 h-8 bg-white text-blue-700 rounded-full transition-all duration-200 hover:bg-blue-700 hover:text-white focus:bg-blue-700 focus:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fa-brands fa-instagram text-[14px]" aria-hidden="true"></i>
                </a>

                <a href="#" aria-label="YouTube"
                    class="flex items-center justify-center w-8 h-8 bg-white text-blue-700 rounded-full transition-all duration-200 hover:bg-blue-700 hover:text-white focus:bg-blue-700 focus:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fa-brands fa-youtube text-[14px]" aria-hidden="true"></i>
                </a>
            </nav>
        </div>

        <!-- LINKS PRINCIPAIS -->
        <nav aria-label="Mapa do site"
            class="grid grid-cols-2 gap-10 md:grid-cols-3 text-sm md:text-center lg:text-left text-[16px] ">
            <!-- COLUNA 1 -->
            <ul class="flex flex-col gap-2">
                <li><span class="font-bold uppercase tracking-wide">Institucional</span></li>
                <li><a href="#" class="hover:underline focus:underline">Administração</a></li>
                <li><a href="#" class="hover:underline focus:underline">Documentos</a></li>
                <li><a href="#" class="hover:underline focus:underline">CONSEC</a></li>
                <li><a href="#" class="hover:underline focus:underline">Departamentos</a></li>
            </ul>

            <!-- COLUNA 2 -->
            <ul class="flex flex-col gap-2">
                <li><span class="font-bold uppercase tracking-wide">Acadêmico</span></li>
                <li><a href="#" class="hover:underline focus:underline">Ensino</a></li>
                <li><a href="#" class="hover:underline focus:underline">Pesquisa</a></li>
                <li><a href="#" class="hover:underline focus:underline">Extensão</a></li>
                <li><a href="#" class="hover:underline focus:underline">Publicações</a></li>
            </ul>

            <!-- COLUNA 3 -->
            <ul class="flex flex-col gap-2">
                <li><span class="font-bold uppercase tracking-wide">Imprensa</span></li>
                <li><a href="#" class="hover:underline focus:underline">Eventos</a></li>
                <li><a href="#" class="hover:underline focus:underline">Orçamento</a></li>
                <li><a href="#" class="hover:underline focus:underline">Notícias</a></li>
                <li><a href="#" class="hover:underline focus:underline">Especiais</a></li>
            </ul>
        </nav>
    </div>

    <!-- LINHA DIVISÓRIA -->
    <div class="border-t-1 border-[#3457CB] my-10 mx-6 lg:mx-auto max-w-screen-xl"></div>

    <!-- BLOCO INFERIOR -->
    <div class="text-center text-sm leading-relaxed max-w-screen-md mx-auto px-6 pt-4 pb-14 text-[16px] ">
        <a id="linkMapa">
            <h3 class="font-bold uppercase">Universidade Federal do Rio Grande do Norte</h3>
            <p class="mt-1">Centro de Ciências Humanas, Letras e Artes</p>
            <p class="mt-4">
                Av. Sen. Salgado Filho, S/n – Lagoa Nova, Natal – RN, 59078-970
            </p>
        </a>

        <script>
            const link = document.getElementById("linkMapa");
            const destino = "CCHLA - CENTRO DE CIÊNCIAS HUMANAS, LETRAS E ARTES - UFRN, Natal, RN";

            // link web padrão (funciona em todos)
            const urlWeb = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(destino)}`;
            // link mobile (abre o app direto)
            const urlMobile = `geo:0,0?q=${encodeURIComponent(destino)}`;

            // Verifica se é mobile
            const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

            // Define comportamento no clique
            link.addEventListener("click", function(e) {
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
            Telefone:
            <strong>(84) +55 84 3342-2243 / 84 99193-6154</strong>
            |
            E-mail:
            <a href="mailto:secretariacchla@gmail.com" class="font-bold hover:underline focus:underline">
                secretariacchla@gmail.com
            </a>
        </p>
    </div>

    <!-- RODAPÉ FINAL -->
    <div class="bg-[#1D2E7A] py-6 text-center">
        <a href="https://agenciaweb.ifrn.edu.br" target="_blank" rel="noopener noreferrer"
            aria-label="Desenvolvido pela Agência Web do IFRN"
            class="inline-block hover:opacity-90 focus:opacity-90 transition-opacity">
            <img src="<?php echo $site_assets; ?>/assets/img/logo-awe.svg" alt="Agência Web do IFRN" class="mx-auto w-8 h-auto">
        </a>
    </div>
</footer>

</body>

</html>