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
