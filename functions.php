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

/**
 * Função auxiliar para exibir publicações
 */
function cchla_get_publicacoes($args = array())
{
    $defaults = array(
        'limite' => 6,
        'tipo' => '',
        'ano' => '',
        'mostrar_capa' => true,
    );

    $args = wp_parse_args($args, $defaults);

    $query_args = array(
        'post_type' => 'publicacoes',
        'posts_per_page' => $args['limite'],
        'orderby' => 'date',
        'order' => 'DESC',
        'post_status' => 'publish',
    );

    if (!empty($args['tipo'])) {
        $query_args['tax_query'] = array(
            array(
                'taxonomy' => 'tipo_publicacao',
                'field' => 'slug',
                'terms' => $args['tipo'],
            ),
        );
    }

    if (!empty($args['ano'])) {
        $query_args['meta_query'] = array(
            array(
                'key' => '_publicacao_ano',
                'value' => $args['ano'],
                'compare' => '=',
            ),
        );
    }

    return new WP_Query($query_args);
}

/**
 * Shortcode para Publicações
 * Uso: [publicacoes limite="6" tipo="livro" ano="2024"]
 */
function cchla_publicacoes_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'limite' => 6,
        'tipo' => '',
        'ano' => '',
        'colunas' => '3',
    ), $atts, 'publicacoes');

    $query = cchla_get_publicacoes(array(
        'limite' => intval($atts['limite']),
        'tipo' => sanitize_text_field($atts['tipo']),
        'ano' => sanitize_text_field($atts['ano']),
    ));

    if (!$query->have_posts()) {
        return '<p class="text-center text-gray-500">' . esc_html__('Nenhuma publicação encontrada.', 'cchla-ufrn') . '</p>';
    }

    ob_start();

    $colunas_class = 'grid-cols-' . esc_attr($atts['colunas']);

    echo '<div class="grid ' . $colunas_class . ' gap-8 max-lg:grid-cols-2 max-md:grid-cols-1">';

    while ($query->have_posts()) {
        $query->the_post();

        $autores = get_post_meta(get_the_ID(), '_publicacao_autores', true);
        $isbn = get_post_meta(get_the_ID(), '_publicacao_isbn', true);
        $link_externo = get_post_meta(get_the_ID(), '_publicacao_link_externo', true);
        $tipos = get_the_terms(get_the_ID(), 'tipo_publicacao');
        $tipo_nome = ($tipos && !is_wp_error($tipos)) ? $tipos[0]->name : 'Livro';

        $link_url = $link_externo ? $link_externo : get_permalink();
        $link_target = $link_externo ? '_blank' : '_self';
        $link_rel = $link_externo ? 'noopener noreferrer' : '';
    ?>

        <a href="<?php echo esc_url($link_url); ?>"
            target="<?php echo esc_attr($link_target); ?>"
            <?php if ($link_rel) echo 'rel="' . esc_attr($link_rel) . '"'; ?>
            class="group flex flex-col justify-between bg-white rounded-md p-6 border border-gray-200 hover:shadow-lg transition-all duration-300">

            <div class="space-y-2">
                <p class="text-xs uppercase text-gray-600 font-medium tracking-wide">
                    <?php echo esc_html($tipo_nome); ?>
                </p>

                <h3 class="font-semibold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">
                    <?php the_title(); ?>
                </h3>

                <?php if ($autores) : ?>
                    <p class="text-sm text-gray-600">
                        <?php echo esc_html($autores); ?>
                    </p>
                <?php endif; ?>

                <?php if ($isbn) : ?>
                    <p class="text-sm text-gray-500">
                        <?php echo esc_html($isbn); ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="flex justify-between items-end mt-4">
                <?php if (has_post_thumbnail()) : ?>
                    <figure class="max-md:hidden">
                        <?php the_post_thumbnail('publicacao-thumb', array(
                            'class' => 'w-24 h-32 object-cover rounded-md shadow-sm group-hover:scale-105 transition-transform duration-300'
                        )); ?>
                    </figure>
                <?php endif; ?>

                <span class="flex items-center gap-1 text-blue-600 text-sm font-medium group-hover:underline">
                    <?php esc_html_e('Leia mais', 'cchla-ufrn'); ?>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </span>
            </div>
        </a>

        <?php
    }

    echo '</div>';

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('publicacoes', 'cchla_publicacoes_shortcode');

/**
 * Widget de Publicações
 */
class CCHLA_Publicacoes_Widget extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'cchla_publicacoes_widget',
            __('CCHLA - Publicações Recentes', 'cchla-ufrn'),
            array('description' => __('Exibe publicações recentes do CCHLA', 'cchla-ufrn'))
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $limite = !empty($instance['limite']) ? $instance['limite'] : 5;
        $tipo = !empty($instance['tipo']) ? $instance['tipo'] : '';

        $query = cchla_get_publicacoes(array(
            'limite' => $limite,
            'tipo' => $tipo,
        ));

        if ($query->have_posts()) {
            echo '<ul class="space-y-4">';

            while ($query->have_posts()) {
                $query->the_post();

                $link_externo = get_post_meta(get_the_ID(), '_publicacao_link_externo', true);
                $link_url = $link_externo ? $link_externo : get_permalink();
        ?>

                <li class="border-b border-gray-200 pb-3 last:border-0">
                    <a href="<?php echo esc_url($link_url); ?>" class="block group">
                        <h4 class="font-semibold text-sm text-gray-900 group-hover:text-blue-600 transition-colors mb-1">
                            <?php the_title(); ?>
                        </h4>
                        <?php
                        $autores = get_post_meta(get_the_ID(), '_publicacao_autores', true);
                        if ($autores) :
                        ?>
                            <p class="text-xs text-gray-600">
                                <?php echo esc_html(wp_trim_words($autores, 5)); ?>
                            </p>
                        <?php endif; ?>
                    </a>
                </li>

        <?php
            }

            echo '</ul>';

            echo '<div class="mt-4 pt-4 border-t border-gray-200">';
            echo '<a href="' . esc_url(get_post_type_archive_link('publicacoes')) . '" class="text-sm text-blue-600 hover:underline">';
            esc_html_e('Ver todas as publicações →', 'cchla-ufrn');
            echo '</a>';
            echo '</div>';

            wp_reset_postdata();
        }

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : __('Publicações Recentes', 'cchla-ufrn');
        $limite = !empty($instance['limite']) ? $instance['limite'] : 5;
        $tipo = !empty($instance['tipo']) ? $instance['tipo'] : '';

        $tipos = get_terms(array(
            'taxonomy' => 'tipo_publicacao',
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

        <?php if ($tipos && !is_wp_error($tipos)) : ?>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('tipo')); ?>">
                    <?php esc_html_e('Tipo:', 'cchla-ufrn'); ?>
                </label>
                <select class="widefat"
                    id="<?php echo esc_attr($this->get_field_id('tipo')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('tipo')); ?>">
                    <option value=""><?php esc_html_e('Todos', 'cchla-ufrn'); ?></option>
                    <?php foreach ($tipos as $term) : ?>
                        <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($tipo, $term->slug); ?>>
                            <?php echo esc_html($term->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
        <?php endif; ?>
    <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['limite'] = (!empty($new_instance['limite'])) ? absint($new_instance['limite']) : 5;
        $instance['tipo'] = (!empty($new_instance['tipo'])) ? sanitize_text_field($new_instance['tipo']) : '';
        return $instance;
    }
}

function cchla_register_publicacoes_widget()
{
    register_widget('CCHLA_Publicacoes_Widget');
}
add_action('widgets_init', 'cchla_register_publicacoes_widget');

/**
 * Adiciona ordenação personalizada no query do arquivo
 */
function cchla_publicacoes_archive_query($query)
{
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('publicacoes')) {

        // Busca
        if (isset($_GET['s']) && !empty($_GET['s'])) {
            $query->set('s', sanitize_text_field($_GET['s']));
        }

        // Ordenação
        if (isset($_GET['orderby']) && isset($_GET['order'])) {
            $orderby = sanitize_text_field($_GET['orderby']);
            $order = sanitize_text_field($_GET['order']);

            if ($orderby === 'title') {
                $query->set('orderby', 'title');
                $query->set('order', strtoupper($order));
            } elseif ($orderby === 'date') {
                $query->set('orderby', 'date');
                $query->set('order', strtoupper($order));
            }
        }
    }
}
add_action('pre_get_posts', 'cchla_publicacoes_archive_query');

/**
 * Adiciona informações de publicação ao RSS Feed
 */
function cchla_publicacoes_rss_item()
{
    if (get_post_type() === 'publicacoes') {
        $autores = get_post_meta(get_the_ID(), '_publicacao_autores', true);
        $isbn = get_post_meta(get_the_ID(), '_publicacao_isbn', true);

        if ($autores) {
            echo '<dc:creator>' . esc_html($autores) . '</dc:creator>';
        }

        if ($isbn) {
            echo '<prism:isbn>' . esc_html($isbn) . '</prism:isbn>';
        }
    }
}
add_action('rss2_item', 'cchla_publicacoes_rss_item');

/**
 * Conta total de publicações por tipo
 */
function cchla_count_publicacoes_by_tipo($slug = '')
{
    $args = array(
        'post_type' => 'publicacoes',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
    );

    if (!empty($slug)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'tipo_publicacao',
                'field' => 'slug',
                'terms' => $slug,
            ),
        );
    }

    $query = new WP_Query($args);
    return $query->found_posts;
}

/**
 * Retorna estatísticas de publicações
 */
function cchla_get_publicacoes_stats()
{
    $stats = array();

    // Total de publicações
    $stats['total'] = wp_count_posts('publicacoes')->publish;

    // Por tipo
    $terms = get_terms(array(
        'taxonomy' => 'tipo_publicacao',
        'hide_empty' => true,
    ));

    if ($terms && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $stats['por_tipo'][$term->slug] = array(
                'nome' => $term->name,
                'total' => $term->count,
            );
        }
    }

    // Por ano
    global $wpdb;
    $anos = $wpdb->get_results("
        SELECT DISTINCT meta_value as ano, COUNT(*) as total
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '_publicacao_ano'
        AND p.post_type = 'publicacoes'
        AND p.post_status = 'publish'
        GROUP BY meta_value
        ORDER BY meta_value DESC
    ");

    if ($anos) {
        foreach ($anos as $ano) {
            $stats['por_ano'][$ano->ano] = $ano->total;
        }
    }

    return $stats;
}

/**
 * Shortcode para estatísticas de publicações
 * Uso: [estatisticas_publicacoes]
 */
