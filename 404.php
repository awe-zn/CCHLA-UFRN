<?php

get_header();

$site_assets = get_template_directory_uri();

?>

<!-- MAIN -->
<main class="flex items-center justify-center px-4 py-12">
    <div class="max-w-5xl w-full py-12">
        <div class="grid lg:grid-cols-2 gap-12 items-center py-12">

            <!-- Lado Esquerdo: Texto e CTAs -->
            <div class="text-center lg:text-left space-y-6 animate-fade-in-up">
                <div>
                    <h1 class="text-5xl md:text-6xl font-bold text-blue-700 mb-2">
                        Oops....
                    </h1>
                    <p class="text-2xl md:text-3xl font-semibold text-gray-800 mb-4">
                        Página não encontrada
                    </p>
                    <p class="text-gray-600 text-lg">
                        A página que você está tentando acessar não existe ou foi removida.
                    </p>
                </div>

                <!-- Cards de Ação -->
                <div class="flex flex-col gap-4 max-w-md mx-auto lg:mx-0">
                    <!-- Card Home -->
                    <a href="<?php echo home_url(); ?>"
                        class="group block rounded-lg bg-blue-50 hover:bg-blue-100 hover:shadow-md transition-all duration-300 p-5 text-left focus:outline-none focus:ring-4 focus:ring-blue-300">
                        <div class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-home" aria-hidden="true"></i>
                            </div>
                            <div class="flex-1">
                                <h2 class="font-semibold text-lg text-gray-900 mb-1">
                                    Acesse a nossa home
                                </h2>
                                <p class="text-gray-600 text-sm">
                                    Acompanhe o nosso departamento e encontre o que precisa.
                                </p>
                            </div>
                        </div>
                    </a>

                    <!-- Card Notícias -->
                    <a href="<?php echo home_url("/blog"); ?>"
                        class="group block rounded-lg bg-blue-50 hover:bg-blue-100 hover:shadow-md transition-all duration-300 p-5 text-left focus:outline-none focus:ring-4 focus:ring-blue-300">
                        <div class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-newspaper" aria-hidden="true"></i>
                            </div>
                            <div class="flex-1">
                                <h2 class="font-semibold text-lg text-gray-900 mb-1">
                                    Notícias
                                </h2>
                                <p class="text-gray-600 text-sm">
                                    Fique por dentro de tudo que acontece no CCHLA.
                                </p>
                            </div>
                        </div>
                    </a>

                </div>
            </div>

            <!-- Lado Direito: Imagem -->
            <div class="flex justify-center lg:justify-end animate-fade-in-up" style="animation-delay: 0.2s;">
                <div class="relative max-w-md w-full animate-float">
                    <img src="<?php echo $site_assets; ?>/assets/img/img-404.png" alt="Ilustração de erro 404 - Robô com tela de erro"
                        class="w-full h-auto drop-shadow-2xl">
                </div>
            </div>

        </div>
    </div>
</main>

<?php get_footer(); ?>