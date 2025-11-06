<?php

/**
 * Single Post Template - Optimized for Reading Experience
 * 
 * Performance Features:
 * - Lazy loading de imagens
 * - Critical CSS inline
 * - Preconnect para recursos externos
 * - Schema.org otimizado
 * - Progressive enhancement
 * 
 * @package CCHLA_UFRN
 * @since 2.0.0
 */

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Cache de variáveis frequentes
$post_id = get_the_ID();
$site_name = get_bloginfo('name');
$site_assets = get_template_directory_uri();

// Meta dados do post
$post_title = get_the_title();
$post_excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words(strip_tags(get_the_content()), 30, '...');
$post_url = get_permalink();
$post_image = has_post_thumbnail() ? get_the_post_thumbnail_url($post_id, 'large') : $site_assets . '/assets/icons/android-chrome-512x512.png';

// Autor
$author_id = get_the_author_meta('ID');
$author_name = get_the_author();
$author_url = get_author_posts_url($author_id);
$author_avatar = get_avatar_url($author_id, array('size' => 96));

// Categorias
$categories = get_the_category();
$category_name = !empty($categories) ? $categories[0]->name : '';
$category_url = !empty($categories) ? get_category_link($categories[0]->term_id) : '';

// Datas
$published_date = get_the_date('c');
$modified_date = get_the_modified_date('c');
$published_human = get_the_date('d \d\e F \d\e Y');
$reading_time = cchla_calculate_reading_time(get_the_content());

// Tags para compartilhamento
$encoded_url = urlencode($post_url);
$encoded_title = urlencode($post_title);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <!-- Preconnect para recursos externos críticos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <!-- DNS Prefetch para recursos secundários -->
    <link rel="dns-prefetch" href="//www.google-analytics.com">


    <!-- SEO Meta Tags -->
    <title><?php echo esc_html($post_title . ' - ' . $site_name); ?></title>
    <meta name="description" content="<?php echo esc_attr($post_excerpt); ?>">
    <meta name="author" content="<?php echo esc_attr($author_name); ?>">
    <meta name="keywords" content="<?php echo esc_attr(cchla_get_meta_keywords()); ?>">
    <link rel="canonical" href="<?php echo esc_url($post_url); ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo esc_url($post_url); ?>">
    <meta property="og:title" content="<?php echo esc_attr($post_title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($post_excerpt); ?>">
    <meta property="og:image" content="<?php echo esc_url($post_image); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>">
    <meta property="og:locale" content="pt_BR">
    <meta property="article:published_time" content="<?php echo esc_attr($published_date); ?>">
    <meta property="article:modified_time" content="<?php echo esc_attr($modified_date); ?>">
    <meta property="article:author" content="<?php echo esc_url($author_url); ?>">
    <?php if ($category_name) : ?>
        <meta property="article:section" content="<?php echo esc_attr($category_name); ?>">
    <?php endif; ?>
    <?php
    // Tags do post
    $tags = get_the_tags();
    if ($tags) {
        foreach ($tags as $tag) {
            echo '<meta property="article:tag" content="' . esc_attr($tag->name) . '">' . "\n";
        }
    }
    ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo esc_url($post_url); ?>">
    <meta name="twitter:title" content="<?php echo esc_attr($post_title); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($post_excerpt); ?>">
    <meta name="twitter:image" content="<?php echo esc_url($post_image); ?>">
    <meta name="twitter:creator" content="@cchla_ufrn">

    <!-- Schema.org JSON-LD Otimizado -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "NewsArticle",
            "mainEntityOfPage": {
                "@type": "WebPage",
                "@id": "<?php echo esc_url($post_url); ?>"
            },
            "headline": "<?php echo esc_js($post_title); ?>",
            "description": "<?php echo esc_js($post_excerpt); ?>",
            "image": {
                "@type": "ImageObject",
                "url": "<?php echo esc_url($post_image); ?>",
                "width": 1200,
                "height": 630
            },
            "datePublished": "<?php echo esc_attr($published_date); ?>",
            "dateModified": "<?php echo esc_attr($modified_date); ?>",
            "author": {
                "@type": "Person",
                "name": "<?php echo esc_js($author_name); ?>",
                "url": "<?php echo esc_url($author_url); ?>",
                "image": "<?php echo esc_url($author_avatar); ?>"
            },
            "publisher": {
                "@type": "Organization",
                "name": "<?php echo esc_js($site_name); ?>",
                "logo": {
                    "@type": "ImageObject",
                    "url": "<?php echo esc_url($site_assets . '/assets/img/logo.svg'); ?>",
                    "width": 600,
                    "height": 60
                }
            },
            "articleSection": "<?php echo esc_js($category_name); ?>",
            "wordCount": <?php echo str_word_count(strip_tags(get_the_content())); ?>,
            "timeRequired": "PT<?php echo $reading_time; ?>M",
            "inLanguage": "pt-BR"
        }
    </script>

    <!-- Breadcrumb Schema -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            "itemListElement": [{
                    "@type": "ListItem",
                    "position": 1,
                    "name": "Início",
                    "item": "<?php echo esc_url(home_url('/')); ?>"
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": "<?php echo esc_js($category_name); ?>",
                    "item": "<?php echo esc_url($category_url); ?>"
                },
                {
                    "@type": "ListItem",
                    "position": 3,
                    "name": "<?php echo esc_js($post_title); ?>",
                    "item": "<?php echo esc_url($post_url); ?>"
                }
            ]
        }
    </script>

    <!-- Favicons -->
    <link rel="icon" href="<?php echo $site_assets; ?>/assets/icons/favicon.svg" type="image/svg+xml">
    <link rel="alternate icon" href="<?php echo $site_assets; ?>/assets/icons/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $site_assets; ?>/assets/icons/apple-touch-icon-180.png">
    <link rel="manifest" href="<?php echo $site_assets; ?>/assets/icons/site.webmanifest">
    <meta name="theme-color" content="#193CB8" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0f1f52" media="(prefers-color-scheme: dark)">

    <!-- Fonts com display swap para melhor performance -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Merriweather:ital,wght@0,400;0,700;1,400&display=swap" as="style">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Merriweather:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Merriweather:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    </noscript>

    <!-- Font Awesome com defer -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" media="print" onload="this.media='all'">

    <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $site_assets; ?>/assets/css/noticias.css" onload="this.media='all'">
    <link rel="stylesheet" href="<?php echo $site_assets; ?>/assets/css/print.css" media="print">

    <style>
        @media print {

            /* Contador de páginas */
            body {
                counter-reset: page;
            }

            /* Rodapé com número da página */
            @page {
                @bottom-right {
                    content: "Página " counter(page) " de " counter(pages);
                    font-size: 9pt;
                    color: #666;
                }

                @bottom-left {
                    content: "CCHLA - UFRN";
                    font-size: 9pt;
                    color: #666;
                }

                @bottom-center {
                    content: "<?php echo get_the_date('d/m/Y'); ?>";
                    font-size: 9pt;
                    color: #666;
                }
            }
        }
    </style>

    <?php wp_head(); ?>