function cchla_estatisticas_publicacoes_shortcode()
{
    $stats = cchla_get_publicacoes_stats();

    ob_start();
    ?>
    <div class="publicacoes-stats bg-gray-50 rounded-lg p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">
            <?php esc_html_e('Estatísticas de Publicações', 'cchla-ufrn'); ?>
        </h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg p-4 text-center border border-blue-200">
                <div class="text-3xl font-bold text-blue-600 mb-1">
                    <?php echo esc_html($stats['total']); ?>
                </div>
                <div class="text-sm text-gray-600">
                    <?php esc_html_e('Total', 'cchla-ufrn'); ?>
                </div>
            </div>

            <?php if (isset($stats['por_tipo'])) : ?>
                <?php $count = 0; ?>
                <?php foreach ($stats['por_tipo'] as $tipo) : ?>
                    <?php if ($count++ >= 3) break; ?>
                    <div class="bg-white rounded-lg p-4 text-center border border-gray-200">
                        <div class="text-3xl font-bold text-gray-700 mb-1">
                            <?php echo esc_html($tipo['total']); ?>
                        </div>
                        <div class="text-sm text-gray-600">
                            <?php echo esc_html($tipo['nome']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (isset($stats['por_ano']) && count($stats['por_ano']) > 0) : ?>
            <div class="border-t border-gray-200 pt-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">
                    <?php esc_html_e('Publicações por Ano', 'cchla-ufrn'); ?>
                </h4>
                <div class="grid grid-cols-5 gap-2">
                    <?php foreach (array_slice($stats['por_ano'], 0, 10) as $ano => $total) : ?>
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-900"><?php echo esc_html($ano); ?></div>
                            <div class="text-xs text-gray-600"><?php echo esc_html($total); ?> pub.</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('estatisticas_publicacoes', 'cchla_estatisticas_publicacoes_shortcode');


/**
 * ============================================
 * FUNÇÕES AUXILIARES PARA FILTROS DE ARQUIVO
 * ============================================
 */

/**
 * Verifica se há filtros ativos
 */
function is_filtered()
{
    return (
        is_category() ||
        is_tag() ||
        is_tax() ||
        is_author() ||
        is_date() ||
        get_search_query() ||
        get_query_var('year') ||
        get_query_var('orderby')
    );
}

/**
 * Modifica query principal para incluir filtros customizados
 */
function cchla_archive_query_modifications($query)
{
    if (!is_admin() && $query->is_main_query()) {

        // Filtro de taxonomia customizada via URL
        if (isset($_GET['tax']) && isset($_GET['term'])) {
            $tax = sanitize_text_field($_GET['tax']);
            $term = sanitize_text_field($_GET['term']);

            $query->set('tax_query', array(
                array(
                    'taxonomy' => $tax,
                    'field' => 'slug',
                    'terms' => $term,
                ),
            ));
        }

        // Filtro de ano
        if (isset($_GET['year']) && !empty($_GET['year'])) {
            $year = absint($_GET['year']);
            $query->set('year', $year);
        }

        // Ordenação personalizada
        if (isset($_GET['orderby']) && isset($_GET['order'])) {
            $orderby = sanitize_text_field($_GET['orderby']);
            $order = sanitize_text_field($_GET['order']);

            switch ($orderby) {
                case 'title':
                    $query->set('orderby', 'title');
                    $query->set('order', strtoupper($order));
                    break;

                case 'date':
                    $query->set('orderby', 'date');
                    $query->set('order', strtoupper($order));
                    break;

                case 'comment_count':
                    $query->set('orderby', 'comment_count');
                    $query->set('order', strtoupper($order));
                    break;

                case 'modified':
                    $query->set('orderby', 'modified');
                    $query->set('order', strtoupper($order));
                    break;
            }
        }

        // Ajusta posts_per_page baseado no post type
        if (is_post_type_archive('publicacoes') || is_tax('tipo_publicacao')) {
            $query->set('posts_per_page', 12);
        } elseif (is_post_type_archive('acesso_rapido') || is_tax('categoria_acesso')) {
            $query->set('posts_per_page', 15);
        }
    }
}
add_action('pre_get_posts', 'cchla_archive_query_modifications');

/**
 * Adiciona body class para identificar tipo de arquivo
 */
function cchla_archive_body_class($classes)
{
    if (is_post_type_archive()) {
        $post_type = get_query_var('post_type');
        $classes[] = 'archive-' . $post_type;
    }

    if (is_tax()) {
        $term = get_queried_object();
        $classes[] = 'taxonomy-' . $term->taxonomy;
        $classes[] = 'term-' . $term->slug;
    }

    if (is_filtered()) {
        $classes[] = 'has-filters';
    }

    return $classes;
}
add_filter('body_class', 'cchla_archive_body_class');

/**
 * Breadcrumb para Custom Post Types
 */
function cchla_get_cpt_breadcrumb()
{
    $breadcrumb = array();

    if (is_post_type_archive()) {
        $post_type_obj = get_post_type_object(get_query_var('post_type'));

        $breadcrumb[] = array(
            'title' => $post_type_obj->labels->name,
            'url' => get_post_type_archive_link(get_query_var('post_type')),
        );
    } elseif (is_tax()) {
        $term = get_queried_object();
        $tax_obj = get_taxonomy($term->taxonomy);
        $post_type = $tax_obj->object_type[0];

        // Adiciona link para o arquivo do post type
        if ($post_type !== 'post') {
            $post_type_obj = get_post_type_object($post_type);
            $breadcrumb[] = array(
                'title' => $post_type_obj->labels->name,
                'url' => get_post_type_archive_link($post_type),
            );
        }

        // Adiciona a taxonomia atual
        $breadcrumb[] = array(
            'title' => $term->name,
            'url' => get_term_link($term),
            'current' => true,
        );
    }

    return $breadcrumb;
}

/**
 * Retorna estatísticas do arquivo atual
 */
function cchla_get_archive_stats()
{
    global $wp_query;

    $stats = array(
        'total' => $wp_query->found_posts,
        'current_page' => max(1, get_query_var('paged')),
        'per_page' => $wp_query->query_vars['posts_per_page'],
        'total_pages' => $wp_query->max_num_pages,
        'showing_start' => (max(1, get_query_var('paged')) - 1) * $wp_query->query_vars['posts_per_page'] + 1,
        'showing_end' => min($wp_query->found_posts, max(1, get_query_var('paged')) * $wp_query->query_vars['posts_per_page']),
    );

    return $stats;
}

/**
 * Exibe informações de paginação
 */
function cchla_archive_pagination_info()
{
    $stats = cchla_get_archive_stats();

    if ($stats['total'] > 0) {
        printf(
            esc_html__('Mostrando %1$s - %2$s de %3$s resultados', 'cchla-ufrn'),
            '<strong>' . number_format_i18n($stats['showing_start']) . '</strong>',
            '<strong>' . number_format_i18n($stats['showing_end']) . '</strong>',
            '<strong>' . number_format_i18n($stats['total']) . '</strong>'
        );
    }
}

/**
 * Widget - Filtros de Arquivo
 */
class CCHLA_Archive_Filters_Widget extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'cchla_archive_filters_widget',
            __('CCHLA - Filtros de Arquivo', 'cchla-ufrn'),
            array('description' => __('Exibe filtros para páginas de arquivo', 'cchla-ufrn'))
        );
    }

    public function widget($args, $instance)
    {
        if (!is_archive() && !is_search()) {
            return;
        }

        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

    ?>
        <div class="archive-filters-widget">

            <!-- Busca -->
            <form method="get" action="<?php echo esc_url(home_url('/')); ?>" class="mb-6">
                <div class="relative">
                    <input
                        type="text"
                        name="s"
                        value="<?php echo get_search_query(); ?>"
                        placeholder="<?php esc_attr_e('Buscar...', 'cchla-ufrn'); ?>"
                        class="w-full px-4 py-2 pr-10 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </div>
            </form>

            <!-- Categorias -->
            <?php
            $categories = get_categories(array('hide_empty' => true));
            if ($categories && (get_post_type() === 'post' || !is_post_type_archive())) :
            ?>
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-900 mb-3">
                        <?php esc_html_e('Categorias', 'cchla-ufrn'); ?>
                    </h4>
                    <ul class="space-y-2">
                        <?php foreach ($categories as $category) : ?>
                            <li>
                                <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>"
                                    class="flex items-center justify-between text-sm text-gray-600 hover:text-blue-600 transition-colors <?php echo is_category($category->term_id) ? 'font-semibold text-blue-600' : ''; ?>">
                                    <span><?php echo esc_html($category->name); ?></span>
                                    <span class="text-xs text-gray-400">(<?php echo $category->count; ?>)</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Anos -->
            <?php
            global $wpdb;
            $years = $wpdb->get_col("
                SELECT DISTINCT YEAR(post_date) 
                FROM $wpdb->posts 
                WHERE post_status = 'publish' 
                AND post_type = '" . esc_sql(get_post_type()) . "'
                ORDER BY post_date DESC
            ");

            if ($years) :
            ?>
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-900 mb-3">
                        <?php esc_html_e('Arquivo por Ano', 'cchla-ufrn'); ?>
                    </h4>
                    <ul class="space-y-2">
                        <?php foreach ($years as $year) : ?>
                            <li>
                                <a href="<?php echo esc_url(get_year_link($year)); ?>"
                                    class="flex items-center justify-between text-sm text-gray-600 hover:text-blue-600 transition-colors">
                                    <span><?php echo esc_html($year); ?></span>
                                    <i class="fa-solid fa-chevron-right text-xs"></i>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

        </div>
    <?php

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : __('Filtros', 'cchla-ufrn');
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
    <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        return $instance;
    }
}

function cchla_register_archive_filters_widget()
{
    register_widget('CCHLA_Archive_Filters_Widget');
}
add_action('widgets_init', 'cchla_register_archive_filters_widget');

/**
 * Adiciona suporte a filtros na busca
 */
function cchla_search_filter($query)
{
    if (!is_admin() && $query->is_search() && $query->is_main_query()) {

        // Filtrar por post type específico se fornecido
        if (isset($_GET['post_type']) && !empty($_GET['post_type'])) {
            $post_type = sanitize_text_field($_GET['post_type']);
            $query->set('post_type', $post_type);
        }

        // Filtrar por categoria
        if (isset($_GET['cat']) && !empty($_GET['cat'])) {
            $query->set('cat', absint($_GET['cat']));
        }

        // Filtrar por tag
        if (isset($_GET['tag']) && !empty($_GET['tag'])) {
            $query->set('tag', sanitize_text_field($_GET['tag']));
        }
    }
}
add_action('pre_get_posts', 'cchla_search_filter');

/**
 * Shortcode para exibir filtros
 * Uso: [filtros_arquivo]
 */
function cchla_filtros_arquivo_shortcode()
{
    if (!is_archive() && !is_search()) {
        return '';
    }

    ob_start();

    the_widget('CCHLA_Archive_Filters_Widget', array(
        'title' => __('Refinar Busca', 'cchla-ufrn')
    ));

    return ob_get_clean();
}
add_shortcode('filtros_arquivo', 'cchla_filtros_arquivo_shortcode');

/**
 * Adiciona informações de debug no admin bar (apenas para admins)
 */
function cchla_admin_bar_archive_info($wp_admin_bar)
{
    if (!current_user_can('manage_options') || !is_archive()) {
        return;
    }

    $stats = cchla_get_archive_stats();

    $wp_admin_bar->add_node(array(
        'id' => 'cchla-archive-stats',
        'title' => sprintf(
            '<span class="ab-icon dashicons dashicons-chart-bar"></span> %s resultados',
            $stats['total']
        ),
        'href' => '#',
    ));

    $wp_admin_bar->add_node(array(
        'id' => 'cchla-archive-type',
        'parent' => 'cchla-archive-stats',
        'title' => 'Tipo: ' . get_post_type(),
    ));

    if (is_filtered()) {
        $wp_admin_bar->add_node(array(
            'id' => 'cchla-archive-filtered',
            'parent' => 'cchla-archive-stats',
            'title' => '✓ Filtros ativos',
        ));
    }
}
add_action('admin_bar_menu', 'cchla_admin_bar_archive_info', 100);

/**
 * ============================================
 * CUSTOM POST TYPE - SERVIÇOS DE EXTENSÃO
 * ============================================
 */

/**
 * Registra o Custom Post Type "Serviços"
 */
