<?php

/**
 * Cria categorias padrão na ativação do tema
 */
function cchla_create_default_categories()
{
    if (!get_option('cchla_default_categories_created')) {

        // Categoria: Destaque
        if (!term_exists('Destaque', 'category')) {
            wp_insert_term('Destaque', 'category', array(
                'description' => 'Notícias em destaque na página inicial',
                'slug' => 'destaque'
            ));
        }

        // Categoria: Outros Destaques
        if (!term_exists('Outros Destaques', 'category')) {
            wp_insert_term('Outros Destaques', 'category', array(
                'description' => 'Outras notícias com imagens',
                'slug' => 'outros-destaques'
            ));
        }

        update_option('cchla_default_categories_created', true);
    }
}
add_action('after_switch_theme', 'cchla_create_default_categories');


/**
 * Função auxiliar para gerar imagem responsiva com srcset
 * 
 * @param int $attachment_id ID da imagem
 * @param string $size Tamanho padrão da imagem
 * @param array $args Argumentos adicionais (class, alt, etc)
 * @return string HTML da imagem com srcset
 */
function cchla_get_responsive_image($attachment_id, $size = 'full', $args = array())
{
    if (!$attachment_id) {
        return '';
    }

    // Argumentos padrão
    $defaults = array(
        'class' => '',
        'alt' => '',
        'loading' => 'lazy',
    );

    $args = wp_parse_args($args, $defaults);

    // Pega metadados da imagem
    $image_meta = wp_get_attachment_metadata($attachment_id);

    if (!$image_meta) {
        return wp_get_attachment_image($attachment_id, $size, false, $args);
    }

    // URL da imagem no tamanho especificado
    $image_src = wp_get_attachment_image_src($attachment_id, $size);

    if (!$image_src) {
        return '';
    }

    // Gera srcset automaticamente
    $srcset = wp_get_attachment_image_srcset($attachment_id, $size);
    $sizes = wp_get_attachment_image_sizes($attachment_id, $size);

    // Alt text
    $alt = !empty($args['alt']) ? $args['alt'] : get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

    // Monta o HTML
    $html = sprintf(
        '<img src="%s" srcset="%s" sizes="%s" alt="%s" class="%s" loading="%s" width="%d" height="%d">',
        esc_url($image_src[0]),
        esc_attr($srcset),
        esc_attr($sizes),
        esc_attr($alt),
        esc_attr($args['class']),
        esc_attr($args['loading']),
        intval($image_src[1]),
        intval($image_src[2])
    );

    return $html;
}

/**
 * Função para gerar srcset customizado com tamanhos específicos
 * 
 * @param int $attachment_id ID da imagem
 * @param array $sizes Array de tamanhos a incluir no srcset
 * @param string $default_size Tamanho padrão para src
 * @param array $args Argumentos adicionais
 * @return string HTML da imagem
 */
function cchla_get_custom_srcset_image($attachment_id, $sizes = array(), $default_size = 'medium', $args = array())
{
    if (!$attachment_id || empty($sizes)) {
        return cchla_get_responsive_image($attachment_id, $default_size, $args);
    }

    $defaults = array(
        'class' => '',
        'alt' => '',
        'loading' => 'lazy',
        'sizes_attr' => '100vw',
    );

    $args = wp_parse_args($args, $defaults);

    // Gera srcset customizado
    $srcset_array = array();

    foreach ($sizes as $size) {
        $image_src = wp_get_attachment_image_src($attachment_id, $size);
        if ($image_src) {
            $srcset_array[] = esc_url($image_src[0]) . ' ' . intval($image_src[1]) . 'w';
        }
    }

    // Imagem padrão
    $default_src = wp_get_attachment_image_src($attachment_id, $default_size);

    if (!$default_src) {
        return '';
    }

    // Alt text
    $alt = !empty($args['alt']) ? $args['alt'] : get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

    // Monta o HTML
    $html = sprintf(
        '<img src="%s" srcset="%s" sizes="%s" alt="%s" class="%s" loading="%s" width="%d" height="%d">',
        esc_url($default_src[0]),
        implode(', ', $srcset_array),
        esc_attr($args['sizes_attr']),
        esc_attr($alt),
        esc_attr($args['class']),
        esc_attr($args['loading']),
        intval($default_src[1]),
        intval($default_src[2])
    );

    return $html;
}

/**
 * Gera automaticamente todos os tamanhos ao fazer upload
 */
function cchla_generate_all_image_sizes($metadata, $attachment_id)
{
    if (!isset($metadata['width']) || !isset($metadata['height'])) {
        return $metadata;
    }

    // Força a geração de todos os tamanhos customizados
    $image_sizes = get_intermediate_image_sizes();

    foreach ($image_sizes as $size) {
        if (!isset($metadata['sizes'][$size])) {
            $resized = image_make_intermediate_size(
                get_attached_file($attachment_id),
                get_option("{$size}_size_w"),
                get_option("{$size}_size_h"),
                get_option("{$size}_crop")
            );

            if ($resized) {
                $metadata['sizes'][$size] = $resized;
            }
        }
    }

    return $metadata;
}
add_filter('wp_generate_attachment_metadata', 'cchla_generate_all_image_sizes', 10, 2);

/**
 * Shortcode para inserir imagem responsiva
 * Uso: [responsive_image id="123" size="cchla-post-card" class="rounded-lg"]
 */
function cchla_responsive_image_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'id' => 0,
        'size' => 'medium',
        'class' => '',
        'alt' => '',
    ), $atts);

    if (!$atts['id']) {
        return '';
    }

    return cchla_get_responsive_image(
        intval($atts['id']),
        sanitize_text_field($atts['size']),
        array(
            'class' => sanitize_text_field($atts['class']),
            'alt' => sanitize_text_field($atts['alt']),
        )
    );
}
add_shortcode('responsive_image', 'cchla_responsive_image_shortcode');

/**
 * Adiciona meta box para visualizar tamanhos gerados
 */
function cchla_image_sizes_meta_box()
{
    add_meta_box(
        'cchla_image_sizes',
        __('Tamanhos de Imagem Gerados', 'cchla-ufrn'),
        'cchla_image_sizes_meta_box_callback',
        'attachment',
        'side',
        'low'
    );
}
add_action('add_meta_boxes_attachment', 'cchla_image_sizes_meta_box');

/**
 * Callback da meta box de tamanhos de imagem
 */
function cchla_image_sizes_meta_box_callback($post)
{
    $metadata = wp_get_attachment_metadata($post->ID);

    if (!isset($metadata['sizes'])) {
        echo '<p>' . esc_html__('Nenhum tamanho adicional gerado.', 'cchla-ufrn') . '</p>';
        return;
    }

    echo '<ul style="margin: 0; padding-left: 20px; font-size: 12px;">';

    foreach ($metadata['sizes'] as $size => $data) {
        echo '<li><strong>' . esc_html($size) . ':</strong> ';
        echo esc_html($data['width']) . 'x' . esc_html($data['height']) . 'px</li>';
    }

    echo '</ul>';

    // Botão para regenerar
    echo '<p style="margin-top: 10px;">';
    echo '<button type="button" class="button button-small" onclick="cchlaRegenerateImage(' . intval($post->ID) . ')">';
    echo esc_html__('Regenerar Tamanhos', 'cchla-ufrn');
    echo '</button>';
    echo '</p>';
}

