<?php

/**
 * Single Template for Cursos
 * 
 * @package CCHLA_UFRN
 */

get_header();

while (have_posts()) : the_post();

    // Buscar todos os meta dados
    $codigo = get_post_meta(get_the_ID(), '_curso_codigo', true);
    $modalidade = get_post_meta(get_the_ID(), '_curso_modalidade', true);
    $turno = get_post_meta(get_the_ID(), '_curso_turno', true);
    $duracao = get_post_meta(get_the_ID(), '_curso_duracao', true);
    $vagas = get_post_meta(get_the_ID(), '_curso_vagas', true);
    $carga_horaria = get_post_meta(get_the_ID(), '_curso_carga_horaria', true);
    $nota_mec = get_post_meta(get_the_ID(), '_curso_nota_mec', true);
    $reconhecimento = get_post_meta(get_the_ID(), '_curso_reconhecimento', true);
    $matriz_curricular = get_post_meta(get_the_ID(), '_curso_matriz_curricular', true);
    $ppc = get_post_meta(get_the_ID(), '_curso_ppc', true);

    // Coordenação
    $coordenador = get_post_meta(get_the_ID(), '_curso_coordenador', true);
    $coordenador_email = get_post_meta(get_the_ID(), '_curso_coordenador_email', true);
    $coordenador_telefone = get_post_meta(get_the_ID(), '_curso_coordenador_telefone', true);
    $vice_coordenador = get_post_meta(get_the_ID(), '_curso_vice_coordenador', true);
    $vice_coordenador_email = get_post_meta(get_the_ID(), '_curso_vice_coordenador_email', true);

    // Departamento
    $dept_id = get_post_meta(get_the_ID(), '_curso_departamento', true);
    $dept = $dept_id ? get_post($dept_id) : null;

    // Tipo de curso
    $tipo_terms = get_the_terms(get_the_ID(), 'tipo_curso');
    $tipo = $tipo_terms && !is_wp_error($tipo_terms) ? $tipo_terms[0]->name : '';
