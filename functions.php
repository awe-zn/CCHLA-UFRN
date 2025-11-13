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
    // Só executa no front-end e na query principal
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // ===== POSTS PADRÃO (Notícias com Categorias) =====
    if (is_category() || is_tag() || (is_home() && !is_front_page())) {
        // Não modifica nada - deixa o WordPress trabalhar normalmente
        return;
    }

    // ===== PUBLICAÇÕES =====
    if (is_post_type_archive('publicacoes') || is_tax('tipo_publicacao')) {

        // Filtro de taxonomia via URL
        if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
            $query->set('tax_query', array(
                array(
                    'taxonomy' => 'tipo_publicacao',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($_GET['tipo']),
                ),
            ));
        }

        // Filtro de ano
        if (isset($_GET['year']) && !empty($_GET['year'])) {
            $year = absint($_GET['year']);
            $query->set('meta_query', array(
                array(
                    'key' => '_publicacao_ano',
                    'value' => $year,
                    'compare' => '=',
                ),
            ));
        }

        // Ordenação
        if (isset($_GET['orderby']) && isset($_GET['order'])) {
            $orderby = sanitize_text_field($_GET['orderby']);
            $order = strtoupper(sanitize_text_field($_GET['order']));

            if ($orderby === 'title') {
                $query->set('orderby', 'title');
                $query->set('order', $order);
            } elseif ($orderby === 'date') {
                $query->set('orderby', 'date');
                $query->set('order', $order);
            } elseif ($orderby === 'year') {
                $query->set('meta_key', '_publicacao_ano');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', $order);
            }
        }

        // Posts por página
        $query->set('posts_per_page', 12);
    }

    // ===== ESPECIAIS =====
    elseif (is_post_type_archive('especiais') || is_tax('categoria_especial')) {

        // Filtro de categoria via URL
        if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
            $query->set('tax_query', array(
                array(
                    'taxonomy' => 'categoria_especial',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($_GET['categoria']),
                ),
            ));
        }

        // Filtro de destaque
        if (isset($_GET['destaque']) && $_GET['destaque'] === '1') {
            $query->set('meta_query', array(
                array(
                    'key' => '_especial_destaque_home',
                    'value' => '1',
                ),
            ));
        }

        // Ordenação (menu_order por padrão)
        if (!isset($_GET['orderby'])) {
            $query->set('orderby', 'menu_order');
            $query->set('order', 'ASC');
        }

        $query->set('posts_per_page', 12);
    }

    // ===== SERVIÇOS =====
    elseif (is_post_type_archive('servicos') || is_tax('categoria_servico')) {

        // Filtro de categoria via URL
        if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
            $query->set('tax_query', array(
                array(
                    'taxonomy' => 'categoria_servico',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($_GET['categoria']),
                ),
            ));
        }

        $query->set('posts_per_page', 12);
    }

    // ===== ACESSO RÁPIDO =====
    elseif (is_post_type_archive('acesso_rapido') || is_tax('categoria_acesso')) {

        // Filtro de categoria via URL
        if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
            $query->set('tax_query', array(
                array(
                    'taxonomy' => 'categoria_acesso',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($_GET['categoria']),
                ),
            ));
        }

        // Ordenação por ordem personalizada
        $query->set('meta_key', '_acesso_ordem');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'ASC');

        $query->set('posts_per_page', 15);
    }
}
add_action('pre_get_posts', 'cchla_archive_query_modifications');

/**
 * Adiciona body class para identificar tipo de arquivo
 */