/**
 * Ajax para regenerar tamanhos de imagem individual
 */
function cchla_regenerate_image_ajax()
{
    check_ajax_referer('cchla-nonce', 'nonce');

    if (!current_user_can('upload_files')) {
        wp_send_json_error('Permissão negada');
    }

    $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;

    if (!$attachment_id) {
        wp_send_json_error('ID inválido');
    }

    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $file = get_attached_file($attachment_id);

    if (!file_exists($file)) {
        wp_send_json_error('Arquivo não encontrado');
    }

    // Remove tamanhos antigos
    $metadata = wp_get_attachment_metadata($attachment_id);

    if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
        foreach ($metadata['sizes'] as $size => $data) {
            $intermediate_file = str_replace(basename($file), $data['file'], $file);
            if (file_exists($intermediate_file)) {
                @unlink($intermediate_file);
            }
        }
    }

    // Gera novos tamanhos
    $new_metadata = wp_generate_attachment_metadata($attachment_id, $file);
    wp_update_attachment_metadata($attachment_id, $new_metadata);

    wp_send_json_success('Tamanhos regenerados com sucesso');
}
add_action('wp_ajax_cchla_regenerate_image', 'cchla_regenerate_image_ajax');

/**
 * Script para regenerar imagem no admin
 */
function cchla_admin_image_scripts()
{
?>
    <script>
        function cchlaRegenerateImage(attachmentId) {
            if (!confirm('<?php esc_html_e('Deseja regenerar todos os tamanhos desta imagem?', 'cchla-ufrn'); ?>')) {
                return;
            }

            var button = event.target;
            button.disabled = true;
            button.textContent = '<?php esc_html_e('Regenerando...', 'cchla-ufrn'); ?>';

            jQuery.post(ajaxurl, {
                action: 'cchla_regenerate_image',
                attachment_id: attachmentId,
                nonce: '<?php echo wp_create_nonce('cchla-nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    alert('<?php esc_html_e('Tamanhos regenerados com sucesso!', 'cchla-ufrn'); ?>');
                    location.reload();
                } else {
                    alert('<?php esc_html_e('Erro ao regenerar tamanhos.', 'cchla-ufrn'); ?>');
                    button.disabled = false;
                    button.textContent = '<?php esc_html_e('Regenerar Tamanhos', 'cchla-ufrn'); ?>';
                }
            });
        }
    </script>
    <?php
}
add_action('admin_footer-post.php', 'cchla_admin_image_scripts');


/**
 * Ativa suporte a imagens destacadas (thumbnails) em posts e custom post types
 */
function cchla_enable_post_thumbnails()
{

    // Ativa thumbnails para posts e páginas
    add_theme_support('post-thumbnails');

    // Define quais post types terão thumbnail
    add_theme_support('post-thumbnails', array(
        'post',
        'page',
        'eventos',
        'cursos',
        'pesquisas',
        'professores'
    ));

    // Define tamanho padrão da thumbnail (usado no admin)
    set_post_thumbnail_size(300, 200, true);
}
add_action('after_setup_theme', 'cchla_enable_post_thumbnails');


/**
 * ============================================
 * MENUS - CONFIGURAÇÃO E GERENCIAMENTO
 * ============================================
 */

/**
 * Registra localizações de menus
 */
function cchla_register_menus()
{
    register_nav_menus(array(
        'primary'     => esc_html__('Menu Principal', 'cchla-ufrn'),
        'footer-menu' => esc_html__('Menu do Rodapé', 'cchla-ufrn'),
        'legal-menu'  => esc_html__('Menu Legal (Privacidade, Termos)', 'cchla-ufrn'),
        'mobile-menu' => esc_html__('Menu Mobile (Opcional)', 'cchla-ufrn'),
    ));
}
add_action('after_setup_theme', 'cchla_register_menus');

/**
 * ============================================
 * TITLE TAG - GERENCIAMENTO COMPLETO
 * ============================================
 */

/**
 * Ativa suporte a título dinâmico
 */
add_theme_support('title-tag');

/**
 * Customiza o separador do título
 */
function cchla_document_title_separator($separator)
{
    return '–';
}
add_filter('document_title_separator', 'cchla_document_title_separator');

/**
 * Modifica as partes do título do documento
 */
function cchla_document_title_parts($title)
{
    global $page, $paged;

    // Página inicial
    if (is_front_page()) {
        $title['title'] = get_bloginfo('name');
        $title['tagline'] = get_bloginfo('description');
        unset($title['site']);
    }

    // Posts e páginas
    elseif (is_singular()) {
        // Usa título customizado se existir
        $custom_title = get_post_meta(get_the_ID(), '_cchla_custom_title', true);
        if (!empty($custom_title)) {
            $title['title'] = $custom_title;
        }

        // Adiciona número da página se for paginado
        if ($page > 1 || $paged > 1) {
            $title['page'] = sprintf(__('Página %s', 'cchla-ufrn'), max($page, $paged));
        }
    }

    // Categorias
    elseif (is_category()) {
        $title['title'] = single_cat_title('', false);

        // Adiciona número da página
        if ($paged > 1) {
            $title['page'] = sprintf(__('Página %s', 'cchla-ufrn'), $paged);
        }
    }

    // Tags
    elseif (is_tag()) {
        $title['title'] = single_tag_title('', false);

        if ($paged > 1) {
            $title['page'] = sprintf(__('Página %s', 'cchla-ufrn'), $paged);
        }
    }

    // Taxonomias customizadas
    elseif (is_tax()) {
        $term = get_queried_object();
        $title['title'] = $term->name;

        if ($paged > 1) {
            $title['page'] = sprintf(__('Página %s', 'cchla-ufrn'), $paged);
        }
    }

    // Arquivo de autor
    elseif (is_author()) {
        $author = get_queried_object();
        $title['title'] = sprintf(__('Posts de %s', 'cchla-ufrn'), $author->display_name);

        if ($paged > 1) {
            $title['page'] = sprintf(__('Página %s', 'cchla-ufrn'), $paged);
        }
    }

    // Arquivo de data
    elseif (is_date()) {
        if (is_day()) {
            $title['title'] = get_the_date();
        } elseif (is_month()) {
            $title['title'] = get_the_date('F Y');
        } elseif (is_year()) {
            $title['title'] = get_the_date('Y');
        }

        if ($paged > 1) {
            $title['page'] = sprintf(__('Página %s', 'cchla-ufrn'), $paged);
        }
    }

    // Busca
    elseif (is_search()) {
        $title['title'] = sprintf(__('Resultados da busca por: %s', 'cchla-ufrn'), get_search_query());

        if ($paged > 1) {
            $title['page'] = sprintf(__('Página %s', 'cchla-ufrn'), $paged);
        }
    }

    // Página 404
    elseif (is_404()) {
        $title['title'] = __('Página não encontrada', 'cchla-ufrn');
        unset($title['tagline']);
    }

    // Custom Post Type Archive
    elseif (is_post_type_archive()) {
        $title['title'] = post_type_archive_title('', false);

        if ($paged > 1) {
            $title['page'] = sprintf(__('Página %s', 'cchla-ufrn'), $paged);
        }
    }

    return $title;
}
add_filter('document_title_parts', 'cchla_document_title_parts');