</head>

<body <?php body_class('antialiased'); ?>>
    <?php wp_body_open(); ?>
    <!-- Skip to Content (Acessibilidade) -->
    <a class="skip-link screen-reader-text" href="#main">
        <?php esc_html_e('Pular para o conteúdo', 'cchla-ufrn'); ?>
    </a>

    <header id="site-header" class="bg-gradient-to-r from-[#002047] from-50% to-[#004499] text-white py-2" role="banner">
        <div class="max-w-screen-xl mx-auto flex items-center justify-between px-6 py-4 lg:py-0">
            <!-- Logo -->
            <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-2" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                <?php
                $custom_logo_id = get_theme_mod('custom_logo');
                if ($custom_logo_id) {
                    $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
                    printf(
                        '<img src="%s" alt="%s" class="h-12" />',
                        esc_url($logo_url),
                        esc_attr(get_bloginfo('name'))
                    );
                } else {
                    printf(
                        '<img src="%s" alt="%s" class="h-12" />',
                        esc_url(get_template_directory_uri() . '/assets/img/logo.svg'),
                        esc_attr(get_bloginfo('name'))
                    );
                }
                ?>
            </a>

            <!-- Menu Desktop -->
            <nav class="hidden lg:flex items-center gap-8" aria-label="<?php esc_attr_e('Menu principal', 'cchla-ufrn'); ?>">
                <?php
                if (has_nav_menu('primary')) {
                    $menu_items = wp_get_nav_menu_items(get_nav_menu_locations()['primary']);

                    if ($menu_items) {
                        // Organiza itens por menu pai
                        $menu_structure = array();

                        foreach ($menu_items as $item) {
                            if ($item->menu_item_parent == 0) {
                                $menu_structure[$item->ID] = array(
                                    'item' => $item,
                                    'children' => array()
                                );
                            }
                        }

                        foreach ($menu_items as $item) {
                            if ($item->menu_item_parent != 0 && isset($menu_structure[$item->menu_item_parent])) {
                                $menu_structure[$item->menu_item_parent]['children'][] = $item;
                            }
                        }

                        // Renderiza o menu
                        foreach ($menu_structure as $parent_id => $parent) {
                            $has_children = !empty($parent['children']);
                            $menu_id = 'menu-' . sanitize_title($parent['item']->title);

                            if ($has_children) :
                ?>
                                <div class="relative group">
                                    <button
                                        class="flex py-5 items-center gap-1 font-semibold focus:outline-none hover:text-blue-200 transition-colors"
                                        aria-expanded="false"
                                        aria-controls="<?php echo esc_attr($menu_id); ?>"
                                        aria-haspopup="true">
                                        <?php echo esc_html(strtoupper($parent['item']->title)); ?>
                                        <i class="fa-solid fa-chevron-down text-xs transition-transform group-hover:rotate-180" aria-hidden="true"></i>
                                    </button>
                                    <ul id="<?php echo esc_attr($menu_id); ?>"
                                        class="absolute hidden group-hover:flex flex-col bg-white text-[#313135] top-full -mt-3 shadow-lg rounded-md overflow-hidden w-48 focus-within:flex transition-all duration-200">
                                        <?php foreach ($parent['children'] as $child) : ?>
                                            <li>
                                                <a href="<?php echo esc_url($child->url); ?>" class="block px-4 py-2 hover:bg-gray-100">
                                                    <?php echo esc_html($child->title); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php
                            else :
                            ?>
                                <a href="<?php echo esc_url($parent['item']->url); ?>" class="font-semibold hover:text-blue-200 transition-colors">
                                    <?php echo esc_html(strtoupper($parent['item']->title)); ?>
                                </a>
                    <?php
                            endif;
                        }
                    }
                } else {
                    // Menu fallback
                    ?>
                    <div class="relative group">
                        <button
                            class="flex py-5 items-center gap-1 font-semibold focus:outline-none hover:text-blue-200 transition-colors"
                            aria-expanded="false" aria-controls="menu-inst">
                            INSTITUCIONAL
                            <i class="fa-solid fa-chevron-down text-xs transition-transform group-hover:rotate-180" aria-hidden="true"></i>
                        </button>
                        <ul id="menu-inst"
                            class="absolute hidden group-hover:flex flex-col bg-white text-[#313135] top-full -mt-3 shadow-lg rounded-md overflow-hidden w-48 focus-within:flex transition-all duration-200">
                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Administração</a></li>
                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Consec</a></li>
                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Orçamentos</a></li>
                        </ul>
                    </div>

                    <div class="relative group">
                        <button
                            class="flex py-5 items-center gap-1 font-semibold focus:outline-none hover:text-blue-200 transition-colors"
                            aria-expanded="false" aria-controls="menu-acad">
                            ACADÊMICO
                            <i class="fa-solid fa-chevron-down text-xs transition-transform group-hover:rotate-180" aria-hidden="true"></i>
                        </button>
                        <ul id="menu-acad"
                            class="absolute hidden group-hover:flex flex-col bg-white text-[#313135] top-full -mt-3 shadow-lg rounded-md overflow-hidden w-48 focus-within:flex transition-all duration-200">
                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Ensino</a></li>
                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">CAs</a></li>
                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Pesquisas</a></li>
                        </ul>
                    </div>

                    <div class="relative group">
                        <button
                            class="flex py-5 items-center gap-1 font-semibold focus:outline-none hover:text-blue-200 transition-colors"
                            aria-expanded="false" aria-controls="menu-imp">
                            IMPRENSA
                            <i class="fa-solid fa-chevron-down text-xs transition-transform group-hover:rotate-180" aria-hidden="true"></i>
                        </button>
                        <ul id="menu-imp"
                            class="absolute hidden group-hover:flex flex-col bg-white text-[#313135] top-full -mt-3 shadow-lg rounded-md overflow-hidden w-48 focus-within:flex transition-all duration-200">
                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Documentos</a></li>
                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Obras</a></li>
                        </ul>
                    </div>

                <?php
                }
                ?>
            </nav>

            <!-- Botão Mobile -->
            <button id="menu-toggle"
                aria-label="<?php esc_attr_e('Abrir menu', 'cchla-ufrn'); ?>"
                aria-expanded="false"
                aria-controls="mobile-menu"
                class="lg:hidden text-2xl hover:text-blue-200 transition-colors">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
        </div>

        <!-- Menu Mobile -->
        <nav id="mobile-menu"
            class="hidden flex-col bg-white text-[#313135] shadow-lg lg:hidden transition-all duration-300"
            aria-label="<?php esc_attr_e('Menu mobile', 'cchla-ufrn'); ?>">
            <ul class="divide-y divide-gray-200">
                <?php
                if (has_nav_menu('primary')) {
                    $menu_items = wp_get_nav_menu_items(get_nav_menu_locations()['primary']);

                    if ($menu_items) {
                        $menu_structure = array();

                        foreach ($menu_items as $item) {
                            if ($item->menu_item_parent == 0) {
                                $menu_structure[$item->ID] = array(
                                    'item' => $item,
                                    'children' => array()
                                );
                            }
                        }

                        foreach ($menu_items as $item) {
                            if ($item->menu_item_parent != 0 && isset($menu_structure[$item->menu_item_parent])) {
                                $menu_structure[$item->menu_item_parent]['children'][] = $item;
                            }
                        }

                        foreach ($menu_structure as $parent_id => $parent) {
                            $has_children = !empty($parent['children']);

                            if ($has_children) :
                ?>
                                <li>
                                    <button class="w-full flex justify-between items-center px-6 py-4 font-medium text-left focus:outline-none"
                                        onclick="toggleDropdown(this)">
                                        <?php echo esc_html($parent['item']->title); ?>
                                        <i class="fa-solid fa-chevron-down transition-transform" aria-hidden="true"></i>
                                    </button>
                                    <ul class="hidden flex-col bg-gray-50">
                                        <?php foreach ($parent['children'] as $child) : ?>
                                            <li>
                                                <a href="<?php echo esc_url($child->url); ?>" class="block px-8 py-2 hover:bg-gray-100 border-l-2 border-[#002047]">
                                                    <?php echo esc_html($child->title); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php
                            else :
                            ?>
                                <li>
                                    <a href="<?php echo esc_url($parent['item']->url); ?>" class="block px-6 py-4 font-medium hover:bg-gray-100 transition">
                                        <?php echo esc_html($parent['item']->title); ?>
                                    </a>
                                </li>
                    <?php
                            endif;
                        }
                    }
                } else {
                    // Menu fallback mobile
                    ?>
                    <li>
                        <button class="w-full flex justify-between items-center px-6 py-4 font-medium text-left focus:outline-none"
                            onclick="toggleDropdown(this)">
                            Institucional
                            <i class="fa-solid fa-chevron-down transition-transform" aria-hidden="true"></i>
                        </button>
                        <ul class="hidden flex-col bg-gray-50">
                            <li><a href="#" class="block px-8 py-2 hover:bg-gray-100 border-l-2 border-[#002047]">Administração</a></li>
                            <li><a href="#" class="block px-8 py-2 hover:bg-gray-100 border-l-2 border-[#002047]">Consec</a></li>
                            <li><a href="#" class="block px-8 py-2 hover:bg-gray-100 border-l-2 border-[#002047]">Orçamentos</a></li>
                        </ul>
                    </li>
                    <li>
                        <button class="w-full flex justify-between items-center px-6 py-4 font-medium text-left focus:outline-none"
                            onclick="toggleDropdown(this)">
                            Acadêmico
                            <i class="fa-solid fa-chevron-down transition-transform" aria-hidden="true"></i>
                        </button>
                        <ul class="hidden flex-col bg-gray-50">
                            <li><a href="#" class="block px-8 py-2 hover:bg-gray-100 border-l-2 border-[#002047]">Ensino</a></li>
                            <li><a href="#" class="block px-8 py-2 hover:bg-gray-100 border-l-2 border-[#002047]">CAs</a></li>
                            <li><a href="#" class="block px-8 py-2 hover:bg-gray-100 border-l-2 border-[#002047]">Pesquisas</a></li>
                        </ul>
                    </li>
                    <li>
                        <button class="w-full flex justify-between items-center px-6 py-4 font-medium text-left focus:outline-none"
                            onclick="toggleDropdown(this)">
                            Imprensa
                            <i class="fa-solid fa-chevron-down transition-transform" aria-hidden="true"></i>
                        </button>
                        <ul class="hidden flex-col bg-gray-50">
                            <li><a href="#" class="block px-8 py-2 hover:bg-gray-100 border-l-2 border-[#002047]">Documentos</a></li>
                            <li><a href="#" class="block px-8 py-2 hover:bg-gray-100 border-l-2 border-[#002047]">Obras</a></li>
                        </ul>
                    </li>
                <?php
                }
                ?>
            </ul>
        </nav>
    </header>

    <script>
        // Toggle do menu mobile
        const menuBtn = document.getElementById("menu-toggle");
        const mobileMenu = document.getElementById("mobile-menu");

        menuBtn.addEventListener("click", () => {
            const expanded = menuBtn.getAttribute("aria-expanded") === "true";
            menuBtn.setAttribute("aria-expanded", !expanded);
            mobileMenu.classList.toggle("hidden");
            menuBtn.innerHTML = expanded ?
                '<i class="fa-solid fa-bars"></i>' :
                '<i class="fa-solid fa-xmark"></i>';
        });

        // Toggle dos submenus no mobile
        function toggleDropdown(button) {
            const submenu = button.nextElementSibling;
            const icon = button.querySelector("i");
            submenu.classList.toggle("hidden");
            icon.classList.toggle("rotate-180");
        }
    </script>

    <!-- Breadcrumb -->
    <?php cchla_breadcrumb(); ?>


    <!-- Progress Bar de Leitura -->
    <div id="reading-progress" class="fixed top-0 left-0 h-1 bg-gradient-to-r from-blue-500 to-blue-700 z-50 transition-all duration-300" style="width: 0%;" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" aria-label="Progresso de leitura"></div>

    <!-- Main Content -->
    <main id="main-content" class="bg-white page-noticia" role="main">
        <?php while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class('max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12'); ?> itemscope itemtype="https://schema.org/NewsArticle">

                <!-- Article Header -->
                <header class="mb-8 lg:mb-12">

                    <!-- Categoria Badge -->
                    <?php if ($category_name) : ?>
                        <div class="mb-4 no-print">
                            <a href="<?php echo esc_url($category_url); ?>"
                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-full text-sm font-semibold hover:bg-blue-100 transition-colors duration-200">
                                <i class="fa-solid fa-tag text-xs" aria-hidden="true"></i>
                                <span itemprop="articleSection"><?php echo esc_html($category_name); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Título -->
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 leading-tight mb-6" itemprop="headline">
                        <?php the_title(); ?>
                    </h1>

                    <!-- Subtítulo/Lead -->
                    <?php if (has_excerpt()) : ?>
                        <div class="text-lg sm:text-xl text-gray-700 leading-relaxed mb-6 font-normal" itemprop="description">
                            <?php the_excerpt(); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Meta Informações -->
                    <div class="flex flex-wrap items-center gap-4 text-sm">

                        <!-- Avatar e Autor -->
                        <div class="flex items-center gap-3" itemprop="author" itemscope itemtype="https://schema.org/Person">
                            <div class="mb-1">
                                <span class="text-gray-700 font-semibold"><?php esc_html_e('Por', 'cchla-ufrn'); ?> </span>

                                <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>" class="hover:text-blue-600 transition-colors no-print" itemprop="url">
                                    <span itemprop="name">
                                        <?php echo get_the_author_meta('display_name', get_the_author_meta('ID')); ?>
                                    </span>
                                </a>
                                <span class="print-only-inline" style="display: none;">
                                    <?php echo get_the_author_meta('display_name', get_the_author_meta('ID')); ?>
                                </span>
                                <span class="mx-2">·</span>
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>" itemprop="datePublished">
                                    <?php echo esc_html(get_the_date('d/m/Y H\hi')); ?>
                                </time>
                                <meta itemprop="dateModified" content="<?php echo esc_attr($modified_date); ?>">
                                <span class="mx-1">·</span>
                                <span class="no-print"><?php echo $reading_time; ?> min de leitura</span>
                            </div>
                        </div>

                    </div>

                </header>

                <!-- Toolbar de Ações -->
                <aside class="bg-white/95 backdrop-blur-sm border-y border-gray-200 sm:px-6 lg:px-8 py-3 mb-8" aria-label="Ações do artigo">
                    <div class="flex items-center justify-between max-w-4xl mx-auto">

                        <!-- Compartilhamento -->
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-700 mr-2 hidden sm:inline">
                                <?php esc_html_e('Compartilhar:', 'cchla-ufrn'); ?>
                            </span>

                            <!-- Facebook -->
                            <button type="button"
                                data-share="facebook"
                                class="group flex items-center  cursor-pointer justify-center w-9 h-9 rounded-full bg-[#1877F2] hover:bg-[#145dbf] text-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"
                                aria-label="<?php esc_attr_e('Compartilhar no Facebook', 'cchla-ufrn'); ?>" title="<?php esc_attr_e('Compartilhar no Facebook', 'cchla-ufrn'); ?>">
                                <i class="fa-brands fa-facebook-f text-sm" aria-hidden="true"></i>
                            </button>

                            <!-- WhatsApp -->
                            <button type="button"
                                data-share="whatsapp"
                                class="group flex items-center  cursor-pointer justify-center w-9 h-9 rounded-full bg-[#25D366] hover:bg-[#1da851] text-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2"
                                aria-label="<?php esc_attr_e('Compartilhar no WhatsApp', 'cchla-ufrn'); ?>" title="<?php esc_attr_e('Compartilhar no WhatsApp', 'cchla-ufrn'); ?>">
                                <i class="fa-brands fa-whatsapp text-base" aria-hidden="true"></i>
                            </button>

                            <!-- Twitter/X -->
                            <button type="button"
                                data-share="twitter"
                                class="group flex items-center  cursor-pointer justify-center w-9 h-9 rounded-full bg-black hover:bg-gray-800 text-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                aria-label="<?php esc_attr_e('Compartilhar no X', 'cchla-ufrn'); ?>" title="<?php esc_attr_e('Compartilhar no X', 'cchla-ufrn'); ?>">
                                <i class="fa-brands fa-x-twitter text-sm" aria-hidden="true"></i>
                            </button>

                            <!-- LinkedIn -->
                            <button type="button"
                                data-share="linkedin"
                                class="group flex items-center  cursor-pointer justify-center w-9 h-9 rounded-full bg-[#0A66C2] hover:bg-[#004182] text-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"
                                aria-label="<?php esc_attr_e('Compartilhar no LinkedIn', 'cchla-ufrn'); ?>" title="<?php esc_attr_e('Compartilhar no LinkedIn', 'cchla-ufrn'); ?>">
                                <i class="fa-brands fa-linkedin-in text-sm" aria-hidden="true"></i>
                            </button>

                            <!-- Copiar Link -->
                            <button type="button"
                                data-share="copy"
                                class="group flex items-center  cursor-pointer justify-center w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                aria-label="<?php esc_attr_e('Copiar link', 'cchla-ufrn'); ?>" title="<?php esc_attr_e('Copiar link', 'cchla-ufrn'); ?>">
                                <i class="fa-solid fa-link text-sm" aria-hidden="true"></i>
                            </button>
                        </div>

                        <!-- Ações Adicionais -->
                        <div class="flex items-center gap-2">

                            <!-- Imprimir -->
                            <button type="button"
                                onclick="window.print()"
                                class="flex items-center cursor-pointer justify-center w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                aria-label="<?php esc_attr_e('Imprimir artigo', 'cchla-ufrn'); ?>" title="<?php esc_attr_e('Imprimir artigo', 'cchla-ufrn'); ?>">
                                <i class="fa-solid fa-print text-sm" aria-hidden="true"></i>
                            </button>

                            <!-- Aumentar Fonte -->
                            <button type="button"
                                id="increase-font"
                                class="hidden sm:flex cursor-pointer items-center justify-center w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                aria-label="<?php esc_attr_e('Aumentar tamanho da fonte', 'cchla-ufrn'); ?>" title="<?php esc_attr_e('Aumentar tamanho da fonte', 'cchla-ufrn'); ?>">
                                <i class="fa-solid fa-text-height text-sm" aria-hidden="true"></i>
                            </button>

                        </div>

                    </div>
                </aside>

                <!-- Imagem Destacada -->
                <?php if (has_post_thumbnail()) : ?>
                    <figure class="mb-8 lg:mb-12 -mx-4 sm:-mx-6 lg:-mx-8" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
                        <?php
                        the_post_thumbnail('large', array(
                            'class' => 'w-full h-auto rounded-lg shadow-lg',
                            'itemprop' => 'url',
                            'loading' => 'eager',
                            'fetchpriority' => 'high'
                        ));
                        ?>
                        <?php
                        $caption = get_the_post_thumbnail_caption();
                        if ($caption) :
                        ?>
                            <figcaption class="mt-3 text-sm text-gray-600 text-center italic px-4 sm:px-6 lg:px-8" itemprop="caption">
                                <?php echo esc_html($caption); ?>
                            </figcaption>
                        <?php endif; ?>
                        <meta itemprop="width" content="1200">
                        <meta itemprop="height" content="630">
                    </figure>
                <?php endif; ?>

                <!-- Conteúdo do Artigo -->
                <div class="prose prose-lg prose-gray max-w-none 
                    prose-headings:font-bold prose-headings:text-gray-900 prose-headings:scroll-mt-24
                    prose-h2:text-2xl prose-h2:mt-12 prose-h2:mb-4
                    prose-h3:text-xl prose-h3:mt-8 prose-h3:mb-3
                    prose-p:text-gray-700 prose-p:leading-relaxed prose-p:mb-6
                    prose-a:text-blue-600 prose-a:no-underline hover:prose-a:underline
                    prose-strong:text-gray-900 prose-strong:font-semibold
                    prose-blockquote:border-l-4 prose-blockquote:border-blue-600 prose-blockquote:pl-6 prose-blockquote:italic prose-blockquote:text-gray-700
                    prose-ul:list-disc prose-ul:pl-6
                    prose-ol:list-decimal prose-ol:pl-6
                    prose-li:text-gray-700 prose-li:mb-2
                    prose-img:rounded-lg prose-img:shadow-md
                    prose-figcaption:text-sm prose-figcaption:text-gray-600 prose-figcaption:text-center prose-figcaption:italic prose-figcaption:mt-2
                    prose-pre:bg-gray-900 prose-pre:text-gray-100
                    prose-code:text-pink-600 prose-code:bg-pink-50 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:text-sm"
                    itemprop="articleBody">
                    <?php the_content(); ?>
                </div>

                <!-- Tags -->
                <?php
                $tags = get_the_tags();
                if ($tags) :
                ?>
                    <footer class="mt-12 pt-8 border-t border-gray-200">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-medium text-gray-700">
                                <i class="fa-solid fa-tags mr-1" aria-hidden="true"></i>
                                <?php esc_html_e('Tags:', 'cchla-ufrn'); ?>
                            </span>
                            <?php foreach ($tags as $tag) : ?>
                                <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"
                                    class="inline-flex items-center px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full text-sm transition-colors duration-200"
                                    rel="tag">
                                    <?php echo esc_html($tag->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </footer>
                <?php endif; ?>


                <!-- QR Code (aparece apenas na impressão) -->
                <div class="print-only" style="display: none; text-align: center; margin-top: 2cm; padding-top: 1cm; border-top: 1pt solid #999;">
                    <figure>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode(get_permalink()); ?>" alt="QR Code" style="width: 3cm; height: 3cm; margin: 0 auto;">
                        <figcaption>
                            Documento impresso em <?php echo date('d/m/Y \à\s H:i'); ?><br>
                            © <?php echo date('Y'); ?> CCHLA - UFRN. Todos os direitos reservados.
                        </figcaption>
                    </figure>
                </div>
            </article>

            <!-- Posts Relacionados -->
            <?php
            // Otimização: busca apenas 4 posts relacionados
            if (!empty($categories)) {
                $category_ids = wp_list_pluck($categories, 'term_id');

                $related_args = array(
                    'post_type' => 'post',
                    'posts_per_page' => 4,
                    'post__not_in' => array($post_id),
                    'category__in' => $category_ids,
                    'orderby' => 'rand',
                    'no_found_rows' => true, // Otimização
                    'update_post_meta_cache' => false, // Otimização
                    'update_post_term_cache' => false, // Otimização
                );

                $related_query = new WP_Query($related_args);

                if ($related_query->have_posts()) :
            ?>
                    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16" aria-labelledby="related-heading">
                        <h2 id="related-heading" class="text-3xl font-bold text-gray-900 mb-8">
                            <?php esc_html_e('Leia também', 'cchla-ufrn'); ?>
                        </h2>

                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                                <article class="group bg-white rounded-lg overflow-hidden border border-gray-200 hover:shadow-lg transition-all duration-300">

                                    <?php if (has_post_thumbnail()) : ?>
                                        <a href="<?php the_permalink(); ?>" class="block aspect-video overflow-hidden bg-gray-100">
                                            <?php the_post_thumbnail('medium', array(
                                                'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300',
                                                'loading' => 'lazy'
                                            )); ?>
                                        </a>
                                    <?php endif; ?>

                                    <div class="p-5">
                                        <div class="text-xs text-gray-500 mb-2">
                                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                                <?php echo get_the_date('d.m.Y'); ?>
                                            </time>
                                        </div>

                                        <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                            <a href="<?php the_permalink(); ?>">
                                                <?php the_title(); ?>
                                            </a>
                                        </h3>

                                        <p class="text-gray-600 line-clamp-3 mb-3" style="font-size: 14px;">
                                            <?php echo wp_trim_words(get_the_excerpt()); ?>
                                        </p>

                                        <a href="<?php the_permalink(); ?>" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 font-medium">
                                            <?php esc_html_e('Continue lendo', 'cchla-ufrn'); ?>
                                            <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </article>
                            <?php endwhile; ?>
                        </div>
                    </section>
            <?php
                endif;
                wp_reset_postdata();
            }
            ?>

        <?php endwhile; ?>
    </main>

    <!-- Botão Voltar ao Topo -->
    <button id="back-to-top"
        class="fixed bottom-6 right-6 w-12 h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg opacity-0 invisible transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 z-50"
        aria-label="<?php esc_attr_e('Voltar ao topo', 'cchla-ufrn'); ?>">
        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
    </button>



    <!-- Scripts Otimizados -->
    <script>
        (function() {
            'use strict';

            // Configurações
            const config = {
                pageUrl: encodeURIComponent(window.location.href),
                pageTitle: encodeURIComponent(document.title),
                facebookAppId: '<?php echo get_theme_mod("cchla_facebook_app_id", ""); ?>'
            };

            // ==========================================
            // 1. PROGRESS BAR DE LEITURA
            // ==========================================
            const progressBar = document.getElementById('reading-progress');
            let ticking = false;

            function updateProgressBar() {
                const winScroll = document.documentElement.scrollTop;
                const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const scrolled = (winScroll / height) * 100;

                if (progressBar) {
                    progressBar.style.width = scrolled + '%';
                    progressBar.setAttribute('aria-valuenow', Math.round(scrolled));
                }

                ticking = false;
            }

            function requestTick() {
                if (!ticking) {
                    window.requestAnimationFrame(updateProgressBar);
                    ticking = true;
                }
            }

            window.addEventListener('scroll', requestTick, {
                passive: true
            });

            // ==========================================
            // 2. COMPARTILHAMENTO SOCIAL
            // ==========================================
            const shareButtons = {
                facebook: document.querySelector('[data-share="facebook"]'),
                whatsapp: document.querySelector('[data-share="whatsapp"]'),
                twitter: document.querySelector('[data-share="twitter"]'),
                linkedin: document.querySelector('[data-share="linkedin"]'),
                copy: document.querySelector('[data-share="copy"]')
            };

            // Facebook
            if (shareButtons.facebook) {
                shareButtons.facebook.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = `https://www.facebook.com/sharer/sharer.php?u=${config.pageUrl}`;
                    openShareWindow(url, 'Facebook', 600, 400);
                    trackShare('Facebook');
                });
            }

            // WhatsApp
            if (shareButtons.whatsapp) {
                shareButtons.whatsapp.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = `https://api.whatsapp.com/send?text=${config.pageTitle}%20${config.pageUrl}`;
                    openShareWindow(url, 'WhatsApp');
                    trackShare('WhatsApp');
                });
            }

            // Twitter/X
            if (shareButtons.twitter) {
                shareButtons.twitter.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = `https://twitter.com/intent/tweet?url=${config.pageUrl}&text=${config.pageTitle}&via=cchla_ufrn`;
                    openShareWindow(url, 'Twitter', 600, 400);
                    trackShare('Twitter');
                });
            }

            // LinkedIn
            if (shareButtons.linkedin) {
                shareButtons.linkedin.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = `https://www.linkedin.com/sharing/share-offsite/?url=${config.pageUrl}`;
                    openShareWindow(url, 'LinkedIn', 600, 600);
                    trackShare('LinkedIn');
                });
            }

            // Copiar Link
            if (shareButtons.copy) {
                shareButtons.copy.addEventListener('click', async function(e) {
                    e.preventDefault();

                    try {
                        await navigator.clipboard.writeText(window.location.href);

                        // Feedback visual
                        const icon = this.querySelector('i');
                        const originalClass = icon.className;
                        icon.className = 'fa-solid fa-check text-sm';
                        this.classList.add('bg-green-500', 'text-white');
                        this.classList.remove('bg-gray-100', 'text-gray-700');

                        setTimeout(() => {
                            icon.className = originalClass;
                            this.classList.remove('bg-green-500', 'text-white');
                            this.classList.add('bg-gray-100', 'text-gray-700');
                        }, 2000);

                        trackShare('Copy Link');

                    } catch (err) {
                        console.error('Erro ao copiar:', err);
                        alert('<?php esc_html_e("Não foi possível copiar o link.", "cchla-ufrn"); ?>');
                    }
                });
            }

            function openShareWindow(url, title, width = 600, height = 400) {
                const left = (screen.width - width) / 2;
                const top = (screen.height - height) / 2;
                const params = `width=${width},height=${height},left=${left},top=${top},menubar=no,toolbar=no,status=no,scrollbars=yes`;
                window.open(url, 'share-' + title, params);
            }

            function trackShare(network) {
                // Google Analytics
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'share', {
                        'event_category': 'Social',
                        'event_label': network,
                        'value': 1
                    });
                }

                // Console para debug
                console.log('Compartilhado via:', network);
            }

            // ==========================================
            // 3. CONTROLE DE FONTE
            // ==========================================
            const increaseFontBtn = document.getElementById('increase-font');
            let currentFontSize = 1; // Multiplicador
            const fontSizes = [1, 1.125, 1.25]; // 100%, 112.5%, 125%
            let fontSizeIndex = 0;

            if (increaseFontBtn) {
                increaseFontBtn.addEventListener('click', function() {
                    fontSizeIndex = (fontSizeIndex + 1) % fontSizes.length;
                    currentFontSize = fontSizes[fontSizeIndex];

                    const articleBody = document.querySelector('[itemprop="articleBody"]');
                    if (articleBody) {
                        articleBody.style.fontSize = currentFontSize + 'rem';

                        // Salvar preferência
                        localStorage.setItem('cchla_font_size', currentFontSize);

                        // Feedback visual
                        this.classList.add('ring-2', 'ring-blue-400');
                        setTimeout(() => {
                            this.classList.remove('ring-2', 'ring-blue-400');
                        }, 300);
                    }
                });

                // Restaurar preferência salva
                const savedFontSize = localStorage.getItem('cchla_font_size');
                if (savedFontSize) {
                    const articleBody = document.querySelector('[itemprop="articleBody"]');
                    if (articleBody) {
                        articleBody.style.fontSize = savedFontSize + 'rem';
                        fontSizeIndex = fontSizes.indexOf(parseFloat(savedFontSize));
                    }
                }
            }

            // ==========================================
            // 4. BOTÃO VOLTAR AO TOPO
            // ==========================================
            const backToTopBtn = document.getElementById('back-to-top');

            if (backToTopBtn) {
                window.addEventListener('scroll', function() {
                    if (window.pageYOffset > 300) {
                        backToTopBtn.classList.remove('opacity-0', 'invisible');
                        backToTopBtn.classList.add('opacity-100', 'visible');
                    } else {
                        backToTopBtn.classList.add('opacity-0', 'invisible');
                        backToTopBtn.classList.remove('opacity-100', 'visible');
                    }
                }, {
                    passive: true
                });

                backToTopBtn.addEventListener('click', function() {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }

            // ==========================================
            // 5. LAZY LOADING DE IMAGENS (Fallback)
            // ==========================================
            if ('loading' in HTMLImageElement.prototype === false) {
                // Polyfill para navegadores antigos
                const images = document.querySelectorAll('img[loading="lazy"]');
                images.forEach(img => {
                    img.src = img.dataset.src || img.src;
                });
            }

            // ==========================================
            // 6. ÂNCORAS SUAVES (Smooth Scroll)
            // ==========================================
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href !== '#' && href !== '#!') {
                        const target = document.querySelector(href);
                        if (target) {
                            e.preventDefault();
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });

                            // Atualizar URL sem pular
                            if (history.pushState) {
                                history.pushState(null, null, href);
                            }
                        }
                    }
                });
            });

            // ==========================================
            // 7. TRACKING DE LEITURA
            // ==========================================
            let readingTime = 0;
            let readingInterval;

            // Inicia contador quando usuário começa a ler
            window.addEventListener('scroll', function startReading() {
                if (!readingInterval && window.pageYOffset > 100) {
                    readingInterval = setInterval(function() {
                        readingTime++;

                        // Envia evento a cada minuto
                        if (readingTime % 60 === 0) {
                            if (typeof gtag !== 'undefined') {
                                gtag('event', 'reading_time', {
                                    'event_category': 'Engagement',
                                    'event_label': 'Minutes Read',
                                    'value': readingTime / 60
                                });
                            }
                        }
                    }, 1000);

                    // Remove listener após iniciar
                    window.removeEventListener('scroll', startReading);
                }
            }, {
                passive: true
            });

            // Envia tempo total ao sair da página
            window.addEventListener('beforeunload', function() {
                if (readingTime > 10 && typeof gtag !== 'undefined') {
                    gtag('event', 'total_reading_time', {
                        'event_category': 'Engagement',
                        'event_label': 'Total Seconds',
                        'value': readingTime
                    });
                }
            });

            // ==========================================
            // 8. OTIMIZAÇÕES DE PERFORMANCE
            // ==========================================

            // Prefetch de links relacionados
            const relatedLinks = document.querySelectorAll('article a[href^="<?php echo esc_url(home_url('/')); ?>"]');
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const link = entry.target;
                        const prefetchLink = document.createElement('link');
                        prefetchLink.rel = 'prefetch';
                        prefetchLink.href = link.href;
                        document.head.appendChild(prefetchLink);
                        observer.unobserve(link);
                    }
                });
            }, {
                rootMargin: '50px'
            });

            relatedLinks.forEach(link => observer.observe(link));

            // ==========================================
            // 9. ACESSIBILIDADE - DETECÇÃO DE MODO ESCURO
            // ==========================================
            const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');

            function handleDarkMode(e) {
                if (e.matches) {
                    // Usuário prefere modo escuro
                    console.log('Modo escuro detectado');
                    // Aqui você pode adicionar classes ou estilos para modo escuro
                }
            }

            darkModeQuery.addListener(handleDarkMode);
            handleDarkMode(darkModeQuery);

            // ==========================================
            // 10. SERVICE WORKER (PWA) - Opcional
            // ==========================================
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('<?php echo get_template_directory_uri(); ?>/sw.js')
                        .then(function(registration) {
                            console.log('ServiceWorker registrado:', registration);
                        })
                        .catch(function(error) {
                            console.log('Erro ao registrar ServiceWorker:', error);
                        });
                });
            }

        })();
    </script>

    <?php wp_footer(); ?>

    <?php get_footer(); ?>