<?php get_header(); ?>

<main class="bg-white py-8 lg:py-10">
    <div class="max-w-screen-xl mx-auto px-4 lg:px-6">

        <!-- Cabeçalho -->
        <header class="mb-6">
            <p class="text-[11px] uppercase tracking-wider text-gray-500 font-medium mb-2">Nosso Blog</p>
            <h1 class="text-3xl lg:text-4xl font-bold text-[#1B4D9E] mb-1">
                <?php echo is_category() ? single_cat_title('', false) : 'Acompanhe nossas notícias'; ?>
            </h1>
        </header>

        <?php
        // --- listagem de categorias
        $exclude_slugs = ['destaque', 'outros-destaques', 'padrao'];

        // mapeia slugs → IDs (só entra quem existir)
        $exclude_ids = array_values(array_filter(array_map(function ($slug) {
            $term = get_category_by_slug($slug);
            return $term ? intval($term->term_id) : 0;
        }, $exclude_slugs)));

        // busca categorias, escondendo as excluídas
        $cats = get_categories([
            'hide_empty' => 1,
            'exclude'    => $exclude_ids,
        ]);

        // estado atual (caso você marque ativo no filtro)
        $is_cat          = is_category();
        $current_term_id = $is_cat ? get_queried_object_id() : 0;

        // link "Todos" → /blog (mantém ativo se não estiver em categoria)
        $blog_url = get_post_type_archive_link('post');
        ?>
        <a href="<?php echo esc_url($blog_url); ?>"
            class="px-4 py-2 text-xs font-semibold rounded transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400
   <?php echo !$is_cat ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700 hover:bg-blue-200'; ?>">
            Todos
        </a>

        <?php foreach ($cats as $cat):
            $active = ($current_term_id === $cat->term_id);
        ?>
            <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
                class="px-4 py-2 text-xs font-semibold rounded transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400
     <?php echo $active ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700 hover:bg-blue-200'; ?>">
                <?php echo esc_html($cat->name); ?>
            </a>
        <?php endforeach; ?>


        <?php
        /** Contexto de categoria (se estiver em /category/slug) */
        $current_cat_id = $is_cat ? get_queried_object_id() : 0;

        /** Verifica estratégia de destaque */
        $tem_cat_destaque = term_exists('destaque', 'category');

        /** 1) Destaques (3 itens) — restringe à categoria atual se houver */
        $args_destaque = [
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => 3,
            'ignore_sticky_posts' => true,
        ];

        if ($current_cat_id) {
            // dentro de uma categoria, sempre filtra por ela
            $args_destaque['cat'] = $current_cat_id;
            // se quiser “destaque” como subfiltro dentro da categoria via meta, deixe como está
            // se quiser obrigar categoria "destaque" + atual, comente a linha acima e use tax_query composto
        } elseif ($tem_cat_destaque) {
            // na página /blog, se existir categoria "destaque"
            $args_destaque['category_name'] = 'destaque';
        } else {
            // fallback via meta destaque=1
            $args_destaque['meta_key']   = 'destaque';
            $args_destaque['meta_value'] = '1';
        }

        $q_destaque   = new WP_Query($args_destaque);
        $ids_destaque = [];
        ?>

        <!-- ===== Destaques ===== -->
        <section class="mb-10" aria-label="Notícias em destaque">
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <?php if ($q_destaque->have_posts()) : while ($q_destaque->have_posts()) : $q_destaque->the_post();
                        $ids_destaque[] = get_the_ID(); ?>
                        <article class="bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300 fade-in">
                            <a href="<?php the_permalink(); ?>" class="block group focus:outline-none focus:ring-2 focus:ring-blue-400 rounded-lg">
                                <?php if (has_post_thumbnail()) : ?>
                                    <figure class="overflow-hidden">
                                        <?php the_post_thumbnail('medium_large', [
                                            'class' => 'w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300',
                                            'loading' => 'lazy'
                                        ]); ?>
                                    </figure>
                                <?php endif; ?>
                                <div class="p-4">
                                    <time datetime="<?php echo get_the_date('Y-m-d'); ?>" class="block text-[11px] text-gray-500 uppercase mb-2">
                                        Publicado em <?php echo get_the_date('d.M.Y'); ?>
                                    </time>
                                    <h3 class="text-base font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                        <?php the_title(); ?>
                                    </h3>
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-3 leading-relaxed">
                                        <?php echo wp_trim_words(get_the_excerpt(), 25); ?>
                                    </p>
                                    <span class="text-blue-600 text-sm font-medium inline-flex items-center gap-1 group-hover:gap-2 transition-all">
                                        Continue lendo <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
                                    </span>
                                </div>
                            </a>
                        </article>
                <?php endwhile;
                    wp_reset_postdata();
                endif; ?>
            </div>
        </section>

        <hr class="border-t border-gray-200 my-8">

        <!-- Cabeçalho Outras -->
        <header class="mb-6">
            <h2 class="text-xl font-bold text-[#1B4D9E] uppercase tracking-wide">Outras Notícias</h2>
        </header>

        <?php
        // paginação
        $paged = max(1, get_query_var('paged'), get_query_var('page'));

        // 2) Outras notícias (sem imagem), exclui destaques, respeita categoria (se houver)
        $args_outras = [
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => 9,
            'paged'               => $paged,
            'post__not_in'        => $ids_destaque,
            'ignore_sticky_posts' => true,
        ];

        if ($current_cat_id) {
            $args_outras['cat'] = $current_cat_id;
        }

        $q_outras   = new WP_Query($args_outras);
        $max_pages  = $q_outras->max_num_pages ?: 1;
        ?>

        <!-- ===== Outras notícias ===== -->
        <div id="post-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
            data-current-page="<?php echo esc_attr($paged); ?>">
            <?php if ($q_outras->have_posts()) : while ($q_outras->have_posts()) : $q_outras->the_post(); ?>
                    <article class="bg-white border border-gray-200 rounded-lg p-5 hover:shadow-lg transition-shadow duration-300">
                        <a href="<?php the_permalink(); ?>" class="block group">
                            <time datetime="<?php echo get_the_date('Y-m-d'); ?>" class="block text-[11px] text-gray-500 uppercase mb-3 font-medium">
                                Publicado em <?php echo get_the_date('d.M.Y'); ?>
                            </time>
                            <h3 class="text-base font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors leading-tight">
                                <?php the_title(); ?>
                            </h3>
                            <p class="text-sm text-gray-700 mb-4 line-clamp-3 leading-relaxed">
                                <?php echo wp_trim_words(get_the_excerpt(), 25); ?>
                            </p>
                            <span class="text-blue-600 text-sm font-semibold inline-flex items-center gap-1 group-hover:gap-2 transition-all underline">
                                Continue lendo
                            </span>
                        </a>
                    </article>
                <?php endwhile;
                wp_reset_postdata();
            else: ?>
                <p class="text-sm text-gray-600">Nenhuma notícia encontrada.</p>
            <?php endif; ?>
        </div>

        <!-- Botão Carregar Mais (respeita categoria atual) -->
        <?php if ($paged < $max_pages) : ?>
            <div class="mt-10 text-center">
                <button id="load-more"
                    class="inline-flex items-center justify-center gap-2 px-8 py-3 bg-blue-600 text-white text-sm font-bold uppercase rounded hover:bg-blue-700 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-300"
                    data-next-page="<?php echo esc_attr($paged + 1); ?>"
                    data-max-pages="<?php echo esc_attr($max_pages); ?>"
                    data-exclude="<?php echo esc_attr(implode(',', $ids_destaque)); ?>"
                    data-cat="<?php echo esc_attr($current_cat_id); ?>">
                    <span>Carregar mais notícias</span>
                    <i class="fa-solid fa-sync text-sm" aria-hidden="true"></i>
                </button>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>