/**
 * ============================================
 * CARREGAMENTO AJAX
 * ============================================
 */
function mytheme_enqueue_loadmore()
{
    wp_enqueue_script(
        'loadmore',
        get_template_directory_uri() . '/assets/scripts/loadmore.js',
        ['jquery'],
        null,
        true
    );

    wp_localize_script('loadmore', 'loadmore', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('loadmore_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'mytheme_enqueue_loadmore');

function mytheme_ajax_load_more()
{
    check_ajax_referer('loadmore_nonce', 'nonce');

    $page      = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $exclude   = !empty($_POST['exclude']) ? array_map('intval', explode(',', $_POST['exclude'])) : [];
    $cat_id    = isset($_POST['cat']) ? intval($_POST['cat']) : 0;
    $tag_id    = isset($_POST['tag']) ? intval($_POST['tag']) : 0;
    $author_id = isset($_POST['author']) ? intval($_POST['author']) : 0;

    $args = [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 9,
        'paged'               => $page,
        'ignore_sticky_posts' => true,
    ];
    if (!empty($exclude)) $args['post__not_in'] = $exclude;
    if ($cat_id)    $args['cat']    = $cat_id;
    if ($tag_id)    $args['tag_id'] = $tag_id;
    if ($author_id) $args['author'] = $author_id;

    $q = new WP_Query($args);

    if ($q->have_posts()):
        while ($q->have_posts()): $q->the_post(); ?>
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
    endif;

    wp_die();
}
add_action('wp_ajax_mytheme_load_more', 'mytheme_ajax_load_more');
add_action('wp_ajax_nopriv_mytheme_load_more', 'mytheme_ajax_load_more');

/**
 * ============================================
 * CUSTOM POST TYPE - ACESSO RÁPIDO
 * ============================================
 */

/**
 * Registra o Custom Post Type "Acesso Rápido"
 */
function cchla_register_acesso_rapido_cpt()
{
    $labels = array(
        'name'                  => _x('Acessos Rápidos', 'Post Type General Name', 'cchla-ufrn'),
        'singular_name'         => _x('Acesso Rápido', 'Post Type Singular Name', 'cchla-ufrn'),
        'menu_name'             => __('Acessos Rápidos', 'cchla-ufrn'),
        'name_admin_bar'        => __('Acesso Rápido', 'cchla-ufrn'),
        'archives'              => __('Arquivo de Acessos', 'cchla-ufrn'),
        'attributes'            => __('Atributos', 'cchla-ufrn'),
        'parent_item_colon'     => __('Acesso Pai:', 'cchla-ufrn'),
        'all_items'             => __('Todos os Acessos', 'cchla-ufrn'),
        'add_new_item'          => __('Adicionar Novo Acesso', 'cchla-ufrn'),
        'add_new'               => __('Adicionar Novo', 'cchla-ufrn'),
        'new_item'              => __('Novo Acesso', 'cchla-ufrn'),
        'edit_item'             => __('Editar Acesso', 'cchla-ufrn'),
        'update_item'           => __('Atualizar Acesso', 'cchla-ufrn'),
        'view_item'             => __('Ver Acesso', 'cchla-ufrn'),
        'view_items'            => __('Ver Acessos', 'cchla-ufrn'),
        'search_items'          => __('Buscar Acesso', 'cchla-ufrn'),
        'not_found'             => __('Nenhum acesso encontrado', 'cchla-ufrn'),
        'not_found_in_trash'    => __('Nenhum acesso na lixeira', 'cchla-ufrn'),
    );

    $args = array(
        'label'                 => __('Acesso Rápido', 'cchla-ufrn'),
        'description'           => __('Links de acesso rápido para sistemas externos', 'cchla-ufrn'),
        'labels'                => $labels,
        'supports'              => array('title', 'thumbnail'),
        'taxonomies'            => array('categoria_acesso'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 25,
        'menu_icon'             => 'dashicons-external',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => 'sistemas',
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite'               => array('slug' => 'sistema', 'with_front' => false),
    );

    register_post_type('acesso_rapido', $args);
}
add_action('init', 'cchla_register_acesso_rapido_cpt', 0);

/**
 * Registra a Taxonomia "Categoria de Acesso"
 */
function cchla_register_categoria_acesso_taxonomy()
{
    $labels = array(
        'name'                       => _x('Categorias de Sistemas', 'Taxonomy General Name', 'cchla-ufrn'),
        'singular_name'              => _x('Categoria de Sistema', 'Taxonomy Singular Name', 'cchla-ufrn'),
        'menu_name'                  => __('Categorias', 'cchla-ufrn'),
        'all_items'                  => __('Todas as Categorias', 'cchla-ufrn'),
        'parent_item'                => __('Categoria Pai', 'cchla-ufrn'),
        'parent_item_colon'          => __('Categoria Pai:', 'cchla-ufrn'),
        'new_item_name'              => __('Nova Categoria', 'cchla-ufrn'),
        'add_new_item'               => __('Adicionar Nova Categoria', 'cchla-ufrn'),
        'edit_item'                  => __('Editar Categoria', 'cchla-ufrn'),
        'update_item'                => __('Atualizar Categoria', 'cchla-ufrn'),
        'view_item'                  => __('Ver Categoria', 'cchla-ufrn'),
        'separate_items_with_commas' => __('Separe categorias com vírgulas', 'cchla-ufrn'),
        'add_or_remove_items'        => __('Adicionar ou remover categorias', 'cchla-ufrn'),
        'choose_from_most_used'      => __('Escolher das mais usadas', 'cchla-ufrn'),
        'popular_items'              => __('Categorias Populares', 'cchla-ufrn'),
        'search_items'               => __('Buscar Categorias', 'cchla-ufrn'),
        'not_found'                  => __('Nenhuma categoria encontrada', 'cchla-ufrn'),
    );

    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'show_in_rest'               => true,
    );

    register_taxonomy('categoria_acesso', array('acesso_rapido'), $args);
}
add_action('init', 'cchla_register_categoria_acesso_taxonomy', 0);

/**
 * Adiciona Meta Boxes para Acesso Rápido
 */
function cchla_acesso_rapido_meta_boxes()
{
    add_meta_box(
        'cchla_acesso_rapido_details',
        __('Detalhes do Acesso', 'cchla-ufrn'),
        'cchla_acesso_rapido_meta_box_callback',
        'acesso_rapido',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cchla_acesso_rapido_meta_boxes');

/**
 * Callback do Meta Box
 */
function cchla_acesso_rapido_meta_box_callback($post)
{
    wp_nonce_field('cchla_save_acesso_rapido_meta', 'cchla_acesso_rapido_nonce');

    $descricao = get_post_meta($post->ID, '_acesso_descricao', true);
    $link_externo = get_post_meta($post->ID, '_acesso_link_externo', true);
    $abertura = get_post_meta($post->ID, '_acesso_abertura', true);
    $icone_url = get_post_meta($post->ID, '_acesso_icone_url', true);
    $icone_classe = get_post_meta($post->ID, '_acesso_icone_classe', true);
    $tipo_icone = get_post_meta($post->ID, '_acesso_tipo_icone', true);
    $ordem = get_post_meta($post->ID, '_acesso_ordem', true);

    // Defaults
    $abertura = $abertura ? $abertura : '_blank';
    $tipo_icone = $tipo_icone ? $tipo_icone : 'imagem';
    $ordem = $ordem ? $ordem : 0;
    ?>

    <div class="cchla-acesso-meta-box">

        <!-- Descrição Curta -->
        <p class="form-field">
            <label for="acesso_descricao">
                <strong><?php esc_html_e('Descrição Curta', 'cchla-ufrn'); ?></strong>
            </label>
            <textarea
                id="acesso_descricao"
                name="acesso_descricao"
                rows="3"
                class="large-text"
                placeholder="<?php esc_attr_e('Ex: Sistema Integrado de Gestão de Atividades Acadêmicas', 'cchla-ufrn'); ?>"><?php echo esc_textarea($descricao); ?></textarea>
            <span class="description">
                <?php esc_html_e('Breve descrição do sistema (recomendado: até 100 caracteres)', 'cchla-ufrn'); ?>
            </span>
        </p>

        <!-- Link Externo -->
        <p class="form-field">
            <label for="acesso_link_externo">
                <strong><?php esc_html_e('Link Externo', 'cchla-ufrn'); ?></strong> <span class="required">*</span>
            </label>
            <input
                type="url"
                id="acesso_link_externo"
                name="acesso_link_externo"
                value="<?php echo esc_url($link_externo); ?>"
                class="large-text"
                placeholder="https://sigaa.ufrn.br"
                required>
            <span class="description">
                <?php esc_html_e('URL completa do sistema externo', 'cchla-ufrn'); ?>
            </span>
        </p>

        <!-- Forma de Abertura -->
        <p class="form-field">
            <label for="acesso_abertura">
                <strong><?php esc_html_e('Como Abrir o Link', 'cchla-ufrn'); ?></strong>
            </label>
            <select id="acesso_abertura" name="acesso_abertura" class="regular-text">
                <option value="_blank" <?php selected($abertura, '_blank'); ?>>
                    <?php esc_html_e('Nova aba (_blank)', 'cchla-ufrn'); ?>
                </option>
                <option value="_self" <?php selected($abertura, '_self'); ?>>
                    <?php esc_html_e('Mesma página (_self)', 'cchla-ufrn'); ?>
                </option>
            </select>
        </p>

        <!-- Tipo de Ícone -->
        <p class="form-field">
            <label>
                <strong><?php esc_html_e('Tipo de Ícone', 'cchla-ufrn'); ?></strong>
            </label>
            <label style="margin-right: 20px;">
                <input type="radio" name="acesso_tipo_icone" value="imagem" <?php checked($tipo_icone, 'imagem'); ?>>
                <?php esc_html_e('Imagem (34x34px)', 'cchla-ufrn'); ?>
            </label>
            <label>
                <input type="radio" name="acesso_tipo_icone" value="classe" <?php checked($tipo_icone, 'classe'); ?>>
                <?php esc_html_e('Classe Font Awesome', 'cchla-ufrn'); ?>
            </label>
        </p>

        <!-- Upload de Imagem -->
        <div class="form-field icone-imagem-field" style="<?php echo $tipo_icone === 'classe' ? 'display:none;' : ''; ?>">
            <label>
                <strong><?php esc_html_e('Ícone (Imagem)', 'cchla-ufrn'); ?></strong>
            </label>
            <div style="display: flex; gap: 10px; align-items: center;">
                <input
                    type="hidden"
                    id="acesso_icone_url"
                    name="acesso_icone_url"
                    value="<?php echo esc_url($icone_url); ?>">
                <button type="button" class="button upload-icone-button">
                    <?php esc_html_e('Escolher Imagem', 'cchla-ufrn'); ?>
                </button>
                <button type="button" class="button remove-icone-button" style="<?php echo empty($icone_url) ? 'display:none;' : ''; ?>">
                    <?php esc_html_e('Remover', 'cchla-ufrn'); ?>
                </button>
            </div>
            <div class="icone-preview" style="margin-top: 10px;">
                <?php if ($icone_url) : ?>
                    <img src="<?php echo esc_url($icone_url); ?>" style="max-width: 34px; max-height: 34px; border: 1px solid #ddd; padding: 5px;">
                <?php endif; ?>
            </div>
            <span class="description">
                <?php esc_html_e('Recomendado: 34x34 pixels (PNG com fundo transparente)', 'cchla-ufrn'); ?>
            </span>
        </div>

        <!-- Classe Font Awesome -->
        <div class="form-field icone-classe-field" style="<?php echo $tipo_icone === 'imagem' ? 'display:none;' : ''; ?>">
            <label for="acesso_icone_classe">
                <strong><?php esc_html_e('Classe Font Awesome', 'cchla-ufrn'); ?></strong>
            </label>
            <input
                type="text"
                id="acesso_icone_classe"
                name="acesso_icone_classe"
                value="<?php echo esc_attr($icone_classe); ?>"
                class="regular-text"
                placeholder="fa-solid fa-graduation-cap">
            <span class="description">
                <?php esc_html_e('Ex: fa-solid fa-graduation-cap, fa-brands fa-github', 'cchla-ufrn'); ?>
                <a href="https://fontawesome.com/icons" target="_blank">
                    <?php esc_html_e('Ver ícones', 'cchla-ufrn'); ?>
                </a>
            </span>
            <div style="margin-top: 10px; font-size: 32px;">
                <i class="<?php echo esc_attr($icone_classe); ?>" style="color: #1e40af;"></i>
            </div>
        </div>

        <!-- Ordem -->
        <p class="form-field">
            <label for="acesso_ordem">
                <strong><?php esc_html_e('Ordem de Exibição', 'cchla-ufrn'); ?></strong>
            </label>
            <input
                type="number"
                id="acesso_ordem"
                name="acesso_ordem"
                value="<?php echo esc_attr($ordem); ?>"
                min="0"
                step="1"
                class="small-text">
            <span class="description">
                <?php esc_html_e('Menor número aparece primeiro (0 = primeiro)', 'cchla-ufrn'); ?>
            </span>
        </p>

    </div>

    <style>
        .cchla-acesso-meta-box .form-field {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f1;
        }

        .cchla-acesso-meta-box .form-field:last-child {
            border-bottom: none;
        }

        .cchla-acesso-meta-box label strong {
            display: block;
            margin-bottom: 5px;
        }

        .cchla-acesso-meta-box .description {
            display: block;
            margin-top: 5px;
            font-style: italic;
            color: #646970;
        }

        .required {
            color: #d63638;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            // Toggle entre imagem e classe
            $('input[name="acesso_tipo_icone"]').on('change', function() {
                if ($(this).val() === 'imagem') {
                    $('.icone-imagem-field').show();
                    $('.icone-classe-field').hide();
                } else {
                    $('.icone-imagem-field').hide();
                    $('.icone-classe-field').show();
                }
            });

            // Upload de imagem
            var mediaUploader;

            $('.upload-icone-button').on('click', function(e) {
                e.preventDefault();

                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }

                mediaUploader = wp.media({
                    title: '<?php esc_html_e('Escolher Ícone', 'cchla-ufrn'); ?>',
                    button: {
                        text: '<?php esc_html_e('Usar este ícone', 'cchla-ufrn'); ?>'
                    },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#acesso_icone_url').val(attachment.url);
                    $('.icone-preview').html('<img src="' + attachment.url + '" style="max-width: 34px; max-height: 34px; border: 1px solid #ddd; padding: 5px;">');
                    $('.remove-icone-button').show();
                });

                mediaUploader.open();
            });

            // Remover imagem
            $('.remove-icone-button').on('click', function(e) {
                e.preventDefault();
                $('#acesso_icone_url').val('');
                $('.icone-preview').html('');
                $(this).hide();
            });

            // Preview da classe Font Awesome
            $('#acesso_icone_classe').on('input', function() {
                var classes = $(this).val();
                $(this).parent().find('i').attr('class', classes);
            });
        });
    </script>
    <?php
}

/**
 * Salva os Meta Dados
 */
function cchla_save_acesso_rapido_meta($post_id)
{
    if (
        !isset($_POST['cchla_acesso_rapido_nonce']) ||
        !wp_verify_nonce($_POST['cchla_acesso_rapido_nonce'], 'cchla_save_acesso_rapido_meta')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Descrição
    if (isset($_POST['acesso_descricao'])) {
        update_post_meta($post_id, '_acesso_descricao', sanitize_textarea_field($_POST['acesso_descricao']));
    }

    // Link Externo
    if (isset($_POST['acesso_link_externo'])) {
        update_post_meta($post_id, '_acesso_link_externo', esc_url_raw($_POST['acesso_link_externo']));
    }

    // Abertura
    if (isset($_POST['acesso_abertura'])) {
        update_post_meta($post_id, '_acesso_abertura', sanitize_text_field($_POST['acesso_abertura']));
    }

    // Tipo de Ícone
    if (isset($_POST['acesso_tipo_icone'])) {
        update_post_meta($post_id, '_acesso_tipo_icone', sanitize_text_field($_POST['acesso_tipo_icone']));
    }

    // Ícone URL
    if (isset($_POST['acesso_icone_url'])) {
        update_post_meta($post_id, '_acesso_icone_url', esc_url_raw($_POST['acesso_icone_url']));
    }

    // Ícone Classe
    if (isset($_POST['acesso_icone_classe'])) {
        update_post_meta($post_id, '_acesso_icone_classe', sanitize_text_field($_POST['acesso_icone_classe']));
    }

    // Ordem
    if (isset($_POST['acesso_ordem'])) {
        update_post_meta($post_id, '_acesso_ordem', absint($_POST['acesso_ordem']));
    }
}
add_action('save_post', 'cchla_save_acesso_rapido_meta');

/**
 * Adiciona colunas personalizadas no admin
 */
function cchla_acesso_rapido_columns($columns)
{
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['icone'] = __('Ícone', 'cchla-ufrn');
    $new_columns['title'] = $columns['title'];
    $new_columns['link'] = __('Link', 'cchla-ufrn');
    $new_columns['ordem'] = __('Ordem', 'cchla-ufrn');
    $new_columns['taxonomy-categoria_acesso'] = __('Categoria', 'cchla-ufrn');
    $new_columns['date'] = $columns['date'];

    return $new_columns;
}
add_filter('manage_acesso_rapido_posts_columns', 'cchla_acesso_rapido_columns');

function cchla_acesso_rapido_column_content($column, $post_id)
{
    switch ($column) {
        case 'icone':
            $tipo_icone = get_post_meta($post_id, '_acesso_tipo_icone', true);

            if ($tipo_icone === 'classe') {
                $classe = get_post_meta($post_id, '_acesso_icone_classe', true);
                if ($classe) {
                    echo '<i class="' . esc_attr($classe) . '" style="font-size: 24px; color: #1e40af;"></i>';
                }
            } else {
                $url = get_post_meta($post_id, '_acesso_icone_url', true);
                if ($url) {
                    echo '<img src="' . esc_url($url) . '" style="max-width: 34px; max-height: 34px;">';
                }
            }
            break;

        case 'link':
            $link = get_post_meta($post_id, '_acesso_link_externo', true);
            if ($link) {
                echo '<a href="' . esc_url($link) . '" target="_blank" rel="noopener">' . esc_html($link) . '</a>';
            }
            break;

        case 'ordem':
            $ordem = get_post_meta($post_id, '_acesso_ordem', true);
            echo '<strong>' . esc_html($ordem ? $ordem : '0') . '</strong>';
            break;
    }
}
add_action('manage_acesso_rapido_posts_custom_column', 'cchla_acesso_rapido_column_content', 10, 2);

/**
 * Torna a coluna Ordem ordenável
 */
function cchla_acesso_rapido_sortable_columns($columns)
{
    $columns['ordem'] = 'acesso_ordem';
    return $columns;
}
add_filter('manage_edit-acesso_rapido_sortable_columns', 'cchla_acesso_rapido_sortable_columns');

function cchla_acesso_rapido_orderby($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ('acesso_ordem' === $query->get('orderby')) {
        $query->set('meta_key', '_acesso_ordem');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'cchla_acesso_rapido_orderby');

# fim de customização do post type

/**
 * Função auxiliar para exibir acessos rápidos em qualquer lugar
 */
function cchla_get_acessos_rapidos($args = array())
{
    $defaults = array(
        'limite' => 6,
        'categoria' => '',
        'ordem' => 'ASC',
        'mostrar_icone' => true,
        'mostrar_descricao' => true,
    );

    $args = wp_parse_args($args, $defaults);

    $query_args = array(
        'post_type' => 'acesso_rapido',
        'posts_per_page' => $args['limite'],
        'orderby' => 'meta_value_num',
        'meta_key' => '_acesso_ordem',
        'order' => $args['ordem'],
        'post_status' => 'publish',
    );

    if (!empty($args['categoria'])) {
        $query_args['tax_query'] = array(
            array(
                'taxonomy' => 'categoria_acesso',
                'field' => 'slug',
                'terms' => $args['categoria'],
            ),
        );
    }

    $query = new WP_Query($query_args);

    if (!$query->have_posts()) {
        return '';
    }

    ob_start();

    while ($query->have_posts()) {
        $query->the_post();

        $descricao = get_post_meta(get_the_ID(), '_acesso_descricao', true);
        $link_externo = get_post_meta(get_the_ID(), '_acesso_link_externo', true);
        $abertura = get_post_meta(get_the_ID(), '_acesso_abertura', true);
        $tipo_icone = get_post_meta(get_the_ID(), '_acesso_tipo_icone', true);
        $icone_url = get_post_meta(get_the_ID(), '_acesso_icone_url', true);
        $icone_classe = get_post_meta(get_the_ID(), '_acesso_icone_classe', true);

        $abertura = $abertura ? $abertura : '_blank';
        $rel = ($abertura === '_blank') ? 'noopener noreferrer' : '';
    ?>

        <a href="<?php echo esc_url($link_externo); ?>"
            target="<?php echo esc_attr($abertura); ?>"
            <?php if ($rel) echo 'rel="' . esc_attr($rel) . '"'; ?>
            class="group flex flex-col gap-2 p-5 border border-blue-200 rounded-sm hover:bg-blue-50 hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300 transition-all duration-200">

            <?php if ($args['mostrar_icone']) : ?>
                <?php if ($tipo_icone === 'classe' && $icone_classe) : ?>
                    <div class="mb-2">
                        <i class="<?php echo esc_attr($icone_classe); ?>"
                            style="font-size: 34px; color: #1e40af;"
                            aria-hidden="true"></i>
                    </div>
                <?php elseif ($tipo_icone === 'imagem' && $icone_url) : ?>
                    <div class="mb-2">
                        <img src="<?php echo esc_url($icone_url); ?>"
                            alt="<?php echo esc_attr(get_the_title()); ?>"
                            class="w-[34px] h-[34px] object-contain">
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <strong class="text-blue-900 group-hover:text-blue-700 transition-colors">
                <?php the_title(); ?>
            </strong>

            <?php if ($args['mostrar_descricao'] && $descricao) : ?>
                <p class="text-sm text-gray-600">
                    <?php echo esc_html($descricao); ?>
                </p>
            <?php endif; ?>
        </a>

    <?php
    }

    wp_reset_postdata();

    return ob_get_clean();
}

/**
 * Shortcode para Acesso Rápido
 * Uso: [acessos_rapidos limite="6" categoria="ufrn"]
 */
function cchla_acessos_rapidos_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'limite' => 6,
        'categoria' => '',
        'colunas' => '3',
        'mostrar_icone' => 'sim',
        'mostrar_descricao' => 'sim',
    ), $atts, 'acessos_rapidos');

    $args = array(
        'limite' => intval($atts['limite']),
        'categoria' => sanitize_text_field($atts['categoria']),
        'mostrar_icone' => $atts['mostrar_icone'] === 'sim',
        'mostrar_descricao' => $atts['mostrar_descricao'] === 'sim',
    );

    $content = cchla_get_acessos_rapidos($args);

    if (empty($content)) {
        return '';
    }

    $colunas_class = 'grid-cols-' . esc_attr($atts['colunas']);

    return sprintf(
        '<div class="grid %s gap-8 max-lg:grid-cols-2 max-md:grid-cols-1">%s</div>',
        $colunas_class,
        $content
    );
}
add_shortcode('acessos_rapidos', 'cchla_acessos_rapidos_shortcode');

/**
 * Widget de Acesso Rápido
 */
class CCHLA_Acessos_Rapidos_Widget extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'cchla_acessos_rapidos_widget',
            __('CCHLA - Acessos Rápidos', 'cchla-ufrn'),
            array('description' => __('Exibe links de acesso rápido', 'cchla-ufrn'))
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $widget_args = array(
            'limite' => !empty($instance['limite']) ? $instance['limite'] : 6,
            'categoria' => !empty($instance['categoria']) ? $instance['categoria'] : '',
            'mostrar_icone' => !empty($instance['mostrar_icone']),
            'mostrar_descricao' => !empty($instance['mostrar_descricao']),
        );

        echo '<div class="acessos-rapidos-widget">';
        echo cchla_get_acessos_rapidos($widget_args);
        echo '</div>';

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : __('Acesso Rápido', 'cchla-ufrn');
        $limite = !empty($instance['limite']) ? $instance['limite'] : 6;
        $categoria = !empty($instance['categoria']) ? $instance['categoria'] : '';
        $mostrar_icone = isset($instance['mostrar_icone']) ? (bool) $instance['mostrar_icone'] : true;
        $mostrar_descricao = isset($instance['mostrar_descricao']) ? (bool) $instance['mostrar_descricao'] : true;

        // Busca categorias
        $categorias = get_terms(array(
            'taxonomy' => 'categoria_acesso',
            'hide_empty' => false,
        ));
    ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Título:', 'cchla-ufrn'); ?>
            </label>
            <input class="widefat"
                id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                type="text"
                value="<?php echo esc_attr($title); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('limite')); ?>">
                <?php esc_html_e('Quantidade:', 'cchla-ufrn'); ?>
            </label>
            <input class="tiny-text"
                id="<?php echo esc_attr($this->get_field_id('limite')); ?>"
                name="<?php echo esc_attr($this->get_field_name('limite')); ?>"
                type="number"
                min="1"
                max="20"
                value="<?php echo esc_attr($limite); ?>">
        </p>

        <?php if ($categorias && !is_wp_error($categorias)) : ?>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('categoria')); ?>">
                    <?php esc_html_e('Categoria:', 'cchla-ufrn'); ?>
                </label>
                <select class="widefat"
                    id="<?php echo esc_attr($this->get_field_id('categoria')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('categoria')); ?>">
                    <option value=""><?php esc_html_e('Todas', 'cchla-ufrn'); ?></option>
                    <?php foreach ($categorias as $cat) : ?>
                        <option value="<?php echo esc_attr($cat->slug); ?>" <?php selected($categoria, $cat->slug); ?>>
                            <?php echo esc_html($cat->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
        <?php endif; ?>

        <p>
            <input class="checkbox"
                type="checkbox"
                <?php checked($mostrar_icone); ?>
                id="<?php echo esc_attr($this->get_field_id('mostrar_icone')); ?>"
                name="<?php echo esc_attr($this->get_field_name('mostrar_icone')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('mostrar_icone')); ?>">
                <?php esc_html_e('Mostrar ícones', 'cchla-ufrn'); ?>
            </label>
        </p>

        <p>
            <input class="checkbox"
                type="checkbox"
                <?php checked($mostrar_descricao); ?>
                id="<?php echo esc_attr($this->get_field_id('mostrar_descricao')); ?>"
                name="<?php echo esc_attr($this->get_field_name('mostrar_descricao')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('mostrar_descricao')); ?>">
                <?php esc_html_e('Mostrar descrições', 'cchla-ufrn'); ?>
            </label>
        </p>
    <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['limite'] = (!empty($new_instance['limite'])) ? absint($new_instance['limite']) : 6;
        $instance['categoria'] = (!empty($new_instance['categoria'])) ? sanitize_text_field($new_instance['categoria']) : '';
        $instance['mostrar_icone'] = !empty($new_instance['mostrar_icone']);
        $instance['mostrar_descricao'] = !empty($new_instance['mostrar_descricao']);
        return $instance;
    }
}