function cchla_archive_body_class($classes)
{
    // A função body_class sempre espera um array de strings
    if (!is_array($classes)) {
        // Se por algum motivo o valor inicial não for um array, retorna o valor original.
        return $classes;
    }

    if (is_post_type_archive()) {
        $post_type = get_query_var('post_type');
        // Garantindo que $post_type é string e não está vazio
        if (is_string($post_type) && !empty($post_type)) {
            $classes[] = 'archive-' . $post_type;
        }
    }

    if (is_tax()) {
        $term = get_queried_object();
        if ($term && is_object($term)) {
            $classes[] = 'taxonomy-' . $term->taxonomy;
            $classes[] = 'term-' . $term->slug;
        }
    }

    // Se is_filtered() retornar um array por engano, ele deve ser corrigido ANTES de ser usado.
    // Presumindo que is_filtered() está correta e retorna booleano:
    if (is_filtered()) {
        $classes[] = 'has-filters';
    }

    // A chave final para resolver o problema de forma geral é garantir que todas as classes
    // sejam strings. Se houver algum Array não resolvido dentro do $classes,
    // o PHP tentará convertê-lo e falhará, gerando o Warning.

    // Filtramos o array para remover qualquer elemento que NÃO seja uma string válida
    $classes = array_filter($classes, 'is_string');

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
    if (is_admin() || !$query->is_search() || !$query->is_main_query()) {
        return;
    }

    // Filtrar por post type específico
    if (isset($_GET['post_type']) && !empty($_GET['post_type'])) {
        $post_type = sanitize_text_field($_GET['post_type']);

        // Valida se o post type existe
        if (post_type_exists($post_type)) {
            $query->set('post_type', $post_type);
        }
    }

    // Filtrar por categoria (apenas para posts padrão)
    if (isset($_GET['cat']) && !empty($_GET['cat']) && !isset($_GET['post_type'])) {
        $query->set('cat', absint($_GET['cat']));
    }

    // Filtrar por tag (apenas para posts padrão)
    if (isset($_GET['tag']) && !empty($_GET['tag']) && !isset($_GET['post_type'])) {
        $query->set('tag', sanitize_text_field($_GET['tag']));
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
        <a href="<?php echo admin_url('post-new.php?post_type=post'); ?>" class="button button-primary" style="color: #1d2e7a; background-color: #e5edff; padding: 15px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
            <span style="font-size: 24px;"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#140b68ff" viewBox="0 0 256 256">
                    <path d="M216,48H40a8,8,0,0,0-8,8V216l32-16,32,16,32-16,32,16,32-16,32,16V56A8,8,0,0,0,216,48ZM112,160H64V96h48Z" opacity="0.2"></path>
                    <path d="M216,40H40A16,16,0,0,0,24,56V216a8,8,0,0,0,11.58,7.15L64,208.94l28.42,14.21a8,8,0,0,0,7.16,0L128,208.94l28.42,14.21a8,8,0,0,0,7.16,0L192,208.94l28.42,14.21A8,8,0,0,0,232,216V56A16,16,0,0,0,216,40Zm0,163.06-20.42-10.22a8,8,0,0,0-7.16,0L160,207.06l-28.42-14.22a8,8,0,0,0-7.16,0L96,207.06,67.58,192.84a8,8,0,0,0-7.16,0L40,203.06V56H216ZM136,112a8,8,0,0,1,8-8h48a8,8,0,0,1,0,16H144A8,8,0,0,1,136,112Zm0,32a8,8,0,0,1,8-8h48a8,8,0,0,1,0,16H144A8,8,0,0,1,136,144ZM64,168h48a8,8,0,0,0,8-8V96a8,8,0,0,0-8-8H64a8,8,0,0,0-8,8v64A8,8,0,0,0,64,168Zm8-64h32v48H72Z"></path>
                </svg></span>
            <span>Nova Notícia</span>
        </a>

        <a href="<?php echo admin_url('post-new.php?post_type=publicacoes'); ?>" class="button button-primary" style="color: #1d2e7a; background-color: #e5edff; padding: 15px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
            <span style="font-size: 24px;"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#140b68ff" viewBox="0 0 256 256">
                    <path d="M48,72h64V184H48ZM190.64,38.39a8,8,0,0,0-9.5-6.21l-46.81,10a8.07,8.07,0,0,0-6.15,9.57L139.79,107l62.46-13.42Z" opacity="0.2"></path>
                    <path d="M231.65,194.55,198.46,36.75a16,16,0,0,0-19-12.39L132.65,34.42a16.08,16.08,0,0,0-12.3,19l33.19,157.8A16,16,0,0,0,169.16,224a16.25,16.25,0,0,0,3.38-.36l46.81-10.06A16.09,16.09,0,0,0,231.65,194.55ZM136,50.15c0-.06,0-.09,0-.09l46.8-10,3.33,15.87L139.33,66Zm6.62,31.47,46.82-10.05,3.34,15.9L146,97.53Zm6.64,31.57,46.82-10.06,13.3,63.24-46.82,10.06ZM216,197.94l-46.8,10-3.33-15.87L212.67,182,216,197.85C216,197.91,216,197.94,216,197.94ZM104,32H56A16,16,0,0,0,40,48V208a16,16,0,0,0,16,16h48a16,16,0,0,0,16-16V48A16,16,0,0,0,104,32ZM56,48h48V64H56Zm0,32h48v96H56Zm48,128H56V192h48v16Z"></path>
                </svg></span>
            <span>Nova Publicação</span>
        </a>

        <a href="<?php echo admin_url('post-new.php?post_type=especiais'); ?>" class="button button-primary" style="color: #1d2e7a; background-color: #e5edff; padding: 15px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
            <span style="font-size: 24px;"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#140b68ff" viewBox="0 0 256 256">
                    <path d="M226.59,71.53a16,16,0,0,0-9.63-11C183.48,47.65,128,48,128,48s-55.48-.35-89,12.58a16,16,0,0,0-9.63,11C27.07,80.54,24,98.09,24,128s3.07,47.46,5.41,56.47A16,16,0,0,0,39,195.42C72.52,208.35,128,208,128,208s55.48.35,89-12.58a16,16,0,0,0,9.63-10.95c2.34-9,5.41-26.56,5.41-56.47S228.93,80.54,226.59,71.53ZM112,160V96l48,32Z" opacity="0.2"></path>
                    <path d="M164.44,121.34l-48-32A8,8,0,0,0,104,96v64a8,8,0,0,0,12.44,6.66l48-32a8,8,0,0,0,0-13.32ZM120,145.05V111l25.58,17ZM234.33,69.52a24,24,0,0,0-14.49-16.4C185.56,39.88,131,40,128,40s-57.56-.12-91.84,13.12a24,24,0,0,0-14.49,16.4C19.08,79.5,16,97.74,16,128s3.08,48.5,5.67,58.48a24,24,0,0,0,14.49,16.41C69,215.56,120.4,216,127.34,216h1.32c6.94,0,58.37-.44,91.18-13.11a24,24,0,0,0,14.49-16.41c2.59-10,5.67-28.22,5.67-58.48S236.92,79.5,234.33,69.52Zm-15.49,113a8,8,0,0,1-4.77,5.49c-31.65,12.22-85.48,12-86.12,12s-54.37.18-86-12a8,8,0,0,1-4.77-5.49C34.8,173.39,32,156.57,32,128s2.8-45.39,5.16-54.47A8,8,0,0,1,41.93,68C73.58,55.82,127.4,56,128.05,56s54.37-.18,86,12a8,8,0,0,1,4.77,5.49C221.2,82.61,224,99.43,224,128S221.2,173.39,218.84,182.47Z"></path>
                </svg></span>
            <span>Novo Especial</span>
        </a>

        <a href="<?php echo admin_url('post-new.php?post_type=servicos'); ?>" class="button button-primary" style="color: #1d2e7a; background-color: #e5edff; padding: 15px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
            <span style="font-size: 24px;"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#140b68ff" viewBox="0 0 256 256">
                    <path d="M216,48V208a8,8,0,0,1-8,8H64L40,192V48a8,8,0,0,1,8-8H208A8,8,0,0,1,216,48Z" opacity="0.2"></path>
                    <path d="M224,48V208a16,16,0,0,1-16,16H136a8,8,0,0,1,0-16h72V48H48v96a8,8,0,0,1-16,0V48A16,16,0,0,1,48,32H208A16,16,0,0,1,224,48ZM125.66,154.34a8,8,0,0,0-11.32,0L64,204.69,45.66,186.34a8,8,0,0,0-11.32,11.32l24,24a8,8,0,0,0,11.32,0l56-56A8,8,0,0,0,125.66,154.34Z"></path>
                </svg></span>
            <span>Novo Serviço</span>
        </a>

        <a href="<?php echo admin_url('customize.php'); ?>" class="button button-secondary" style="color: #1d2e7a; background-color: #e5edff; padding: 15px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
            <span style="font-size: 24px;"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256">
                    <path d="M135.88,43.11l-25,143.14a35.71,35.71,0,0,1-41.34,29.2h0a36,36,0,0,1-28.95-41.71l25-143.13a8,8,0,0,1,9.19-6.49l54.67,9.73A8,8,0,0,1,135.88,43.11Z" opacity="0.2"></path>
                    <path d="M88,180a12,12,0,1,1-12-12A12,12,0,0,1,88,180Zm152-23.81V208a16,16,0,0,1-16,16H76a46.36,46.36,0,0,1-7.94-.68,44,44,0,0,1-35.43-50.95l25-143.13a15.94,15.94,0,0,1,18.47-13L130.84,26a16,16,0,0,1,12.92,18.52l-12.08,69L199.49,89a16,16,0,0,1,20.45,9.52L239,150.69A18.35,18.35,0,0,1,240,156.19ZM103,184.87,128,41.74,73.46,32l-25,143.1A28,28,0,0,0,70.9,207.57,27.29,27.29,0,0,0,91.46,203,27.84,27.84,0,0,0,103,184.87ZM116.78,195,224,156.11,204.92,104,128.5,131.7l-9.78,55.92A44.63,44.63,0,0,1,116.78,195ZM224,173.12,127.74,208H224Z"></path>
                </svg></span>
            <span>Personalizar</span>
        </a>

        <a href="<?php echo home_url(); ?>" target="_blank" class="button button-secondary" style="color: #1d2e7a; background-color: #e5edff; padding: 15px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
            <span style="font-size: 24px;"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256">
                    <path d="M215,168.71a96.42,96.42,0,0,1-30.54,37l-9.36-9.37a8,8,0,0,0-3.63-2.09L150,188.59a8,8,0,0,1-5.88-8.9l2.38-16.2a8,8,0,0,1,4.84-6.22l30.46-12.66a8,8,0,0,1,8.47,1.49ZM159.89,105,182.06,79.2A8,8,0,0,0,184,74V50A96,96,0,0,0,50.49,184.65l9.92-6.52A8,8,0,0,0,64,171.49l.21-36.23a8.06,8.06,0,0,1,1.35-4.41l20.94-31.3a8,8,0,0,1,11.34-2l19.81,13a8.06,8.06,0,0,0,5.77,1.45l31.46-4.26A8,8,0,0,0,159.89,105Z" opacity="0.2"></path>
                    <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,16a87.5,87.5,0,0,1,48,14.28V74L153.83,99.74,122.36,104l-.31-.22L102.38,90.92A16,16,0,0,0,79.87,95.1L58.93,126.4a16,16,0,0,0-2.7,8.81L56,171.44l-3.27,2.15A88,88,0,0,1,128,40ZM62.29,186.47l2.52-1.65A16,16,0,0,0,72,171.53l.21-36.23L93.17,104a3.62,3.62,0,0,0,.32.22l19.67,12.87a15.94,15.94,0,0,0,11.35,2.77L156,115.59a16,16,0,0,0,10-5.41l22.17-25.76A16,16,0,0,0,192,74V67.67A87.87,87.87,0,0,1,211.77,155l-16.14-14.76a16,16,0,0,0-16.93-3l-30.46,12.65a16.08,16.08,0,0,0-9.68,12.45l-2.39,16.19a16,16,0,0,0,11.77,17.81L169.4,202l2.36,2.37A87.88,87.88,0,0,1,62.29,186.47ZM185,195l-4.3-4.31a16,16,0,0,0-7.26-4.18L152,180.85l2.39-16.19L184.84,152,205,170.48A88.43,88.43,0,0,1,185,195Z"></path>
                </svg></span>
            <span>Ver Site</span>
        </a>
    </div>
    <?php
}

/**
 * Widget de Estatísticas - Versão Corrigida
 */
function cchla_dashboard_stats_widget()
{
    // Configuração dos cards
    $stats_config = array(
        'acesso_rapido' => array(
            'label' => 'Links rápidos',
            'gradient' => 'linear-gradient(135deg, #2E3CB9, #183AB3)',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#140b68ff" viewBox="0 0 256 256"><path d="M184,80V208a8,8,0,0,1-8,8H48a8,8,0,0,1-8-8V80a8,8,0,0,1,8-8H176A8,8,0,0,1,184,80Z" opacity="0.2"></path><path d="M224,104a8,8,0,0,1-16,0V59.32l-66.33,66.34a8,8,0,0,1-11.32-11.32L196.68,48H152a8,8,0,0,1,0-16h64a8,8,0,0,1,8,8Zm-40,24a8,8,0,0,0-8,8v72H48V80h72a8,8,0,0,0,0-16H48A16,16,0,0,0,32,80V208a16,16,0,0,0,16,16H176a16,16,0,0,0,16-16V136A8,8,0,0,0,184,128Z"></path></svg>'
        ),
        'publicacoes' => array(
            'label' => 'Publicações',
            'gradient' => 'linear-gradient(135deg, #00a32a, #008a20)',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#1b4e27ff" viewBox="0 0 256 256"><path d="M48,72h64V184H48ZM190.64,38.39a8,8,0,0,0-9.5-6.21l-46.81,10a8.07,8.07,0,0,0-6.15,9.57L139.79,107l62.46-13.42Z" opacity="0.2"></path><path d="M231.65,194.55,198.46,36.75a16,16,0,0,0-19-12.39L132.65,34.42a16.08,16.08,0,0,0-12.3,19l33.19,157.8A16,16,0,0,0,169.16,224a16.25,16.25,0,0,0,3.38-.36l46.81-10.06A16.09,16.09,0,0,0,231.65,194.55ZM136,50.15c0-.06,0-.09,0-.09l46.8-10,3.33,15.87L139.33,66Zm6.62,31.47,46.82-10.05,3.34,15.9L146,97.53Zm6.64,31.57,46.82-10.06,13.3,63.24-46.82,10.06ZM216,197.94l-46.8,10-3.33-15.87L212.67,182,216,197.85C216,197.91,216,197.94,216,197.94ZM104,32H56A16,16,0,0,0,40,48V208a16,16,0,0,0,16,16h48a16,16,0,0,0,16-16V48A16,16,0,0,0,104,32ZM56,48h48V64H56Zm0,32h48v96H56Zm48,128H56V192h48v16Z"></path></svg>'
        ),
        'especiais' => array(
            'label' => 'Especiais',
            'gradient' => 'linear-gradient(135deg, #dc3232, #a02222)',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#611111ff" viewBox="0 0 256 256"><path d="M226.59,71.53a16,16,0,0,0-9.63-11C183.48,47.65,128,48,128,48s-55.48-.35-89,12.58a16,16,0,0,0-9.63,11C27.07,80.54,24,98.09,24,128s3.07,47.46,5.41,56.47A16,16,0,0,0,39,195.42C72.52,208.35,128,208,128,208s55.48.35,89-12.58a16,16,0,0,0,9.63-10.95c2.34-9,5.41-26.56,5.41-56.47S228.93,80.54,226.59,71.53ZM112,160V96l48,32Z" opacity="0.2"></path><path d="M164.44,121.34l-48-32A8,8,0,0,0,104,96v64a8,8,0,0,0,12.44,6.66l48-32a8,8,0,0,0,0-13.32ZM120,145.05V111l25.58,17ZM234.33,69.52a24,24,0,0,0-14.49-16.4C185.56,39.88,131,40,128,40s-57.56-.12-91.84,13.12a24,24,0,0,0-14.49,16.4C19.08,79.5,16,97.74,16,128s3.08,48.5,5.67,58.48a24,24,0,0,0,14.49,16.41C69,215.56,120.4,216,127.34,216h1.32c6.94,0,58.37-.44,91.18-13.11a24,24,0,0,0,14.49-16.41c2.59-10,5.67-28.22,5.67-58.48S236.92,79.5,234.33,69.52Zm-15.49,113a8,8,0,0,1-4.77,5.49c-31.65,12.22-85.48,12-86.12,12s-54.37.18-86-12a8,8,0,0,1-4.77-5.49C34.8,173.39,32,156.57,32,128s2.8-45.39,5.16-54.47A8,8,0,0,1,41.93,68C73.58,55.82,127.4,56,128.05,56s54.37-.18,86,12a8,8,0,0,1,4.77,5.49C221.2,82.61,224,99.43,224,128S221.2,173.39,218.84,182.47Z"></path></svg>'
        ),
        'servicos' => array(
            'label' => 'Serviços',
            'gradient' => 'linear-gradient(135deg, #f0b849, #dda230)',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#775717ff" viewBox="0 0 256 256"><path d="M216,48V208a8,8,0,0,1-8,8H64L40,192V48a8,8,0,0,1,8-8H208A8,8,0,0,1,216,48Z" opacity="0.2"></path><path d="M224,48V208a16,16,0,0,1-16,16H136a8,8,0,0,1,0-16h72V48H48v96a8,8,0,0,1-16,0V48A16,16,0,0,1,48,32H208A16,16,0,0,1,224,48ZM125.66,154.34a8,8,0,0,0-11.32,0L64,204.69,45.66,186.34a8,8,0,0,0-11.32,11.32l24,24a8,8,0,0,0,11.32,0l56-56A8,8,0,0,0,125.66,154.34Z"></path></svg>'
        ),
    );

    echo '<div class="cchla-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px;">';

    $total_posts = 0;

    foreach ($stats_config as $post_type => $config) {
        // Verifica se o CPT existe
        $exists = post_type_exists($post_type);

        // Obtém contagem
        $count = 0;
        if ($exists) {
            $count_obj = wp_count_posts($post_type);
            if ($count_obj && isset($count_obj->publish)) {
                $count = (int) $count_obj->publish;
            }
        }

        $total_posts += $count;

        // Define opacidade baseado na existência
        $opacity = $exists ? '1' : '0.5';
        $title = $exists ? '' : ' title="Este tipo de post não está registrado"';

        printf(
            '<div style="background: %s; color: white; padding: 20px; border-radius: 8px; text-align: center; opacity: %s; position: relative; cursor: %s;"%s>
                <div style="font-size: 24px; margin-bottom: 8px;">%s</div>
                <div style="font-size: 32px; font-weight: bold;">%d</div>
                <div style="font-size: 14px; opacity: 0.9;">%s</div>
                %s
            </div>',
            esc_attr($config['gradient']),
            esc_attr($opacity),
            $exists ? 'default' : 'help',
            $title,
            $config['icon'],
            $count,
            esc_html($config['label']),
            !$exists ? '<div style="position: absolute; top: 5px; right: 8px; font-size: 18px;" title="CPT não registrado">⚠️</div>' : ''
        );
    }

    echo '</div>';

    // Mensagem se nenhum post existe
    if ($total_posts === 0) {
        echo '<div style="margin-top: 15px; padding: 15px; background: #f0f6fc; border-radius: 8px; text-align: center; color: #2271b1; border-left: 4px solid #2271b1;">';
        echo '<p style="margin: 0;"><strong>💡 Dica:</strong> Comece criando seu primeiro conteúdo usando os botões de acesso rápido acima!</p>';
        echo '</div>';

        // Debug info para admins
        if (current_user_can('manage_options')) {
            echo '<div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 8px; font-size: 12px; color: #856404;">';
            echo '<strong>🔧 Debug (visível apenas para administradores):</strong><br>';
            echo 'Verifique se os Custom Post Types foram registrados corretamente. ';
            echo 'Execute a função de debug acima para ver a lista completa.';
            echo '</div>';
        }
    }
}

/**
 * Garante que todos os CPTs estão registrados
 * Executa na prioridade correta
 */
function cchla_ensure_cpts_registered()
{
    // Lista de funções de registro
    $register_functions = array(
        'cchla_register_noticias_cpt',
        'cchla_register_publicacoes_cpt',
        'cchla_register_especiais_cpt',
        'cchla_register_servicos_cpt',
    );

    foreach ($register_functions as $function) {
        if (function_exists($function)) {
            call_user_func($function);
        }
    }
}
add_action('init', 'cchla_ensure_cpts_registered', 0);

/**
 * Força flush de rewrite rules uma vez
 */
function cchla_force_flush_once()
{
    $flushed = get_option('cchla_flushed_rules_v2');

    if (!$flushed) {
        flush_rewrite_rules();
        update_option('cchla_flushed_rules_v2', true);

        if (current_user_can('manage_options')) {
            add_action('admin_notices', function () {
    ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>✅ Permalinks recarregados!</strong> Os Custom Post Types devem aparecer agora.</p>
                </div>
        <?php
            });
        }
    }
}
add_action('admin_init', 'cchla_force_flush_once');

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



/**
 * ============================================
 * BREADCRUMB - FUNÇÃO PRINCIPAL
 * ============================================
 */

/**
 * Exibe o breadcrumb de navegação
 * 
 * @param array $args Argumentos opcionais
 * @return string|void HTML do breadcrumb ou void se echo = true
 */
function cchla_breadcrumb($args = array())
{
    // Não exibe na home
    if (is_front_page()) {
        return;
    }

    // Configurações padrão
    $defaults = array(
        'home_text' => 'Início',
        'separator' => '›',
        'show_current' => true,
        'echo' => true,
    );

    $args = wp_parse_args($args, $defaults);

    // Se não quiser ecoar, captura o output
    if (!$args['echo']) {
        ob_start();
    }

    // Torna os argumentos disponíveis globalmente para o template
    global $cchla_breadcrumb_args;
    $cchla_breadcrumb_args = $args;

    // Carrega o template part
    get_template_part('parts/extra/template-parts/breadcrumb');

    // Limpa a variável global
    unset($cchla_breadcrumb_args);

    // Retorna o conteúdo se echo = false
    if (!$args['echo']) {
        return ob_get_clean();
    }
}

/**
 * Shortcode para breadcrumb
 * Uso: [breadcrumb] ou [breadcrumb home_text="Home" separator="/"]
 */
function cchla_breadcrumb_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'home_text' => 'Início',
        'separator' => '›',
        'show_current' => 'yes',
    ), $atts);

    // Converte 'yes'/'no' para boolean
    $atts['show_current'] = ($atts['show_current'] === 'yes');

    // Retorna o breadcrumb
    return cchla_breadcrumb(array_merge($atts, array('echo' => false)));
}
add_shortcode('breadcrumb', 'cchla_breadcrumb_shortcode');





/**
 * ============================================
 * SISTEMA DE BUSCA AVANÇADO
 * ============================================
 */

/**
 * Conta resultados de busca por tipo de post
 * 
 * @param string $search_query Termo de busca
 * @return array Contagem por tipo
 */
function cchla_get_search_counts_by_type($search_query)
{
    if (empty($search_query)) {
        return array();
    }

    $post_types = array('post', 'page', 'publicacoes', 'especiais', 'servicos', 'acesso_rapido');
    $counts = array();

    foreach ($post_types as $post_type) {
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            's' => $search_query,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => false,
        );

        $query = new WP_Query($args);
        $counts[$post_type] = $query->found_posts;
        wp_reset_postdata();
    }

    return $counts;
}

/**
 * Modifica a query de busca para incluir todos os post types
 */
function cchla_search_query_modification($query)
{
    if (!is_admin() && $query->is_search() && $query->is_main_query()) {

        // Se filtro de post_type foi aplicado via URL
        if (isset($_GET['post_type']) && !empty($_GET['post_type'])) {
            $post_type = sanitize_text_field($_GET['post_type']);

            if (post_type_exists($post_type)) {
                $query->set('post_type', $post_type);
            }
        } else {
            // Busca em todos os tipos
            $query->set('post_type', array(
                'post',
                'page',
                'publicacoes',
                'especiais',
                'servicos',
                'acesso_rapido'
            ));
        }

        // Posts por página
        $query->set('posts_per_page', 10);
    }
}
add_action('pre_get_posts', 'cchla_search_query_modification');

/**
 * Destaca o termo de busca no conteúdo
 * 
 * @param string $text Texto original
 * @param string $search_term Termo a destacar
 * @return string Texto com termo destacado
 */
function cchla_highlight_search_term($text, $search_term)
{
    if (empty($search_term)) {
        return $text;
    }

    $highlighted = preg_replace(
        '/(' . preg_quote($search_term, '/') . ')/iu',
        '<mark class="bg-yellow-200 font-semibold">$1</mark>',
        $text
    );

    return $highlighted;
}

/**
 * Widget de Busca Avançada
 */
