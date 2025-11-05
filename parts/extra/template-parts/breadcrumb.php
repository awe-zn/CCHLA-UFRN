<?php

/**
 * Template part - Breadcrumb
 * 
 * Breadcrumb completo com suporte a:
 * - Posts padrão (Notícias) com categorias
 * - Custom Post Types (Publicações, Especiais, Serviços, Acesso Rápido)
 * - Taxonomias customizadas
 * - Páginas hierárquicas
 * - Arquivos e buscas
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

// Não exibe breadcrumb na home
if (is_front_page()) {
    return;
}

// Configurações (pode ser sobrescrito pela função cchla_breadcrumb())
global $cchla_breadcrumb_args;
$args = isset($cchla_breadcrumb_args) ? $cchla_breadcrumb_args : array(
    'home_text' => 'Início',
    'separator' => '›',
    'show_current' => true,
);

$separator = '<span class="text-gray-400 mx-2">' . esc_html($args['separator']) . '</span>';
?>

<nav class="bg-gray-100 border-b border-gray-300" aria-label="breadcrumb">
    <div class="max-w-screen-xl mx-auto px-4 py-3 text-sm text-gray-600">
        <ol class="flex flex-wrap items-center gap-1">

            <!-- HOME -->
            <li>
                <a href="<?php echo esc_url(home_url('/')); ?>"
                    class="hover:text-blue-600 transition-colors duration-200 font-medium">
                    <i class="fa-solid fa-house text-xs mr-1"></i>
                    <?php echo esc_html($args['home_text']); ?>
                </a>
            </li>

            <?php
            // ===== POSTS PADRÃO (NOTÍCIAS) =====
            if (is_singular('post')) {
                // Categoria
                $categories = get_the_category();
                if ($categories) {
                    $category = $categories[0];

                    echo $separator;
                    echo '<li>';
                    echo '<a href="' . esc_url(get_category_link($category->term_id)) . '" class="hover:text-blue-600 transition-colors">';
                    echo esc_html($category->name);
                    echo '</a>';
                    echo '</li>';
                }

                // Post atual
                if ($args['show_current']) {
                    echo $separator;
                    echo '<li aria-current="page" class="text-gray-700 font-medium">';
                    echo esc_html(wp_trim_words(get_the_title(), 10, '...'));
                    echo '</li>';
                }
            }

            // ===== CATEGORIA =====
            elseif (is_category()) {
                $category = get_queried_object();

                // Categoria pai (se existir)
                if ($category->parent) {
                    $parent_cat = get_category($category->parent);
                    echo $separator;
                    echo '<li>';
                    echo '<a href="' . esc_url(get_category_link($parent_cat->term_id)) . '" class="hover:text-blue-600 transition-colors">';
                    echo esc_html($parent_cat->name);
                    echo '</a>';
                    echo '</li>';
                }

                // Categoria atual
                echo $separator;
                echo '<li aria-current="page" class="text-gray-700 font-medium">';
                echo esc_html($category->name);
                echo '</li>';
            }

            // ===== TAG =====
            elseif (is_tag()) {
                $tag = get_queried_object();

                echo $separator;
                echo '<li aria-current="page" class="text-gray-700 font-medium">';
                echo '<i class="fa-solid fa-tag text-xs mr-1"></i>';
                echo esc_html($tag->name);
                echo '</li>';
            }

            // ===== PUBLICAÇÕES =====
            elseif (is_singular('publicacoes')) {
                // Link para arquivo de publicações
                echo $separator;
                echo '<li>';
                echo '<a href="' . esc_url(get_post_type_archive_link('publicacoes')) . '" class="hover:text-blue-600 transition-colors">';
                echo '<i class="fa-solid fa-book text-xs mr-1"></i>';
                echo esc_html__('Publicações', 'cchla-ufrn');
                echo '</a>';
                echo '</li>';

                // Tipo de publicação (se existir)
                $tipos = get_the_terms(get_the_ID(), 'tipo_publicacao');
                if ($tipos && !is_wp_error($tipos)) {
                    $tipo = $tipos[0];
                    echo $separator;
                    echo '<li>';
                    echo '<a href="' . esc_url(get_term_link($tipo)) . '" class="hover:text-blue-600 transition-colors">';
                    echo esc_html($tipo->name);
                    echo '</a>';
                    echo '</li>';
                }

                // Publicação atual
                if ($args['show_current']) {
                    echo $separator;
                    echo '<li aria-current="page" class="text-gray-700 font-medium">';
                    echo esc_html(wp_trim_words(get_the_title(), 10, '...'));
                    echo '</li>';
                }
            }

            // ===== ARQUIVO DE PUBLICAÇÕES =====
            elseif (is_post_type_archive('publicacoes')) {
                echo $separator;
                echo '<li aria-current="page" class="text-gray-700 font-medium">';
                echo '<i class="fa-solid fa-book text-xs mr-1"></i>';
                echo esc_html__('Publicações', 'cchla-ufrn');
                echo '</li>';
            }

            // ===== TIPO DE PUBLICAÇÃO (TAXONOMIA) =====
            elseif (is_tax('tipo_publicacao')) {
                $term = get_queried_object();

                // Link para arquivo de publicações
                echo $separator;
                echo '<li>';
                echo '<a href="' . esc_url(get_post_type_archive_link('publicacoes')) . '" class="hover:text-blue-600 transition-colors">';
                echo '<i class="fa-solid fa-book text-xs mr-1"></i>';
                echo esc_html__('Publicações', 'cchla-ufrn');
                echo '</a>';
                echo '</li>';

                // Tipo atual
                echo $separator;
                echo '<li aria-current="page" class="text-gray-700 font-medium">';
                echo esc_html($term->name);
                echo '</li>';
            }

            // ===== ESPECIAIS =====
            elseif (is_singular('especiais')) {
                // Link para arquivo de especiais
                echo $separator;
                echo '<li>';
                echo '<a href="' . esc_url(get_post_type_archive_link('especiais')) . '" class="hover:text-blue-600 transition-colors">';
                echo '<i class="fa-solid fa-video text-xs mr-1"></i>';
                echo esc_html__('Especiais', 'cchla-ufrn');
                echo '</a>';
                echo '</li>';

                // Categoria do especial (se existir)
                $categorias = get_the_terms(get_the_ID(), 'categoria_especial');
                if ($categorias && !is_wp_error($categorias)) {
                    $categoria = $categorias[0];
                    echo $separator;
                    echo '<li>';
                    echo '<a href="' . esc_url(get_term_link($categoria)) . '" class="hover:text-blue-600 transition-colors">';
                    echo esc_html($categoria->name);
                    echo '</a>';
                    echo '</li>';
                }

                // Especial atual
                if ($args['show_current']) {
                    echo $separator;
                    echo '<li aria-current="page" class="text-gray-700 font-medium">';
                    echo esc_html(wp_trim_words(get_the_title(), 10, '...'));
                    echo '</li>';
                }
            }

            // ===== ARQUIVO DE ESPECIAIS =====
            elseif (is_post_type_archive('especiais')) {
                echo $separator;
                echo '<li aria-current="page" class="text-gray-700 font-medium">';
                echo '<i class="fa-solid fa-video text-xs mr-1"></i>';
                echo esc_html__('Especiais', 'cchla-ufrn');
                echo '</li>';
            }

            // ===== CATEGORIA DE ESPECIAL (TAXONOMIA) =====
            elseif (is_tax('categoria_especial')) {
                $term = get_queried_object();

                // Link para arquivo de especiais
                echo $separator;
                echo '<li>';
                echo '<a href="' . esc_url(get_post_type_archive_link('especiais')) . '" class="hover:text-blue-600 transition-colors">';
                echo '<i class="fa-solid fa-video text-xs mr-1"></i>';
                echo esc_html__('Especiais', 'cchla-ufrn');
                echo '</a>';
                echo '</li>';

                // Categoria atual
                echo $separator;
                echo '<li aria-current="page" class="text-gray-700 font-medium">';
                echo esc_html($term->name);
                echo '</li>';
            }

            // ===== SERVIÇOS =====
            elseif (is_singular('servicos')) {
                // Link para arquivo de serviços
                echo $separator;
                echo '<li>';
                echo '<a href="' . esc_url(get_post_type_archive_link('servicos')) . '" class="hover:text-blue-600 transition-colors">';
                echo '<i class="fa-solid fa-hand-holding-heart text-xs mr-1"></i>';
                echo esc_html__('Serviços', 'cchla-ufrn');
                echo '</a>';
                echo '</li>';

                // Categoria do serviço (se existir)
                $categorias = get_the_terms(get_the_ID(), 'categoria_servico');
                if ($categorias && !is_wp_error($categorias)) {
                    $categoria = $categorias[0];
                    echo $separator;
                    echo '<li>';
                    echo '<a href="' . esc_url(get_term_link($categoria)) . '" class="hover:text-blue-600 transition-colors">';
                    echo esc_html($categoria->name);
                    echo '</a>';
                    echo '</li>';
                }

                // Serviço atual
                if ($args['show_current']) {
                    echo $separator;
                    echo '<li aria-current="page" class="text-gray-700 font-medium">';
                    echo esc_html(wp_trim_words(get_the_title(), 10, '...'));
                    echo '</li>';
                }
            }

            // ===== ARQUIVO DE SERVIÇOS =====
            elseif (is_post_type_archive('servicos')) {
                echo $separator;
                echo '<li aria-current="page" class="text-gray-700 font-medium">';
                echo '<i class="fa-solid fa-hand-holding-heart text-xs mr-1"></i>';
                echo esc_html__('Serviços', 'cchla-ufrn');
                echo '</li>';
            }

            // ===== CATEGORIA DE SERVIÇO (TAXONOMIA) =====
            elseif (is_tax('categoria_servico')) {
                $term = get_queried_object();

                // Link para arquivo de serviços
                echo $separator;
                echo '<li>';
                echo '<a href="' . esc_url(get_post_type_archive_link('servicos')) . '" class="hover:text-blue-600 transition-colors">';
                echo '<i class="fa-solid fa-hand-holding-heart text-xs mr-1"></i>';
                echo esc_html__('Serviços', 'cchla-ufrn');
                echo '</a>';
                echo '</li>';

                // Categoria atual
                echo $separator;
                echo '<li aria-current="page" class="text-gray-700 font-medium">';
                echo esc_html($term->name);
                echo '</li>';
            }

            // ===== ACESSO RÁPIDO =====
            elseif (is_singular('acesso_rapido')) {
                // Link para arquivo de acesso rápido
                echo $separator;
                echo '<li>';
                echo '<a href="' . esc_url(get_post_type_archive_link('acesso_rapido')) . '" class="hover:text-blue-600 transition-colors">';
                echo '<i class="fa-solid fa-link text-xs mr-1"></i>';
                echo esc_html__('Sistemas', 'cchla-ufrn');
                echo '</a>';
                echo '</li>';

                // Categoria do acesso (se existir)
                $categorias = get_the_terms(get_the_ID(), 'categoria_acesso');
                if ($categorias && !is_wp_error($categorias)) {
                    $categoria = $categorias[0];
                    echo $separator;
                    echo '<li>';
                    echo '<a href="' . esc_url(get_term_link($categoria)) . '" class="hover:text-blue-600 transition-colors">';
                    echo esc_html($categoria->name);
                    echo '</a>';
                    echo '</li>';
                }

                // Acesso atual
                if ($args['show_current']) {
                    echo $separator;
                    echo '<li aria-current="page" class="text-gray-700 font-medium">';
                    echo esc_html(wp_trim_words(get_the_title(), 10, '...'));
                    echo '</li>';
                }
            }

            // ===== ARQUIVO DE ACESSO RÁPIDO =====
            elseif (is_post_type_archive('acesso_rapido')) {
                echo $separator;
                echo '<li aria-current="page" class="text-gray-700 font-medium">';
                echo '<i class="fa-solid fa-link text-xs mr-1"></i>';
                echo esc_html__('Sistemas', 'cchla-ufrn');
                echo '</li>';
            }

            // ===== CATEGORIA DE ACESSO (TAXONOMIA) =====
            elseif (is_tax('categoria_acesso')) {
                $term = get_queried_object();

                // Link para arquivo de acesso rápido
                echo $separator;
                echo '<li>';
                echo '<a href="' . esc_url(get_post_type_archive_link('acesso_rapido')) . '" class="hover:text-blue-600 transition-colors">';
                echo '<i class="fa-solid fa-link text-xs mr-1"></i>';
                echo esc_html__('Sistemas', 'cchla-ufrn');
                echo '</a>';
                echo '</li>';

                // Categoria atual
                echo $separator;
                echo '<li aria-current="page" class="text-gray-700 font-medium">';
                echo esc_html($term->name);
                echo '</li>';
            }

            // ===== PÁGINAS =====
            elseif (is_page()) {
                // Páginas hierárquicas (com pais)
                if (wp_get_post_parent_id(get_the_ID())) {
                    $parent_id = wp_get_post_parent_id(get_the_ID());
                    $breadcrumbs = array();

                    // Coleta todas as páginas pai
                    while ($parent_id) {
                        $page = get_post($parent_id);
                        $breadcrumbs[] = array(
                            'title' => get_the_title($page->ID),
                            'url' => get_permalink($page->ID)
                        );
                        $parent_id = $page->post_parent;
                    }

                    // Inverte para ordem correta (do mais alto para o mais baixo)
                    $breadcrumbs = array_reverse($breadcrumbs);

                    // Exibe páginas pai
                    foreach ($breadcrumbs as $crumb) {
                        echo $separator;
                        echo '<li>';
                        echo '<a href="' . esc_url($crumb['url']) . '" class="hover:text-blue-600 transition-colors">';
                        echo esc_html($crumb['title']);
                        echo '</a>';
                        echo '</li>';
                    }
                }

                // Página atual
                if ($args['show_current']) {
                    echo $separator;
                    echo '<li aria-current="page" class="text-gray-700 font-medium">';
                    echo esc_html(wp_trim_words(get_the_title(), 10, '...'));
                    echo '</li>';
                }
            }

            // ===== BUSCA =====
            elseif (is_search()) {
                echo $separator;
                echo '<li aria-current="page" class="text-gray-700 font-medium">';
                echo '<i class="fa-solid fa-search text-xs mr-1"></i>';
                printf(esc_html__('Resultados para: %s', 'cchla-ufrn'), '<strong>"' . esc_html(get_search_query()) . '"</strong>');
                echo '</li>';
            }

            // ===== AUTOR =====
            elseif (is_author()) {
                $author = get_queried_object();
                echo $separator;
                echo '<li aria-current="page" class="text-gray-700 font-medium">';
                echo '<i class="fa-solid fa-user text-xs mr-1"></i>';
                printf(esc_html__('Posts de %s', 'cchla-ufrn'), esc_html($author->display_name));
                echo '</li>';
            }

            // ===== ARQUIVO DE DATA =====
            elseif (is_date()) {
                if (is_day()) {
                    echo $separator;
                    echo '<li aria-current="page" class="text-gray-700 font-medium">';
                    echo '<i class="fa-solid fa-calendar text-xs mr-1"></i>';
                    echo get_the_date();
                    echo '</li>';
                } elseif (is_month()) {
                    echo $separator;
                    echo '<li aria-current="page" class="text-gray-700 font-medium">';
                    echo '<i class="fa-solid fa-calendar text-xs mr-1"></i>';
                    echo get_the_date('F Y');
                    echo '</li>';
                } elseif (is_year()) {
                    echo $separator;
                    echo '<li aria-current="page" class="text-gray-700 font-medium">';
                    echo '<i class="fa-solid fa-calendar text-xs mr-1"></i>';
                    echo get_the_date('Y');
                    echo '</li>';
                }
            }

            // ===== 404 =====
            elseif (is_404()) {
                echo $separator;
                echo '<li aria-current="page" class="text-gray-700 font-medium">';
                echo '<i class="fa-solid fa-triangle-exclamation text-xs mr-1"></i>';
                echo esc_html__('Página não encontrada', 'cchla-ufrn');
                echo '</li>';
            }
            ?>

        </ol>
    </div>
</nav>

<?php

/**

## **Estruturas de Breadcrumb por Tipo**

### **Posts Padrão (Notícias)**
```
Início › Destaque › Título da Notícia
Início › Outros Destaques › Título da Notícia
```

### **Publicações**
```
Início › Publicações › Título da Publicação
Início › Publicações › E-book › Título da Publicação
Início › Publicações › Livro › Título do Livro
```

### **Especiais**
```
Início › Especiais › Título do Especial
Início › Especiais › Comunicação › Título do Especial
Início › Especiais › Educação › Título do Especial
```

### **Serviços**
```
Início › Serviços › Título do Serviço
Início › Serviços › Extensão › Título do Serviço
Início › Serviços › Cultura › Título do Serviço
```

### **Acesso Rápido (Sistemas)**
```
Início › Sistemas › Nome do Sistema
Início › Sistemas › UFRN › SIGAA
Início › Sistemas › Externos › Nome do Sistema
```

### **Páginas Hierárquicas**
```
Início › Sobre › História › Linha do Tempo
Início › Departamentos › Filosofia
```

### **Arquivos**
```
Início › Publicações
Início › Publicações › E-book
Início › Especiais › Comunicação
```

### **Outros**
```
Início › Resultados para: "busca"
Início › Posts de João Silva
Início › Destaque (categoria)
Início › 2024 (ano)
Início › Página não encontrada
 **/
