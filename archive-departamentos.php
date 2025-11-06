<?php

/**
 * Archive Template for Departamentos
 * 
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

get_header();
?>
<!-- Breadcrumb -->
<?php cchla_breadcrumb(); ?>
<main class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Cabeçalho -->
        <header class="mb-12 text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                <?php _e('Departamentos do CCHLA', 'cchla-ufrn'); ?>
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                <?php _e('Conheça os departamentos que compõem o Centro de Ciências Humanas, Letras e Artes', 'cchla-ufrn'); ?>
            </p>
        </header>

        <!-- Busca e Filtros -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <form method="get" class="grid md:grid-cols-3 gap-4">

                <!-- Campo de Busca -->
                <div class="md:col-span-2">
                    <label for="s" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Buscar departamento', 'cchla-ufrn'); ?>
                    </label>
                    <input type="text"
                        id="s"
                        name="s"
                        value="<?php echo get_search_query(); ?>"
                        placeholder="<?php _e('Digite o nome ou sigla...', 'cchla-ufrn'); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Filtro por Área -->
                <div>
                    <label for="area" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php _e('Área de Conhecimento', 'cchla-ufrn'); ?>
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

                <div class="md:col-span-3 flex gap-3">
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fa-solid fa-search mr-2"></i>
                        <?php _e('Buscar', 'cchla-ufrn'); ?>
                    </button>
                    <a href="<?php echo get_post_type_archive_link('departamentos'); ?>"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        <?php _e('Limpar filtros', 'cchla-ufrn'); ?>
                    </a>
                </div>
            </form>
        </div>

        <?php if (have_posts()) : ?>

            <!-- Grid de Departamentos -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

                <?php while (have_posts()) : the_post();
                    $sigla = get_post_meta(get_the_ID(), '_departamento_sigla', true);
                    $email = get_post_meta(get_the_ID(), '_departamento_email', true);
                    $telefone = get_post_meta(get_the_ID(), '_departamento_telefone', true);
                    $chefe = get_post_meta(get_the_ID(), '_departamento_chefe', true);

                    // Conta cursos
                    $cursos_count = get_posts(array(
                        'post_type' => 'cursos',
                        'posts_per_page' => -1,
                        'meta_query' => array(
                            array(
                                'key' => '_curso_departamento',
                                'value' => get_the_ID(),
                            )
                        ),
                        'fields' => 'ids'
                    ));
                ?>

                    <article class="bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden group">

                        <!-- Imagem -->
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="aspect-video overflow-hidden bg-gray-100">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium', array(
                                        'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300'
                                    )); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="p-6">

                            <!-- Sigla Badge -->
                            <?php if ($sigla) : ?>
                                <div class="mb-3">
                                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                                        <?php echo esc_html($sigla); ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <!-- Título -->
                            <h2 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h2>

                            <!-- Excerpt -->
                            <?php if (has_excerpt()) : ?>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                    <?php echo get_the_excerpt(); ?>
                                </p>
                            <?php endif; ?>

                            <!-- Meta Info -->
                            <div class="space-y-2 mb-4 text-sm text-gray-600">
                                <?php if ($chefe) : ?>
                                    <div class="flex items-start gap-2">
                                        <i class="fa-solid fa-user-tie text-blue-600 mt-0.5"></i>
                                        <span><?php echo esc_html($chefe); ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($email) : ?>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-envelope text-blue-600"></i>
                                        <a href="mailto:<?php echo esc_attr($email); ?>" class="hover:text-blue-600">
                                            <?php echo esc_html($email); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if ($telefone) : ?>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-phone text-blue-600"></i>
                                        <a href="tel:<?php echo esc_attr($telefone); ?>" class="hover:text-blue-600">
                                            <?php echo esc_html($telefone); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($cursos_count)) : ?>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-graduation-cap text-blue-600"></i>
                                        <span><?php echo count($cursos_count); ?> <?php echo _n('curso', 'cursos', count($cursos_count), 'cchla-ufrn'); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Link -->
                            <a href="<?php the_permalink(); ?>"
                                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium">
                                <?php _e('Ver detalhes', 'cchla-ufrn'); ?>
                                <i class="fa-solid fa-arrow-right text-sm"></i>
                            </a>

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
                <i class="fa-solid fa-search text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    <?php _e('Nenhum departamento encontrado', 'cchla-ufrn'); ?>
                </h2>
                <p class="text-gray-600 mb-6">
                    <?php _e('Tente ajustar seus filtros de busca', 'cchla-ufrn'); ?>
                </p>
                <a href="<?php echo get_post_type_archive_link('departamentos'); ?>"
                    class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <?php _e('Ver todos os departamentos', 'cchla-ufrn'); ?>
                </a>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>