class CCHLA_Advanced_Search_Widget extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'cchla_advanced_search_widget',
            __('CCHLA - Busca Avançada', 'cchla-ufrn'),
            array('description' => __('Busca em todos os tipos de conteúdo', 'cchla-ufrn'))
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        get_search_form();

        // Estatísticas rápidas
        if (!empty($instance['show_stats'])) {
            $stats = array(
                'post' => wp_count_posts('post')->publish,
                'publicacoes' => wp_count_posts('publicacoes')->publish,
                'especiais' => wp_count_posts('especiais')->publish,
            );

            echo '<div class="search-stats mt-4 text-xs text-gray-600">';
            echo '<p>' . sprintf(
                __('Busque em %s notícias, %s publicações e %s especiais', 'cchla-ufrn'),
                number_format_i18n($stats['post']),
                number_format_i18n($stats['publicacoes']),
                number_format_i18n($stats['especiais'])
            ) . '</p>';
            echo '</div>';
        }

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : __('Buscar no Site', 'cchla-ufrn');
        $show_stats = isset($instance['show_stats']) ? (bool) $instance['show_stats'] : false;
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
            <label>
                <input type="checkbox"
                    name="<?php echo esc_attr($this->get_field_name('show_stats')); ?>"
                    value="1"
                    <?php checked($show_stats); ?>>
                <?php esc_html_e('Mostrar estatísticas', 'cchla-ufrn'); ?>
            </label>
        </p>
    <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['show_stats'] = !empty($new_instance['show_stats']);
        return $instance;
    }
}

function cchla_register_advanced_search_widget()
{
    register_widget('CCHLA_Advanced_Search_Widget');
}
add_action('widgets_init', 'cchla_register_advanced_search_widget');

/**
 * Shortcode para formulário de busca
 * Uso: [search_form]
 */
function cchla_search_form_shortcode($atts)
{
    ob_start();
    get_search_form();
    return ob_get_clean();
}
add_shortcode('search_form', 'cchla_search_form_shortcode');


/**
 * AJAX para sugestões de busca
 */