function cchla_register_servicos_cpt()
{
    $labels = array(
        'name'                  => _x('Serviços', 'Post Type General Name', 'cchla-ufrn'),
        'singular_name'         => _x('Serviço', 'Post Type Singular Name', 'cchla-ufrn'),
        'menu_name'             => __('Serviços', 'cchla-ufrn'),
        'name_admin_bar'        => __('Serviço', 'cchla-ufrn'),
        'archives'              => __('Arquivo de Serviços', 'cchla-ufrn'),
        'attributes'            => __('Atributos', 'cchla-ufrn'),
        'parent_item_colon'     => __('Serviço Pai:', 'cchla-ufrn'),
        'all_items'             => __('Todos os Serviços', 'cchla-ufrn'),
        'add_new_item'          => __('Adicionar Novo Serviço', 'cchla-ufrn'),
        'add_new'               => __('Adicionar Novo', 'cchla-ufrn'),
        'new_item'              => __('Novo Serviço', 'cchla-ufrn'),
        'edit_item'             => __('Editar Serviço', 'cchla-ufrn'),
        'update_item'           => __('Atualizar Serviço', 'cchla-ufrn'),
        'view_item'             => __('Ver Serviço', 'cchla-ufrn'),
        'view_items'            => __('Ver Serviços', 'cchla-ufrn'),
        'search_items'          => __('Buscar Serviço', 'cchla-ufrn'),
        'not_found'             => __('Nenhum serviço encontrado', 'cchla-ufrn'),
        'not_found_in_trash'    => __('Nenhum serviço na lixeira', 'cchla-ufrn'),
    );

    $args = array(
        'label'                 => __('Serviço', 'cchla-ufrn'),
        'description'           => __('Serviços de extensão oferecidos pelo CCHLA', 'cchla-ufrn'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'excerpt', 'thumbnail'),
        'taxonomies'            => array('categoria_servico'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 27,
        'menu_icon'             => 'dashicons-heart',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => 'servicos',
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite'               => array('slug' => 'servico', 'with_front' => false),
    );

    register_post_type('servicos', $args);
}
add_action('init', 'cchla_register_servicos_cpt', 0);

/**
 * Registra a Taxonomia "Categoria de Serviço"
 */
function cchla_register_categoria_servico_taxonomy()
{
    $labels = array(
        'name'                       => _x('Categorias de Serviços', 'Taxonomy General Name', 'cchla-ufrn'),
        'singular_name'              => _x('Categoria de Serviço', 'Taxonomy Singular Name', 'cchla-ufrn'),
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

    register_taxonomy('categoria_servico', array('servicos'), $args);
}
add_action('init', 'cchla_register_categoria_servico_taxonomy', 0);

/**
 * Cria categorias padrão
 */
function cchla_insert_default_categoria_servico_terms()
{
    $categorias = array(
        'Extensão' => 'Programas e projetos de extensão',
        'Cultura' => 'Atividades culturais e artísticas',
        'Atendimento' => 'Serviços de atendimento ao público',
        'Educação' => 'Cursos e capacitações',
    );

    foreach ($categorias as $nome => $descricao) {
        if (!term_exists($nome, 'categoria_servico')) {
            wp_insert_term($nome, 'categoria_servico', array(
                'slug' => sanitize_title($nome),
                'description' => $descricao,
            ));
        }
    }
}
add_action('init', 'cchla_insert_default_categoria_servico_terms');

/**
 * Adiciona Meta Boxes para Serviços
 */
function cchla_servicos_meta_boxes()
{
    add_meta_box(
        'cchla_servico_details',
        __('Detalhes do Serviço', 'cchla-ufrn'),
        'cchla_servico_meta_box_callback',
        'servicos',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cchla_servicos_meta_boxes');

/**
 * Callback do Meta Box
 */
function cchla_servico_meta_box_callback($post)
{
    wp_nonce_field('cchla_save_servico_meta', 'cchla_servico_nonce');

    $icone_tipo = get_post_meta($post->ID, '_servico_icone_tipo', true);
    $icone_classe = get_post_meta($post->ID, '_servico_icone_classe', true);
    $icone_svg = get_post_meta($post->ID, '_servico_icone_svg', true);
    $link_externo = get_post_meta($post->ID, '_servico_link_externo', true);
    $link_botao_texto = get_post_meta($post->ID, '_servico_link_botao_texto', true);
    $responsavel = get_post_meta($post->ID, '_servico_responsavel', true);
    $contato = get_post_meta($post->ID, '_servico_contato', true);
    $horario = get_post_meta($post->ID, '_servico_horario', true);
    $localizacao = get_post_meta($post->ID, '_servico_localizacao', true);

    $icone_tipo = $icone_tipo ? $icone_tipo : 'classe';
    $link_botao_texto = $link_botao_texto ? $link_botao_texto : 'Leia mais';
    ?>

    <div class="cchla-servico-meta-box">

        <!-- Tipo de Ícone -->
        <p class="form-field">
            <label>
                <strong><?php esc_html_e('Tipo de Ícone', 'cchla-ufrn'); ?></strong>
            </label>
            <label style="margin-right: 20px;">
                <input type="radio" name="servico_icone_tipo" value="classe" <?php checked($icone_tipo, 'classe'); ?>>
                <?php esc_html_e('Classe Font Awesome', 'cchla-ufrn'); ?>
            </label>
            <label>
                <input type="radio" name="servico_icone_tipo" value="svg" <?php checked($icone_tipo, 'svg'); ?>>
                <?php esc_html_e('Código SVG', 'cchla-ufrn'); ?>
            </label>
        </p>

        <!-- Classe Font Awesome -->
        <div class="form-field icone-classe-field" style="<?php echo $icone_tipo === 'svg' ? 'display:none;' : ''; ?>">
            <label for="servico_icone_classe">
                <strong><?php esc_html_e('Classe Font Awesome', 'cchla-ufrn'); ?></strong>
            </label>
            <input
                type="text"
                id="servico_icone_classe"
                name="servico_icone_classe"
                value="<?php echo esc_attr($icone_classe); ?>"
                class="regular-text"
                placeholder="fa-solid fa-graduation-cap">
            <span class="description">
                <?php esc_html_e('Ex: fa-solid fa-graduation-cap, fa-solid fa-book', 'cchla-ufrn'); ?>
                <a href="https://fontawesome.com/icons" target="_blank">
                    <?php esc_html_e('Ver ícones', 'cchla-ufrn'); ?>
                </a>
            </span>
            <div style="margin-top: 10px; font-size: 32px; color: #2563eb;">
                <i class="<?php echo esc_attr($icone_classe); ?>"></i>
            </div>
        </div>

        <!-- Código SVG -->
        <div class="form-field icone-svg-field" style="<?php echo $icone_tipo === 'classe' ? 'display:none;' : ''; ?>">
            <label for="servico_icone_svg">
                <strong><?php esc_html_e('Código SVG', 'cchla-ufrn'); ?></strong>
            </label>
            <textarea
                id="servico_icone_svg"
                name="servico_icone_svg"
                rows="5"
                class="large-text code"
                placeholder='<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">...</svg>'><?php echo esc_textarea($icone_svg); ?></textarea>
            <span class="description">
                <?php esc_html_e('Cole o código SVG completo aqui', 'cchla-ufrn'); ?>
            </span>
            <?php if ($icone_svg) : ?>
                <div style="margin-top: 10px; width: 32px; height: 32px; color: #2563eb;">
                    <?php echo wp_kses_post($icone_svg); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Link Externo -->
        <p class="form-field">
            <label for="servico_link_externo">
                <strong><?php esc_html_e('Link Externo (Opcional)', 'cchla-ufrn'); ?></strong>
            </label>
            <input
                type="url"
                id="servico_link_externo"
                name="servico_link_externo"
                value="<?php echo esc_url($link_externo); ?>"
                class="large-text"
                placeholder="https://servico.cchla.ufrn.br">
            <span class="description">
                <?php esc_html_e('Se preenchido, o card direcionará para este link ao invés da página interna', 'cchla-ufrn'); ?>
            </span>
        </p>

        <!-- Texto do Botão -->
        <p class="form-field">
            <label for="servico_link_botao_texto">
                <strong><?php esc_html_e('Texto do Botão', 'cchla-ufrn'); ?></strong>
            </label>
            <input
                type="text"
                id="servico_link_botao_texto"
                name="servico_link_botao_texto"
                value="<?php echo esc_attr($link_botao_texto); ?>"
                class="regular-text"
                placeholder="Leia mais">
        </p>

        <!-- Responsável -->
        <p class="form-field">
            <label for="servico_responsavel">
                <strong><?php esc_html_e('Responsável', 'cchla-ufrn'); ?></strong>
            </label>
            <input
                type="text"
                id="servico_responsavel"
                name="servico_responsavel"
                value="<?php echo esc_attr($responsavel); ?>"
                class="large-text"
                placeholder="Prof. Dr. João Silva">
        </p>

        <!-- Contato -->
        <p class="form-field">
            <label for="servico_contato">
                <strong><?php esc_html_e('Contato', 'cchla-ufrn'); ?></strong>
            </label>
            <input
                type="text"
                id="servico_contato"
                name="servico_contato"
                value="<?php echo esc_attr($contato); ?>"
                class="large-text"
                placeholder="(84) 3342-2243 | servico@cchla.ufrn.br">
        </p>

        <!-- Horário de Funcionamento -->
        <p class="form-field">
            <label for="servico_horario">
                <strong><?php esc_html_e('Horário de Funcionamento', 'cchla-ufrn'); ?></strong>
            </label>
            <input
                type="text"
                id="servico_horario"
                name="servico_horario"
                value="<?php echo esc_attr($horario); ?>"
                class="large-text"
                placeholder="Segunda a Sexta, 8h às 17h">
        </p>

        <!-- Localização -->
        <p class="form-field">
            <label for="servico_localizacao">
                <strong><?php esc_html_e('Localização', 'cchla-ufrn'); ?></strong>
            </label>
            <input
                type="text"
                id="servico_localizacao"
                name="servico_localizacao"
                value="<?php echo esc_attr($localizacao); ?>"
                class="large-text"
                placeholder="Sala 201, Bloco A, CCHLA">
        </p>

    </div>

    <style>
        .cchla-servico-meta-box .form-field {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f1;
        }

        .cchla-servico-meta-box .form-field:last-child {
            border-bottom: none;
        }

        .cchla-servico-meta-box label strong {
            display: block;
            margin-bottom: 5px;
        }

        .cchla-servico-meta-box .description {
            display: block;
            margin-top: 5px;
            font-style: italic;
            color: #646970;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            // Toggle entre classe e SVG
            $('input[name="servico_icone_tipo"]').on('change', function() {
                if ($(this).val() === 'classe') {
                    $('.icone-classe-field').show();
                    $('.icone-svg-field').hide();
                } else {
                    $('.icone-classe-field').hide();
                    $('.icone-svg-field').show();
                }
            });

            // Preview da classe Font Awesome
            $('#servico_icone_classe').on('input', function() {
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
function cchla_save_servico_meta($post_id)
{
    if (
        !isset($_POST['cchla_servico_nonce']) ||
        !wp_verify_nonce($_POST['cchla_servico_nonce'], 'cchla_save_servico_meta')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = array(
        'servico_icone_tipo',
        'servico_icone_classe',
        'servico_link_externo',
        'servico_link_botao_texto',
        'servico_responsavel',
        'servico_contato',
        'servico_horario',
        'servico_localizacao',
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            if ($field === 'servico_link_externo') {
                update_post_meta($post_id, '_' . $field, esc_url_raw($_POST[$field]));
            } else {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }
    }

    // SVG precisa de sanitização especial
    if (isset($_POST['servico_icone_svg'])) {
        $svg = wp_kses($_POST['servico_icone_svg'], array(
            'svg' => array(
                'xmlns' => array(),
                'viewbox' => array(),
                'width' => array(),
                'height' => array(),
                'fill' => array(),
                'class' => array(),
            ),
            'path' => array(
                'd' => array(),
                'fill' => array(),
                'stroke' => array(),
                'stroke-width' => array(),
                'stroke-linecap' => array(),
                'stroke-linejoin' => array(),
            ),
            'circle' => array(
                'cx' => array(),
                'cy' => array(),
                'r' => array(),
                'fill' => array(),
                'stroke' => array(),
            ),
            'rect' => array(
                'x' => array(),
                'y' => array(),
                'width' => array(),
                'height' => array(),
                'fill' => array(),
                'stroke' => array(),
            ),
            'g' => array(
                'fill' => array(),
                'stroke' => array(),
            ),
        ));
        update_post_meta($post_id, '_servico_icone_svg', $svg);
    }
}
add_action('save_post', 'cchla_save_servico_meta');

/**
 * Adiciona colunas personalizadas no admin
 */
function cchla_servicos_columns($columns)
{
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['icone'] = __('Ícone', 'cchla-ufrn');
    $new_columns['title'] = $columns['title'];
    $new_columns['taxonomy-categoria_servico'] = __('Categoria', 'cchla-ufrn');
    $new_columns['responsavel'] = __('Responsável', 'cchla-ufrn');
    $new_columns['date'] = $columns['date'];

    return $new_columns;
}
add_filter('manage_servicos_posts_columns', 'cchla_servicos_columns');

function cchla_servicos_column_content($column, $post_id)
{
    switch ($column) {
        case 'icone':
            $tipo = get_post_meta($post_id, '_servico_icone_tipo', true);

            if ($tipo === 'svg') {
                $svg = get_post_meta($post_id, '_servico_icone_svg', true);
                if ($svg) {
                    echo '<div style="width: 24px; height: 24px; color: #2563eb;">' . wp_kses_post($svg) . '</div>';
                }
            } else {
                $classe = get_post_meta($post_id, '_servico_icone_classe', true);
                if ($classe) {
                    echo '<i class="' . esc_attr($classe) . '" style="font-size: 24px; color: #2563eb;"></i>';
                }
            }
            break;

        case 'responsavel':
            $responsavel = get_post_meta($post_id, '_servico_responsavel', true);
            echo $responsavel ? esc_html($responsavel) : '<span style="color: #ccc;">—</span>';
            break;
    }
}
add_action('manage_servicos_posts_custom_column', 'cchla_servicos_column_content', 10, 2);

/**
 * Shortcode para Serviços
 * Uso: [servicos limite="4" categoria="extensao"]
 */
function cchla_servicos_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'limite' => 4,
        'categoria' => '',
        'colunas' => '4',
    ), $atts, 'servicos');

    $args = array(
        'post_type' => 'servicos',
        'posts_per_page' => intval($atts['limite']),
        'orderby' => 'menu_order',
        'order' => 'ASC',
    );

    if (!empty($atts['categoria'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'categoria_servico',
                'field' => 'slug',
                'terms' => sanitize_text_field($atts['categoria']),
            ),
        );
    }

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '';
    }

    ob_start();

    $colunas_class = 'xl:grid-cols-' . esc_attr($atts['colunas']);

    echo '<div class="grid gap-6 sm:grid-cols-2 ' . $colunas_class . '">';

    while ($query->have_posts()) {
        $query->the_post();

        $icone_tipo = get_post_meta(get_the_ID(), '_servico_icone_tipo', true);
        $icone_classe = get_post_meta(get_the_ID(), '_servico_icone_classe', true);
        $icone_svg = get_post_meta(get_the_ID(), '_servico_icone_svg', true);
        $link_externo = get_post_meta(get_the_ID(), '_servico_link_externo', true);
        $link_botao_texto = get_post_meta(get_the_ID(), '_servico_link_botao_texto', true);

        $link_url = $link_externo ? $link_externo : get_permalink();
        $botao_texto = $link_botao_texto ? $link_botao_texto : __('Leia mais', 'cchla-ufrn');
    ?>

        <a href="<?php echo esc_url($link_url); ?>"
            class="block p-6 border border-blue-200 rounded-lg hover:shadow-md hover:-translate-y-1 transition-all duration-200">

            <div class="text-blue-600 mb-3" style="width: 32px; height: 32px;">
                <?php if ($icone_tipo === 'svg' && $icone_svg) : ?>
                    <?php echo wp_kses_post($icone_svg); ?>
                <?php elseif ($icone_classe) : ?>
                    <i class="<?php echo esc_attr($icone_classe); ?>" style="font-size: 32px;"></i>
                <?php endif; ?>
            </div>

            <h3 class="font-semibold mb-2">
                <?php the_title(); ?>
            </h3>

            <p class="text-sm text-zinc-600 mb-3">
                <?php echo has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 20); ?>
            </p>

            <span class="text-blue-600 font-medium inline-flex items-center gap-1">
                <?php echo esc_html($botao_texto); ?>
                <span aria-hidden="true">→</span>
            </span>
        </a>

    <?php
    }

    echo '</div>';

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('servicos', 'cchla_servicos_shortcode');

/**
 * Ajusta ordem dos serviços no admin
 */
function cchla_servicos_menu_order_support()
{
    add_post_type_support('servicos', 'page-attributes');
}
add_action('init', 'cchla_servicos_menu_order_support');

/**
 * Flush rewrite rules ao ativar o tema
 */
function cchla_flush_rewrite_rules_on_activation()
{
    // Registra os post types
    cchla_register_servicos_cpt();
    cchla_register_publicacoes_cpt();
    cchla_register_acesso_rapido_cpt();

    // Flush das regras
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'cchla_flush_rewrite_rules_on_activation');

/**
 * Flush rewrite rules apenas uma vez após registrar CPTs
 */
function cchla_maybe_flush_rewrite_rules()
{
    $flush_option = 'cchla_flush_rewrite_rules';

    if (get_option($flush_option) !== 'done') {
        flush_rewrite_rules();
        update_option($flush_option, 'done');
    }
}
add_action('init', 'cchla_maybe_flush_rewrite_rules', 999);

/**
 * ============================================
 * CUSTOM POST TYPE - ESPECIAIS CCHLA
 * ============================================
 */

/**
 * Registra o Custom Post Type "Especiais"
 */
function cchla_register_especiais_cpt()
{
    $labels = array(
        'name'                  => _x('Especiais', 'Post Type General Name', 'cchla-ufrn'),
        'singular_name'         => _x('Especial', 'Post Type Singular Name', 'cchla-ufrn'),
        'menu_name'             => __('Especiais CCHLA', 'cchla-ufrn'),
        'name_admin_bar'        => __('Especial', 'cchla-ufrn'),
        'archives'              => __('Arquivo de Especiais', 'cchla-ufrn'),
        'attributes'            => __('Atributos', 'cchla-ufrn'),
        'parent_item_colon'     => __('Especial Pai:', 'cchla-ufrn'),
        'all_items'             => __('Todos os Especiais', 'cchla-ufrn'),
        'add_new_item'          => __('Adicionar Novo Especial', 'cchla-ufrn'),
        'add_new'               => __('Adicionar Novo', 'cchla-ufrn'),
        'new_item'              => __('Novo Especial', 'cchla-ufrn'),
        'edit_item'             => __('Editar Especial', 'cchla-ufrn'),
        'update_item'           => __('Atualizar Especial', 'cchla-ufrn'),
        'view_item'             => __('Ver Especial', 'cchla-ufrn'),
        'view_items'            => __('Ver Especiais', 'cchla-ufrn'),
        'search_items'          => __('Buscar Especial', 'cchla-ufrn'),
        'not_found'             => __('Nenhum especial encontrado', 'cchla-ufrn'),
        'not_found_in_trash'    => __('Nenhum especial na lixeira', 'cchla-ufrn'),
    );

    $args = array(
        'label'                 => __('Especial', 'cchla-ufrn'),
        'description'           => __('Projetos especiais do CCHLA', 'cchla-ufrn'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'),
        'taxonomies'            => array('categoria_especial'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 28,
        'menu_icon'             => 'dashicons-video-alt3',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => 'especiais',
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite'               => array('slug' => 'especial', 'with_front' => false),
    );

    register_post_type('especiais', $args);
}
add_action('init', 'cchla_register_especiais_cpt', 0);

/**
 * Registra a Taxonomia "Categoria de Especial"
 */
function cchla_register_categoria_especial_taxonomy()
{
    $labels = array(
        'name'                       => _x('Categorias', 'Taxonomy General Name', 'cchla-ufrn'),
        'singular_name'              => _x('Categoria', 'Taxonomy Singular Name', 'cchla-ufrn'),
        'menu_name'                  => __('Categorias', 'cchla-ufrn'),
        'all_items'                  => __('Todas as Categorias', 'cchla-ufrn'),
        'parent_item'                => __('Categoria Pai', 'cchla-ufrn'),
        'parent_item_colon'          => __('Categoria Pai:', 'cchla-ufrn'),
        'new_item_name'              => __('Nova Categoria', 'cchla-ufrn'),
        'add_new_item'               => __('Adicionar Nova Categoria', 'cchla-ufrn'),
        'edit_item'                  => __('Editar Categoria', 'cchla-ufrn'),
        'update_item'                => __('Atualizar Categoria', 'cchla-ufrn'),
        'view_item'                  => __('Ver Categoria', 'cchla-ufrn'),
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

    register_taxonomy('categoria_especial', array('especiais'), $args);
}
add_action('init', 'cchla_register_categoria_especial_taxonomy', 0);

/**
 * Cria categorias padrão
 */
function cchla_insert_default_categoria_especial_terms()
{
    $categorias = array(
        'Comunicação' => 'Projetos de comunicação social',
        'Educação' => 'Projetos educacionais',
        'Cultura' => 'Projetos culturais',
        'Inclusão' => 'Projetos de inclusão social',
        'Saúde' => 'Projetos relacionados à saúde',
    );

    foreach ($categorias as $nome => $descricao) {
        if (!term_exists($nome, 'categoria_especial')) {
            wp_insert_term($nome, 'categoria_especial', array(
                'slug' => sanitize_title($nome),
                'description' => $descricao,
            ));
        }
    }
}
add_action('init', 'cchla_insert_default_categoria_especial_terms');

/**
 * Adiciona Meta Boxes para Especiais
 */
function cchla_especiais_meta_boxes()
{
    add_meta_box(
        'cchla_especial_video',
        __('Configurações de Vídeo', 'cchla-ufrn'),
        'cchla_especial_video_meta_box_callback',
        'especiais',
        'normal',
        'high'
    );

    add_meta_box(
        'cchla_especial_info',
        __('Informações Adicionais', 'cchla-ufrn'),
        'cchla_especial_info_meta_box_callback',
        'especiais',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'cchla_especiais_meta_boxes');

/**
 * Callback do Meta Box de Vídeo
 */
function cchla_especial_video_meta_box_callback($post)
{
    wp_nonce_field('cchla_save_especial_meta', 'cchla_especial_nonce');

    $video_tipo = get_post_meta($post->ID, '_especial_video_tipo', true);
    $video_url = get_post_meta($post->ID, '_especial_video_url', true);
    $video_embed = get_post_meta($post->ID, '_especial_video_embed', true);
    $video_arquivo_id = get_post_meta($post->ID, '_especial_video_arquivo_id', true);

    $video_tipo = $video_tipo ? $video_tipo : 'url';
    ?>

    <div class="cchla-especial-video-box">

        <!-- Tipo de Vídeo -->
        <p class="form-field">
            <label>
                <strong><?php esc_html_e('Tipo de Vídeo', 'cchla-ufrn'); ?></strong>
            </label>
            <label style="display: block; margin: 10px 0;">
                <input type="radio" name="especial_video_tipo" value="url" <?php checked($video_tipo, 'url'); ?>>
                <?php esc_html_e('URL (YouTube, Vimeo, etc)', 'cchla-ufrn'); ?>
            </label>
            <label style="display: block; margin: 10px 0;">
                <input type="radio" name="especial_video_tipo" value="embed" <?php checked($video_tipo, 'embed'); ?>>
                <?php esc_html_e('Código Embed', 'cchla-ufrn'); ?>
            </label>
            <label style="display: block; margin: 10px 0;">
                <input type="radio" name="especial_video_tipo" value="arquivo" <?php checked($video_tipo, 'arquivo'); ?>>
                <?php esc_html_e('Arquivo de Vídeo (Upload)', 'cchla-ufrn'); ?>
            </label>
        </p>

        <!-- URL do Vídeo -->
        <div class="video-url-field" style="<?php echo $video_tipo !== 'url' ? 'display:none;' : ''; ?>">
            <p class="form-field">
                <label for="especial_video_url">
                    <strong><?php esc_html_e('URL do Vídeo', 'cchla-ufrn'); ?></strong>
                </label>
                <input
                    type="url"
                    id="especial_video_url"
                    name="especial_video_url"
                    value="<?php echo esc_url($video_url); ?>"
                    class="large-text"
                    placeholder="https://www.youtube.com/watch?v=...">
                <span class="description">
                    <?php esc_html_e('Cole a URL do YouTube, Vimeo ou outro serviço', 'cchla-ufrn'); ?>
                </span>

                <?php if ($video_url) : ?>
            <div style="margin-top: 15px;">
                <strong><?php esc_html_e('Preview:', 'cchla-ufrn'); ?></strong>
                <div style="margin-top: 10px; max-width: 100%;">
                    <?php echo wp_oembed_get($video_url, array('width' => 600)); ?>
                </div>
            </div>
        <?php endif; ?>
        </p>
        </div>

        <!-- Código Embed -->
        <div class="video-embed-field" style="<?php echo $video_tipo !== 'embed' ? 'display:none;' : ''; ?>">
            <p class="form-field">
                <label for="especial_video_embed">
                    <strong><?php esc_html_e('Código Embed', 'cchla-ufrn'); ?></strong>
                </label>
                <textarea
                    id="especial_video_embed"
                    name="especial_video_embed"
                    rows="6"
                    class="large-text code"
                    placeholder='<iframe src="..." ...></iframe>'><?php echo esc_textarea($video_embed); ?></textarea>
                <span class="description">
                    <?php esc_html_e('Cole o código iframe do vídeo', 'cchla-ufrn'); ?>
                </span>
            </p>
        </div>

        <!-- Arquivo de Vídeo -->
        <div class="video-arquivo-field" style="<?php echo $video_tipo !== 'arquivo' ? 'display:none;' : ''; ?>">
            <p class="form-field">
                <label>
                    <strong><?php esc_html_e('Arquivo de Vídeo', 'cchla-ufrn'); ?></strong>
                </label>
                <input type="hidden" id="especial_video_arquivo_id" name="especial_video_arquivo_id" value="<?php echo esc_attr($video_arquivo_id); ?>">
            <div style="margin: 10px 0;">
                <button type="button" class="button upload-video-button">
                    <i class="dashicons dashicons-upload" style="vertical-align: middle;"></i>
                    <?php esc_html_e('Fazer Upload de Vídeo', 'cchla-ufrn'); ?>
                </button>
                <button type="button" class="button remove-video-button" style="<?php echo !$video_arquivo_id ? 'display:none;' : ''; ?>">
                    <?php esc_html_e('Remover Vídeo', 'cchla-ufrn'); ?>
                </button>
            </div>
            <?php if ($video_arquivo_id) :
                $video_url_preview = wp_get_attachment_url($video_arquivo_id);
            ?>
                <div class="video-preview" style="margin-top: 10px;">
                    <video src="<?php echo esc_url($video_url_preview); ?>" controls style="max-width: 100%; height: auto;"></video>
                    <p class="description">
                        <?php echo basename($video_url_preview); ?>
                    </p>
                </div>
            <?php endif; ?>
            <span class="description">
                <?php esc_html_e('Formatos: MP4, WebM, OGV. Recomendado: MP4 até 50MB', 'cchla-ufrn'); ?>
            </span>
            </p>
        </div>

        <!-- Observação sobre Thumbnail -->
        <div style="background: #f0f6fc; border-left: 4px solid #0073aa; padding: 15px; margin-top: 20px;">
            <strong style="display: block; margin-bottom: 8px;">
                <i class="dashicons dashicons-format-image" style="vertical-align: middle;"></i>
                <?php esc_html_e('Thumbnail do Vídeo', 'cchla-ufrn'); ?>
            </strong>
            <p style="margin: 0; color: #555;">
                <?php esc_html_e('Use o box "Imagem Destacada" ao lado direito para definir a thumbnail que aparece antes do vídeo carregar.', 'cchla-ufrn'); ?>
            </p>
        </div>

    </div>

    <script>
        jQuery(document).ready(function($) {
            // Toggle entre tipos de vídeo
            $('input[name="especial_video_tipo"]').on('change', function() {
                $('.video-url-field, .video-embed-field, .video-arquivo-field').hide();

                if ($(this).val() === 'url') {
                    $('.video-url-field').show();
                } else if ($(this).val() === 'embed') {
                    $('.video-embed-field').show();
                } else if ($(this).val() === 'arquivo') {
                    $('.video-arquivo-field').show();
                }
            });

            // Upload de vídeo
            var mediaUploader;

            $('.upload-video-button').on('click', function(e) {
                e.preventDefault();

                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }

                mediaUploader = wp.media({
                    title: '<?php esc_html_e('Escolher Vídeo', 'cchla-ufrn'); ?>',
                    button: {
                        text: '<?php esc_html_e('Usar este vídeo', 'cchla-ufrn'); ?>'
                    },
                    library: {
                        type: 'video'
                    },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#especial_video_arquivo_id').val(attachment.id);

                    var videoHtml = '<video src="' + attachment.url + '" controls style="max-width: 100%; height: auto;"></video>';
                    videoHtml += '<p class="description">' + attachment.filename + '</p>';

                    $('.video-preview').html(videoHtml);
                    $('.remove-video-button').show();
                });

                mediaUploader.open();
            });

            // Remover vídeo
            $('.remove-video-button').on('click', function(e) {
                e.preventDefault();
                $('#especial_video_arquivo_id').val('');
                $('.video-preview').html('');
                $(this).hide();
            });
        });
    </script>

    <style>
        .cchla-especial-video-box .form-field {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f1;
        }

        .cchla-especial-video-box .form-field:last-child {
            border-bottom: none;
        }

        .cchla-especial-video-box label strong {
            display: block;
            margin-bottom: 5px;
        }

        .cchla-especial-video-box .description {
            display: block;
            margin-top: 5px;
            font-style: italic;
            color: #646970;
        }
    </style>
<?php
}

/**
 * Callback do Meta Box de Informações
 */
function cchla_especial_info_meta_box_callback($post)
{
    $link_projeto = get_post_meta($post->ID, '_especial_link_projeto', true);
    $destaque_home = get_post_meta($post->ID, '_especial_destaque_home', true);
?>

    <div class="cchla-especial-info-box">

        <!-- Link do Projeto -->
        <p style="margin-bottom: 15px;">
            <label for="especial_link_projeto">
                <strong><?php esc_html_e('Link do Projeto', 'cchla-ufrn'); ?></strong>
            </label>
            <input
                type="url"
                id="especial_link_projeto"
                name="especial_link_projeto"
                value="<?php echo esc_url($link_projeto); ?>"
                class="widefat"
                placeholder="https://projeto.cchla.ufrn.br">
            <span style="display: block; margin-top: 5px; font-size: 12px; color: #666;">
                <?php esc_html_e('Link externo do projeto', 'cchla-ufrn'); ?>
            </span>
        </p>

        <!-- Destacar na Home -->
        <p style="margin-bottom: 15px;">
            <label>
                <input type="checkbox" name="especial_destaque_home" value="1" <?php checked($destaque_home, '1'); ?>>
                <strong><?php esc_html_e('Destacar na Home', 'cchla-ufrn'); ?></strong>
            </label>
            <span style="display: block; margin-top: 5px; font-size: 12px; color: #666;">
                <?php esc_html_e('Exibir como destaque principal', 'cchla-ufrn'); ?>
            </span>
        </p>

        <!-- Ordem -->
        <p style="background: #f0f6fc; padding: 10px; border-radius: 4px;">
            <strong style="display: block; margin-bottom: 5px;">
                <?php esc_html_e('💡 Dica:', 'cchla-ufrn'); ?>
            </strong>
            <span style="font-size: 12px; color: #555;">
                <?php esc_html_e('Use o campo "Ordem" no box "Atributos" ao lado para definir a sequência de exibição.', 'cchla-ufrn'); ?>
            </span>
        </p>

    </div>
    <?php
}

/**
 * Salva os Meta Dados
 */
function cchla_save_especial_meta($post_id)
{
    if (
        !isset($_POST['cchla_especial_nonce']) ||
        !wp_verify_nonce($_POST['cchla_especial_nonce'], 'cchla_save_especial_meta')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Tipo de vídeo
    if (isset($_POST['especial_video_tipo'])) {
        update_post_meta($post_id, '_especial_video_tipo', sanitize_text_field($_POST['especial_video_tipo']));
    }

    // URL do vídeo
    if (isset($_POST['especial_video_url'])) {
        update_post_meta($post_id, '_especial_video_url', esc_url_raw($_POST['especial_video_url']));
    }

    // Embed
    if (isset($_POST['especial_video_embed'])) {
        $embed = wp_kses($_POST['especial_video_embed'], array(
            'iframe' => array(
                'src' => array(),
                'width' => array(),
                'height' => array(),
                'frameborder' => array(),
                'allowfullscreen' => array(),
                'allow' => array(),
                'title' => array(),
            ),
        ));
        update_post_meta($post_id, '_especial_video_embed', $embed);
    }

    // Arquivo de vídeo
    if (isset($_POST['especial_video_arquivo_id'])) {
        update_post_meta($post_id, '_especial_video_arquivo_id', absint($_POST['especial_video_arquivo_id']));
    }

    // Link do projeto
    if (isset($_POST['especial_link_projeto'])) {
        update_post_meta($post_id, '_especial_link_projeto', esc_url_raw($_POST['especial_link_projeto']));
    }

    // Destaque home
    if (isset($_POST['especial_destaque_home'])) {
        update_post_meta($post_id, '_especial_destaque_home', '1');
    } else {
        delete_post_meta($post_id, '_especial_destaque_home');
    }
}
add_action('save_post', 'cchla_save_especial_meta');

/**
 * Adiciona colunas personalizadas no admin
 */
function cchla_especiais_columns($columns)
{
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['featured_image'] = __('Thumbnail', 'cchla-ufrn');
    $new_columns['title'] = $columns['title'];
    $new_columns['video'] = __('Vídeo', 'cchla-ufrn');
    $new_columns['destaque'] = __('Destaque', 'cchla-ufrn');
    $new_columns['taxonomy-categoria_especial'] = __('Categoria', 'cchla-ufrn');
    $new_columns['menu_order'] = __('Ordem', 'cchla-ufrn');
    $new_columns['date'] = $columns['date'];

    return $new_columns;
}
add_filter('manage_especiais_posts_columns', 'cchla_especiais_columns');

function cchla_especiais_column_content($column, $post_id)
{
    switch ($column) {
        case 'featured_image':
            if (has_post_thumbnail($post_id)) {
                echo get_the_post_thumbnail($post_id, array(80, 60));
            } else {
                echo '<span style="color: #ccc;">—</span>';
            }
            break;

        case 'video':
            $tipo = get_post_meta($post_id, '_especial_video_tipo', true);
            $icons = array(
                'url' => '<i class="dashicons dashicons-video-alt3" style="color: #2271b1;"></i> URL',
                'embed' => '<i class="dashicons dashicons-video-alt2" style="color: #2271b1;"></i> Embed',
                'arquivo' => '<i class="dashicons dashicons-media-video" style="color: #2271b1;"></i> Arquivo',
            );
            echo isset($icons[$tipo]) ? $icons[$tipo] : '<span style="color: #ccc;">—</span>';
            break;

        case 'destaque':
            $destaque = get_post_meta($post_id, '_especial_destaque_home', true);
            if ($destaque === '1') {
                echo '<span style="color: #00a32a; font-weight: bold;">★ Sim</span>';
            } else {
                echo '<span style="color: #ccc;">—</span>';
            }
            break;

        case 'menu_order':
            $order = get_post_field('menu_order', $post_id);
            echo '<strong>' . esc_html($order) . '</strong>';
            break;
    }
}
add_action('manage_especiais_posts_custom_column', 'cchla_especiais_column_content', 10, 2);

/**
 * Torna colunas ordenáveis
 */
function cchla_especiais_sortable_columns($columns)
{
    $columns['menu_order'] = 'menu_order';
    $columns['destaque'] = 'destaque_home';
    return $columns;
}
add_filter('manage_edit-especiais_sortable_columns', 'cchla_especiais_sortable_columns');
/**
 * Shortcode para Especiais
 * Uso: [especiais limite="4" categoria="comunicacao"]
 */
function cchla_especiais_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'limite' => 4,
        'categoria' => '',
        'destaque' => 'nao',
    ), $atts, 'especiais');

    $args = array(
        'post_type' => 'especiais',
        'posts_per_page' => intval($atts['limite']),
        'orderby' => 'menu_order',
        'order' => 'ASC',
    );

    if (!empty($atts['categoria'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'categoria_especial',
                'field' => 'slug',
                'terms' => sanitize_text_field($atts['categoria']),
            ),
        );
    }

    if ($atts['destaque'] === 'sim') {
        $args['meta_query'] = array(
            array(
                'key' => '_especial_destaque_home',
                'value' => '1',
            ),
        );
    }

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '';
    }

    ob_start();

    echo '<div class="especiais-grid grid gap-6 md:grid-cols-2 lg:grid-cols-' . min(4, intval($atts['limite'])) . '">';

    while ($query->have_posts()) {
        $query->the_post();

        $link_projeto = get_post_meta(get_the_ID(), '_especial_link_projeto', true);
        $link_url = $link_projeto ? $link_projeto : get_permalink();
    ?>

        <article class="group bg-white rounded-lg overflow-hidden shadow hover:shadow-lg transition-shadow">
            <a href="<?php echo esc_url($link_url); ?>"
                <?php if ($link_projeto) echo 'target="_blank" rel="noopener noreferrer"'; ?>>

                <div class="relative overflow-hidden aspect-video">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('medium', array(
                            'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300'
                        )); ?>
                    <?php else : ?>
                        <div class="w-full h-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center">
                            <i class="fa-solid fa-video text-4xl text-white opacity-50"></i>
                        </div>
                    <?php endif; ?>

                    <div class="absolute top-3 left-3 bg-black/70 text-white px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-play"></i>
                        <?php esc_html_e('Vídeo', 'cchla-ufrn'); ?>
                    </div>
                </div>

                <div class="p-5">
                    <h3 class="font-semibold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                        <?php the_title(); ?>
                    </h3>

                    <span class="text-blue-600 text-sm font-medium">
                        <?php esc_html_e('Assistir →', 'cchla-ufrn'); ?>
                    </span>
                </div>
            </a>
        </article>

        <?php
    }

    echo '</div>';

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('especiais', 'cchla_especiais_shortcode');

/**
 * Widget de Especiais
 */
class CCHLA_Especiais_Widget extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'cchla_especiais_widget',
            __('CCHLA - Especiais', 'cchla-ufrn'),
            array('description' => __('Exibe especiais do CCHLA', 'cchla-ufrn'))
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $limite = !empty($instance['limite']) ? $instance['limite'] : 3;

        $widget_args = array(
            'post_type' => 'especiais',
            'posts_per_page' => $limite,
            'orderby' => 'menu_order',
            'order' => 'ASC',
        );

        $query = new WP_Query($widget_args);

        if ($query->have_posts()) {
            echo '<div class="especiais-widget space-y-4">';

            while ($query->have_posts()) {
                $query->the_post();

                $link_projeto = get_post_meta(get_the_ID(), '_especial_link_projeto', true);
                $link_url = $link_projeto ? $link_projeto : get_permalink();
        ?>

                <article class="flex gap-3">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="flex-shrink-0 w-24 h-16 overflow-hidden rounded">
                            <a href="<?php echo esc_url($link_url); ?>">
                                <?php the_post_thumbnail('thumbnail', array('class' => 'w-full h-full object-cover')); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="flex-1">
                        <h4 class="text-sm font-semibold mb-1">
                            <a href="<?php echo esc_url($link_url); ?>" class="hover:text-blue-600">
                                <?php the_title(); ?>
                            </a>
                        </h4>
                        <span class="text-xs text-blue-600">
                            <i class="fa-solid fa-play"></i>
                            <?php esc_html_e('Assistir', 'cchla-ufrn'); ?>
                        </span>
                    </div>
                </article>

        <?php
            }

            echo '</div>';

            echo '<div class="mt-4 pt-4 border-t">';
            echo '<a href="' . esc_url(get_post_type_archive_link('especiais')) . '" class="text-sm text-blue-600 hover:underline">';
            esc_html_e('Ver todos os especiais →', 'cchla-ufrn');
            echo '</a>';
            echo '</div>';

            wp_reset_postdata();
        }

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : __('Especiais CCHLA', 'cchla-ufrn');
        $limite = !empty($instance['limite']) ? $instance['limite'] : 3;
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
                max="10"
                value="<?php echo esc_attr($limite); ?>">
        </p>
    <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['limite'] = (!empty($new_instance['limite'])) ? absint($new_instance['limite']) : 3;
        return $instance;
    }
}

function cchla_register_especiais_widget()
{
    register_widget('CCHLA_Especiais_Widget');
}
add_action('widgets_init', 'cchla_register_especiais_widget');

/**
 * Adiciona suporte a tamanhos de imagem para vídeos
 */
function cchla_add_especial_image_sizes()
{
    add_image_size('especial-thumb', 260, 180, true);
    add_image_size('especial-large', 800, 450, true);
}
add_action('after_setup_theme', 'cchla_add_especial_image_sizes');

/**
 * Modifica query do arquivo para ordenar por menu_order
 */
function cchla_especiais_archive_query($query)
{
    if (!is_admin() && $query->is_main_query() && (is_post_type_archive('especiais') || is_tax('categoria_especial'))) {
        $query->set('orderby', 'menu_order');
        $query->set('order', 'ASC');
    }
}
add_action('pre_get_posts', 'cchla_especiais_archive_query');

/**
 * ============================================
 * WORDPRESS CUSTOMIZER - CONFIGURAÇÕES DO TEMA
 * ============================================
 */

/**
 * Registra configurações no Customizer
 */
function cchla_customize_register($wp_customize)
{

    /**
     * ========================================
     * SEÇÃO: INFORMAÇÕES GERAIS
     * ========================================
     */
    $wp_customize->add_section('cchla_info_geral', array(
        'title' => __('Informações Gerais', 'cchla-ufrn'),
        'priority' => 30,
        'description' => __('Configure informações básicas do CCHLA', 'cchla-ufrn'),
    ));

    // Nome completo da instituição
    $wp_customize->add_setting('cchla_nome_completo', array(
        'default' => 'Centro de Ciências Humanas, Letras e Artes',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));

    $wp_customize->add_control('cchla_nome_completo', array(
        'label' => __('Nome Completo', 'cchla-ufrn'),
        'section' => 'cchla_info_geral',
        'type' => 'text',
    ));

    // Sigla
    $wp_customize->add_setting('cchla_sigla', array(
        'default' => 'CCHLA',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));

    $wp_customize->add_control('cchla_sigla', array(
        'label' => __('Sigla', 'cchla-ufrn'),
        'section' => 'cchla_info_geral',
        'type' => 'text',
    ));

    // Descrição curta
    $wp_customize->add_setting('cchla_descricao_curta', array(
        'default' => 'Ensino, pesquisa, cultura e extensão',
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport' => 'refresh',
    ));

    $wp_customize->add_control('cchla_descricao_curta', array(
        'label' => __('Descrição Curta', 'cchla-ufrn'),
        'description' => __('Usada no rodapé e meta tags', 'cchla-ufrn'),
        'section' => 'cchla_info_geral',
        'type' => 'textarea',
    ));

    /**
     * ========================================
     * SEÇÃO: CONTATO
     * ========================================
     */
    $wp_customize->add_section('cchla_contato', array(
        'title' => __('Informações de Contato', 'cchla-ufrn'),
        'priority' => 31,
        'description' => __('Configure telefones, emails e endereço', 'cchla-ufrn'),
    ));

    // Telefone Principal
    $wp_customize->add_setting('cchla_telefone_principal', array(
        'default' => '(84) 3342-2243',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('cchla_telefone_principal', array(
        'label' => __('Telefone Principal', 'cchla-ufrn'),
        'section' => 'cchla_contato',
        'type' => 'text',
    ));

    // Telefone Secundário
    $wp_customize->add_setting('cchla_telefone_secundario', array(
        'default' => '(84) 99193-6154',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('cchla_telefone_secundario', array(
        'label' => __('Telefone Secundário', 'cchla-ufrn'),
        'section' => 'cchla_contato',
        'type' => 'text',
    ));

    // Email Principal
    $wp_customize->add_setting('cchla_email_principal', array(
        'default' => 'secretariacchla@gmail.com',
        'sanitize_callback' => 'sanitize_email',
    ));

    $wp_customize->add_control('cchla_email_principal', array(
        'label' => __('Email Principal', 'cchla-ufrn'),
        'section' => 'cchla_contato',
        'type' => 'email',
    ));

    // Email Secundário
    $wp_customize->add_setting('cchla_email_secundario', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_email',
    ));

    $wp_customize->add_control('cchla_email_secundario', array(
        'label' => __('Email Secundário (Opcional)', 'cchla-ufrn'),
        'section' => 'cchla_contato',
        'type' => 'email',
    ));

    // Endereço Completo
    $wp_customize->add_setting('cchla_endereco_completo', array(
        'default' => 'Av. Sen. Salgado Filho, S/n – Lagoa Nova, Natal – RN, 59078-970',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));

    $wp_customize->add_control('cchla_endereco_completo', array(
        'label' => __('Endereço Completo', 'cchla-ufrn'),
        'section' => 'cchla_contato',
        'type' => 'textarea',
    ));

    // Link Google Maps
    $wp_customize->add_setting('cchla_google_maps_link', array(
        'default' => 'https://www.google.com/maps/search/?api=1&query=CCHLA+-+CENTRO+DE+CI%C3%8ANCIAS+HUMANAS%2C+LETRAS+E+ARTES+-+UFRN%2C+Natal%2C+RN',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('cchla_google_maps_link', array(
        'label' => __('Link Google Maps', 'cchla-ufrn'),
        'section' => 'cchla_contato',
        'type' => 'url',
    ));

    // Horário de Funcionamento
    $wp_customize->add_setting('cchla_horario_funcionamento', array(
        'default' => 'Segunda a Sexta, 8h às 17h',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('cchla_horario_funcionamento', array(
        'label' => __('Horário de Funcionamento', 'cchla-ufrn'),
        'section' => 'cchla_contato',
        'type' => 'text',
    ));

    /**
     * ========================================
     * SEÇÃO: REDES SOCIAIS
     * ========================================
     */
    $wp_customize->add_section('cchla_redes_sociais', array(
        'title' => __('Redes Sociais', 'cchla-ufrn'),
        'priority' => 32,
        'description' => __('Configure os links das redes sociais do CCHLA', 'cchla-ufrn'),
    ));

    // Facebook
    $wp_customize->add_setting('cchla_facebook', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('cchla_facebook', array(
        'label' => __('Facebook', 'cchla-ufrn'),
        'section' => 'cchla_redes_sociais',
        'type' => 'url',
        'description' => __('https://facebook.com/seuperfil', 'cchla-ufrn'),
    ));

    // Instagram
    $wp_customize->add_setting('cchla_instagram', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('cchla_instagram', array(
        'label' => __('Instagram', 'cchla-ufrn'),
        'section' => 'cchla_redes_sociais',
        'type' => 'url',
        'description' => __('https://instagram.com/seuperfil', 'cchla-ufrn'),
    ));

    // Twitter/X
    $wp_customize->add_setting('cchla_twitter', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('cchla_twitter', array(
        'label' => __('Twitter / X', 'cchla-ufrn'),
        'section' => 'cchla_redes_sociais',
        'type' => 'url',
        'description' => __('https://twitter.com/seuperfil', 'cchla-ufrn'),
    ));

    // YouTube
    $wp_customize->add_setting('cchla_youtube', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('cchla_youtube', array(
        'label' => __('YouTube', 'cchla-ufrn'),
        'section' => 'cchla_redes_sociais',
        'type' => 'url',
        'description' => __('https://youtube.com/@seucanal', 'cchla-ufrn'),
    ));

    // LinkedIn
    $wp_customize->add_setting('cchla_linkedin', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('cchla_linkedin', array(
        'label' => __('LinkedIn', 'cchla-ufrn'),
        'section' => 'cchla_redes_sociais',
        'type' => 'url',
        'description' => __('https://linkedin.com/company/suaempresa', 'cchla-ufrn'),
    ));

    // TikTok
    $wp_customize->add_setting('cchla_tiktok', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('cchla_tiktok', array(
        'label' => __('TikTok', 'cchla-ufrn'),
        'section' => 'cchla_redes_sociais',
        'type' => 'url',
        'description' => __('https://tiktok.com/@seuperfil', 'cchla-ufrn'),
    ));

    // WhatsApp
    $wp_customize->add_setting('cchla_whatsapp', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('cchla_whatsapp', array(
        'label' => __('WhatsApp (Número)', 'cchla-ufrn'),
        'section' => 'cchla_redes_sociais',
        'type' => 'text',
        'description' => __('Apenas números: 5584991936154', 'cchla-ufrn'),
    ));

    /**
     * ========================================
     * SEÇÃO: RODAPÉ
     * ========================================
     */
    $wp_customize->add_section('cchla_rodape', array(
        'title' => __('Configurações do Rodapé', 'cchla-ufrn'),
        'priority' => 33,
        'description' => __('Personalize textos e informações do rodapé', 'cchla-ufrn'),
    ));

    // Texto do Rodapé
    $wp_customize->add_setting('cchla_rodape_texto', array(
        'default' => 'Centro de Ciências Humanas, Letras e Artes',
        'sanitize_callback' => 'wp_kses_post',
    ));

    $wp_customize->add_control('cchla_rodape_texto', array(
        'label' => __('Texto do Rodapé', 'cchla-ufrn'),
        'description' => __('Texto que aparece ao lado do logo no rodapé', 'cchla-ufrn'),
        'section' => 'cchla_rodape',
        'type' => 'textarea',
    ));

    // Copyright
    $wp_customize->add_setting('cchla_copyright', array(
        'default' => '© ' . date('Y') . ' CCHLA - UFRN. Todos os direitos reservados.',
        'sanitize_callback' => 'wp_kses_post',
    ));

    $wp_customize->add_control('cchla_copyright', array(
        'label' => __('Texto de Copyright', 'cchla-ufrn'),
        'description' => __('Use [ano] para o ano atual', 'cchla-ufrn'),
        'section' => 'cchla_rodape',
        'type' => 'textarea',
    ));

    // Créditos
    $wp_customize->add_setting('cchla_creditos', array(
        'default' => 'Desenvolvido por Agência Web - IFRN',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('cchla_creditos', array(
        'label' => __('Créditos', 'cchla-ufrn'),
        'section' => 'cchla_rodape',
        'type' => 'text',
    ));

    // Link dos Créditos
    $wp_customize->add_setting('cchla_creditos_link', array(
        'default' => 'https://agenciaweb.ifrn.edu.br',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('cchla_creditos_link', array(
        'label' => __('Link dos Créditos', 'cchla-ufrn'),
        'section' => 'cchla_rodape',
        'type' => 'url',
    ));

    // Logo do Rodapé
    $wp_customize->add_setting('cchla_rodape_logo', array(
        'default' => '',
        'sanitize_callback' => 'absint',
    ));

    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'cchla_rodape_logo', array(
        'label' => __('Logo do Rodapé', 'cchla-ufrn'),
        'description' => __('Se não definido, usa o logo principal', 'cchla-ufrn'),
        'section' => 'cchla_rodape',
        'mime_type' => 'image',
    )));

    /**
     * ========================================
     * SEÇÃO: SEO E COMPARTILHAMENTO
     * ========================================
     */
    $wp_customize->add_section('cchla_seo', array(
        'title' => __('SEO e Compartilhamento', 'cchla-ufrn'),
        'priority' => 34,
        'description' => __('Configure imagens e textos para SEO', 'cchla-ufrn'),
    ));

    // Imagem padrão de compartilhamento
    $wp_customize->add_setting('cchla_default_share_image', array(
        'default' => '',
        'sanitize_callback' => 'absint',
    ));

    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'cchla_default_share_image', array(
        'label' => __('Imagem Padrão de Compartilhamento', 'cchla-ufrn'),
        'description' => __('Usada quando posts não têm imagem destacada (1200x630px)', 'cchla-ufrn'),
        'section' => 'cchla_seo',
        'mime_type' => 'image',
    )));

    // Google Analytics ID
    $wp_customize->add_setting('cchla_google_analytics', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('cchla_google_analytics', array(
        'label' => __('Google Analytics ID', 'cchla-ufrn'),
        'description' => __('Ex: G-XXXXXXXXXX ou UA-XXXXXXXXX', 'cchla-ufrn'),
        'section' => 'cchla_seo',
        'type' => 'text',
    ));

    // Facebook App ID
    $wp_customize->add_setting('cchla_facebook_app_id', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('cchla_facebook_app_id', array(
        'label' => __('Facebook App ID', 'cchla-ufrn'),
        'description' => __('Para insights do Facebook', 'cchla-ufrn'),
        'section' => 'cchla_seo',
        'type' => 'text',
    ));

    /**
     * ========================================
     * SEÇÃO: SCRIPTS PERSONALIZADOS
     * ========================================
     */
    $wp_customize->add_section('cchla_scripts', array(
        'title' => __('Scripts Personalizados', 'cchla-ufrn'),
        'priority' => 35,
        'description' => __('Adicione scripts customizados ao site', 'cchla-ufrn'),
    ));

    // Header Scripts
    $wp_customize->add_setting('cchla_header_scripts', array(
        'default' => '',
        'sanitize_callback' => 'cchla_sanitize_scripts',
    ));

    $wp_customize->add_control('cchla_header_scripts', array(
        'label' => __('Scripts no Header', 'cchla-ufrn'),
        'description' => __('Código inserido antes do </head>', 'cchla-ufrn'),
        'section' => 'cchla_scripts',
        'type' => 'textarea',
    ));

    // Footer Scripts
    $wp_customize->add_setting('cchla_footer_scripts', array(
        'default' => '',
        'sanitize_callback' => 'cchla_sanitize_scripts',
    ));

    $wp_customize->add_control('cchla_footer_scripts', array(
        'label' => __('Scripts no Footer', 'cchla-ufrn'),
        'description' => __('Código inserido antes do </body>', 'cchla-ufrn'),
        'section' => 'cchla_scripts',
        'type' => 'textarea',
    ));
}
add_action('customize_register', 'cchla_customize_register');

/**
 * Sanitiza scripts personalizados
 */
function cchla_sanitize_scripts($input)
{
    return wp_kses_post($input);
}

/**
 * Adiciona scripts personalizados no header
 */
function cchla_add_header_scripts()
{
    $header_scripts = get_theme_mod('cchla_header_scripts', '');

    if (!empty($header_scripts)) {
        echo "\n<!-- Custom Header Scripts -->\n";
        echo $header_scripts;
        echo "\n<!-- /Custom Header Scripts -->\n";
    }

    // Google Analytics
    $ga_id = get_theme_mod('cchla_google_analytics', '');
    if (!empty($ga_id)) {
    ?>
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($ga_id); ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', '<?php echo esc_js($ga_id); ?>');
        </script>
    <?php
    }
}
add_action('wp_head', 'cchla_add_header_scripts');

/**
 * Adiciona scripts personalizados no footer
 */
function cchla_add_footer_scripts()
{
    $footer_scripts = get_theme_mod('cchla_footer_scripts', '');

    if (!empty($footer_scripts)) {
        echo "\n<!-- Custom Footer Scripts -->\n";
        echo $footer_scripts;
        echo "\n<!-- /Custom Footer Scripts -->\n";
    }
}
add_action('wp_footer', 'cchla_add_footer_scripts');
/**
 * ============================================
 * FUNÇÕES AUXILIARES PARA CUSTOMIZER
 * ============================================
 */

/**
 * Retorna informações de contato
 */
function cchla_get_contato_info($campo = '')
{
    $contatos = array(
        'telefone_principal' => get_theme_mod('cchla_telefone_principal', '(84) 3342-2243'),
        'telefone_secundario' => get_theme_mod('cchla_telefone_secundario', '(84) 99193-6154'),
        'email_principal' => get_theme_mod('cchla_email_principal', 'secretariacchla@gmail.com'),
        'email_secundario' => get_theme_mod('cchla_email_secundario', ''),
        'endereco' => get_theme_mod('cchla_endereco_completo', 'Av. Sen. Salgado Filho, S/n – Lagoa Nova, Natal – RN, 59078-970'),
        'google_maps' => get_theme_mod('cchla_google_maps_link', ''),
        'horario' => get_theme_mod('cchla_horario_funcionamento', 'Segunda a Sexta, 8h às 17h'),
    );

    if (!empty($campo) && isset($contatos[$campo])) {
        return $contatos[$campo];
    }

    return $contatos;
}

/**
 * Retorna redes sociais configuradas
 */
function cchla_get_redes_sociais()
{
    $redes = array(
        'facebook' => array(
            'url' => get_theme_mod('cchla_facebook', ''),
            'icon' => 'fa-brands fa-facebook-f',
            'label' => 'Facebook',
        ),
        'instagram' => array(
            'url' => get_theme_mod('cchla_instagram', ''),
            'icon' => 'fa-brands fa-instagram',
            'label' => 'Instagram',
        ),
        'twitter' => array(
            'url' => get_theme_mod('cchla_twitter', ''),
            'icon' => 'fa-brands fa-twitter',
            'label' => 'Twitter',
        ),
        'youtube' => array(
            'url' => get_theme_mod('cchla_youtube', ''),
            'icon' => 'fa-brands fa-youtube',
            'label' => 'YouTube',
        ),
        'linkedin' => array(
            'url' => get_theme_mod('cchla_linkedin', ''),
            'icon' => 'fa-brands fa-linkedin-in',
            'label' => 'LinkedIn',
        ),
        'tiktok' => array(
            'url' => get_theme_mod('cchla_tiktok', ''),
            'icon' => 'fa-brands fa-tiktok',
            'label' => 'TikTok',
        ),
        'whatsapp' => array(
            'url' => get_theme_mod('cchla_whatsapp', ''),
            'icon' => 'fa-brands fa-whatsapp',
            'label' => 'WhatsApp',
        ),
    );

    // Remove redes sem URL configurada
    return array_filter($redes, function ($rede) {
        return !empty($rede['url']);
    });
}

/**
 * Exibe redes sociais
 */
function cchla_display_redes_sociais($classe_wrapper = 'flex gap-4', $classe_link = 'w-8 h-8')
{
    $redes = cchla_get_redes_sociais();

    if (empty($redes)) {
        return;
    }

    echo '<nav aria-label="' . esc_attr__('Redes sociais', 'cchla-ufrn') . '" class="' . esc_attr($classe_wrapper) . '">';

    foreach ($redes as $key => $rede) {
        $url = $rede['url'];

        // WhatsApp usa formato especial
        if ($key === 'whatsapp') {
            $numero = preg_replace('/[^0-9]/', '', $url);
            $url = 'https://wa.me/' . $numero;
        }

        printf(
            '<a href="%s" aria-label="%s" target="_blank" rel="noopener noreferrer" class="%s flex items-center justify-center bg-white text-blue-700 rounded-full transition-all duration-200 hover:bg-blue-700 hover:text-white focus:bg-blue-700 focus:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"><i class="%s text-[14px]" aria-hidden="true"></i></a>',
            esc_url($url),
            esc_attr($rede['label']),
            esc_attr($classe_link),
            esc_attr($rede['icon'])
        );
    }

    echo '</nav>';
}

/**
 * Retorna copyright com substituição de variáveis
 */
function cchla_get_copyright()
{
    $copyright = get_theme_mod('cchla_copyright', '© ' . date('Y') . ' CCHLA - UFRN. Todos os direitos reservados.');

    // Substitui [ano] pelo ano atual
    $copyright = str_replace('[ano]', date('Y'), $copyright);

    return $copyright;
}

/**
 * Shortcode para exibir informações de contato
 * Uso: [contato campo="telefone_principal"]
 */
function cchla_contato_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'campo' => 'telefone_principal',
        'icone' => 'sim',
    ), $atts);

    $valor = cchla_get_contato_info($atts['campo']);

    if (empty($valor)) {
        return '';
    }

    $icones = array(
        'telefone_principal' => 'fa-solid fa-phone',
        'telefone_secundario' => 'fa-solid fa-phone',
        'email_principal' => 'fa-solid fa-envelope',
        'email_secundario' => 'fa-solid fa-envelope',
        'endereco' => 'fa-solid fa-location-dot',
        'horario' => 'fa-solid fa-clock',
    );

    $output = '';

    if ($atts['icone'] === 'sim' && isset($icones[$atts['campo']])) {
        $output .= '<i class="' . esc_attr($icones[$atts['campo']]) . '"></i> ';
    }

    // Formata links
    if (strpos($atts['campo'], 'email') !== false) {
        $output .= '<a href="mailto:' . esc_attr($valor) . '">' . esc_html($valor) . '</a>';
    } elseif (strpos($atts['campo'], 'telefone') !== false) {
        $tel_limpo = preg_replace('/[^0-9+]/', '', $valor);
        $output .= '<a href="tel:' . esc_attr($tel_limpo) . '">' . esc_html($valor) . '</a>';
    } else {
        $output .= esc_html($valor);
    }

    return $output;
}
add_shortcode('contato', 'cchla_contato_shortcode');

/**
 * Shortcode para exibir redes sociais
 * Uso: [redes_sociais]
 */
function cchla_redes_sociais_shortcode()
{
    ob_start();
    cchla_display_redes_sociais();
    return ob_get_clean();
}
add_shortcode('redes_sociais', 'cchla_redes_sociais_shortcode');
/**
 * Widget de Informações de Contato
 */
class CCHLA_Contato_Widget extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'cchla_contato_widget',
            __('CCHLA - Informações de Contato', 'cchla-ufrn'),
            array('description' => __('Exibe informações de contato do CCHLA', 'cchla-ufrn'))
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $mostrar = isset($instance['mostrar']) ? $instance['mostrar'] : array('telefone', 'email', 'endereco');

        echo '<div class="contato-widget space-y-3">';

        if (in_array('telefone', $mostrar)) {
            $telefone = cchla_get_contato_info('telefone_principal');
            if ($telefone) {
                echo '<div class="flex items-center gap-3">';
                echo '<i class="fa-solid fa-phone text-blue-600"></i>';
                echo '<a href="tel:' . esc_attr(preg_replace('/[^0-9+]/', '', $telefone)) . '" class="text-sm hover:text-blue-600">' . esc_html($telefone) . '</a>';
                echo '</div>';
            }
        }

        if (in_array('email', $mostrar)) {
            $email = cchla_get_contato_info('email_principal');
            if ($email) {
                echo '<div class="flex items-center gap-3">';
                echo '<i class="fa-solid fa-envelope text-blue-600"></i>';
                echo '<a href="mailto:' . esc_attr($email) . '" class="text-sm hover:text-blue-600 break-all">' . esc_html($email) . '</a>';
                echo '</div>';
            }
        }

        if (in_array('endereco', $mostrar)) {
            $endereco = cchla_get_contato_info('endereco');
            if ($endereco) {
                echo '<div class="flex items-start gap-3">';
                echo '<i class="fa-solid fa-location-dot text-blue-600 mt-1"></i>';
                echo '<address class="text-sm not-italic">' . esc_html($endereco) . '</address>';
                echo '</div>';
            }
        }

        if (in_array('horario', $mostrar)) {
            $horario = cchla_get_contato_info('horario');
            if ($horario) {
                echo '<div class="flex items-center gap-3">';
                echo '<i class="fa-solid fa-clock text-blue-600"></i>';
                echo '<span class="text-sm">' . esc_html($horario) . '</span>';
                echo '</div>';
            }
        }

        echo '</div>';

        // Redes sociais (se ativado)
        if (isset($instance['mostrar_redes']) && $instance['mostrar_redes']) {
            echo '<div class="mt-4 pt-4 border-t">';
            cchla_display_redes_sociais('flex gap-2', 'w-8 h-8 text-xs');
            echo '</div>';
        }

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : __('Contato', 'cchla-ufrn');
        $mostrar = isset($instance['mostrar']) ? $instance['mostrar'] : array('telefone', 'email', 'endereco');
        $mostrar_redes = isset($instance['mostrar_redes']) ? (bool) $instance['mostrar_redes'] : true;
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
            <strong><?php esc_html_e('Mostrar:', 'cchla-ufrn'); ?></strong><br>

            <label>
                <input type="checkbox"
                    name="<?php echo esc_attr($this->get_field_name('mostrar')); ?>[]"
                    value="telefone"
                    <?php checked(in_array('telefone', $mostrar)); ?>>
                <?php esc_html_e('Telefone', 'cchla-ufrn'); ?>
            </label><br>

            <label>
                <input type="checkbox"
                    name="<?php echo esc_attr($this->get_field_name('mostrar')); ?>[]"
                    value="email"
                    <?php checked(in_array('email', $mostrar)); ?>>
                <?php esc_html_e('Email', 'cchla-ufrn'); ?>
            </label><br>

            <label>
                <input type="checkbox"
                    name="<?php echo esc_attr($this->get_field_name('mostrar')); ?>[]"
                    value="endereco"
                    <?php checked(in_array('endereco', $mostrar)); ?>>
                <?php esc_html_e('Endereço', 'cchla-ufrn'); ?>
            </label><br>

            <label>
                <input type="checkbox"
                    name="<?php echo esc_attr($this->get_field_name('mostrar')); ?>[]"
                    value="horario"
                    <?php checked(in_array('horario', $mostrar)); ?>>
                <?php esc_html_e('Horário', 'cchla-ufrn'); ?>
            </label>
        </p>

        <p>
            <label>
                <input type="checkbox"
                    name="<?php echo esc_attr($this->get_field_name('mostrar_redes')); ?>"
                    value="1"
                    <?php checked($mostrar_redes); ?>>
                <?php esc_html_e('Mostrar redes sociais', 'cchla-ufrn'); ?>
            </label>
        </p>
    <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['mostrar'] = isset($new_instance['mostrar']) ? array_map('sanitize_text_field', $new_instance['mostrar']) : array();
        $instance['mostrar_redes'] = isset($new_instance['mostrar_redes']) ? 1 : 0;
        return $instance;
    }
}

function cchla_register_contato_widget()
{
    register_widget('CCHLA_Contato_Widget');
}
add_action('widgets_init', 'cchla_register_contato_widget');
/**
 * Registra menus do rodapé
 */
function cchla_register_footer_menus()
{
    register_nav_menus(array(
        'footer-institucional' => __('Rodapé - Institucional', 'cchla-ufrn'),
        'footer-academico' => __('Rodapé - Acadêmico', 'cchla-ufrn'),
        'footer-imprensa' => __('Rodapé - Imprensa', 'cchla-ufrn'),
    ));
}
add_action('after_setup_theme', 'cchla_register_footer_menus');


/**
 * ============================================
 * CUSTOMIZAÇÃO DO ADMIN - CCHLA
 * ============================================
 */

/**
 * Enfileira estilos e scripts do admin
 */
function cchla_admin_enqueue_scripts($hook)
{
    // CSS do Login
    if ($hook === 'login') {
        wp_enqueue_style(
            'cchla-admin-login',
            get_template_directory_uri() . '/admin/css/admin-login.css',
            array(),
            filemtime(get_template_directory() . '/admin/css/admin-login.css')
        );
    }

    // CSS do Dashboard
    wp_enqueue_style(
        'cchla-admin-dashboard',
        get_template_directory_uri() . '/admin/css/admin-dashboard.css',
        array(),
        filemtime(get_template_directory() . '/admin/css/admin-dashboard.css')
    );

    // JavaScript Customizado
    wp_enqueue_script(
        'cchla-admin-custom',
        get_template_directory_uri() . '/admin/js/admin-custom.js',
        array('jquery', 'wp-util'),
        filemtime(get_template_directory() . '/admin/js/admin-custom.js'),
        true
    );

    // Passa variáveis para o JavaScript
    wp_localize_script('cchla-admin-custom', 'cchlaAdmin', array(
        'themeUrl' => get_template_directory_uri(),
        'adminUrl' => admin_url(),
        'siteUrl' => home_url(),
        'showWelcome' => current_user_can('manage_options'),
        'nonce' => wp_create_nonce('cchla-admin-nonce'),
    ));

    // Enfileira Media Uploader quando necessário
    if (in_array($hook, array('post.php', 'post-new.php'))) {
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'cchla_admin_enqueue_scripts');
add_action('login_enqueue_scripts', 'cchla_admin_enqueue_scripts');

/**
 * Customiza a URL do logo de login
 */
function cchla_login_logo_url()
{
    return home_url();
}
add_filter('login_headerurl', 'cchla_login_logo_url');

/**
 * Customiza o título do logo de login
 */
function cchla_login_logo_url_title()
{
    return get_bloginfo('name') . ' - ' . get_bloginfo('description');
}
add_filter('login_headertext', 'cchla_login_logo_url_title');

/**
 * Enfileira estilos e scripts do login
 */
function cchla_login_enqueue_scripts()
{
    // CSS do Login
    wp_enqueue_style(
        'cchla-admin-login',
        get_template_directory_uri() . '/admin/css/admin-login.css',
        array(),
        filemtime(get_template_directory() . '/admin/css/admin-login.css')
    );

    // JavaScript do Login
    wp_enqueue_script(
        'cchla-admin-login-js',
        get_template_directory_uri() . '/admin/js/admin-login.js',
        array(),
        filemtime(get_template_directory() . '/admin/js/admin-login.js'),
        true
    );
}
add_action('login_enqueue_scripts', 'cchla_login_enqueue_scripts');

/**
 * Customiza a mensagem de login
 */
function cchla_login_message($message)
{
    // Remove mensagem padrão se estiver vazia
    if (strpos($message, 'log in') !== false || strpos($message, 'register') !== false) {
        return '';
    }
    return $message;
}
add_filter('login_message', 'cchla_login_message');

/**
 * Adiciona classes personalizadas ao body do login
 */
function cchla_login_body_class($classes)
{
    $classes[] = 'cchla-login';
    $classes[] = 'cchla-theme';
    return $classes;
}
add_filter('login_body_class', 'cchla_login_body_class');

/**
 * Remove a mensagem "Lembrar-me por 2 semanas"
 */
function cchla_customize_login_text($text)
{
    if ($text === 'Lembrar-me') {
        return 'Manter-me conectado';
    }
    return $text;
}
add_filter('gettext', 'cchla_customize_login_text', 20, 3);

/**
 * Customiza o rodapé do admin
 */
function cchla_admin_footer_text()
{
    $text = sprintf(
        __('Desenvolvido com %s pela equipe CCHLA', 'cchla-ufrn'),
        '<span style="color: #dc3232;">❤</span>'
    );
    return $text;
}
add_filter('admin_footer_text', 'cchla_admin_footer_text');

/**
 * Customiza a versão do WordPress no rodapé
 */
function cchla_admin_footer_version()
{
    return sprintf(
        __('CCHLA v%s', 'cchla-ufrn'),
        wp_get_theme()->get('Version')
    );
}
add_filter('update_footer', 'cchla_admin_footer_version', 11);

/**
 * Remove itens desnecessários do menu admin
 */
function cchla_remove_admin_menu_items()
{
    // Para não administradores
    if (!current_user_can('manage_options')) {
        remove_menu_page('tools.php');        // Ferramentas
        remove_menu_page('edit-comments.php'); // Comentários (se não usar)
    }
}
add_action('admin_menu', 'cchla_remove_admin_menu_items', 999);

/**
 * Personaliza o Dashboard Welcome Panel
 */
function cchla_custom_dashboard_welcome_panel()
{
    // Esconde o padrão
    remove_action('welcome_panel', 'wp_welcome_panel');
}
add_action('load-index.php', 'cchla_custom_dashboard_welcome_panel');

/**
 * Adiciona widgets personalizados ao dashboard
 */
function cchla_add_dashboard_widgets()
{
    wp_add_dashboard_widget(
        'cchla_quick_links',
        '🚀 Acesso Rápido - CCHLA',
        'cchla_dashboard_quick_links_widget'
    );

    wp_add_dashboard_widget(
        'cchla_stats',
        '📊 Estatísticas do Site',
        'cchla_dashboard_stats_widget'
    );
}
add_action('wp_dashboard_setup', 'cchla_add_dashboard_widgets');

/**
 * Widget de Links Rápidos
 */
function cchla_dashboard_quick_links_widget()
{
    ?>
    <div class="cchla-quick-links" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
        <a href="<?php echo admin_url('post-new.php?post_type=noticias'); ?>" class="button button-primary" style="padding: 15px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
            <span style="font-size: 24px;">📰</span>
            <span>Nova Notícia</span>
        </a>

        <a href="<?php echo admin_url('post-new.php?post_type=publicacoes'); ?>" class="button button-primary" style="padding: 15px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
            <span style="font-size: 24px;">📚</span>
            <span>Nova Publicação</span>
        </a>

        <a href="<?php echo admin_url('post-new.php?post_type=especiais'); ?>" class="button button-primary" style="padding: 15px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
            <span style="font-size: 24px;">🎬</span>
            <span>Novo Especial</span>
        </a>

        <a href="<?php echo admin_url('post-new.php?post_type=servicos'); ?>" class="button button-primary" style="padding: 15px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
            <span style="font-size: 24px;">💼</span>
            <span>Novo Serviço</span>
        </a>

        <a href="<?php echo admin_url('customize.php'); ?>" class="button button-secondary" style="padding: 15px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
            <span style="font-size: 24px;">🎨</span>
            <span>Personalizar</span>
        </a>

        <a href="<?php echo home_url(); ?>" target="_blank" class="button button-secondary" style="padding: 15px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
            <span style="font-size: 24px;">🌐</span>
            <span>Ver Site</span>
        </a>
    </div>
<?php
}

/**
 * Widget de Estatísticas
 */
function cchla_dashboard_stats_widget()
{
    // Função auxiliar para contar posts com segurança
    $get_post_count = function ($post_type) {
        if (!post_type_exists($post_type)) {
            return 0;
        }

        $count = wp_count_posts($post_type);
        return isset($count->publish) ? (int) $count->publish : 0;
    };

    $noticias_count = $get_post_count('noticias');
    $publicacoes_count = $get_post_count('publicacoes');
    $especiais_count = $get_post_count('especiais');
    $servicos_count = $get_post_count('servicos');

?>
    <div class="cchla-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px;">
        <div style="background: linear-gradient(135deg, #2E3CB9, #183AB3); color: white; padding: 20px; border-radius: 8px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold;"><?php echo $noticias_count; ?></div>
            <div style="font-size: 14px; opacity: 0.9;">Notícias</div>
        </div>

        <div style="background: linear-gradient(135deg, #00a32a, #008a20); color: white; padding: 20px; border-radius: 8px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold;"><?php echo $publicacoes_count; ?></div>
            <div style="font-size: 14px; opacity: 0.9;">Publicações</div>
        </div>

        <div style="background: linear-gradient(135deg, #dc3232, #a02222); color: white; padding: 20px; border-radius: 8px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold;"><?php echo $especiais_count; ?></div>
            <div style="font-size: 14px; opacity: 0.9;">Especiais</div>
        </div>

        <div style="background: linear-gradient(135deg, #f0b849, #dda230); color: white; padding: 20px; border-radius: 8px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold;"><?php echo $servicos_count; ?></div>
            <div style="font-size: 14px; opacity: 0.9;">Serviços</div>
        </div>
    </div>
<?php
}

/**
 * Remove widgets desnecessários do dashboard
 */
function cchla_remove_dashboard_widgets()
{
    // Para não administradores
    if (!current_user_can('manage_options')) {
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        remove_meta_box('dashboard_secondary', 'dashboard', 'side');
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
        remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
    }
}
add_action('wp_dashboard_setup', 'cchla_remove_dashboard_widgets');

/**
 * Adiciona favicon customizado no admin
 */
function cchla_admin_favicon()
{
    $favicon = get_template_directory_uri() . '/assets/img/favicon.ico';
    echo '<link rel="shortcut icon" href="' . esc_url($favicon) . '" />';
}
add_action('admin_head', 'cchla_admin_favicon');
add_action('login_head', 'cchla_admin_favicon');

/**
 * Adiciona classes CSS personalizadas no body do admin
 */
function cchla_admin_body_class($classes)
{
    $classes .= ' cchla-admin';

    // Adiciona classe baseada no papel do usuário
    $user = wp_get_current_user();
    if (!empty($user->roles)) {
        $classes .= ' role-' . $user->roles[0];
    }

    return $classes;
}
add_filter('admin_body_class', 'cchla_admin_body_class');

/**
 * Customiza o editor TinyMCE
 */
function cchla_customize_tinymce($settings)
{
    $settings['content_css'] = get_template_directory_uri() . '/admin/css/editor-style.css';
    return $settings;
}
add_filter('tiny_mce_before_init', 'cchla_customize_tinymce');

/**
 * ============================================
 * CUSTOMIZAÇÕES DO ADMIN BAR
 * ============================================
 */

/**
 * Personaliza a saudação do admin bar
 */
function cchla_replace_howdy($wp_admin_bar)
{
    // Validação do objeto WP_Admin_Bar
    if (!is_object($wp_admin_bar) || !method_exists($wp_admin_bar, 'get_node')) {
        return;
    }

    // Obtém o nó my-account
    $my_account = $wp_admin_bar->get_node('my-account');

    // Validações completas
    if (!is_object($my_account)) {
        return;
    }

    if (!property_exists($my_account, 'title')) {
        return;
    }

    if (empty($my_account->title)) {
        return;
    }

    // Lista de saudações para substituir
    $replacements = array(
        'Howdy,' => 'Olá,',
        'Howdy' => 'Olá',
        'Hi,' => 'Olá,',
    );

    // Aplica as substituições
    $new_title = str_replace(
        array_keys($replacements),
        array_values($replacements),
        $my_account->title
    );

    // Atualiza apenas se houve mudança
    if ($new_title !== $my_account->title) {
        $wp_admin_bar->add_node(array(
            'id' => 'my-account',
            'title' => $new_title,
        ));
    }
}
add_filter('admin_bar_menu', 'cchla_replace_howdy', 25);

/**
 * Adiciona link para o site no admin bar
 */
function cchla_admin_bar_site_link($wp_admin_bar)
{
    // Validação
    if (!is_object($wp_admin_bar) || !method_exists($wp_admin_bar, 'add_node')) {
        return;
    }

    $args = array(
        'id' => 'cchla-view-site',
        'title' => '<span class="ab-icon dashicons dashicons-external"></span><span class="ab-label">Ver Site</span>',
        'href' => home_url(),
        'meta' => array(
            'target' => '_blank',
            'title' => 'Abrir o site em nova aba',
            'class' => 'cchla-view-site-link'
        )
    );

    $wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'cchla_admin_bar_site_link', 100);

/**
 *```

 *## **7. Criar Imagens Necessárias**

 *Crie ou adicione as seguintes imagens na pasta `/admin/images/`:

 *1. **logo-login.svg** - Logo do CCHLA para a tela de login
 *2. **logo-admin.svg** - Logo menor para o menu lateral
 *3. **bg-login.png** - Textura de fundo (opcional)
 *4. **favicon.ico** - Favicon do CCHLA

 *## **8. Como Aplicar em Outros Projetos**

 *### **Passo 1: Copiar arquivos**
 *```
 * /admin/
 *├── css/
 *│   ├── admin-variables.css
 *│   ├── admin-login.css
 *│   └── admin-dashboard.css
 *├── js/
 *│   └── admin-custom.js
 *└── images/
 *
 **/
