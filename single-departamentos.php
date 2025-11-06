<?php

/**
 * Single Template for Departamentos
 * 
 * @package CCHLA_UFRN
 */

get_header();

while (have_posts()) : the_post();

    // Meta dados
    $sigla = get_post_meta(get_the_ID(), '_departamento_sigla', true);
    $codigo = get_post_meta(get_the_ID(), '_departamento_codigo', true);
    $fundacao = get_post_meta(get_the_ID(), '_departamento_fundacao', true);
    $localizacao = get_post_meta(get_the_ID(), '_departamento_localizacao', true);
    $sala = get_post_meta(get_the_ID(), '_departamento_sala', true);

    // Contatos
    $telefone = get_post_meta(get_the_ID(), '_departamento_telefone', true);
    $telefone_2 = get_post_meta(get_the_ID(), '_departamento_telefone_2', true);
    $email = get_post_meta(get_the_ID(), '_departamento_email', true);
    $email_secretaria = get_post_meta(get_the_ID(), '_departamento_email_secretaria', true);
    $horario_atendimento = get_post_meta(get_the_ID(), '_departamento_horario_atendimento', true);

    // Responsáveis
    $chefe = get_post_meta(get_the_ID(), '_departamento_chefe', true);
    $chefe_email = get_post_meta(get_the_ID(), '_departamento_chefe_email', true);
    $subchefe = get_post_meta(get_the_ID(), '_departamento_subchefe', true);
    $subchefe_email = get_post_meta(get_the_ID(), '_departamento_subchefe_email', true);
    $coordenador = get_post_meta(get_the_ID(), '_departamento_coordenador', true);
    $coordenador_email = get_post_meta(get_the_ID(), '_departamento_coordenador_email', true);

    // Links
    $site = get_post_meta(get_the_ID(), '_departamento_site', true);
    $lattes = get_post_meta(get_the_ID(), '_departamento_lattes', true);
    $instagram = get_post_meta(get_the_ID(), '_departamento_instagram', true);
    $facebook = get_post_meta(get_the_ID(), '_departamento_facebook', true);
    $youtube = get_post_meta(get_the_ID(), '_departamento_youtube', true);

    // Cursos do departamento
    $cursos = get_posts(array(
        'post_type' => 'cursos',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_curso_departamento',
                'value' => get_the_ID(),
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    ));
?>
    <!-- Breadcrumb -->
    <?php cchla_breadcrumb(); ?>
    <main class="bg-gray-50 min-h-screen">

        <!-- Hero Section -->
        <section class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-16 bg-[url('<?php echo get_template_directory_uri(); ?>/assets/img/bg-textura.png')] bg-cover bg-center">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4 mb-6">
                    <a href="<?php echo get_post_type_archive_link('departamentos'); ?>"
                        class="text-white/80 hover:text-white">
                        <i class="fa-solid fa-arrow-left"></i>
                        <?php _e('Voltar', 'cchla-ufrn'); ?>
                    </a>
                    <?php if ($sigla) : ?>
                        <span class="px-4 py-2 bg-white/20 rounded-full text-sm font-semibold">
                            <?php echo esc_html($sigla); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <?php the_title(); ?>
                </h1>

                <?php if (has_excerpt()) : ?>
                    <p class="text-xl text-white/90 max-w-3xl">
                        <?php the_excerpt(); ?>
                    </p>
                <?php endif; ?>
            </div>
        </section>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid lg:grid-cols-3 gap-8">

                <!-- Conteúdo Principal -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- Sobre o Departamento -->
                    <?php if (get_the_content()) : ?>
                        <section class="bg-white rounded-lg shadow-sm p-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                                <?php _e('Sobre o Departamento', 'cchla-ufrn'); ?>
                            </h2>
                            <div class="prose prose-lg max-w-none">
                                <?php the_content(); ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- Cursos Oferecidos -->
                    <?php if (!empty($cursos)) : ?>
                        <section class="bg-white rounded-lg shadow-sm p-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                                <i class="fa-solid fa-graduation-cap text-blue-600 mr-2"></i>
                                <?php _e('Cursos Oferecidos', 'cchla-ufrn'); ?>
                            </h2>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <?php foreach ($cursos as $curso) :
                                    $tipo_terms = get_the_terms($curso->ID, 'tipo_curso');
                                    $tipo = $tipo_terms && !is_wp_error($tipo_terms) ? $tipo_terms[0]->name : '';
                                ?>
                                    <a href="<?php echo get_permalink($curso->ID); ?>"
                                        class="block p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:shadow-md transition-all duration-200 group">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 mb-1">
                                                    <?php echo esc_html($curso->post_title); ?>
                                                </h3>
                                                <?php if ($tipo) : ?>
                                                    <span class="text-sm text-gray-600">
                                                        <?php echo esc_html($tipo); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <i class="fa-solid fa-arrow-right text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all"></i>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- Equipe e Coordenação -->
                    <?php if ($chefe || $subchefe || $coordenador) : ?>
                        <section class="bg-white rounded-lg shadow-sm p-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                                <i class="fa-solid fa-users text-blue-600 mr-2"></i>
                                <?php _e('Equipe e Coordenação', 'cchla-ufrn'); ?>
                            </h2>

                            <div class="space-y-6">
                                <?php if ($chefe) : ?>
                                    <div class="flex items-start gap-4 pb-6 border-b border-gray-200">
                                        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fa-solid fa-user-tie text-blue-600 text-xl"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900"><?php _e('Chefe do Departamento', 'cchla-ufrn'); ?></h3>
                                            <p class="text-gray-700"><?php echo esc_html($chefe); ?></p>
                                            <?php if ($chefe_email) : ?>
                                                <a href="mailto:<?php echo esc_attr($chefe_email); ?>"
                                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                                    <?php echo esc_html($chefe_email); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($subchefe) : ?>
                                    <div class="flex items-start gap-4 pb-6 border-b border-gray-200">
                                        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fa-solid fa-user text-blue-600 text-xl"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900"><?php _e('Subchefe do Departamento', 'cchla-ufrn'); ?></h3>
                                            <p class="text-gray-700"><?php echo esc_html($subchefe); ?></p>
                                            <?php if ($subchefe_email) : ?>
                                                <a href="mailto:<?php echo esc_attr($subchefe_email); ?>"
                                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                                    <?php echo esc_html($subchefe_email); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($coordenador) : ?>
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fa-solid fa-chalkboard-teacher text-green-600 text-xl"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900"><?php _e('Coordenador Acadêmico', 'cchla-ufrn'); ?></h3>
                                            <p class="text-gray-700"><?php echo esc_html($coordenador); ?></p>
                                            <?php if ($coordenador_email) : ?>
                                                <a href="mailto:<?php echo esc_attr($coordenador_email); ?>"
                                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                                    <?php echo esc_html($coordenador_email); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                </div>

                <!-- Sidebar -->
                <aside class="lg:col-span-1 space-y-6">

                    <!-- Informações de Contato -->
                    <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">
                            <i class="fa-solid fa-address-card text-blue-600 mr-2"></i>
                            <?php _e('Informações de Contato', 'cchla-ufrn'); ?>
                        </h3>

                        <div class="space-y-4">
                            <?php if ($email) : ?>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('E-mail Principal', 'cchla-ufrn'); ?>
                                    </label>
                                    <a href="mailto:<?php echo esc_attr($email); ?>"
                                        class="flex items-center gap-2 text-gray-700 hover:text-blue-600">
                                        <i class="fa-solid fa-envelope text-blue-600"></i>
                                        <span class="text-sm break-all"><?php echo esc_html($email); ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if ($email_secretaria) : ?>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('E-mail Secretaria', 'cchla-ufrn'); ?>
                                    </label>
                                    <a href="mailto:<?php echo esc_attr($email_secretaria); ?>"
                                        class="flex items-center gap-2 text-gray-700 hover:text-blue-600">
                                        <i class="fa-solid fa-envelope text-blue-600"></i>
                                        <span class="text-sm break-all"><?php echo esc_html($email_secretaria); ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if ($telefone) : ?>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('Telefone', 'cchla-ufrn'); ?>
                                    </label>
                                    <a href="tel:<?php echo esc_attr($telefone); ?>"
                                        class="flex items-center gap-2 text-gray-700 hover:text-blue-600">
                                        <i class="fa-solid fa-phone text-blue-600"></i>
                                        <span class="text-sm"><?php echo esc_html($telefone); ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if ($telefone_2) : ?>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('Telefone Secundário', 'cchla-ufrn'); ?>
                                    </label>
                                    <a href="tel:<?php echo esc_attr($telefone_2); ?>"
                                        class="flex items-center gap-2 text-gray-700 hover:text-blue-600">
                                        <i class="fa-solid fa-phone text-blue-600"></i>
                                        <span class="text-sm"><?php echo esc_html($telefone_2); ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if ($localizacao) : ?>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('Localização', 'cchla-ufrn'); ?>
                                    </label>
                                    <div class="flex items-start gap-2 text-gray-700">
                                        <i class="fa-solid fa-map-marker-alt text-blue-600 mt-0.5"></i>
                                        <span class="text-sm"><?php echo esc_html($localizacao); ?>
                                            <?php if ($sala) : ?>
                                                <br><?php echo esc_html($sala); ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($horario_atendimento) : ?>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('Horário de Atendimento', 'cchla-ufrn'); ?>
                                    </label>
                                    <div class="flex items-start gap-2 text-gray-700">
                                        <i class="fa-solid fa-clock text-blue-600 mt-0.5"></i>
                                        <span class="text-sm whitespace-pre-line"><?php echo esc_html($horario_atendimento); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($fundacao) : ?>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('Fundação', 'cchla-ufrn'); ?>
                                    </label>
                                    <div class="flex items-center gap-2 text-gray-700">
                                        <i class="fa-solid fa-calendar text-blue-600"></i>
                                        <span class="text-sm"><?php echo date('d/m/Y', strtotime($fundacao)); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($codigo) : ?>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('Código', 'cchla-ufrn'); ?>
                                    </label>
                                    <div class="flex items-center gap-2 text-gray-700">
                                        <i class="fa-solid fa-hashtag text-blue-600"></i>
                                        <span class="text-sm font-mono"><?php echo esc_html($codigo); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Links e Redes Sociais -->
                        <?php if ($site || $instagram || $facebook || $youtube || $lattes) : ?>
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-3">
                                    <?php _e('Links e Redes Sociais', 'cchla-ufrn'); ?>
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    <?php if ($site) : ?>
                                        <a href="<?php echo esc_url($site); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex items-center justify-center w-10 h-10 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                                            title="<?php _e('Site Oficial', 'cchla-ufrn'); ?>">
                                            <i class="fa-solid fa-globe"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($lattes) : ?>
                                        <a href="<?php echo esc_url($lattes); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex items-center justify-center w-10 h-10 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors"
                                            title="<?php _e('Grupo Lattes/CNPq', 'cchla-ufrn'); ?>">
                                            <i class="fa-solid fa-microscope"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($instagram) : ?>
                                        <a href="<?php echo esc_url($instagram); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg transition-colors"
                                            title="Instagram">
                                            <i class="fa-brands fa-instagram"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($facebook) : ?>
                                        <a href="<?php echo esc_url($facebook); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex items-center justify-center w-10 h-10 bg-[#1877F2] hover:bg-[#145dbf] text-white rounded-lg transition-colors"
                                            title="Facebook">
                                            <i class="fa-brands fa-facebook-f"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($youtube) : ?>
                                        <a href="<?php echo esc_url($youtube); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex items-center justify-center w-10 h-10 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors"
                                            title="YouTube">
                                            <i class="fa-brands fa-youtube"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Áreas de Conhecimento -->
                    <?php
                    $areas = get_the_terms(get_the_ID(), 'area_conhecimento');
                    if ($areas && !is_wp_error($areas)) :
                    ?>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">
                                <i class="fa-solid fa-book text-blue-600 mr-2"></i>
                                <?php _e('Áreas de Conhecimento', 'cchla-ufrn'); ?>
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($areas as $area) : ?>
                                    <a href="<?php echo get_term_link($area); ?>"
                                        class="inline-block px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-sm hover:bg-blue-100 transition-colors">
                                        <?php echo esc_html($area->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </aside>

            </div>
        </div>

    </main>

<?php
endwhile;
get_footer();
?>