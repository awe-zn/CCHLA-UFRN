<?php

/**
 * Header Template
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

// Variáveis para SEO dinâmico
$site_name = get_bloginfo('name');
$site_description = get_bloginfo('description');
$page_title = wp_get_document_title();
$current_url = home_url(add_query_arg(array(), $GLOBALS['wp']->request));
$site_assets = get_template_directory_uri();

// Meta description dinâmica
if (is_singular()) {
    $meta_description = has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 30, '...');
    $meta_description = wp_strip_all_tags($meta_description);
} elseif (is_category() || is_tag() || is_tax()) {
    $term = get_queried_object();
    $meta_description = $term->description ? $term->description : sprintf(__('Confira os posts sobre %s', 'cchla-ufrn'), $term->name);
} else {
    $meta_description = $site_description;
}

// Imagem para compartilhamento
if (is_singular() && has_post_thumbnail()) {
    $og_image = get_the_post_thumbnail_url(get_the_ID(), 'large');
} else {
    $og_image = get_theme_mod('cchla_default_share_image', $site_assets . '/assets/icons/android-chrome-512x512.png');
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    // Meta tags básicas
    $descricao = get_theme_mod('cchla_descricao_curta', 'Ensino, pesquisa, cultura e extensão');
    ?>

    <!-- Primary Meta Tags -->
    <meta name="description" content="<?php echo esc_attr($descricao); ?>">
    <meta name="author" content="<?php echo esc_attr(get_theme_mod('cchla_nome_completo', 'CCHLA - UFRN')); ?>">
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="<?php echo esc_url($current_url); ?>" />


    <!-- Favicons -->
    <link rel="icon" href="<?php echo $site_assets; ?>/assets/icons/favicon.svg" type="image/svg+xml" />
    <link rel="alternate icon" href="<?php echo $site_assets; ?>/assets/icons/favicon.ico" sizes="any" />

    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" sizes="120x120" href="<?php echo $site_assets; ?>/assets/icons/apple-touch-icon-120.png" />
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo $site_assets; ?>/assets/icons/apple-touch-icon-152.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $site_assets; ?>/assets/icons/apple-touch-icon-180.png" />
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?php echo $site_assets; ?>/assets/icons/site.webmanifest" />
    <meta name="theme-color" content="#193CB8" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo esc_url(home_url('/')); ?>">
    <meta property="og:title" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
    <meta property="og:description" content="<?php echo esc_attr($descricao); ?>">
    <?php
    $share_image_id = get_theme_mod('cchla_default_share_image', '');
    if ($share_image_id) {
        $share_image = wp_get_attachment_image_url($share_image_id, 'full');
        if ($share_image) {
            echo '<meta property="og:image" content="' . esc_url($share_image) . '">';
        }
    }

    $fb_app_id = get_theme_mod('cchla_facebook_app_id', '');
    if ($fb_app_id) {
        echo '<meta property="fb:app_id" content="' . esc_attr($fb_app_id) . '">';
    }
    ?>


    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo esc_url(home_url('/')); ?>">
    <meta property="twitter:title" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
    <meta property="twitter:description" content="<?php echo esc_attr($descricao); ?>">
    <?php if ($share_image_id && $share_image) : ?>
        <meta property="twitter:image" content="<?php echo esc_url($share_image); ?>">
    <?php endif; ?>

    <!-- Preconnect para otimização -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

    <!-- Tailwind Browser -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/style.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/page.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/noticias.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Raleway:ital,wght@0,100..900;1,100..900&family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&family=Tiny5&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>

    <!-- Custom Scripts -->
    <script defer src="<?php echo get_template_directory_uri(); ?>/assets/scripts/script.js"></script>


    <?php wp_head(); ?>
</head>

<body <?php body_class('font-[Inter]'); ?>>
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