function cchla_search_suggestions()
{
    check_ajax_referer('cchla-search-nonce', 'nonce');

    $term = sanitize_text_field($_POST['term']);

    if (strlen($term) < 3) {
        wp_send_json_error();
    }

    $args = array(
        'post_type' => array('post', 'page', 'publicacoes', 'especiais', 'servicos'),
        'posts_per_page' => 5,
        's' => $term,
    );

    $query = new WP_Query($args);
    $suggestions = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $post_type_obj = get_post_type_object(get_post_type());

            $suggestions[] = array(
                'title' => get_the_title(),
                'url' => get_permalink(),
                'type' => $post_type_obj->labels->singular_name,
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success($suggestions);
}
add_action('wp_ajax_cchla_search_suggestions', 'cchla_search_suggestions');
add_action('wp_ajax_nopriv_cchla_search_suggestions', 'cchla_search_suggestions');

/**
 * Enfileira script de autocomplete
 */
function cchla_enqueue_search_autocomplete()
{
    wp_enqueue_script(
        'cchla-search-autocomplete',
        get_template_directory_uri() . '/assets/scripts/search-autocomplete.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_localize_script('cchla-search-autocomplete', 'cchlaSearch', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('cchla-search-nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'cchla_enqueue_search_autocomplete');



/**
 * ==========================================
 * FUNÇÕES PARA SINGLE POST
 * ==========================================
 */

/**
 * Calcula tempo estimado de leitura
 * 
 * @param string $content Conteúdo do post
 * @return int Minutos de leitura
 */
function cchla_calculate_reading_time($content)
{
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // 200 palavras por minuto

    return max(1, $reading_time); // Mínimo 1 minuto
}

/**
 * Gera keywords meta tag baseado em tags e categorias
 * 
 * @return string Keywords separadas por vírgula
 */
function cchla_get_meta_keywords()
{
    if (!is_single()) {
        return '';
    }

    $keywords = array();

    // Adiciona categorias
    $categories = get_the_category();
    if ($categories) {
        foreach ($categories as $category) {
            $keywords[] = $category->name;
        }
    }

    // Adiciona tags
    $tags = get_the_tags();
    if ($tags) {
        foreach ($tags as $tag) {
            $keywords[] = $tag->name;
        }
    }

    return implode(', ', array_slice($keywords, 0, 10));
}

/**
 * Conta visualizações do post (opcional)
 * 
 * @param int $post_id ID do post
 * @return int Número de visualizações
 */
function cchla_get_post_views($post_id)
{
    $count_key = 'post_views_count';
    $count = get_post_meta($post_id, $count_key, true);

    return $count ? intval($count) : 0;
}

/**
 * Incrementa contador de visualizações
 */
function cchla_set_post_views()
{
    if (is_single()) {
        global $post;
        $post_id = $post->ID;
        $count_key = 'post_views_count';
        $count = get_post_meta($post_id, $count_key, true);

        if ($count == '') {
            delete_post_meta($post_id, $count_key);
            add_post_meta($post_id, $count_key, '1');
        } else {
            $count++;
            update_post_meta($post_id, $count_key, $count);
        }
    }
}
add_action('wp_head', 'cchla_set_post_views');

/**
 * Remove contador de visualizações das queries
 */
function cchla_exclude_post_views($query)
{
    $query->query_vars['update_post_meta_cache'] = false;
}
add_action('pre_get_posts', 'cchla_exclude_post_views');

/**
 * Enfileira CSS de impressão
 */
function cchla_enqueue_print_styles()
{
    wp_enqueue_style(
        'cchla-print-styles',
        get_template_directory_uri() . '/assets/css/print.css',
        array(),
        filemtime(get_template_directory() . '/assets/css/print.css'),
        'print' // ← IMPORTANTE: Carrega apenas para impressão
    );
}
add_action('wp_enqueue_scripts', 'cchla_enqueue_print_styles');


/**
 * ==========================================
 * SISTEMA DE DEPARTAMENTOS E CURSOS - CCHLA
 * ==========================================
 * 
 * Estrutura:
 * - CPT: Departamentos
 * - CPT: Cursos
 * - Taxonomia: Tipo de Curso (Graduação, Pós-Graduação, Extensão)
 * - Taxonomia: Área de Conhecimento
 * - Relacionamento: Departamento → Cursos
 * 
 * @package CCHLA_UFRN
 * @version 1.0.0
 */

/**
 * ==========================================
 * 1. CUSTOM POST TYPE: DEPARTAMENTOS
 * ==========================================
 */
function cchla_register_departamentos_cpt()
{
    $labels = array(
        'name'                  => _x('Departamentos', 'Post Type General Name', 'cchla-ufrn'),
        'singular_name'         => _x('Departamento', 'Post Type Singular Name', 'cchla-ufrn'),
        'menu_name'             => __('Departamentos', 'cchla-ufrn'),
        'name_admin_bar'        => __('Departamento', 'cchla-ufrn'),
        'archives'              => __('Arquivo de Departamentos', 'cchla-ufrn'),
        'attributes'            => __('Atributos do Departamento', 'cchla-ufrn'),
        'parent_item_colon'     => __('Departamento Pai:', 'cchla-ufrn'),
        'all_items'             => __('Todos os Departamentos', 'cchla-ufrn'),
        'add_new_item'          => __('Adicionar Novo Departamento', 'cchla-ufrn'),
        'add_new'               => __('Adicionar Novo', 'cchla-ufrn'),
        'new_item'              => __('Novo Departamento', 'cchla-ufrn'),
        'edit_item'             => __('Editar Departamento', 'cchla-ufrn'),
        'update_item'           => __('Atualizar Departamento', 'cchla-ufrn'),
        'view_item'             => __('Ver Departamento', 'cchla-ufrn'),
        'view_items'            => __('Ver Departamentos', 'cchla-ufrn'),
        'search_items'          => __('Buscar Departamento', 'cchla-ufrn'),
        'not_found'             => __('Nenhum departamento encontrado', 'cchla-ufrn'),
        'not_found_in_trash'    => __('Nenhum departamento encontrado na lixeira', 'cchla-ufrn'),
        'featured_image'        => __('Imagem do Departamento', 'cchla-ufrn'),
        'set_featured_image'    => __('Definir imagem do departamento', 'cchla-ufrn'),
        'remove_featured_image' => __('Remover imagem do departamento', 'cchla-ufrn'),
        'use_featured_image'    => __('Usar como imagem do departamento', 'cchla-ufrn'),
        'insert_into_item'      => __('Inserir no departamento', 'cchla-ufrn'),
        'uploaded_to_this_item' => __('Enviado para este departamento', 'cchla-ufrn'),
        'items_list'            => __('Lista de departamentos', 'cchla-ufrn'),
        'items_list_navigation' => __('Navegação da lista de departamentos', 'cchla-ufrn'),
        'filter_items_list'     => __('Filtrar lista de departamentos', 'cchla-ufrn'),
    );

    $args = array(
        'label'                 => __('Departamento', 'cchla-ufrn'),
        'description'           => __('Departamentos do CCHLA', 'cchla-ufrn'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes'),
        'taxonomies'            => array('area_conhecimento'),
        'hierarchical'          => true,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-building',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => 'departamentos',
        'rewrite'               => array(
            'slug'       => 'departamento',
            'with_front' => false,
            'pages'      => true,
            'feeds'      => true,
        ),
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
        'show_in_rest'          => true,
    );

    register_post_type('departamentos', $args);
}
add_action('init', 'cchla_register_departamentos_cpt', 0);

/**
 * ==========================================
 * 2. CUSTOM POST TYPE: CURSOS
 * ==========================================
 */
function cchla_register_cursos_cpt()
{
    $labels = array(
        'name'                  => _x('Cursos', 'Post Type General Name', 'cchla-ufrn'),
        'singular_name'         => _x('Curso', 'Post Type Singular Name', 'cchla-ufrn'),
        'menu_name'             => __('Cursos', 'cchla-ufrn'),
        'name_admin_bar'        => __('Curso', 'cchla-ufrn'),
        'archives'              => __('Arquivo de Cursos', 'cchla-ufrn'),
        'attributes'            => __('Atributos do Curso', 'cchla-ufrn'),
        'parent_item_colon'     => __('Curso Pai:', 'cchla-ufrn'),
        'all_items'             => __('Todos os Cursos', 'cchla-ufrn'),
        'add_new_item'          => __('Adicionar Novo Curso', 'cchla-ufrn'),
        'add_new'               => __('Adicionar Novo', 'cchla-ufrn'),
        'new_item'              => __('Novo Curso', 'cchla-ufrn'),
        'edit_item'             => __('Editar Curso', 'cchla-ufrn'),
        'update_item'           => __('Atualizar Curso', 'cchla-ufrn'),
        'view_item'             => __('Ver Curso', 'cchla-ufrn'),
        'view_items'            => __('Ver Cursos', 'cchla-ufrn'),
        'search_items'          => __('Buscar Curso', 'cchla-ufrn'),
        'not_found'             => __('Nenhum curso encontrado', 'cchla-ufrn'),
        'not_found_in_trash'    => __('Nenhum curso encontrado na lixeira', 'cchla-ufrn'),
        'featured_image'        => __('Imagem do Curso', 'cchla-ufrn'),
        'set_featured_image'    => __('Definir imagem do curso', 'cchla-ufrn'),
        'remove_featured_image' => __('Remover imagem do curso', 'cchla-ufrn'),
        'use_featured_image'    => __('Usar como imagem do curso', 'cchla-ufrn'),
        'insert_into_item'      => __('Inserir no curso', 'cchla-ufrn'),
        'uploaded_to_this_item' => __('Enviado para este curso', 'cchla-ufrn'),
        'items_list'            => __('Lista de cursos', 'cchla-ufrn'),
        'items_list_navigation' => __('Navegação da lista de cursos', 'cchla-ufrn'),
        'filter_items_list'     => __('Filtrar lista de cursos', 'cchla-ufrn'),
    );

    $args = array(
        'label'                 => __('Curso', 'cchla-ufrn'),
        'description'           => __('Cursos oferecidos pelos departamentos', 'cchla-ufrn'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions'),
        'taxonomies'            => array('tipo_curso', 'area_conhecimento'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 21,
        'menu_icon'             => 'dashicons-welcome-learn-more',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => 'cursos',
        'rewrite'               => array(
            'slug'       => 'curso',
            'with_front' => false,
            'pages'      => true,
            'feeds'      => true,
        ),
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );

    register_post_type('cursos', $args);
}
add_action('init', 'cchla_register_cursos_cpt', 0);

/**
 * ==========================================
 * 3. TAXONOMIA: TIPO DE CURSO
 * ==========================================
 */
function cchla_register_tipo_curso_taxonomy()
{
    $labels = array(
        'name'                       => _x('Tipos de Curso', 'Taxonomy General Name', 'cchla-ufrn'),
        'singular_name'              => _x('Tipo de Curso', 'Taxonomy Singular Name', 'cchla-ufrn'),
        'menu_name'                  => __('Tipos de Curso', 'cchla-ufrn'),
        'all_items'                  => __('Todos os Tipos', 'cchla-ufrn'),
        'parent_item'                => __('Tipo Pai', 'cchla-ufrn'),
        'parent_item_colon'          => __('Tipo Pai:', 'cchla-ufrn'),
        'new_item_name'              => __('Nome do Novo Tipo', 'cchla-ufrn'),
        'add_new_item'               => __('Adicionar Novo Tipo', 'cchla-ufrn'),
        'edit_item'                  => __('Editar Tipo', 'cchla-ufrn'),
        'update_item'                => __('Atualizar Tipo', 'cchla-ufrn'),
        'view_item'                  => __('Ver Tipo', 'cchla-ufrn'),
        'separate_items_with_commas' => __('Separar tipos com vírgulas', 'cchla-ufrn'),
        'add_or_remove_items'        => __('Adicionar ou remover tipos', 'cchla-ufrn'),
        'choose_from_most_used'      => __('Escolher entre os mais usados', 'cchla-ufrn'),
        'popular_items'              => __('Tipos Populares', 'cchla-ufrn'),
        'search_items'               => __('Buscar Tipos', 'cchla-ufrn'),
        'not_found'                  => __('Não Encontrado', 'cchla-ufrn'),
        'no_terms'                   => __('Nenhum tipo', 'cchla-ufrn'),
        'items_list'                 => __('Lista de tipos', 'cchla-ufrn'),
        'items_list_navigation'      => __('Navegação da lista de tipos', 'cchla-ufrn'),
    );

    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'rewrite'                    => array('slug' => 'tipo-curso'),
        'show_in_rest'               => true,
    );

    register_taxonomy('tipo_curso', array('cursos'), $args);
}
add_action('init', 'cchla_register_tipo_curso_taxonomy', 0);

/**
 * ==========================================
 * 4. TAXONOMIA: ÁREA DE CONHECIMENTO
 * ==========================================
 */
function cchla_register_area_conhecimento_taxonomy()
{
    $labels = array(
        'name'                       => _x('Áreas de Conhecimento', 'Taxonomy General Name', 'cchla-ufrn'),
        'singular_name'              => _x('Área de Conhecimento', 'Taxonomy Singular Name', 'cchla-ufrn'),
        'menu_name'                  => __('Áreas de Conhecimento', 'cchla-ufrn'),
        'all_items'                  => __('Todas as Áreas', 'cchla-ufrn'),
        'parent_item'                => __('Área Pai', 'cchla-ufrn'),
        'parent_item_colon'          => __('Área Pai:', 'cchla-ufrn'),
        'new_item_name'              => __('Nome da Nova Área', 'cchla-ufrn'),
        'add_new_item'               => __('Adicionar Nova Área', 'cchla-ufrn'),
        'edit_item'                  => __('Editar Área', 'cchla-ufrn'),
        'update_item'                => __('Atualizar Área', 'cchla-ufrn'),
        'view_item'                  => __('Ver Área', 'cchla-ufrn'),
        'separate_items_with_commas' => __('Separar áreas com vírgulas', 'cchla-ufrn'),
        'add_or_remove_items'        => __('Adicionar ou remover áreas', 'cchla-ufrn'),
        'choose_from_most_used'      => __('Escolher entre as mais usadas', 'cchla-ufrn'),
        'popular_items'              => __('Áreas Populares', 'cchla-ufrn'),
        'search_items'               => __('Buscar Áreas', 'cchla-ufrn'),
        'not_found'                  => __('Não Encontrado', 'cchla-ufrn'),
        'no_terms'                   => __('Nenhuma área', 'cchla-ufrn'),
        'items_list'                 => __('Lista de áreas', 'cchla-ufrn'),
        'items_list_navigation'      => __('Navegação da lista de áreas', 'cchla-ufrn'),
    );

    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
        'rewrite'                    => array('slug' => 'area'),
        'show_in_rest'               => true,
    );

    register_taxonomy('area_conhecimento', array('departamentos', 'cursos'), $args);
}
add_action('init', 'cchla_register_area_conhecimento_taxonomy', 0);

/**
 * ==========================================
 * 5. META BOXES - INFORMAÇÕES DO DEPARTAMENTO
 * ==========================================
 */
function cchla_add_departamento_meta_boxes()
{
    add_meta_box(
        'cchla_departamento_info',
        __('Informações do Departamento', 'cchla-ufrn'),
        'cchla_departamento_info_callback',
        'departamentos',
        'normal',
        'high'
    );

    add_meta_box(
        'cchla_departamento_contato',
        __('Contatos do Departamento', 'cchla-ufrn'),
        'cchla_departamento_contato_callback',
        'departamentos',
        'normal',
        'high'
    );

    add_meta_box(
        'cchla_departamento_responsaveis',
        __('Responsáveis e Coordenação', 'cchla-ufrn'),
        'cchla_departamento_responsaveis_callback',
        'departamentos',
        'normal',
        'high'
    );

    add_meta_box(
        'cchla_departamento_links',
        __('Links e Recursos', 'cchla-ufrn'),
        'cchla_departamento_links_callback',
        'departamentos',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'cchla_add_departamento_meta_boxes');

/**
 * Callback: Informações do Departamento
 */
function cchla_departamento_info_callback($post)
{
    wp_nonce_field('cchla_save_departamento_info', 'cchla_departamento_info_nonce');

    $sigla = get_post_meta($post->ID, '_departamento_sigla', true);
    $codigo = get_post_meta($post->ID, '_departamento_codigo', true);
    $fundacao = get_post_meta($post->ID, '_departamento_fundacao', true);
    $localizacao = get_post_meta($post->ID, '_departamento_localizacao', true);
    $sala = get_post_meta($post->ID, '_departamento_sala', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="departamento_sigla"><?php _e('Sigla', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="text"
                    id="departamento_sigla"
                    name="departamento_sigla"
                    value="<?php echo esc_attr($sigla); ?>"
                    class="regular-text"
                    placeholder="Ex: DGEO">
                <p class="description"><?php _e('Sigla oficial do departamento', 'cchla-ufrn'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="departamento_codigo"><?php _e('Código', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="text"
                    id="departamento_codigo"
                    name="departamento_codigo"
                    value="<?php echo esc_attr($codigo); ?>"
                    class="regular-text"
                    placeholder="Ex: 1234">
                <p class="description"><?php _e('Código institucional do departamento', 'cchla-ufrn'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="departamento_fundacao"><?php _e('Data de Fundação', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="date"
                    id="departamento_fundacao"
                    name="departamento_fundacao"
                    value="<?php echo esc_attr($fundacao); ?>"
                    class="regular-text">
            </td>
        </tr>
        <tr>
            <th><label for="departamento_localizacao"><?php _e('Localização', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="text"
                    id="departamento_localizacao"
                    name="departamento_localizacao"
                    value="<?php echo esc_attr($localizacao); ?>"
                    class="large-text"
                    placeholder="Ex: Prédio do CCHLA, 2º andar">
                <p class="description"><?php _e('Localização física do departamento', 'cchla-ufrn'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="departamento_sala"><?php _e('Sala', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="text"
                    id="departamento_sala"
                    name="departamento_sala"
                    value="<?php echo esc_attr($sala); ?>"
                    class="regular-text"
                    placeholder="Ex: Sala 201">
            </td>
        </tr>
    </table>
<?php
}

/**
 * Callback: Contatos do Departamento
 */
function cchla_departamento_contato_callback($post)
{
    wp_nonce_field('cchla_save_departamento_contato', 'cchla_departamento_contato_nonce');

    $telefone = get_post_meta($post->ID, '_departamento_telefone', true);
    $telefone_2 = get_post_meta($post->ID, '_departamento_telefone_2', true);
    $email = get_post_meta($post->ID, '_departamento_email', true);
    $email_secretaria = get_post_meta($post->ID, '_departamento_email_secretaria', true);
    $horario_atendimento = get_post_meta($post->ID, '_departamento_horario_atendimento', true);
?>
    <table class="form-table">
        <tr>
            <th><label for="departamento_telefone"><?php _e('Telefone Principal', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="tel"
                    id="departamento_telefone"
                    name="departamento_telefone"
                    value="<?php echo esc_attr($telefone); ?>"
                    class="regular-text"
                    placeholder="(84) 3342-2234">
            </td>
        </tr>
        <tr>
            <th><label for="departamento_telefone_2"><?php _e('Telefone Secundário', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="tel"
                    id="departamento_telefone_2"
                    name="departamento_telefone_2"
                    value="<?php echo esc_attr($telefone_2); ?>"
                    class="regular-text"
                    placeholder="(84) 3342-2235">
            </td>
        </tr>
        <tr>
            <th><label for="departamento_email"><?php _e('E-mail Principal', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="email"
                    id="departamento_email"
                    name="departamento_email"
                    value="<?php echo esc_attr($email); ?>"
                    class="large-text"
                    placeholder="departamento@cchla.ufrn.br">
            </td>
        </tr>
        <tr>
            <th><label for="departamento_email_secretaria"><?php _e('E-mail da Secretaria', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="email"
                    id="departamento_email_secretaria"
                    name="departamento_email_secretaria"
                    value="<?php echo esc_attr($email_secretaria); ?>"
                    class="large-text"
                    placeholder="secretaria@cchla.ufrn.br">
            </td>
        </tr>
        <tr>
            <th><label for="departamento_horario_atendimento"><?php _e('Horário de Atendimento', 'cchla-ufrn'); ?></label></th>
            <td>
                <textarea id="departamento_horario_atendimento"
                    name="departamento_horario_atendimento"
                    rows="3"
                    class="large-text"
                    placeholder="Segunda a Sexta: 8h às 12h e 14h às 18h"><?php echo esc_textarea($horario_atendimento); ?></textarea>
            </td>
        </tr>
    </table>
<?php
}

/**
 * Callback: Responsáveis e Coordenação
 */
function cchla_departamento_responsaveis_callback($post)
{
    wp_nonce_field('cchla_save_departamento_responsaveis', 'cchla_departamento_responsaveis_nonce');

    $chefe = get_post_meta($post->ID, '_departamento_chefe', true);
    $chefe_email = get_post_meta($post->ID, '_departamento_chefe_email', true);
    $subchefe = get_post_meta($post->ID, '_departamento_subchefe', true);
    $subchefe_email = get_post_meta($post->ID, '_departamento_subchefe_email', true);
    $coordenador = get_post_meta($post->ID, '_departamento_coordenador', true);
    $coordenador_email = get_post_meta($post->ID, '_departamento_coordenador_email', true);
?>
    <table class="form-table">
        <tr>
            <th colspan="2">
                <h3><?php _e('Chefia do Departamento', 'cchla-ufrn'); ?></h3>
            </th>
        </tr>
        <tr>
            <th><label for="departamento_chefe"><?php _e('Chefe', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="text"
                    id="departamento_chefe"
                    name="departamento_chefe"
                    value="<?php echo esc_attr($chefe); ?>"
                    class="large-text"
                    placeholder="Prof. Dr. Nome Completo">
            </td>
        </tr>
        <tr>
            <th><label for="departamento_chefe_email"><?php _e('E-mail do Chefe', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="email"
                    id="departamento_chefe_email"
                    name="departamento_chefe_email"
                    value="<?php echo esc_attr($chefe_email); ?>"
                    class="large-text">
            </td>
        </tr>
        <tr>
            <th><label for="departamento_subchefe"><?php _e('Subchefe', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="text"
                    id="departamento_subchefe"
                    name="departamento_subchefe"
                    value="<?php echo esc_attr($subchefe); ?>"
                    class="large-text"
                    placeholder="Prof. Dr. Nome Completo">
            </td>
        </tr>
        <tr>
            <th><label for="departamento_subchefe_email"><?php _e('E-mail do Subchefe', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="email"
                    id="departamento_subchefe_email"
                    name="departamento_subchefe_email"
                    value="<?php echo esc_attr($subchefe_email); ?>"
                    class="large-text">
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <h3><?php _e('Coordenação Acadêmica', 'cchla-ufrn'); ?></h3>
            </th>
        </tr>
        <tr>
            <th><label for="departamento_coordenador"><?php _e('Coordenador(a)', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="text"
                    id="departamento_coordenador"
                    name="departamento_coordenador"
                    value="<?php echo esc_attr($coordenador); ?>"
                    class="large-text"
                    placeholder="Prof. Dr. Nome Completo">
            </td>
        </tr>
        <tr>
            <th><label for="departamento_coordenador_email"><?php _e('E-mail do Coordenador', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="email"
                    id="departamento_coordenador_email"
                    name="departamento_coordenador_email"
                    value="<?php echo esc_attr($coordenador_email); ?>"
                    class="large-text">
            </td>
        </tr>
    </table>
<?php
}

/**
 * Callback: Links e Recursos
 */
function cchla_departamento_links_callback($post)
{
    wp_nonce_field('cchla_save_departamento_links', 'cchla_departamento_links_nonce');

    $site = get_post_meta($post->ID, '_departamento_site', true);
    $lattes = get_post_meta($post->ID, '_departamento_lattes', true);
    $instagram = get_post_meta($post->ID, '_departamento_instagram', true);
    $facebook = get_post_meta($post->ID, '_departamento_facebook', true);
    $youtube = get_post_meta($post->ID, '_departamento_youtube', true);
?>
    <p>
        <label for="departamento_site"><strong><?php _e('Site Oficial', 'cchla-ufrn'); ?></strong></label>
        <input type="url"
            id="departamento_site"
            name="departamento_site"
            value="<?php echo esc_url($site); ?>"
            class="widefat"
            placeholder="https://exemplo.cchla.ufrn.br">
    </p>
    <p>
        <label for="departamento_lattes"><strong><?php _e('Grupo Lattes/CNPq', 'cchla-ufrn'); ?></strong></label>
        <input type="url"
            id="departamento_lattes"
            name="departamento_lattes"
            value="<?php echo esc_url($lattes); ?>"
            class="widefat"
            placeholder="http://dgp.cnpq.br/...">
    </p>

    <p>
        <label for="departamento_instagram"><strong><?php _e('Instagram', 'cchla-ufrn'); ?></strong></label>
        <input type="url"
            id="departamento_instagram"
            name="departamento_instagram"
            value="<?php echo esc_url($instagram); ?>"
            class="widefat"
            placeholder="https://instagram.com/...">
    </p>

    <p>
        <label for="departamento_facebook"><strong><?php _e('Facebook', 'cchla-ufrn'); ?></strong></label>
        <input type="url"
            id="departamento_facebook"
            name="departamento_facebook"
            value="<?php echo esc_url($facebook); ?>"
            class="widefat"
            placeholder="https://facebook.com/...">
    </p>

    <p>
        <label for="departamento_youtube"><strong><?php _e('YouTube', 'cchla-ufrn'); ?></strong></label>
        <input type="url"
            id="departamento_youtube"
            name="departamento_youtube"
            value="<?php echo esc_url($youtube); ?>"
            class="widefat"
            placeholder="https://youtube.com/@...">
    </p>
<?php
}

/**
 * ==========================================
 * 6. META BOXES - INFORMAÇÕES DO CURSO
 * ==========================================
 */
function cchla_add_curso_meta_boxes()
{
    add_meta_box(
        'cchla_curso_info',
        __('Informações do Curso', 'cchla-ufrn'),
        'cchla_curso_info_callback',
        'cursos',
        'normal',
        'high'
    );

    add_meta_box(
        'cchla_curso_departamento',
        __('Departamento Responsável', 'cchla-ufrn'),
        'cchla_curso_departamento_callback',
        'cursos',
        'side',
        'high'
    );

    add_meta_box(
        'cchla_curso_coordenacao',
        __('Coordenação do Curso', 'cchla-ufrn'),
        'cchla_curso_coordenacao_callback',
        'cursos',
        'normal',
        'high'
    );

    add_meta_box(
        'cchla_curso_detalhes',
        __('Detalhes Acadêmicos', 'cchla-ufrn'),
        'cchla_curso_detalhes_callback',
        'cursos',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'cchla_add_curso_meta_boxes');

/**
 * Callback: Informações do Curso
 */
function cchla_curso_info_callback($post)
{
    wp_nonce_field('cchla_save_curso_info', 'cchla_curso_info_nonce');

    $codigo = get_post_meta($post->ID, '_curso_codigo', true);
    $modalidade = get_post_meta($post->ID, '_curso_modalidade', true);
    $turno = get_post_meta($post->ID, '_curso_turno', true);
    $duracao = get_post_meta($post->ID, '_curso_duracao', true);
    $vagas = get_post_meta($post->ID, '_curso_vagas', true);
?>
    <table class="form-table">
        <tr>
            <th><label for="curso_codigo"><?php _e('Código do Curso', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="text"
                    id="curso_codigo"
                    name="curso_codigo"
                    value="<?php echo esc_attr($codigo); ?>"
                    class="regular-text"
                    placeholder="Ex: 1234567">
                <p class="description"><?php _e('Código oficial do curso no sistema acadêmico', 'cchla-ufrn'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="curso_modalidade"><?php _e('Modalidade', 'cchla-ufrn'); ?></label></th>
            <td>
                <select id="curso_modalidade" name="curso_modalidade" class="regular-text">
                    <option value=""><?php _e('Selecione...', 'cchla-ufrn'); ?></option>
                    <option value="presencial" <?php selected($modalidade, 'presencial'); ?>><?php _e('Presencial', 'cchla-ufrn'); ?></option>
                    <option value="ead" <?php selected($modalidade, 'ead'); ?>><?php _e('EaD', 'cchla-ufrn'); ?></option>
                    <option value="hibrido" <?php selected($modalidade, 'hibrido'); ?>><?php _e('Híbrido', 'cchla-ufrn'); ?></option>
                    <option value="semipresencial" <?php selected($modalidade, 'semipresencial'); ?>><?php _e('Semipresencial', 'cchla-ufrn'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="curso_turno"><?php _e('Turno', 'cchla-ufrn'); ?></label></th>
            <td>
                <select id="curso_turno" name="curso_turno" class="regular-text">
                    <option value=""><?php _e('Selecione...', 'cchla-ufrn'); ?></option>
                    <option value="matutino" <?php selected($turno, 'matutino'); ?>><?php _e('Matutino', 'cchla-ufrn'); ?></option>
                    <option value="vespertino" <?php selected($turno, 'vespertino'); ?>><?php _e('Vespertino', 'cchla-ufrn'); ?></option>
                    <option value="noturno" <?php selected($turno, 'noturno'); ?>><?php _e('Noturno', 'cchla-ufrn'); ?></option>
                    <option value="integral" <?php selected($turno, 'integral'); ?>><?php _e('Integral', 'cchla-ufrn'); ?></option>
                    <option value="variado" <?php selected($turno, 'variado'); ?>><?php _e('Variado', 'cchla-ufrn'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="curso_duracao"><?php _e('Duração', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="text"
                    id="curso_duracao"
                    name="curso_duracao"
                    value="<?php echo esc_attr($duracao); ?>"
                    class="regular-text"
                    placeholder="Ex: 4 anos / 8 semestres">
                <p class="description"><?php _e('Duração mínima do curso', 'cchla-ufrn'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="curso_vagas"><?php _e('Vagas Anuais', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="number"
                    id="curso_vagas"
                    name="curso_vagas"
                    value="<?php echo esc_attr($vagas); ?>"
                    class="small-text"
                    min="0"
                    placeholder="50">
            </td>
        </tr>
    </table>
<?php
}

/**
 * Callback: Departamento Responsável
 */
function cchla_curso_departamento_callback($post)
{
    wp_nonce_field('cchla_save_curso_departamento', 'cchla_curso_departamento_nonce');

    $departamento_id = get_post_meta($post->ID, '_curso_departamento', true);

    $departamentos = get_posts(array(
        'post_type' => 'departamentos',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));
?>
    <p>
        <label for="curso_departamento"><strong><?php _e('Selecione o Departamento', 'cchla-ufrn'); ?></strong></label>
        <select id="curso_departamento" name="curso_departamento" class="widefat">
            <option value=""><?php _e('-- Selecione --', 'cchla-ufrn'); ?></option>
            <?php foreach ($departamentos as $dept) : ?>
                <option value="<?php echo $dept->ID; ?>" <?php selected($departamento_id, $dept->ID); ?>>
                    <?php echo esc_html($dept->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <?php if ($departamento_id) : ?>
        <p class="description">
            <a href="<?php echo get_edit_post_link($departamento_id); ?>" target="_blank">
                <?php _e('Editar Departamento', 'cchla-ufrn'); ?> ↗
            </a>
        </p>
    <?php endif; ?>
<?php
}

/**
 * Callback: Coordenação do Curso
 */
function cchla_curso_coordenacao_callback($post)
{
    wp_nonce_field('cchla_save_curso_coordenacao', 'cchla_curso_coordenacao_nonce');

    $coordenador = get_post_meta($post->ID, '_curso_coordenador', true);
    $coordenador_email = get_post_meta($post->ID, '_curso_coordenador_email', true);
    $coordenador_telefone = get_post_meta($post->ID, '_curso_coordenador_telefone', true);
    $vice_coordenador = get_post_meta($post->ID, '_curso_vice_coordenador', true);
    $vice_coordenador_email = get_post_meta($post->ID, '_curso_vice_coordenador_email', true);
?>
    <table class="form-table">
        <tr>
            <th><label for="curso_coordenador"><?php _e('Coordenador(a)', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="text"
                    id="curso_coordenador"
                    name="curso_coordenador"
                    value="<?php echo esc_attr($coordenador); ?>"
                    class="large-text"
                    placeholder="Prof. Dr. Nome Completo">
            </td>
        </tr>
        <tr>
            <th><label for="curso_coordenador_email"><?php _e('E-mail do Coordenador', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="email"
                    id="curso_coordenador_email"
                    name="curso_coordenador_email"
                    value="<?php echo esc_attr($coordenador_email); ?>"
                    class="large-text">
            </td>
        </tr>
        <tr>
            <th><label for="curso_coordenador_telefone"><?php _e('Telefone do Coordenador', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="tel"
                    id="curso_coordenador_telefone"
                    name="curso_coordenador_telefone"
                    value="<?php echo esc_attr($coordenador_telefone); ?>"
                    class="regular-text"
                    placeholder="(84) 3342-2234">
            </td>
        </tr>
        <tr>
            <th><label for="curso_vice_coordenador"><?php _e('Vice-Coordenador(a)', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="text"
                    id="curso_vice_coordenador"
                    name="curso_vice_coordenador"
                    value="<?php echo esc_attr($vice_coordenador); ?>"
                    class="large-text"
                    placeholder="Prof. Dr. Nome Completo">
            </td>
        </tr>
        <tr>
            <th><label for="curso_vice_coordenador_email"><?php _e('E-mail do Vice-Coordenador', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="email"
                    id="curso_vice_coordenador_email"
                    name="curso_vice_coordenador_email"
                    value="<?php echo esc_attr($vice_coordenador_email); ?>"
                    class="large-text">
            </td>
        </tr>
    </table>
<?php
}

/**
 * Callback: Detalhes Acadêmicos
 */
function cchla_curso_detalhes_callback($post)
{
    wp_nonce_field('cchla_save_curso_detalhes', 'cchla_curso_detalhes_nonce');

    $carga_horaria = get_post_meta($post->ID, '_curso_carga_horaria', true);
    $nota_mec = get_post_meta($post->ID, '_curso_nota_mec', true);
    $reconhecimento = get_post_meta($post->ID, '_curso_reconhecimento', true);
    $matriz_curricular = get_post_meta($post->ID, '_curso_matriz_curricular', true);
    $ppc = get_post_meta($post->ID, '_curso_ppc', true);
?>
    <table class="form-table">
        <tr>
            <th><label for="curso_carga_horaria"><?php _e('Carga Horária Total', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="text"
                    id="curso_carga_horaria"
                    name="curso_carga_horaria"
                    value="<?php echo esc_attr($carga_horaria); ?>"
                    class="regular-text"
                    placeholder="Ex: 2800 horas">
            </td>
        </tr>
        <tr>
            <th><label for="curso_nota_mec"><?php _e('Nota MEC/ENADE', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="text"
                    id="curso_nota_mec"
                    name="curso_nota_mec"
                    value="<?php echo esc_attr($nota_mec); ?>"
                    class="small-text"
                    placeholder="5">
                <p class="description"><?php _e('Nota de avaliação do MEC (1 a 5)', 'cchla-ufrn'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="curso_reconhecimento"><?php _e('Reconhecimento/Portaria', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="text"
                    id="curso_reconhecimento"
                    name="curso_reconhecimento"
                    value="<?php echo esc_attr($reconhecimento); ?>"
                    class="large-text"
                    placeholder="Portaria MEC nº 123/2020">
            </td>
        </tr>
        <tr>
            <th><label for="curso_matriz_curricular"><?php _e('Link da Matriz Curricular', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="url"
                    id="curso_matriz_curricular"
                    name="curso_matriz_curricular"
                    value="<?php echo esc_url($matriz_curricular); ?>"
                    class="large-text"
                    placeholder="https://...">
            </td>
        </tr>
        <tr>
            <th><label for="curso_ppc"><?php _e('Link do PPC (Projeto Pedagógico)', 'cchla-ufrn'); ?></label></th>
            <td>
                <input type="url"
                    id="curso_ppc"
                    name="curso_ppc"
                    value="<?php echo esc_url($ppc); ?>"
                    class="large-text"
                    placeholder="https://...">
            </td>
        </tr>
    </table>
<?php
}

/**
 * ==========================================
 * 7. SALVAR META BOXES - DEPARTAMENTO
 * ==========================================
 */
function cchla_save_departamento_meta($post_id)
{
    // Verifica autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Verifica permissões
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Salvar Informações
    if (isset($_POST['cchla_departamento_info_nonce']) && wp_verify_nonce($_POST['cchla_departamento_info_nonce'], 'cchla_save_departamento_info')) {
        $fields = array('sigla', 'codigo', 'fundacao', 'localizacao', 'sala');
        foreach ($fields as $field) {
            if (isset($_POST["departamento_$field"])) {
                update_post_meta($post_id, "_departamento_$field", sanitize_text_field($_POST["departamento_$field"]));
            }
        }
    }

    // Salvar Contatos
    if (isset($_POST['cchla_departamento_contato_nonce']) && wp_verify_nonce($_POST['cchla_departamento_contato_nonce'], 'cchla_save_departamento_contato')) {
        $fields = array('telefone', 'telefone_2', 'email', 'email_secretaria');
        foreach ($fields as $field) {
            if (isset($_POST["departamento_$field"])) {
                if (strpos($field, 'email') !== false) {
                    update_post_meta($post_id, "_departamento_$field", sanitize_email($_POST["departamento_$field"]));
                } else {
                    update_post_meta($post_id, "_departamento_$field", sanitize_text_field($_POST["departamento_$field"]));
                }
            }
        }
        if (isset($_POST['departamento_horario_atendimento'])) {
            update_post_meta($post_id, '_departamento_horario_atendimento', sanitize_textarea_field($_POST['departamento_horario_atendimento']));
        }
    }

    // Salvar Responsáveis
    if (isset($_POST['cchla_departamento_responsaveis_nonce']) && wp_verify_nonce($_POST['cchla_departamento_responsaveis_nonce'], 'cchla_save_departamento_responsaveis')) {
        $fields = array('chefe', 'chefe_email', 'subchefe', 'subchefe_email', 'coordenador', 'coordenador_email');
        foreach ($fields as $field) {
            if (isset($_POST["departamento_$field"])) {
                if (strpos($field, 'email') !== false) {
                    update_post_meta($post_id, "_departamento_$field", sanitize_email($_POST["departamento_$field"]));
                } else {
                    update_post_meta($post_id, "_departamento_$field", sanitize_text_field($_POST["departamento_$field"]));
                }
            }
        }
    }

    // Salvar Links
    if (isset($_POST['cchla_departamento_links_nonce']) && wp_verify_nonce($_POST['cchla_departamento_links_nonce'], 'cchla_save_departamento_links')) {
        $fields = array('site', 'lattes', 'instagram', 'facebook', 'youtube');
        foreach ($fields as $field) {
            if (isset($_POST["departamento_$field"])) {
                update_post_meta($post_id, "_departamento_$field", esc_url_raw($_POST["departamento_$field"]));
            }
        }
    }
}
add_action('save_post_departamentos', 'cchla_save_departamento_meta');

/**
 * ==========================================
 * 8. SALVAR META BOXES - CURSO
 * ==========================================
 */
function cchla_save_curso_meta($post_id)
{
    // Verifica autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Verifica permissões
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Salvar Informações
    if (isset($_POST['cchla_curso_info_nonce']) && wp_verify_nonce($_POST['cchla_curso_info_nonce'], 'cchla_save_curso_info')) {
        $fields = array('codigo', 'modalidade', 'turno', 'duracao', 'vagas');
        foreach ($fields as $field) {
            if (isset($_POST["curso_$field"])) {
                update_post_meta($post_id, "_curso_$field", sanitize_text_field($_POST["curso_$field"]));
            }
        }
    }

    // Salvar Departamento
    if (isset($_POST['cchla_curso_departamento_nonce']) && wp_verify_nonce($_POST['cchla_curso_departamento_nonce'], 'cchla_save_curso_departamento')) {
        if (isset($_POST['curso_departamento'])) {
            update_post_meta($post_id, '_curso_departamento', intval($_POST['curso_departamento']));
        }
    }

    // Salvar Coordenação
    if (isset($_POST['cchla_curso_coordenacao_nonce']) && wp_verify_nonce($_POST['cchla_curso_coordenacao_nonce'], 'cchla_save_curso_coordenacao')) {
        $fields = array('coordenador', 'coordenador_email', 'coordenador_telefone', 'vice_coordenador', 'vice_coordenador_email');
        foreach ($fields as $field) {
            if (isset($_POST["curso_$field"])) {
                if (strpos($field, 'email') !== false) {
                    update_post_meta($post_id, "_curso_$field", sanitize_email($_POST["curso_$field"]));
                } else {
                    update_post_meta($post_id, "_curso_$field", sanitize_text_field($_POST["curso_$field"]));
                }
            }
        }
    }

    // Salvar Detalhes
    if (isset($_POST['cchla_curso_detalhes_nonce']) && wp_verify_nonce($_POST['cchla_curso_detalhes_nonce'], 'cchla_save_curso_detalhes')) {
        $text_fields = array('carga_horaria', 'nota_mec', 'reconhecimento');
        foreach ($text_fields as $field) {
            if (isset($_POST["curso_$field"])) {
                update_post_meta($post_id, "_curso_$field", sanitize_text_field($_POST["curso_$field"]));
            }
        }

        $url_fields = array('matriz_curricular', 'ppc');
        foreach ($url_fields as $field) {
            if (isset($_POST["curso_$field"])) {
                update_post_meta($post_id, "_curso_$field", esc_url_raw($_POST["curso_$field"]));
            }
        }
    }
}
add_action('save_post_cursos', 'cchla_save_curso_meta');

/**
 * ==========================================
 * 9. COLUNAS CUSTOMIZADAS - ADMIN
 * ==========================================
 */

// Colunas Departamentos
function cchla_departamentos_columns($columns)
{
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = __('Departamento', 'cchla-ufrn');
    $new_columns['sigla'] = __('Sigla', 'cchla-ufrn');
    $new_columns['area'] = __('Área', 'cchla-ufrn');
    $new_columns['chefe'] = __('Chefe', 'cchla-ufrn');
    $new_columns['contato'] = __('Contato', 'cchla-ufrn');
    $new_columns['cursos'] = __('Cursos', 'cchla-ufrn');
    $new_columns['date'] = $columns['date'];
    return $new_columns;
}
add_filter('manage_departamentos_posts_columns', 'cchla_departamentos_columns');

function cchla_departamentos_column_content($column, $post_id)
{
    switch ($column) {
        case 'sigla':
            $sigla = get_post_meta($post_id, '_departamento_sigla', true);
            echo $sigla ? '<strong>' . esc_html($sigla) . '</strong>' : '—';
            break;

        case 'area':
            $terms = get_the_terms($post_id, 'area_conhecimento');
            if ($terms && !is_wp_error($terms)) {
                $areas = array();
                foreach ($terms as $term) {
                    $areas[] = $term->name;
                }
                echo implode(', ', $areas);
            } else {
                echo '—';
            }
            break;

        case 'chefe':
            $chefe = get_post_meta($post_id, '_departamento_chefe', true);
            echo $chefe ? esc_html($chefe) : '—';
            break;

        case 'contato':
            $email = get_post_meta($post_id, '_departamento_email', true);
            $telefone = get_post_meta($post_id, '_departamento_telefone', true);
            if ($email) {
                echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a><br>';
            }
            if ($telefone) {
                echo '<a href="tel:' . esc_attr($telefone) . '">' . esc_html($telefone) . '</a>';
            }
            if (!$email && !$telefone) {
                echo '—';
            }
            break;

        case 'cursos':
            $cursos = get_posts(array(
                'post_type' => 'cursos',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_curso_departamento',
                        'value' => $post_id,
                    )
                )
            ));
            echo '<strong>' . count($cursos) . '</strong> ' . _n('curso', 'cursos', count($cursos), 'cchla-ufrn');
            break;
    }
}
add_action('manage_departamentos_posts_custom_column', 'cchla_departamentos_column_content', 10, 2);

// Colunas Cursos
function cchla_cursos_columns($columns)
{
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = __('Curso', 'cchla-ufrn');
    $new_columns['tipo'] = __('Tipo', 'cchla-ufrn');
    $new_columns['departamento'] = __('Departamento', 'cchla-ufrn');
    $new_columns['modalidade'] = __('Modalidade', 'cchla-ufrn');
    $new_columns['coordenador'] = __('Coordenador', 'cchla-ufrn');
    $new_columns['vagas'] = __('Vagas', 'cchla-ufrn');
    $new_columns['date'] = $columns['date'];
    return $new_columns;
}
add_filter('manage_cursos_posts_columns', 'cchla_cursos_columns');

function cchla_cursos_column_content($column, $post_id)
{
    switch ($column) {
        case 'tipo':
            $terms = get_the_terms($post_id, 'tipo_curso');
            if ($terms && !is_wp_error($terms)) {
                echo '<span class="curso-tipo-badge">' . esc_html($terms[0]->name) . '</span>';
            } else {
                echo '—';
            }
            break;

        case 'departamento':
            $dept_id = get_post_meta($post_id, '_curso_departamento', true);
            if ($dept_id) {
                $dept = get_post($dept_id);
                if ($dept) {
                    echo '<a href="' . get_edit_post_link($dept_id) . '">' . esc_html($dept->post_title) . '</a>';
                } else {
                    echo '—';
                }
            } else {
                echo '—';
            }
            break;

        case 'modalidade':
            $modalidade = get_post_meta($post_id, '_curso_modalidade', true);
            if ($modalidade) {
                $modalidades = array(
                    'presencial' => 'Presencial',
                    'ead' => 'EaD',
                    'hibrido' => 'Híbrido',
                    'semipresencial' => 'Semipresencial'
                );
                echo isset($modalidades[$modalidade]) ? $modalidades[$modalidade] : ucfirst($modalidade);
            } else {
                echo '—';
            }
            break;

        case 'coordenador':
            $coordenador = get_post_meta($post_id, '_curso_coordenador', true);
            echo $coordenador ? esc_html($coordenador) : '—';
            break;

        case 'vagas':
            $vagas = get_post_meta($post_id, '_curso_vagas', true);
            echo $vagas ? '<strong>' . esc_html($vagas) . '</strong>' : '—';
            break;
    }
}
add_action('manage_cursos_posts_custom_column', 'cchla_cursos_column_content', 10, 2);

/**
 * ==========================================
 * 10. FILTROS NO ADMIN
 * ==========================================
 */

// Filtro por Área de Conhecimento - Departamentos
function cchla_departamentos_filters()
{
    global $typenow;

    if ($typenow == 'departamentos') {
        $taxonomy = 'area_conhecimento';
        $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';

        $info = get_taxonomy($taxonomy);
        wp_dropdown_categories(array(
            'show_option_all' => sprintf(__('Todas as %s', 'cchla-ufrn'), $info->label),
            'taxonomy'        => $taxonomy,
            'name'            => $taxonomy,
            'orderby'         => 'name',
            'selected'        => $selected,
            'show_count'      => true,
            'hide_empty'      => true,
            'value_field'     => 'slug',
        ));
    }
}
add_action('restrict_manage_posts', 'cchla_departamentos_filters');

// Filtro por Tipo e Departamento - Cursos
function cchla_cursos_filters()
{
    global $typenow;

    if ($typenow == 'cursos') {
        // Filtro por Tipo de Curso
        $taxonomy = 'tipo_curso';
        $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';

        $info = get_taxonomy($taxonomy);
        wp_dropdown_categories(array(
            'show_option_all' => sprintf(__('Todos os %s', 'cchla-ufrn'), $info->label),
            'taxonomy'        => $taxonomy,
            'name'            => $taxonomy,
            'orderby'         => 'name',
            'selected'        => $selected,
            'show_count'      => true,
            'hide_empty'      => true,
            'value_field'     => 'slug',
        ));

        // Filtro por Departamento
        $departamentos = get_posts(array(
            'post_type' => 'departamentos',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        $selected_dept = isset($_GET['departamento_filter']) ? $_GET['departamento_filter'] : '';

        echo '<select name="departamento_filter">';
        echo '<option value="">' . __('Todos os Departamentos', 'cchla-ufrn') . '</option>';
        foreach ($departamentos as $dept) {
            printf(
                '<option value="%s"%s>%s</option>',
                $dept->ID,
                $dept->ID == $selected_dept ? ' selected="selected"' : '',
                esc_html($dept->post_title)
            );
        }
        echo '</select>';
    }
}
add_action('restrict_manage_posts', 'cchla_cursos_filters');

// Aplica o filtro de departamento
function cchla_cursos_filter_query($query)
{
    global $pagenow;
    $type = 'cursos';

    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }

    if ('cursos' == $type && is_admin() && $pagenow == 'edit.php' && isset($_GET['departamento_filter']) && $_GET['departamento_filter'] != '') {
        $query->set('meta_key', '_curso_departamento');
        $query->set('meta_value', $_GET['departamento_filter']);
    }
}
add_filter('parse_query', 'cchla_cursos_filter_query');

/**
 * ==========================================
 * 11. WIDGET DE DASHBOARD - ESTATÍSTICAS
 * ==========================================
 */
function cchla_dashboard_widget()
{
    wp_add_dashboard_widget(
        'cchla_stats_widget',
        __('Estatísticas - Departamentos e Cursos', 'cchla-ufrn'),
        'cchla_dashboard_widget_display'
    );
}
add_action('wp_dashboard_setup', 'cchla_dashboard_widget');

function cchla_dashboard_widget_display()
{
    $total_departamentos = wp_count_posts('departamentos')->publish;
    $total_cursos = wp_count_posts('cursos')->publish;

    // Cursos por tipo
    $graduacao = get_posts(array(
        'post_type' => 'cursos',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'tipo_curso',
                'field' => 'slug',
                'terms' => 'graduacao'
            )
        ),
        'fields' => 'ids'
    ));

    $pos_graduacao = get_posts(array(
        'post_type' => 'cursos',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'tipo_curso',
                'field' => 'slug',
                'terms' => 'pos-graduacao'
            )
        ),
        'fields' => 'ids'
    ));
?>
    <div class="cchla-dashboard-stats">
        <style>
            .cchla-dashboard-stats {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                margin-bottom: 20px;
            }

            .cchla-stat-box {
                background: #f0f6fc;
                padding: 15px;
                border-radius: 8px;
                text-align: center;
                border-left: 4px solid #2271b1;
            }

            .cchla-stat-box h3 {
                margin: 0 0 10px 0;
                font-size: 32px;
                font-weight: 700;
                color: #2271b1;
            }

            .cchla-stat-box p {
                margin: 0;
                color: #50575e;
                font-size: 14px;
            }

            .cchla-quick-links {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                margin-top: 15px;
            }

            .cchla-quick-links a {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 8px 12px;
                background: #2271b1;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                font-size: 13px;
            }

            .inside>.cchla-quick-links.buttons a {
                color: white !important;

            }

            .cchla-quick-links a:hover {
                background: #135e96;
            }
        </style>

        <div class="cchla-stat-box">
            <h3><?php echo $total_departamentos; ?></h3>
            <p><?php _e('Departamentos', 'cchla-ufrn'); ?></p>
        </div>

        <div class="cchla-stat-box">
            <h3><?php echo $total_cursos; ?></h3>
            <p><?php _e('Cursos Total', 'cchla-ufrn'); ?></p>
        </div>

        <div class="cchla-stat-box">
            <h3><?php echo count($graduacao); ?></h3>
            <p><?php _e('Graduação', 'cchla-ufrn'); ?></p>
        </div>

        <div class="cchla-stat-box">
            <h3><?php echo count($pos_graduacao); ?></h3>
            <p><?php _e('Pós-Graduação', 'cchla-ufrn'); ?></p>
        </div>
    </div>

    <div class="cchla-quick-links buttons">
        <a href="<?php echo admin_url('post-new.php?post_type=departamentos'); ?>">
            <span class="dashicons dashicons-plus-alt"></span>
            <?php _e('Novo Departamento', 'cchla-ufrn'); ?>
        </a>
        <a href="<?php echo admin_url('post-new.php?post_type=cursos'); ?>">
            <span class="dashicons dashicons-plus-alt"></span>
            <?php _e('Novo Curso', 'cchla-ufrn'); ?>
        </a>
        <a href="<?php echo admin_url('edit.php?post_type=departamentos'); ?>">
            <span class="dashicons dashicons-list-view"></span>
            <?php _e('Ver Todos', 'cchla-ufrn'); ?>
        </a>
    </div>
<?php
}

/**
 * ==========================================
 * 12. FLUSH REWRITE RULES
 * ==========================================
 */
function cchla_rewrite_flush()
{
    cchla_register_departamentos_cpt();
    cchla_register_cursos_cpt();
    cchla_register_tipo_curso_taxonomy();
    cchla_register_area_conhecimento_taxonomy();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'cchla_rewrite_flush');

/**
 * ==========================================
 * 13. POPULAR TAXONOMIAS PADRÃO (Primeira Vez)
 * ==========================================
 */
function cchla_populate_taxonomies()
{
    // Verifica se já foi populado
    if (get_option('cchla_taxonomies_populated')) {
        return;
    }

    // Tipos de Curso
    $tipos_curso = array(
        'Graduação',
        'Pós-Graduação - Mestrado',
        'Pós-Graduação - Doutorado',
        'Especialização',
        'Extensão',
        'Aperfeiçoamento'
    );

    foreach ($tipos_curso as $tipo) {
        if (!term_exists($tipo, 'tipo_curso')) {
            wp_insert_term($tipo, 'tipo_curso');
        }
    }

    // Áreas de Conhecimento
    $areas = array(
        'Ciências Humanas',
        'Linguística, Letras e Artes',
        'Geografia',
        'História',
        'Filosofia',
        'Sociologia',
        'Antropologia',
        'Ciência Política',
        'Psicologia',
        'Educação',
        'Artes',
        'Música',
        'Teatro',
        'Letras'
    );

    foreach ($areas as $area) {
        if (!term_exists($area, 'area_conhecimento')) {
            wp_insert_term($area, 'area_conhecimento');
        }
    }

    update_option('cchla_taxonomies_populated', true);
}
add_action('init', 'cchla_populate_taxonomies', 999);


/**
 * ==========================================
 * BUSCA UNIFICADA - TODOS OS POST TYPES
 * ==========================================
 * Inclui: posts, pages, departamentos, cursos, publicacoes, especiais, servicos, acesso_rapido
 */

/**
 * Modifica query principal de busca para incluir todos os CPTs
 */
function cchla_unified_search_filter($query)
{
    // Só executa no frontend, em buscas, na query principal
    if (is_admin() || !$query->is_search() || !$query->is_main_query()) {
        return;
    }

    // Lista completa de post types pesquisáveis
    $searchable_post_types = array(
        'post',              // Posts (Notícias)
        'page',              // Páginas
        'departamentos',     // Departamentos
        'cursos',            // Cursos
        'publicacoes',       // Publicações acadêmicas
        'especiais',         // Especiais (vídeos/projetos)
        'servicos',          // Serviços de extensão
        'acesso_rapido'      // Acesso rápido (sistemas externos)
    );

    // Se houver filtro específico via GET
    if (isset($_GET['post_type']) && !empty($_GET['post_type'])) {
        $requested_type = sanitize_key($_GET['post_type']);

        // Valida se o tipo solicitado é permitido
        if (in_array($requested_type, $searchable_post_types)) {
            $query->set('post_type', $requested_type);
        } else {
            // Se tipo inválido, busca em todos
            $query->set('post_type', $searchable_post_types);
        }
    } else {
        // Sem filtro = busca em todos os tipos
        $query->set('post_type', $searchable_post_types);
    }

    // Define número de resultados por página
    $query->set('posts_per_page', 10);
}
add_action('pre_get_posts', 'cchla_unified_search_filter');

/**
 * ==========================================
 * BUSCA EM META FIELDS
 * ==========================================
 * Expande busca para incluir campos customizados
 */

/**
 * Busca em meta fields de DEPARTAMENTOS
 */
function cchla_search_departamentos_meta_fields($search, $wp_query)
{
    global $wpdb;

    // Validações
    if (empty($search) || !$wp_query->is_search() || empty($wp_query->get('s'))) {
        return $search;
    }

    $search_term = $wp_query->get('s');
    $post_types = $wp_query->get('post_type');

    // Converte para array se for string
    if (!is_array($post_types)) {
        $post_types = array($post_types);
    }

    // Só aplica se departamentos estiver incluído na busca
    if (!in_array('departamentos', $post_types)) {
        return $search;
    }

    // Meta keys pesquisáveis para departamentos
    $dept_meta_keys = array(
        '_departamento_sigla',
        '_departamento_codigo',
        '_departamento_chefe',
        '_departamento_subchefe',
        '_departamento_coordenador',
        '_departamento_email',
        '_departamento_email_secretaria',
        '_departamento_telefone',
        '_departamento_telefone_2',
        '_departamento_localizacao'
    );

    $meta_conditions = array();
    foreach ($dept_meta_keys as $key) {
        $meta_conditions[] = $wpdb->prepare(
            "({$wpdb->postmeta}.meta_key = %s AND {$wpdb->postmeta}.meta_value LIKE %s)",
            $key,
            '%' . $wpdb->esc_like($search_term) . '%'
        );
    }

    // Adiciona condição de meta fields à busca
    if (!empty($meta_conditions)) {
        $search .= " OR ({$wpdb->posts}.ID IN (
            SELECT DISTINCT post_id FROM {$wpdb->postmeta}
            WHERE " . implode(' OR ', $meta_conditions) . "
        ))";
    }

    return $search;
}
add_filter('posts_search', 'cchla_search_departamentos_meta_fields', 10, 2);

/**
 * Busca em meta fields de CURSOS
 */
function cchla_search_cursos_meta_fields($search, $wp_query)
{
    global $wpdb;

    // Validações
    if (empty($search) || !$wp_query->is_search() || empty($wp_query->get('s'))) {
        return $search;
    }

    $search_term = $wp_query->get('s');
    $post_types = $wp_query->get('post_type');

    // Converte para array se for string
    if (!is_array($post_types)) {
        $post_types = array($post_types);
    }

    // Só aplica se cursos estiver incluído na busca
    if (!in_array('cursos', $post_types)) {
        return $search;
    }

    // Meta keys pesquisáveis para cursos
    $curso_meta_keys = array(
        '_curso_codigo',
        '_curso_coordenador',
        '_curso_vice_coordenador',
        '_curso_coordenador_email',
        '_curso_modalidade',
        '_curso_turno',
        '_curso_reconhecimento'
    );

    $meta_conditions = array();
    foreach ($curso_meta_keys as $key) {
        $meta_conditions[] = $wpdb->prepare(
            "({$wpdb->postmeta}.meta_key = %s AND {$wpdb->postmeta}.meta_value LIKE %s)",
            $key,
            '%' . $wpdb->esc_like($search_term) . '%'
        );
    }

    // Adiciona condição de meta fields à busca
    if (!empty($meta_conditions)) {
        $search .= " OR ({$wpdb->posts}.ID IN (
            SELECT DISTINCT post_id FROM {$wpdb->postmeta}
            WHERE " . implode(' OR ', $meta_conditions) . "
        ))";
    }

    return $search;
}
add_filter('posts_search', 'cchla_search_cursos_meta_fields', 11, 2);

/**
 * ==========================================
 * CONTADORES POR TIPO (PARA FILTROS)
 * ==========================================
 */

/**
 * Obtém contagem de resultados por post type
 * CORRIGIDO: Nome diferente da função anterior
 */
function cchla_get_search_results_count_by_type($search_query)
{
    $post_types = array(
        'post',
        'page',
        'departamentos',
        'cursos',
        'publicacoes',
        'especiais',
        'servicos',
        'acesso_rapido'
    );

    $counts = array();

    foreach ($post_types as $post_type) {
        // Tenta buscar do cache
        $cache_key = 'search_count_' . md5($search_query . '_' . $post_type);
        $count = wp_cache_get($cache_key, 'cchla_search');

        if (false === $count) {
            // Cache miss - executa query
            $args = array(
                'post_type' => $post_type,
                'post_status' => 'publish',
                's' => $search_query,
                'posts_per_page' => 1, // Otimização: só precisamos da contagem
                'fields' => 'ids',
                'no_found_rows' => false,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            );

            $query = new WP_Query($args);
            $count = $query->found_posts;

            // Armazena em cache por 15 minutos
            wp_cache_set($cache_key, $count, 'cchla_search', 900);

            wp_reset_postdata();
        }

        $counts[$post_type] = $count;
    }

    return $counts;
}

/**
 * ==========================================
 * LABELS AMIGÁVEIS PARA POST TYPES
 * ==========================================
 */

/**
 * Retorna label traduzido para post type
 */
function cchla_get_post_type_label($post_type, $plural = true)
{
    $labels = array(
        'post' => array(
            'singular' => __('Notícia', 'cchla-ufrn'),
            'plural' => __('Notícias', 'cchla-ufrn')
        ),
        'page' => array(
            'singular' => __('Página', 'cchla-ufrn'),
            'plural' => __('Páginas', 'cchla-ufrn')
        ),
        'departamentos' => array(
            'singular' => __('Departamento', 'cchla-ufrn'),
            'plural' => __('Departamentos', 'cchla-ufrn')
        ),
        'cursos' => array(
            'singular' => __('Curso', 'cchla-ufrn'),
            'plural' => __('Cursos', 'cchla-ufrn')
        ),
        'publicacoes' => array(
            'singular' => __('Publicação', 'cchla-ufrn'),
            'plural' => __('Publicações', 'cchla-ufrn')
        ),
        'especiais' => array(
            'singular' => __('Especial', 'cchla-ufrn'),
            'plural' => __('Especiais', 'cchla-ufrn')
        ),
        'servicos' => array(
            'singular' => __('Serviço', 'cchla-ufrn'),
            'plural' => __('Serviços', 'cchla-ufrn')
        ),
        'acesso_rapido' => array(
            'singular' => __('Sistema', 'cchla-ufrn'),
            'plural' => __('Sistemas', 'cchla-ufrn')
        ),
    );

    if (!isset($labels[$post_type])) {
        return $post_type;
    }

    return $plural ? $labels[$post_type]['plural'] : $labels[$post_type]['singular'];
}

/**
 * ==========================================
 * ÍCONES PARA POST TYPES
 * ==========================================
 */

/**
 * Retorna classe de ícone Font Awesome para post type
 */
function cchla_get_post_type_icon($post_type)
{
    $icons = array(
        'post' => 'fa-newspaper',
        'page' => 'fa-file',
        'departamentos' => 'fa-building',
        'cursos' => 'fa-graduation-cap',
        'publicacoes' => 'fa-book',
        'especiais' => 'fa-video',
        'servicos' => 'fa-hand-holding-heart',
        'acesso_rapido' => 'fa-link',
    );

    return isset($icons[$post_type]) ? $icons[$post_type] : 'fa-file';
}

/**
 * Retorna cor do badge para post type
 */
function cchla_get_post_type_color($post_type)
{
    $colors = array(
        'post' => 'blue',
        'page' => 'gray',
        'departamentos' => 'indigo',
        'cursos' => 'green',
        'publicacoes' => 'purple',
        'especiais' => 'red',
        'servicos' => 'yellow',
        'acesso_rapido' => 'pink',
    );

    return isset($colors[$post_type]) ? $colors[$post_type] : 'gray';
}

/**
 * ==========================================
 * LIMPA CACHE AO PUBLICAR/ATUALIZAR
 * ==========================================
 */

/**
 * Limpa cache de busca quando posts são publicados
 */
function cchla_clear_search_cache_on_save($post_id)
{
    // Limpa todo o cache de busca
    wp_cache_flush_group('cchla_search');

    // Também limpa transients antigos
    global $wpdb;
    $wpdb->query(
        "DELETE FROM $wpdb->options 
         WHERE option_name LIKE '_transient_search_count_%' 
         OR option_name LIKE '_transient_timeout_search_count_%'"
    );
}
add_action('save_post', 'cchla_clear_search_cache_on_save');
add_action('delete_post', 'cchla_clear_search_cache_on_save');

/**
 * ==========================================
 * DESTAQUE DE TERMOS NOS RESULTADOS
 * ==========================================
 */

/**
 * Destaca termo de busca no texto (já existe, mas garantindo)
 */
if (!function_exists('cchla_highlight_search_term')) {
    function cchla_highlight_search_term($text, $search_term)
    {
        if (empty($search_term) || empty($text)) {
            return $text;
        }

        return preg_replace(
            '/(' . preg_quote($search_term, '/') . ')/iu',
            '<mark class="bg-yellow-200 font-semibold px-1 rounded">$1</mark>',
            $text
        );
    }
}

/**
 * Shortcode para listar departamentos
 * Uso: [lista_departamentos area="geografia" limite="5"]
 */
function cchla_lista_departamentos_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'area' => '',
        'limite' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ), $atts);

    $args = array(
        'post_type' => 'departamentos',
        'posts_per_page' => intval($atts['limite']),
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
    );

    if (!empty($atts['area'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'area_conhecimento',
                'field' => 'slug',
                'terms' => $atts['area']
            )
        );
    }

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>' . __('Nenhum departamento encontrado.', 'cchla-ufrn') . '</p>';
    }

    ob_start();
?>
    <div class="cchla-departamentos-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php while ($query->have_posts()) : $query->the_post();
            $sigla = get_post_meta(get_the_ID(), '_departamento_sigla', true);
        ?>
            <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-lg transition-shadow">
                <?php if ($sigla) : ?>
                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold mb-3">
                        <?php echo esc_html($sigla); ?>
                    </span>
                <?php endif; ?>

                <h3 class="text-xl font-bold text-gray-900 mb-2">
                    <a href="<?php the_permalink(); ?>" class="hover:text-blue-600">
                        <?php the_title(); ?>
                    </a>
                </h3>

                <?php if (has_excerpt()) : ?>
                    <p class="text-gray-600 text-sm mb-4">
                        <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                    </p>
                <?php endif; ?>

                <a href="<?php the_permalink(); ?>"
                    class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium text-sm">
                    <?php _e('Ver detalhes', 'cchla-ufrn'); ?>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
<?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('lista_departamentos', 'cchla_lista_departamentos_shortcode');

/**
 * Shortcode para listar cursos
 * Uso: [lista_cursos tipo="graduacao" departamento="5" limite="10"]
 */
function cchla_lista_cursos_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'tipo' => '',
        'departamento' => '',
        'limite' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ), $atts);

    $args = array(
        'post_type' => 'cursos',
        'posts_per_page' => intval($atts['limite']),
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
    );

    if (!empty($atts['tipo'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'tipo_curso',
                'field' => 'slug',
                'terms' => $atts['tipo']
            )
        );
    }

    if (!empty($atts['departamento'])) {
        $args['meta_query'] = array(
            array(
                'key' => '_curso_departamento',
                'value' => intval($atts['departamento'])
            )
        );
    }

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>' . __('Nenhum curso encontrado.', 'cchla-ufrn') . '</p>';
    }

    ob_start();
?>
    <div class="cchla-cursos-list space-y-4">
        <?php while ($query->have_posts()) : $query->the_post();
            $tipo_terms = get_the_terms(get_the_ID(), 'tipo_curso');
            $tipo = $tipo_terms && !is_wp_error($tipo_terms) ? $tipo_terms[0]->name : '';
        ?>
            <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-lg transition-shadow">
                <div class="flex justify-between items-start gap-4">
                    <div class="flex-1">
                        <?php if ($tipo) : ?>
                            <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold mb-2">
                                <?php echo esc_html($tipo); ?>
                            </span>
                        <?php endif; ?>

                        <h3 class="text-lg font-bold text-gray-900 mb-2">
                            <a href="<?php the_permalink(); ?>" class="hover:text-green-600">
                                <?php the_title(); ?>
                            </a>
                        </h3>

                        <?php if (has_excerpt()) : ?>
                            <p class="text-gray-600 text-sm">
                                <?php echo wp_trim_words(get_the_excerpt(), 25); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <a href="<?php the_permalink(); ?>"
                        class="flex-shrink-0 text-green-600 hover:text-green-800">
                        <i class="fa-solid fa-arrow-right text-xl"></i>
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('lista_cursos', 'cchla_lista_cursos_shortcode');


/**
 * ==========================================
 * FILTROS CUSTOMIZADOS PARA ARCHIVE
 * ==========================================
 * Interpreta parâmetros personalizados da URL e modifica a query
 */

/**
 * Modifica query do archive para interpretar filtros customizados
 */
function cchla_archive_custom_filters($query)
{
    // Só executa no frontend, em archives, na query principal
    if (is_admin() || !$query->is_main_query() || !$query->is_archive()) {
        return;
    }

    // ==========================================
    // FILTRO POR TAXONOMIA CUSTOMIZADA
    // ==========================================
    // URL: ?tax=tipo_publicacao&term=artigo
    if (isset($_GET['tax']) && isset($_GET['term'])) {
        $taxonomy = sanitize_key($_GET['tax']);
        $term = sanitize_text_field($_GET['term']);

        // Valida se a taxonomia existe
        if (taxonomy_exists($taxonomy)) {
            $tax_query = $query->get('tax_query') ?: array();

            $tax_query[] = array(
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $term,
            );

            $query->set('tax_query', $tax_query);
        }
    }

    // ==========================================
    // FILTRO POR CATEGORIA (via ID)
    // ==========================================
    // URL: ?cat=5
    if (isset($_GET['cat']) && !is_category()) {
        $cat_id = intval($_GET['cat']);
        if ($cat_id > 0) {
            $query->set('cat', $cat_id);
        }
    }

    // ==========================================
    // FILTRO POR TAG (via slug)
    // ==========================================
    // URL: ?tag=wordpress
    if (isset($_GET['tag']) && !is_tag()) {
        $tag_slug = sanitize_title($_GET['tag']);
        if (!empty($tag_slug)) {
            $query->set('tag', $tag_slug);
        }
    }

    // ==========================================
    // FILTRO POR ANO
    // ==========================================
    // URL: ?year=2024
    if (isset($_GET['year']) && !is_date()) {
        $year = intval($_GET['year']);
        if ($year > 1900 && $year <= date('Y') + 1) {
            $query->set('year', $year);
        }
    }

    // ==========================================
    // FILTRO POR MÊS
    // ==========================================
    // URL: ?year=2024&month=12
    if (isset($_GET['month']) && isset($_GET['year']) && !is_date()) {
        $month = intval($_GET['month']);
        $year = intval($_GET['year']);

        if ($month >= 1 && $month <= 12) {
            $query->set('year', $year);
            $query->set('monthnum', $month);
        }
    }

    // ==========================================
    // ORDENAÇÃO CUSTOMIZADA
    // ==========================================
    // URL: ?orderby=title&order=asc
    if (isset($_GET['orderby'])) {
        $orderby = sanitize_key($_GET['orderby']);
        $order = isset($_GET['order']) ? strtoupper(sanitize_key($_GET['order'])) : 'DESC';

        // Valida ordem
        if (!in_array($order, array('ASC', 'DESC'))) {
            $order = 'DESC';
        }

        // Valida orderby
        $allowed_orderby = array(
            'date',
            'title',
            'name',
            'modified',
            'author',
            'comment_count',
            'rand',
            'menu_order'
        );

        if (in_array($orderby, $allowed_orderby)) {
            $query->set('orderby', $orderby);
            $query->set('order', $order);
        }
    }

    // ==========================================
    // FILTRO POR AUTOR
    // ==========================================
    // URL: ?author_name=joao-silva
    if (isset($_GET['author_name']) && !is_author()) {
        $author_name = sanitize_user($_GET['author_name']);
        if (!empty($author_name)) {
            $query->set('author_name', $author_name);
        }
    }

    // URL: ?author=5
    if (isset($_GET['author']) && !is_author()) {
        $author_id = intval($_GET['author']);
        if ($author_id > 0) {
            $query->set('author', $author_id);
        }
    }
}
add_action('pre_get_posts', 'cchla_archive_custom_filters', 20);

/**
 * Função auxiliar: verifica se há filtros ativos
 */
function cchla_is_filtered()
{
    $filters = array('cat', 'tag', 'tax', 'term', 'year', 'month', 'author', 'author_name', 's');

    foreach ($filters as $filter) {
        if (isset($_GET[$filter]) && !empty($_GET[$filter])) {
            return true;
        }
    }

    return false;
}
/**
 * Suporte para filtros em tags
 */
function cchla_tag_archive_filters($query)
{
    if (is_admin() || !$query->is_main_query() || !$query->is_tag()) {
        return;
    }

    // Filtro por categoria
    if (isset($_GET['cat']) && !empty($_GET['cat'])) {
        $cat_id = intval($_GET['cat']);
        if ($cat_id > 0) {
            $query->set('cat', $cat_id);
        }
    }

    // Filtro por ano
    if (isset($_GET['year']) && !empty($_GET['year'])) {
        $year = intval($_GET['year']);
        if ($year > 1900 && $year <= date('Y') + 1) {
            $query->set('year', $year);
        }
    }

    // Ordenação
    if (isset($_GET['orderby'])) {
        $orderby = sanitize_key($_GET['orderby']);
        $order = isset($_GET['order']) ? strtoupper(sanitize_key($_GET['order'])) : 'DESC';

        if (!in_array($order, array('ASC', 'DESC'))) {
            $order = 'DESC';
        }

        $allowed_orderby = array('date', 'title', 'comment_count', 'rand');

        if (in_array($orderby, $allowed_orderby)) {
            $query->set('orderby', $orderby);
            $query->set('order', $order);
        }
    }
}
add_action('pre_get_posts', 'cchla_tag_archive_filters', 20);



/**
 * Aplica filtros personalizados no blog/home
 */
function cchla_blog_custom_filters($query)
{
    // Só executa no frontend, na query principal, em home/archive de posts
    if (is_admin() || !$query->is_main_query() || (!$query->is_home() && !$query->is_archive())) {
        return;
    }

    // Apenas para posts
    if ($query->get('post_type') != 'post' && !$query->is_home() && !$query->is_category() && !$query->is_tag() && !$query->is_author()) {
        return;
    }

    // ==========================================
    // FILTRO POR CATEGORIA (via GET)
    // ==========================================
    if (isset($_GET['cat']) && !empty($_GET['cat']) && !is_category()) {
        $cat_id = intval($_GET['cat']);
        if ($cat_id > 0) {
            $query->set('cat', $cat_id);
        }
    }

    // ==========================================
    // FILTRO POR TAG (via GET)
    // ==========================================
    if (isset($_GET['tag']) && !empty($_GET['tag']) && !is_tag()) {
        $tag_slug = sanitize_title($_GET['tag']);
        if (!empty($tag_slug)) {
            $query->set('tag', $tag_slug);
        }
    }

    // ==========================================
    // FILTRO POR AUTOR (via GET)
    // ==========================================
    if (isset($_GET['author']) && !empty($_GET['author']) && !is_author()) {
        $author_id = intval($_GET['author']);
        if ($author_id > 0) {
            $query->set('author', $author_id);
        }
    }

    // ==========================================
    // FILTRO POR ANO
    // ==========================================
    if (isset($_GET['year']) && !empty($_GET['year']) && !is_date()) {
        $year = intval($_GET['year']);
        if ($year > 1900 && $year <= date('Y') + 1) {
            $query->set('year', $year);
        }
    }

    // ==========================================
    // ORDENAÇÃO
    // ==========================================
    if (isset($_GET['orderby'])) {
        $orderby = sanitize_key($_GET['orderby']);
        $order = isset($_GET['order']) ? strtoupper(sanitize_key($_GET['order'])) : 'DESC';

        // Valida ordem
        if (!in_array($order, array('ASC', 'DESC'))) {
            $order = 'DESC';
        }

        // Valida orderby
        $allowed_orderby = array('date', 'title', 'comment_count', 'rand', 'modified');

        if (in_array($orderby, $allowed_orderby)) {
            $query->set('orderby', $orderby);
            $query->set('order', $order);
        }
    }
}
add_action('pre_get_posts', 'cchla_blog_custom_filters', 20);


/**
 * Template fallback para resultados de busca
 * Usado quando não existe template específico para o post type
 */
function cchla_display_search_result_fallback()
{
    $post_type = get_post_type();
    $post_type_object = get_post_type_object($post_type);
    $type_label = $post_type_object ? $post_type_object->labels->singular_name : ucfirst($post_type);

    // Ícone baseado no post type
    $icons = array(
        'post' => 'fa-newspaper',
        'page' => 'fa-file',
        'departamentos' => 'fa-building',
        'cursos' => 'fa-graduation-cap',
        'publicacoes' => 'fa-book',
        'especiais' => 'fa-video',
        'servicos' => 'fa-hand-holding-heart',
        'acesso_rapido' => 'fa-link',
    );

    $icon = isset($icons[$post_type]) ? $icons[$post_type] : 'fa-file';

    // Badge de cor
    $colors = array(
        'post' => 'blue',
        'page' => 'gray',
        'departamentos' => 'indigo',
        'cursos' => 'green',
        'publicacoes' => 'purple',
        'especiais' => 'red',
        'servicos' => 'yellow',
        'acesso_rapido' => 'pink',
    );

    $color = isset($colors[$post_type]) ? $colors[$post_type] : 'gray';

    // Busca termo para destacar
    $search_term = get_search_query();
?>

    <article class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">

        <!-- Badge do Tipo -->
        <div class="flex items-center gap-2 mb-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-<?php echo $color; ?>-50 text-<?php echo $color; ?>-700 rounded-full text-xs font-semibold">
                <i class="fa-solid <?php echo $icon; ?>"></i>
                <?php echo esc_html($type_label); ?>
            </span>

            <?php if (get_post_type() === 'post') : ?>
                <time datetime="<?php echo get_the_date('c'); ?>" class="text-xs text-gray-500">
                    <?php echo get_the_date(); ?>
                </time>
            <?php endif; ?>
        </div>

        <!-- Título -->
        <h3 class="text-xl font-bold text-gray-900 mb-3 hover:text-blue-600 transition-colors">
            <a href="<?php the_permalink(); ?>">
                <?php
                $title = get_the_title();
                if ($search_term) {
                    echo cchla_highlight_search_term($title, $search_term);
                } else {
                    echo esc_html($title);
                }
                ?>
            </a>
        </h3>

        <!-- Excerpt -->
        <?php if (has_excerpt() || get_the_content()) : ?>
            <div class="text-gray-600 mb-4 line-clamp-3">
                <?php
                $excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 30);
                if ($search_term) {
                    echo cchla_highlight_search_term($excerpt, $search_term);
                } else {
                    echo wp_kses_post($excerpt);
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Link -->
        <a href="<?php the_permalink(); ?>"
            class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium text-sm">
            <?php _e('Ver detalhes', 'cchla-ufrn'); ?>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>

    </article>

<?php
}



/**
 * Constrói árvore hierárquica do menu
 * Organiza itens em pais (colunas) e filhos (links)
 * 
 * @param array $items Array de itens do menu
 * @return array Árvore hierárquica
 */
function cchla_build_menu_tree($items)
{
    if (empty($items)) {
        return array();
    }

    $tree = array();
    $children = array();

    // Separa pais e filhos
    foreach ($items as $item) {
        if ($item->menu_item_parent == 0) {
            // Item pai (será título da coluna)
            $item->children = array();
            $tree[$item->ID] = $item;
        } else {
            // Item filho (será link na coluna)
            if (!isset($children[$item->menu_item_parent])) {
                $children[$item->menu_item_parent] = array();
            }
            $children[$item->menu_item_parent][] = $item;
        }
    }

    // Anexa filhos aos pais
    foreach ($tree as $parent_id => $parent) {
        if (isset($children[$parent_id])) {
            $tree[$parent_id]->children = $children[$parent_id];
        }
    }

    return $tree;
}

/**
 * Registra menus do footer
 */
function cchla_register_footer_menus()
{
    register_nav_menus(array(
        'mapa-do-site' => __('Mapa do Site (Rodapé)', 'cchla-ufrn'),
    ));
}
add_action('after_setup_theme', 'cchla_register_footer_menus');



/**
 * Adiciona configurações do footer no Customizer
 */
function cchla_customize_footer($wp_customize)
{

    // Seção Footer
    $wp_customize->add_section('cchla_footer_section', array(
        'title'    => __('Footer - CCHLA', 'cchla-ufrn'),
        'priority' => 130,
    ));

    // Sigla
    $wp_customize->add_setting('cchla_sigla', array(
        'default'           => 'CCHLA',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('cchla_sigla', array(
        'label'    => __('Sigla', 'cchla-ufrn'),
        'section'  => 'cchla_footer_section',
        'type'     => 'text',
    ));

    // Texto Rodapé
    $wp_customize->add_setting('cchla_rodape_texto', array(
        'default'           => 'Centro de Ciências Humanas,<br>Letras e Artes',
        'sanitize_callback' => 'wp_kses_post',
    ));

    $wp_customize->add_control('cchla_rodape_texto', array(
        'label'       => __('Texto do Rodapé', 'cchla-ufrn'),
        'section'     => 'cchla_footer_section',
        'type'        => 'textarea',
        'description' => __('Use <br> para quebras de linha', 'cchla-ufrn'),
    ));

    // Nome Completo
    $wp_customize->add_setting('cchla_nome_completo', array(
        'default'           => 'UNIVERSIDADE FEDERAL DO RIO GRANDE DO NORTE',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('cchla_nome_completo', array(
        'label'   => __('Nome Completo da Instituição', 'cchla-ufrn'),
        'section' => 'cchla_footer_section',
        'type'    => 'text',
    ));

    // Subtítulo Footer
    $wp_customize->add_setting('cchla_subtitulo_footer', array(
        'default'           => 'Centro de Ciências Humanas, Letras e Artes',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('cchla_subtitulo_footer', array(
        'label'   => __('Subtítulo (abaixo do nome)', 'cchla-ufrn'),
        'section' => 'cchla_footer_section',
        'type'    => 'text',
    ));

    // Créditos - Link
    $wp_customize->add_setting('cchla_creditos_link', array(
        'default'           => 'https://agenciaweb.ifrn.edu.br',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('cchla_creditos_link', array(
        'label'   => __('Link dos Créditos', 'cchla-ufrn'),
        'section' => 'cchla_footer_section',
        'type'    => 'url',
    ));

    // Créditos - Texto
    $wp_customize->add_setting('cchla_creditos', array(
        'default'           => 'Desenvolvido pela Agência Web do IFRN',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('cchla_creditos', array(
        'label'   => __('Texto dos Créditos', 'cchla-ufrn'),
        'section' => 'cchla_footer_section',
        'type'    => 'text',
    ));

    // Créditos - Logo
    $wp_customize->add_setting('cchla_creditos_logo', array(
        'default'           => get_template_directory_uri() . '/assets/img/logo-awe.svg',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'cchla_creditos_logo', array(
        'label'   => __('Logo dos Créditos', 'cchla-ufrn'),
        'section' => 'cchla_footer_section',
    )));
}
add_action('customize_register', 'cchla_customize_footer');