function cchla_register_acessos_rapidos_widget()
{
    register_widget('CCHLA_Acessos_Rapidos_Widget');
}
add_action('widgets_init', 'cchla_register_acessos_rapidos_widget');

/**
 * ============================================
 * CUSTOM POST TYPE - PUBLICAÇÕES
 * ============================================
 */

/**
 * Registra o Custom Post Type "Publicações"
 */
function cchla_register_publicacoes_cpt()
{
    $labels = array(
        'name'                  => _x('Publicações', 'Post Type General Name', 'cchla-ufrn'),
        'singular_name'         => _x('Publicação', 'Post Type Singular Name', 'cchla-ufrn'),
        'menu_name'             => __('Publicações', 'cchla-ufrn'),
        'name_admin_bar'        => __('Publicação', 'cchla-ufrn'),
        'archives'              => __('Arquivo de Publicações', 'cchla-ufrn'),
        'attributes'            => __('Atributos', 'cchla-ufrn'),
        'parent_item_colon'     => __('Publicação Pai:', 'cchla-ufrn'),
        'all_items'             => __('Todas as Publicações', 'cchla-ufrn'),
        'add_new_item'          => __('Adicionar Nova Publicação', 'cchla-ufrn'),
        'add_new'               => __('Adicionar Nova', 'cchla-ufrn'),
        'new_item'              => __('Nova Publicação', 'cchla-ufrn'),
        'edit_item'             => __('Editar Publicação', 'cchla-ufrn'),
        'update_item'           => __('Atualizar Publicação', 'cchla-ufrn'),
        'view_item'             => __('Ver Publicação', 'cchla-ufrn'),
        'view_items'            => __('Ver Publicações', 'cchla-ufrn'),
        'search_items'          => __('Buscar Publicação', 'cchla-ufrn'),
        'not_found'             => __('Nenhuma publicação encontrada', 'cchla-ufrn'),
        'not_found_in_trash'    => __('Nenhuma publicação na lixeira', 'cchla-ufrn'),
    );

    $args = array(
        'label'                 => __('Publicação', 'cchla-ufrn'),
        'description'           => __('Publicações acadêmicas do CCHLA', 'cchla-ufrn'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
        'taxonomies'            => array('tipo_publicacao'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 26,
        'menu_icon'             => 'dashicons-book-alt',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => 'publicacoes',
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite'               => array('slug' => 'publicacao', 'with_front' => false),
    );

    register_post_type('publicacoes', $args);
}
add_action('init', 'cchla_register_publicacoes_cpt', 0);

/**
 * Registra a Taxonomia "Tipo de Publicação"
 */
function cchla_register_tipo_publicacao_taxonomy()
{
    $labels = array(
        'name'                       => _x('Tipos de Publicação', 'Taxonomy General Name', 'cchla-ufrn'),
        'singular_name'              => _x('Tipo de Publicação', 'Taxonomy Singular Name', 'cchla-ufrn'),
        'menu_name'                  => __('Tipos', 'cchla-ufrn'),
        'all_items'                  => __('Todos os Tipos', 'cchla-ufrn'),
        'parent_item'                => __('Tipo Pai', 'cchla-ufrn'),
        'parent_item_colon'          => __('Tipo Pai:', 'cchla-ufrn'),
        'new_item_name'              => __('Novo Tipo', 'cchla-ufrn'),
        'add_new_item'               => __('Adicionar Novo Tipo', 'cchla-ufrn'),
        'edit_item'                  => __('Editar Tipo', 'cchla-ufrn'),
        'update_item'                => __('Atualizar Tipo', 'cchla-ufrn'),
        'view_item'                  => __('Ver Tipo', 'cchla-ufrn'),
        'separate_items_with_commas' => __('Separe tipos com vírgulas', 'cchla-ufrn'),
        'add_or_remove_items'        => __('Adicionar ou remover tipos', 'cchla-ufrn'),
        'choose_from_most_used'      => __('Escolher dos mais usados', 'cchla-ufrn'),
        'popular_items'              => __('Tipos Populares', 'cchla-ufrn'),
        'search_items'               => __('Buscar Tipos', 'cchla-ufrn'),
        'not_found'                  => __('Nenhum tipo encontrado', 'cchla-ufrn'),
    );

    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'show_in_rest'               => true,
    );

    register_taxonomy('tipo_publicacao', array('publicacoes'), $args);
}
add_action('init', 'cchla_register_tipo_publicacao_taxonomy', 0);

/**
 * Cria termos padrão para Tipo de Publicação
 */
function cchla_insert_default_tipo_publicacao_terms()
{
    if (!term_exists('Livro', 'tipo_publicacao')) {
        wp_insert_term('Livro', 'tipo_publicacao', array('slug' => 'livro'));
    }
    if (!term_exists('Artigo', 'tipo_publicacao')) {
        wp_insert_term('Artigo', 'tipo_publicacao', array('slug' => 'artigo'));
    }
    if (!term_exists('Revista', 'tipo_publicacao')) {
        wp_insert_term('Revista', 'tipo_publicacao', array('slug' => 'revista'));
    }
    if (!term_exists('Periódico', 'tipo_publicacao')) {
        wp_insert_term('Periódico', 'tipo_publicacao', array('slug' => 'periodico'));
    }
    if (!term_exists('E-book', 'tipo_publicacao')) {
        wp_insert_term('E-book', 'tipo_publicacao', array('slug' => 'ebook'));
    }
    if (!term_exists('Capítulo de Livro', 'tipo_publicacao')) {
        wp_insert_term('Capítulo de Livro', 'tipo_publicacao', array('slug' => 'capitulo'));
    }
    if (!term_exists('Tese', 'tipo_publicacao')) {
        wp_insert_term('Tese', 'tipo_publicacao', array('slug' => 'tese'));
    }
    if (!term_exists('Dissertação', 'tipo_publicacao')) {
        wp_insert_term('Dissertação', 'tipo_publicacao', array('slug' => 'dissertacao'));
    }
}
add_action('init', 'cchla_insert_default_tipo_publicacao_terms');

/**
 * Adiciona Meta Boxes para Publicações
 */
function cchla_publicacoes_meta_boxes()
{
    add_meta_box(
        'cchla_publicacao_details',
        __('Detalhes da Publicação', 'cchla-ufrn'),
        'cchla_publicacao_meta_box_callback',
        'publicacoes',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cchla_publicacoes_meta_boxes');

/**
 * Callback do Meta Box
 */
function cchla_publicacao_meta_box_callback($post)
{
    wp_nonce_field('cchla_save_publicacao_meta', 'cchla_publicacao_nonce');

    $autores = get_post_meta($post->ID, '_publicacao_autores', true);
    $isbn = get_post_meta($post->ID, '_publicacao_isbn', true);
    $paginas = get_post_meta($post->ID, '_publicacao_paginas', true);
    $link_externo = get_post_meta($post->ID, '_publicacao_link_externo', true);
    $ano = get_post_meta($post->ID, '_publicacao_ano', true);
    $editora = get_post_meta($post->ID, '_publicacao_editora', true);
    ?>

    <div class="cchla-publicacao-meta-box">

        <!-- Autores/Organizadores -->
        <p class="form-field">
            <label for="publicacao_autores">
                <strong><?php esc_html_e('Autores/Organizadores', 'cchla-ufrn'); ?></strong>
            </label>
            <input
                type="text"
                id="publicacao_autores"
                name="publicacao_autores"
                value="<?php echo esc_attr($autores); ?>"
                class="large-text"
                placeholder="<?php esc_attr_e('Ex: João Silva, Maria Santos', 'cchla-ufrn'); ?>">
            <span class="description">
                <?php esc_html_e('Nomes dos autores, organizadores ou editores (separados por vírgula)', 'cchla-ufrn'); ?>
            </span>
        </p>

        <!-- ISBN/ISSN -->
        <p class="form-field">
            <label for="publicacao_isbn">
                <strong><?php esc_html_e('ISBN / ISSN / DOI', 'cchla-ufrn'); ?></strong>
            </label>
            <input
                type="text"
                id="publicacao_isbn"
                name="publicacao_isbn"
                value="<?php echo esc_attr($isbn); ?>"
                class="large-text"
                placeholder="<?php esc_attr_e('Ex: 978-65-5477-017-0', 'cchla-ufrn'); ?>">
            <span class="description">
                <?php esc_html_e('Código de identificação da publicação', 'cchla-ufrn'); ?>
            </span>
        </p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Ano de Publicação -->
            <div class="form-field">
                <label for="publicacao_ano">
                    <strong><?php esc_html_e('Ano de Publicação', 'cchla-ufrn'); ?></strong>
                </label>
                <input
                    type="number"
                    id="publicacao_ano"
                    name="publicacao_ano"
                    value="<?php echo esc_attr($ano); ?>"
                    class="regular-text"
                    min="1900"
                    max="<?php echo date('Y') + 1; ?>"
                    placeholder="<?php echo date('Y'); ?>">
            </div>

            <!-- Número de Páginas -->
            <div class="form-field">
                <label for="publicacao_paginas">
                    <strong><?php esc_html_e('Número de Páginas', 'cchla-ufrn'); ?></strong>
                </label>
                <input
                    type="number"
                    id="publicacao_paginas"
                    name="publicacao_paginas"
                    value="<?php echo esc_attr($paginas); ?>"
                    class="regular-text"
                    min="1"
                    placeholder="<?php esc_attr_e('Ex: 250', 'cchla-ufrn'); ?>">
            </div>
        </div>

        <!-- Editora -->
        <p class="form-field">
            <label for="publicacao_editora">
                <strong><?php esc_html_e('Editora', 'cchla-ufrn'); ?></strong>
            </label>
            <input
                type="text"
                id="publicacao_editora"
                name="publicacao_editora"
                value="<?php echo esc_attr($editora); ?>"
                class="large-text"
                placeholder="<?php esc_attr_e('Ex: EDUFRN', 'cchla-ufrn'); ?>">
        </p>

        <!-- Link Externo -->
        <p class="form-field">
            <label for="publicacao_link_externo">
                <strong><?php esc_html_e('Link da Publicação', 'cchla-ufrn'); ?></strong>
            </label>
            <input
                type="url"
                id="publicacao_link_externo"
                name="publicacao_link_externo"
                value="<?php echo esc_url($link_externo); ?>"
                class="large-text"
                placeholder="https://repositorio.ufrn.br/handle/123456789">
            <span class="description">
                <?php esc_html_e('URL para acessar a publicação online (PDF, repositório, etc)', 'cchla-ufrn'); ?>
            </span>
        </p>

        <!-- Instruções sobre a Capa -->
        <div class="form-field" style="background: #f0f6fc; border-left: 4px solid #0073aa; padding: 15px;">
            <strong style="display: block; margin-bottom: 8px;">
                <i class="dashicons dashicons-format-image" style="font-size: 18px; vertical-align: middle;"></i>
                <?php esc_html_e('Imagem de Capa', 'cchla-ufrn'); ?>
            </strong>
            <p style="margin: 0; color: #555;">
                <?php esc_html_e('Use o box "Imagem Destacada" ao lado direito para adicionar a capa da publicação.', 'cchla-ufrn'); ?><br>
                <strong><?php esc_html_e('Dimensões recomendadas:', 'cchla-ufrn'); ?></strong> 400x600px (proporção de capa de livro)<br>
                <strong><?php esc_html_e('Formato:', 'cchla-ufrn'); ?></strong> JPG ou PNG
            </p>
        </div>

    </div>

    <style>
        .cchla-publicacao-meta-box .form-field {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f1;
        }

        .cchla-publicacao-meta-box .form-field:last-child {
            border-bottom: none;
        }

        .cchla-publicacao-meta-box label strong {
            display: block;
            margin-bottom: 5px;
        }

        .cchla-publicacao-meta-box .description {
            display: block;
            margin-top: 5px;
            font-style: italic;
            color: #646970;
        }
    </style>
<?php
}

/**
 * Salva os Meta Dados
 */
function cchla_save_publicacao_meta($post_id)
{
    if (
        !isset($_POST['cchla_publicacao_nonce']) ||
        !wp_verify_nonce($_POST['cchla_publicacao_nonce'], 'cchla_save_publicacao_meta')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Autores
    if (isset($_POST['publicacao_autores'])) {
        update_post_meta($post_id, '_publicacao_autores', sanitize_text_field($_POST['publicacao_autores']));
    }

    // ISBN
    if (isset($_POST['publicacao_isbn'])) {
        update_post_meta($post_id, '_publicacao_isbn', sanitize_text_field($_POST['publicacao_isbn']));
    }

    // Páginas
    if (isset($_POST['publicacao_paginas'])) {
        update_post_meta($post_id, '_publicacao_paginas', absint($_POST['publicacao_paginas']));
    }

    // Link Externo
    if (isset($_POST['publicacao_link_externo'])) {
        update_post_meta($post_id, '_publicacao_link_externo', esc_url_raw($_POST['publicacao_link_externo']));
    }

    // Ano
    if (isset($_POST['publicacao_ano'])) {
        update_post_meta($post_id, '_publicacao_ano', absint($_POST['publicacao_ano']));
    }

    // Editora
    if (isset($_POST['publicacao_editora'])) {
        update_post_meta($post_id, '_publicacao_editora', sanitize_text_field($_POST['publicacao_editora']));
    }
}
add_action('save_post', 'cchla_save_publicacao_meta');

/**
 * Adiciona colunas personalizadas no admin
 */
function cchla_publicacoes_columns($columns)
{
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['featured_image'] = __('Capa', 'cchla-ufrn');
    $new_columns['title'] = $columns['title'];
    $new_columns['autores'] = __('Autores', 'cchla-ufrn');
    $new_columns['taxonomy-tipo_publicacao'] = __('Tipo', 'cchla-ufrn');
    $new_columns['ano'] = __('Ano', 'cchla-ufrn');
    $new_columns['date'] = $columns['date'];

    return $new_columns;
}
add_filter('manage_publicacoes_posts_columns', 'cchla_publicacoes_columns');

function cchla_publicacoes_column_content($column, $post_id)
{
    switch ($column) {
        case 'featured_image':
            if (has_post_thumbnail($post_id)) {
                echo get_the_post_thumbnail($post_id, array(50, 70));
            } else {
                echo '<span style="color: #ccc;">—</span>';
            }
            break;

        case 'autores':
            $autores = get_post_meta($post_id, '_publicacao_autores', true);
            echo $autores ? esc_html($autores) : '<span style="color: #ccc;">—</span>';
            break;

        case 'ano':
            $ano = get_post_meta($post_id, '_publicacao_ano', true);
            echo $ano ? '<strong>' . esc_html($ano) . '</strong>' : '<span style="color: #ccc;">—</span>';
            break;
    }
}
add_action('manage_publicacoes_posts_custom_column', 'cchla_publicacoes_column_content', 10, 2);

/**
 * Define tamanho de imagem personalizado para capas
 */
function cchla_add_publicacao_image_sizes()
{
    add_image_size('publicacao-capa', 400, 600, true); // Capa de livro
    add_image_size('publicacao-thumb', 96, 144, true); // Miniatura
}
add_action('after_setup_theme', 'cchla_add_publicacao_image_sizes');
