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
    <?php get_template_part('parts/extra/template-parts/servicos-sociedade');

    /** 
     * 
     * Com shortcode:
     * [servicos limite="4" categoria="extensao" colunas="4"]
     */

    ?>


    <!-- section | noticias -->
    <?php get_template_part('parts/extra/template-parts/section', 'other-highlights'); ?>


    <!-- section | mais noticias -->
    <?php get_template_part('parts/extra/template-parts/section', 'latest-news'); ?>




    <!-- section | especial da cchla -->
    <?php get_template_part('parts/extra/template-parts/especial-cchla');

    /**
     * 
     * Com shortcode:
     * [especiais limite="4" categoria="comunicacao"]
     * [especiais destaque="sim" limite="1"]
     * 
     */


    ?>



    <!-- section | produção -->

    <?php


    get_template_part('parts/extra/template-parts/publicacoes-recentes');

    /** 
     * 
     *  ---- Para fazer publicaçoes  Com shortcode no editor:
     * [publicacoes limite="6" tipo="livro" colunas="3"]
     * --- Estatísticas:
     * [estatisticas_publicacoes]
     * 
     * */

    ?>

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
