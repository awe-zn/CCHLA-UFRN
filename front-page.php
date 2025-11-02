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



get_header(); ?>




<main>

    <!-- 5 Notícias em Destaque -->
    <?php get_template_part('parts/extra/template-parts/section', 'featured-news'); ?>

    <hr class="border-t-1 border-blue-500 mt-16">
    <!-- section | servicos a sociedade -->
    <section class="max-w-screen-xl mx-auto px-4 pt-10 pb-20">
        <!-- Cabeçalho da seção -->
        <header class="mb-10">
            <p class="text-sm uppercase tracking-wide text-blue-700 border-b border-blue-300 inline-block pb-1">
                Extensão
            </p>
            <h2 class="text-3xl md:text-4xl font-semibold mt-4">Serviços à sociedade</h2>
            <p class="text-zinc-600 mt-2">
                Tenha acesso aos serviços ofertados pelo nosso departamento.
            </p>
        </header>

        <!-- Grid responsivo -->
        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <!-- Card 1 -->
            <a href="#"
                class="block p-6 border border-blue-200 rounded-lg hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="text-blue-600 mb-3">
                    <img src="assets/icons/icon-clima.svg" alt="" class="w-8 h-8">
                </div>
                <h3 class="font-semibold mb-2">Estação Climatológica</h3>
                <p class="text-sm text-zinc-600 mb-3">
                    Desenvolve observação meteorológica, através de convênio entre a UFRN e o INMET.
                </p>
                <span class="text-blue-600 font-medium inline-flex items-center gap-1">
                    Leia mais <span aria-hidden="true">→</span>
                </span>
            </a>

            <!-- Card 2 -->
            <a href="#"
                class="block p-6 border border-blue-200 rounded-lg hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="text-blue-600 mb-3">
                    <img src="assets/icons/icon-agora.svg" alt="" class="w-8 h-8">
                </div>
                <h3 class="font-semibold mb-2">Ágora</h3>
                <p class="text-sm text-zinc-600 mb-3">
                    Oferece curso de línguas estrangeiras para alunos, servidores técnicos e docentes.
                </p>
                <span class="text-blue-600 font-medium inline-flex items-center gap-1">
                    Leia mais <span aria-hidden="true">→</span>
                </span>
            </a>

            <!-- Card 3 -->
            <a href="#"
                class="block p-6 border border-blue-200 rounded-lg hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="text-blue-600 mb-3">
                    <img src="assets/icons/icon-biblio.svg" alt="" class="w-8 h-8">
                </div>
                <h3 class="font-semibold mb-2">Bibliotecas</h3>
                <p class="text-sm text-zinc-600 mb-3">
                    Tenha acesso ao acervo da Biblioteca setorial do CCHLA.
                </p>
                <span class="text-blue-600 font-medium inline-flex items-center gap-1">
                    Leia mais <span aria-hidden="true">→</span>
                </span>
            </a>

            <!-- Card 4 -->
            <a href="#"
                class="block p-6 border border-blue-200 rounded-lg hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="text-blue-600 mb-3">
                    <img src="assets/icons/icon-clima.svg" alt="" class="w-8 h-8">
                </div>
                <h3 class="font-semibold mb-2">Estação Climatológica</h3>
                <p class="text-sm text-zinc-600 mb-3">
                    Desenvolve observação meteorológica, através de convênio entre a UFRN e o INMET.
                </p>
                <span class="text-blue-600 font-medium inline-flex items-center gap-1">
                    Leia mais <span aria-hidden="true">→</span>
                </span>
            </a>
        </div>
    </section>

    <!-- section | noticias -->
    <?php get_template_part('parts/extra/template-parts/section', 'other-highlights'); ?>


    <!-- section | mais noticias -->
    <?php get_template_part('parts/extra/template-parts/section', 'latest-news'); ?>




    <!-- section | especial da cchla -->
    <section class="bg-[#183AB3] text-[#DFEBF0]" id="section-especial">
        <!-- BLOCO SUPERIOR -->
        <div class="relative bg-[url('assets/img/bg-textura.png')] bg-cover bg-center">
            <div class="max-w-screen-xl mx-auto px-4 md:px-10 xl:px-0 py-16 space-y-10">
                <header class="space-y-2">
                    <h2 class="text-3xl md:text-4xl font-light">Especial CCHLA</h2>
                    <p class="text-[#A1CBFF] text-base leading-relaxed">
                        Conheça projetos do CCHLA que fazem a diferença na sociedade.
                    </p>
                </header>

                <!-- Destaque principal -->
                <article class="grid gap-10 lg:grid-cols-2 items-start">
                    <figure class="relative group">
                        <video src="assets/videos/videoplayback.mp4" poster="assets/img/especial-video-tumb-1.png"
                            class="w-full h-auto rounded-lg border border-blue-300" controls></video>
                    </figure>

                    <div class="flex flex-col gap-4">
                        <h3 class="text-2xl font-semibold text-white">
                            Comunicação Inclusiva: Acessibilidade para Todos
                        </h3>
                        <div class="space-y-4 text-[#DFEBF0] text-base leading-relaxed">
                            <p>
                                O projeto <strong>"Comunicação Inclusiva: Acessibilidade para Todos"</strong> visa
                                promover a comunicação acessível para pessoas com deficiência, utilizando
                                ferramentas e recursos que garantem o acesso à informação e a participação social de
                                todos os cidadãos.
                            </p>
                            <p>
                                Através de ações educativas, de sensibilização e da produção de materiais
                                informativos, o projeto busca romper barreiras comunicacionais e construir uma
                                sociedade mais justa e inclusiva.
                            </p>
                        </div>
                        <a href="#"
                            class="flex items-center gap-2 text-white font-semibold hover:text-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-400 rounded transition">
                            Acesse o link do projeto
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    </div>
                </article>
            </div>
        </div>

        <!-- BLOCO INFERIOR -->
        <div class="bg-gradient-to-b from-[#183AB3] to-[#162556] text-[#B2C8FF] py-16 px-4 md:px-10 xl:px-0">
            <div class="max-w-screen-xl mx-auto flex flex-col gap-12">

                <!-- Carrossel -->
                <div class="relative">
                    <div id="carrossel-especiais"
                        class="overflow-x-auto scroll-smooth snap-x snap-mandatory flex gap-8 pb-4 xl:overflow-visible">
                        <a href="#" class="min-w-[260px] snap-start group">
                            <figure class="flex flex-col gap-4 font-semibold">
                                <img src="assets/img/especial-video-tumb-2.png"
                                    alt="Miniatura do vídeo Diálogos Transgeracionais"
                                    class="rounded-lg transition duration-300 group-hover:ring-4 group-hover:ring-blue-400" />
                                <figcaption class="group-hover:text-[#E5EDFF] transition duration-300">
                                    Diálogos Transgeracionais: Comunicação para todas as idades
                                </figcaption>
                            </figure>
                        </a>
                        <a href="#" class="min-w-[260px] snap-start group">
                            <figure class="flex flex-col gap-4 font-semibold">
                                <img src="assets/img/especial-video-tumb-3.png"
                                    alt="Miniatura do vídeo Educomunicação"
                                    class="rounded-lg transition duration-300 group-hover:ring-4 group-hover:ring-blue-400" />
                                <figcaption class="group-hover:text-[#E5EDFF] transition duration-300">
                                    Educomunicação: Transformando Realidades Através da Comunicação
                                </figcaption>
                            </figure>
                        </a>
                        <a href="#" class="min-w-[260px] snap-start group">
                            <figure class="flex flex-col gap-4 font-semibold">
                                <img src="assets/img/especial-video-tumb-4.png"
                                    alt="Miniatura do vídeo Saúde em Foco"
                                    class="rounded-lg transition duration-300 group-hover:ring-4 group-hover:ring-blue-400" />
                                <figcaption class="group-hover:text-[#E5EDFF] transition duration-300">
                                    Saúde em Foco: Comunicação para a Prevenção e o Bem-Estar
                                </figcaption>
                            </figure>
                        </a>
                        <a href="#" class="min-w-[260px] snap-start group">
                            <figure class="flex flex-col gap-4 font-semibold">
                                <img src="assets/img/especial-video-tumb-5.png"
                                    alt="Miniatura do vídeo Narrativas Digitais"
                                    class="rounded-lg transition duration-300 group-hover:ring-4 group-hover:ring-blue-400" />
                                <figcaption class="group-hover:text-[#E5EDFF] transition duration-300">
                                    Narrativas Digitais: Contando Histórias que Inspiram
                                </figcaption>
                            </figure>
                        </a>
                    </div>

                    <!-- Botões -->
                    <div class="flex justify-between items-center mt-10">
                        <a href="#"
                            class="inline-flex items-center gap-3 border border-[#ACBCE6] px-6 py-3 rounded-md text-sm text-[#E5EDFF] font-semibold hover:bg-white hover:text-[#183AB3] transition focus:outline-none focus:ring-2 focus:ring-blue-300">
                            Ver todos os especiais
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>

                        <div class="flex gap-4 text-[#EFEFF0] lg:hidden">
                            <button id="prev-especial" aria-label="Anterior"
                                class="flex items-center justify-center w-10 h-10 border border-[#EFEFF0] rounded-full hover:bg-white hover:text-[#193CB8] transition focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <button id="next-especial" aria-label="Próximo"
                                class="flex items-center justify-center w-10 h-10 border border-[#EFEFF0] rounded-full hover:bg-white hover:text-[#193CB8] transition focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- SCRIPT DO CARROSSEL -->
    <script>
        const carrossel = document.getElementById("carrossel-especiais");
        const prevBtn = document.getElementById("prev-especial");
        const nextBtn = document.getElementById("next-especial");

        const scrollAmount = 300; // pixels por clique

        prevBtn.addEventListener("click", () => {
            carrossel.scrollBy({
                left: -scrollAmount,
                behavior: "smooth"
            });
        });

        nextBtn.addEventListener("click", () => {
            carrossel.scrollBy({
                left: scrollAmount,
                behavior: "smooth"
            });
        });

        // Acessibilidade: setas controláveis por teclado (setas ← →)
        document.addEventListener("keydown", (e) => {
            if (e.key === "ArrowLeft") carrossel.scrollBy({
                left: -scrollAmount,
                behavior: "smooth"
            });
            if (e.key === "ArrowRight") carrossel.scrollBy({
                left: scrollAmount,
                behavior: "smooth"
            });
        });
    </script>

    <!-- section | produção -->

    <section class="bg-[#F4F6F9] pb-16 md:px-0 xl:px-0">
        <div class="bg-[#EFF2FB] py-10 border-b border-b-[#BAC6ED]">
            <header class="max-w-screen-xl mx-auto space-y-4 px-4 md:px-10">
                <p
                    class="text-xs text-gray-500 uppercase tracking-wider border-b border-gray-300 pb-1 inline-block">
                    Produção no CCHLA
                </p>
                <h2 class="text-4xl font-light text-gray-900 max-sm:text-2xl">
                    Publicações recentes do CCHLA
                </h2>
                <p class="text-gray-600 text-base">
                    Acompanhe as últimas publicações realizadas no nosso departamento.
                </p>
            </header>
        </div>
        <div class="max-w-screen-xl mx-auto mt-16 px-4 md:px-10">

            <!-- GRID FLUIDO -->
            <div class="grid grid-cols-1 sm:grid-cols-1 lg:grid-cols-3  gap-10 sm:gap-6 md:gap-8">

                <!-- CARD 1 -->
                <a href="#"
                    class="group flex flex-col justify-between bg-white rounded-md p-6 hover:shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <div class="space-y-2">
                        <p class="text-xs uppercase text-gray-600 font-medium tracking-wide">Livro</p>
                        <h3 class="font-semibold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">
                            Estórias ao redor do fogo [recurso eletrônico]
                        </h3>
                        <p class="text-sm text-gray-600">Organizadores: Tânia Lima; Izabel Nascimento.</p>
                        <p class="text-sm text-gray-500">ISBN 978-65-5477-017-0</p>
                    </div>
                    <div class="flex justify-between items-end mt-4">
                        <figure>
                            <img src="assets/img/Rectangle 37.png" alt="Capa do livro Estórias ao redor do fogo"
                                class="w-24 h-32 object-cover rounded-md shadow-sm group-hover:scale-105 transition-transform duration-300">
                        </figure>
                        <span
                            class="flex items-center gap-1 text-blue-600 text-sm font-medium group-hover:underline">
                            Leia mais
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- CARD 2 -->
                <a href="#"
                    class="group flex flex-col justify-between bg-white rounded-md p-6 hover:shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <div class="space-y-2">
                        <p class="text-xs uppercase text-gray-600 font-medium tracking-wide">Livro</p>
                        <h3 class="font-semibold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">
                            Sandowane: estórias que vêm do chão [recurso eletrônico]
                        </h3>
                        <p class="text-sm text-gray-600">Organizadores: Paulina Chiziane; Tânia Lima; Izabel
                            Nascimento.</p>
                        <p class="text-sm text-gray-500">ISBN 978-65-5477-020-0 – Livro virtual</p>
                    </div>
                    <div class="flex justify-between items-end mt-4">
                        <figure>
                            <img src="assets/img/Rectangle 37 (1).png" alt="Capa do livro Sandowane"
                                class="w-24 h-32 object-cover rounded-md shadow-sm group-hover:scale-105 transition-transform duration-300">
                        </figure>
                        <span
                            class="flex items-center gap-1 text-blue-600 text-sm font-medium group-hover:underline">
                            Leia mais
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- CARD 3 -->
                <a href="#"
                    class="group flex flex-col justify-between bg-white rounded-md p-6 hover:shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <div class="space-y-2">
                        <p class="text-xs uppercase text-gray-600 font-medium tracking-wide">Livro</p>
                        <h3 class="font-semibold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">
                            v. 14 n. 22 (2021): Revista Bagoas - Estudos Gays: gênero e sexualidades
                        </h3>
                        <p class="text-sm text-gray-600">Revista Bagoas</p>
                        <p class="text-sm text-gray-500">ISSN 2316-6185 (Periódico)</p>
                    </div>
                    <div class="flex justify-between items-end mt-4">
                        <figure>
                            <img src="assets/img/Rectangle 37 (2).png" alt="Capa da Revista Bagoas"
                                class="w-24 h-32 object-cover rounded-md shadow-sm group-hover:scale-105 transition-transform duration-300">
                        </figure>
                        <span
                            class="flex items-center gap-1 text-blue-600 text-sm font-medium group-hover:underline">
                            Leia mais
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- CARD 4 -->
                <a href="#"
                    class="group flex flex-col justify-between bg-white rounded-md p-6 hover:shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <div class="space-y-2">
                        <p class="text-xs uppercase text-gray-600 font-medium tracking-wide">Livro</p>
                        <h3 class="font-semibold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">
                            AFROLIC - Literatura Desigualdade Ensino
                        </h3>
                        <p class="text-sm text-gray-600">
                            Organizadores: Rosilda Bezerra, Tânia Lima, Carmen Tindó Secco e Sávio Freitas.
                        </p>
                        <p class="text-sm text-gray-500">ISBN 978-65-86643-84-8 – Livro virtual</p>
                    </div>
                    <div class="flex justify-between items-end mt-4">
                        <figure>
                            <img src="assets/img/Rectangle 37 (3).png" alt="Capa do livro AFROLIC"
                                class="w-24 h-32 object-cover rounded-md shadow-sm group-hover:scale-105 transition-transform duration-300">
                        </figure>
                        <span
                            class="flex items-center gap-1 text-blue-600 text-sm font-medium group-hover:underline">
                            Leia mais
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- CARD 5 -->
                <a href="#"
                    class="group flex flex-col justify-between bg-white rounded-md p-6 hover:shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <div class="space-y-2">
                        <p class="text-xs uppercase text-gray-600 font-medium tracking-wide">Livro</p>
                        <h3 class="font-semibold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">
                            CAMUS E OS ANTIGOS
                        </h3>
                        <p class="text-sm text-gray-600">
                            Autores: Markus Figueira da Silva e Alice Bitencourt Haddad. Número de páginas: 98.
                        </p>
                        <p class="text-sm text-gray-500">ISBN 978-65-995185-2-2</p>
                    </div>
                    <div class="flex justify-between items-end mt-4">
                        <figure>
                            <img src="assets/img/Rectangle 38.png" alt="Capa do livro Camus e os Antigos"
                                class="w-24 h-32 object-cover rounded-md shadow-sm group-hover:scale-105 transition-transform duration-300">
                        </figure>
                        <span
                            class="flex items-center gap-1 text-blue-600 text-sm font-medium group-hover:underline">
                            Leia mais
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- CARD 6 -->
                <a href="#"
                    class="group flex flex-col justify-between bg-white rounded-md p-6 hover:shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <div class="space-y-2">
                        <p class="text-xs uppercase text-gray-600 font-medium tracking-wide">Livro</p>
                        <h3 class="font-semibold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">
                            E-BOOK: O HABITAR E O INABITUAL
                        </h3>
                        <p class="text-sm text-gray-600">
                            Organizadores: Oscar F. Bauchwitz, Eduardo Pellejero e Gilvânio Moreira. Número de
                            páginas: 631.
                        </p>
                        <p class="text-sm text-gray-500">ISBN 978-65-995185-1-5</p>
                    </div>
                    <div class="flex justify-between items-end mt-4">
                        <figure>
                            <img src="assets/img/Rectangle 37.svg" alt="Capa do e-book O Habitar e o Inabitual"
                                class="w-24 h-32 object-cover rounded-md shadow-sm group-hover:scale-105 transition-transform duration-300">
                        </figure>
                        <span
                            class="flex items-center gap-1 text-blue-600 text-sm font-medium group-hover:underline">
                            Leia mais
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </span>
                    </div>
                </a>

            </div>

            <!-- LINK FINAL -->
            <div class="mt-12 flex justify-end">
                <a href="#"
                    class="flex items-center gap-2 text-blue-700 font-semibold hover:underline focus:outline-none focus:ring-2 focus:ring-blue-300 rounded">
                    Acesse todas as publicações
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>

        </div>
    </section>


    <!-- acesso rapido section-->
    <?php /*!-- 
    <section class="mx-auto max-w-screen-xl px-4 py-16">
        <header class="mb-10 max-md:px-4">
            <p class="text-gray-600 text-sm font-light pb-2 border-b border-blue-200 w-fit">ACESSO RÁPIDO</p>
            <h2 class="text-4xl font-light mt-2">Sistemas</h2>
        </header>

        <div class="grid grid-cols-3 gap-8 max-lg:grid-cols-2 max-md:grid-cols-1">
            <!-- ITEM -->
            <a href="https://sigaa.ufrn.br/" target="_blank" rel="noopener noreferrer"
                class="group flex flex-col gap-2 p-5 border border-blue-200 rounded-sm hover:bg-blue-50 hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300 transition-all duration-200">
                <strong class="text-blue-900 group-hover:text-blue-700 transition-colors">SIGAA</strong>
                <p class="text-sm text-gray-600">
                    Sistema Integrado de Gestão de Atividades Acadêmicas.
                </p>
            </a>

            <a href="https://sipac.ufrn.br/" target="_blank" rel="noopener noreferrer"
                class="group flex flex-col gap-2 p-5 border border-blue-200 rounded-sm hover:bg-blue-50 hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300 transition-all duration-200">
                <strong class="text-blue-900 group-hover:text-blue-700 transition-colors">SIPAC</strong>
                <p class="text-sm text-gray-600">
                    Sistema Integrado de Patrimônio, Administração e Contratos.
                </p>
            </a>

            <a href="https://sigrh.ufrn.br/" target="_blank" rel="noopener noreferrer"
                class="group flex flex-col gap-2 p-5 border border-blue-200 rounded-sm hover:bg-blue-50 hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300 transition-all duration-200">
                <strong class="text-blue-900 group-hover:text-blue-700 transition-colors">SIGRH</strong>
                <p class="text-sm text-gray-600">
                    Sistema Integrado de Gestão de Recursos Humanos.
                </p>
            </a>

            <a href="https://chamados.ufrn.br/" target="_blank" rel="noopener noreferrer"
                class="group flex flex-col gap-2 p-5 border border-blue-200 rounded-sm hover:bg-blue-50 hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300 transition-all duration-200">
                <strong class="text-blue-900 group-hover:text-blue-700 transition-colors">GTI Chamados</strong>
                <p class="text-sm text-gray-600">
                    Sistema de chamados para Gestão de Tecnologia da Informação.
                </p>
            </a>

            <a href="https://espacos.ufrn.br/" target="_blank" rel="noopener noreferrer"
                class="group flex flex-col gap-2 p-5 border border-blue-200 rounded-sm hover:bg-blue-50 hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300 transition-all duration-200">
                <strong class="text-blue-900 group-hover:text-blue-700 transition-colors">Espaço Físico Setor
                    2</strong>
                <p class="text-sm text-gray-600">
                    Visualizador dos espaços físicos disponíveis.
                </p>
            </a>

            <a href="https://reserva.cchla.ufrn.br/" target="_blank" rel="noopener noreferrer"
                class="group flex flex-col gap-2 p-5 border border-blue-200 rounded-sm hover:bg-blue-50 hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300 transition-all duration-200">
                <strong class="text-blue-900 group-hover:text-blue-700 transition-colors">Reserva CCHLA</strong>
                <p class="text-sm text-gray-600">
                    Sistema de reserva de auditório do CCHLA.
                </p>
            </a>
        </div>
    </section>

    --*/ ?>

    <?php get_template_part('parts/extra/template-parts/acesso-rapido'); ?>

</main>


<?php
get_footer();