?>

    <main class="bg-gray-50">

        <!-- Hero Section -->
        <section class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-16 bg-[url('<?php echo get_template_directory_uri(); ?>/assets/img/bg-textura.png')] bg-cover bg-center">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4 mb-6">
                    <a href="<?php echo get_post_type_archive_link('cursos'); ?>"
                        class="text-white/80 hover:text-white">
                        <i class="fa-solid fa-arrow-left"></i>
                        <?php _e('Voltar', 'cchla-ufrn'); ?>
                    </a>
                    <?php if ($tipo) : ?>
                        <span class="px-4 py-2 bg-white/20rounded-full text-sm font-semibold">
                            <?php echo esc_html($tipo); ?>
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

                <!-- Informações Rápidas -->
                <div class="flex flex-wrap gap-6 mt-8 text-white/90">
                    <?php if ($modalidade) : ?>
                        <div class="flex items-center gap-2">
                            <span><?php echo esc_html(ucfirst($modalidade)); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($turno) : ?>
                        <div class="flex items-center gap-2">
                            <span><?php echo esc_html(ucfirst($turno)); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($duracao) : ?>
                        <div class="flex items-center gap-2">
                            <span><?php echo esc_html($duracao); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($vagas) : ?>
                        <div class="flex items-center gap-2">
                            <span><?php echo esc_html($vagas); ?> vagas/ano</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid lg:grid-cols-3 gap-8">

                <!-- Conteúdo Principal -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- Sobre o Curso -->
                    <?php if (get_the_content()) : ?>
                        <section class="bg-white rounded-lg shadow-sm p-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                                <?php _e('Sobre o Curso', 'cchla-ufrn'); ?>
                            </h2>
                            <div class="prose prose-lg max-w-none">
                                <?php the_content(); ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- Coordenação -->
                    <?php if ($coordenador || $vice_coordenador) : ?>
                        <section class="bg-white rounded-lg shadow-sm p-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                                <?php _e('Coordenação do Curso', 'cchla-ufrn'); ?>
                            </h2>

                            <div class="space-y-6">
                                <?php if ($coordenador) : ?>
                                    <div class="flex items-start gap-4 pb-6 border-b border-gray-200">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900"><?php _e('Coordenador(a)', 'cchla-ufrn'); ?></h3>
                                            <p class="text-gray-700"><?php echo esc_html($coordenador); ?></p>
                                            <?php if ($coordenador_email) : ?>
                                                <a href="mailto:<?php echo esc_attr($coordenador_email); ?>"
                                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                                    <i class="fa-solid fa-envelope mr-1"></i>
                                                    <?php echo esc_html($coordenador_email); ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($coordenador_telefone) : ?>
                                                <br>
                                                <a href="tel:<?php echo esc_attr($coordenador_telefone); ?>"
                                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                                    <i class="fa-solid fa-phone mr-1"></i>
                                                    <?php echo esc_html($coordenador_telefone); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($vice_coordenador) : ?>
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fa-solid fa-user text-blue-600 text-xl"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900"><?php _e('Vice-Coordenador(a)', 'cchla-ufrn'); ?></h3>
                                            <p class="text-gray-700"><?php echo esc_html($vice_coordenador); ?></p>
                                            <?php if ($vice_coordenador_email) : ?>
                                                <a href="mailto:<?php echo esc_attr($vice_coordenador_email); ?>"
                                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                                    <i class="fa-solid fa-envelope mr-1"></i>
                                                    <?php echo esc_html($vice_coordenador_email); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- Departamento Responsável -->
                    <?php if ($dept) : ?>
                        <section class="bg-gradient-to-r from-blue-50 to-indigo-100 rounded-lg p-8">
                            <h2 class="text-md text-gray-600 mb-4">
                                <?php _e('Departamento Responsável', 'cchla-ufrn'); ?>
                            </h2>

                            <div class="flex items-start gap-4">
                                <?php if (has_post_thumbnail($dept->ID)) : ?>
                                    <div class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden bg-white">
                                        <?php echo get_the_post_thumbnail($dept->ID, 'thumbnail', array('class' => 'w-full h-full object-cover')); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                                        <a href="<?php echo get_permalink($dept->ID); ?>" class="hover:text-blue-600">
                                            <?php echo esc_html($dept->post_title); ?>
                                        </a>
                                    </h3>

                                    <?php if ($dept->post_excerpt) : ?>
                                        <p class="text-gray-700 mb-3">
                                            <?php echo esc_html($dept->post_excerpt); ?>
                                        </p>
                                    <?php endif; ?>

                                    <a href="<?php echo get_permalink($dept->ID); ?>"
                                        class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium">
                                        <?php _e('Ver departamento', 'cchla-ufrn'); ?>
                                        <i class="fa-solid fa-arrow-right text-sm"></i>
                                    </a>
                                </div>
                            </div>
                        </section>
                    <?php endif; ?>

                </div>

                <!-- Sidebar -->
                <aside class="lg:col-span-1 space-y-6">

                    <!-- Informações Acadêmicas -->
                    <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">
                            <?php _e('Informações Acadêmicas', 'cchla-ufrn'); ?>
                        </h3>

                        <div class="space-y-4">
                            <?php if ($codigo) : ?>
                                <div>
                                    <label class="text-xs text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('Código do Curso', 'cchla-ufrn'); ?>
                                    </label>
                                    <div class="text-gray-700 font-mono text-sm">
                                        <?php echo esc_html($codigo); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($carga_horaria) : ?>
                                <div>
                                    <label class="text-xs text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('Carga Horária Total', 'cchla-ufrn'); ?>
                                        <?php echo esc_html($carga_horaria); ?>
                                    </label>
                                    <div class="text-gray-700 text-sm">
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($modalidade) : ?>
                                <div>
                                    <label class="text-xs text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('Modalidade', 'cchla-ufrn'); ?>
                                    </label>
                                    <span class="text-gray-700 text-sm">
                                        <?php
                                        $modalidades = array(
                                            'presencial' => 'Presencial',
                                            'ead' => 'Educação a Distância (EaD)',
                                            'hibrido' => 'Híbrido',
                                            'semipresencial' => 'Semipresencial'
                                        );
                                        echo isset($modalidades[$modalidade]) ? $modalidades[$modalidade] : ucfirst($modalidade);
                                        ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php if ($turno) : ?>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('Turno', 'cchla-ufrn'); ?>
                                    </label>
                                    <div class="text-gray-700 text-sm">
                                        <?php echo esc_html(ucfirst($turno)); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($duracao) : ?>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('Duração', 'cchla-ufrn'); ?>
                                    </label>
                                    <div class="text-gray-700 text-sm">
                                        <?php echo esc_html($duracao); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($vagas) : ?>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('Vagas Anuais', 'cchla-ufrn'); ?>
                                    </label>
                                    <div class="text-gray-700 text-sm">
                                        <?php echo esc_html($vagas); ?> vagas por ano
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($nota_mec) : ?>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('Avaliação MEC', 'cchla-ufrn'); ?>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <div class="flex gap-1">
                                            <?php $nota = floatval($nota_mec);                                             ?>
                                        </div>
                                        <span class="text-gray-700 text-sm font-semibold"><?php echo esc_html($nota_mec); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($reconhecimento) : ?>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
                                        <?php _e('Reconhecimento', 'cchla-ufrn'); ?>
                                    </label>
                                    <div class="text-gray-700 text-sm">
                                        <?php echo esc_html($reconhecimento); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Documentos -->
                        <?php if ($matriz_curricular || $ppc) : ?>
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <label class="text-xs text-gray-700 uppercase tracking-wider block mb-3">
                                    <?php _e('Documentos', 'cchla-ufrn'); ?>
                                </label>
                                <div class="space-y-2">
                                    <?php if ($matriz_curricular) : ?>
                                        <a href="<?php echo esc_url($matriz_curricular); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex items-center gap-2 px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition-colors text-sm">
                                            <i class="fa-solid fa-file-alt"></i>
                                            <span><?php _e('Matriz Curricular', 'cchla-ufrn'); ?></span>
                                            <i class="fa-solid fa-external-link-alt text-xs ml-auto"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($ppc) : ?>
                                        <a href="<?php echo esc_url($ppc); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex items-center gap-2 px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition-colors text-sm">
                                            <i class="fa-solid fa-book"></i>
                                            <span><?php _e('Projeto Pedagógico (PPC)', 'cchla-ufrn'); ?></span>
                                            <i class="fa-solid fa-external-link-alt text-xs ml-auto"></i>
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
                            <h3 class="text-lg font-bold text-gray-700 mb-4">
                                <?php _e('Áreas de Conhecimento', 'cchla-ufrn'); ?>
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($areas as $area) : ?>
                                    <a href="<?php echo get_term_link($area); ?>"
                                        class="inline-block px-3 py-1 bg-gray-50 text-gray-700 rounded-full text-sm hover:bg-blue-100 transition-colors">
                                        <?php echo esc_html($area->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- CTA de Contato -->
                    <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 text-white">
                        <h3 class="text-lg font-bold mb-2">
                            <?php _e('Ficou interessado?', 'cchla-ufrn'); ?>
                        </h3>
                        <p class="text-white/90 text-sm mb-6">
                            <?php _e('Entre em contato com a coordenação do curso para mais informações', 'cchla-ufrn'); ?>
                        </p>
                        <?php if ($coordenador_email) : ?>
                            <a href="mailto:<?php echo esc_attr($coordenador_email); ?>"
                                class="block w-full px-4 py-3 border font-semibold text-center rounded-lg ">
                                <?php _e('Entrar em Contato', 'cchla-ufrn'); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                </aside>

            </div>
        </div>

    </main>

<?php
endwhile;
get_footer();
?>