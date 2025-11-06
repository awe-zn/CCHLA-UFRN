<?php

/**
 * Archive Template for Cursos
 * 
 * @package CCHLA_UFRN
 */

get_header();
?>

<main class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Cabeçalho -->
        <header class="mb-12 text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                <?php _e('Cursos do CCHLA', 'cchla-ufrn'); ?>
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                <?php _e('Explore os cursos de graduação, pós-graduação e extensão oferecidos pelos nossos departamentos', 'cchla-ufrn'); ?>
            </p>
        </header>

        <!-- Busca e Filtros -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <form method="get" class="grid md:grid-cols-4 gap-4">

                <!-- Campo de Busca -->
                <div class="md:col-span-2">
                    <label for="s" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Buscar curso', 'cchla-ufrn'); ?>
                    </label>
                    <input type="text"
                        id="s"
                        name="s"
                        value="<?php echo get_search_query(); ?>"
                        placeholder="<?php _e('Digite o nome do curso...', 'cchla-ufrn'); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Filtro por Tipo -->
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Tipo de Curso', 'cchla-ufrn'); ?>
                    </label>
                    <?php
                    $tipos = get_terms(array(
                        'taxonomy' => 'tipo_curso',
                        'hide_empty' => true,
                    ));

                    $selected_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
                    ?>
                    <select name="tipo"
                        id="tipo"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value=""><?php _e('Todos os tipos', 'cchla-ufrn'); ?></option>
                        <?php foreach ($tipos as $tipo) : ?>
                            <option value="<?php echo esc_attr($tipo->slug); ?>" <?php selected($selected_tipo, $tipo->slug); ?>>
                                <?php echo esc_html($tipo->name); ?> (<?php echo $tipo->count; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtro por Área -->
                <div>
                    <label for="area" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Área', 'cchla-ufrn'); ?>
                    </label>
                    <?php
                    $areas = get_terms(array(
                        'taxonomy' => 'area_conhecimento',
                        'hide_empty' => true,
                    ));

                    $selected_area = isset($_GET['area']) ? $_GET['area'] : '';
                    ?>
                    <select name="area"
                        id="area"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value=""><?php _e('Todas as áreas', 'cchla-ufrn'); ?></option>
                        <?php foreach ($areas as $area) : ?>
                            <option value="<?php echo esc_attr($area->slug); ?>" <?php selected($selected_area, $area->slug); ?>>
                                <?php echo esc_html($area->name); ?> (<?php echo $area->count; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-4 flex gap-3">
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fa-solid fa-search mr-2"></i>
                        <?php _e('Buscar', 'cchla-ufrn'); ?>
                    </button>
                    <a href="<?php echo get_post_type_archive_link('cursos'); ?>"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        <?php _e('Limpar filtros', 'cchla-ufrn'); ?>
                    </a>
                </div>
            </form>
        </div>

        <?php if (have_posts()) : ?>

            <!-- Lista de Cursos -->
            <div class="space-y-4">

                <?php while (have_posts()) : the_post();
                    $tipo_terms = get_the_terms(get_the_ID(), 'tipo_curso');
                    $tipo = $tipo_terms && !is_wp_error($tipo_terms) ? $tipo_terms[0]->name : '';

                    $dept_id = get_post_meta(get_the_ID(), '_curso_departamento', true);
                    $dept_name = $dept_id ? get_the_title($dept_id) : '';

                    $modalidade = get_post_meta(get_the_ID(), '_curso_modalidade', true);
                    $turno = get_post_meta(get_the_ID(), '_curso_turno', true);
                    $duracao = get_post_meta(get_the_ID(), '_curso_duracao', true);
                    $vagas = get_post_meta(get_the_ID(), '_curso_vagas', true);
                    $coordenador = get_post_meta(get_the_ID(), '_curso_coordenador', true);
                ?>

                    <article class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden">
                        <div class="p-6">
                            <div class="flex flex-col lg:flex-row gap-6">

                                <!-- Imagem -->
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="lg:w-48 flex-shrink-0">
                                        <a href="<?php the_permalink(); ?>" class="block aspect-video lg:aspect-square overflow-hidden rounded-lg bg-gray-100">
                                            <?php the_post_thumbnail('medium', array(
                                                'class' => 'w-full h-full object-cover hover:scale-105 transition-transform duration-300'
                                            )); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <!-- Conteúdo -->
                                <div class="flex-1">

                                    <!-- Badges -->
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <?php if ($tipo) : ?>
                                            <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                                <?php echo esc_html($tipo); ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ($modalidade) : ?>
                                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                                                <?php echo esc_html(ucfirst($modalidade)); ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ($turno) : ?>
                                            <span class="inline-block px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">
                                                <?php echo esc_html(ucfirst($turno)); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Título -->
                                    <h2 class="text-2xl font-bold text-gray-900 mb-2 hover:text-blue-600 transition-colors">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_title(); ?>
                                        </a>
                                    </h2>

                                    <!-- Departamento -->
                                    <?php if ($dept_name) : ?>
                                        <p class="text-sm text-gray-600 mb-3">
                                            <i class="fa-solid fa-building mr-1"></i>
                                            <a href="<?php echo get_permalink($dept_id); ?>" class="hover:text-blue-600">
                                                <?php echo esc_html($dept_name); ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>

                                    <!-- Excerpt -->
                                    <?php if (has_excerpt()) : ?>
                                        <p class="text-gray-700 mb-4 line-clamp-2">
                                            <?php echo get_the_excerpt(); ?>
                                        </p>
                                    <?php endif; ?>

                                    <!-- Meta Info -->
                                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-4">
                                        <?php if ($duracao) : ?>
                                            <div class="flex items-center gap-1">
                                                <i class="fa-solid fa-clock text-blue-600"></i>
                                                <span><?php echo esc_html($duracao); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($vagas) : ?>
                                            <div class="flex items-center gap-1">
                                                <i class="fa-solid fa-users text-blue-600"></i>
                                                <span><?php echo esc_html($vagas); ?> <?php _e('vagas/ano', 'cchla-ufrn'); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($coordenador) : ?>
                                            <div class="flex items-center gap-1">
                                                <i class="fa-solid fa-user-tie text-blue-600"></i>
                                                <span><?php echo esc_html($coordenador); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Link -->
                                    <a href="<?php the_permalink(); ?>"
                                        class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium">
                                        <?php _e('Ver detalhes do curso', 'cchla-ufrn'); ?>
                                        <i class="fa-solid fa-arrow-right text-sm"></i>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </article>

                <?php endwhile; ?>

            </div>

            <!-- Paginação -->
            <div class="mt-12">
                <?php
                the_posts_pagination(array(
                    'mid_size' => 2,
                    'prev_text' => '<i class="fa-solid fa-chevron-left"></i> ' . __('Anterior', 'cchla-ufrn'),
                    'next_text' => __('Próxima', 'cchla-ufrn') . ' <i class="fa-solid fa-chevron-right"></i>',
                    'class' => 'pagination',
                ));
                ?>
            </div>

        <?php else : ?>

            <!-- Nenhum resultado -->
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <i class="fa-solid fa-graduation-cap text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    <?php _e('Nenhum curso encontrado', 'cchla-ufrn'); ?>
                </h2>
                <p class="text-gray-600 mb-6">
                    <?php _e('Tente ajustar seus filtros de busca', 'cchla-ufrn'); ?>
                </p>
                <a href="<?php echo get_post_type_archive_link('cursos'); ?>"
                    class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <?php _e('Ver todos os cursos', 'cchla-ufrn'); ?>
                </a>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>