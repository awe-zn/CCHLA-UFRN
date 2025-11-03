<?php

/**
 * Single Post Template
 *
 * @package CCHLA_UFRN
 * @since 1.0.0
 */

// Variáveis para SEO
$site_name = get_bloginfo('name');
$post_title = get_the_title();
$post_excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 30, '...');
$post_excerpt = wp_strip_all_tags($post_excerpt);
$post_url = get_permalink();
$post_image = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : get_template_directory_uri() . '/assets/icons/android-chrome-512x512.png';
$author_name = get_the_author();
$categories = get_the_category();
$category_name = !empty($categories) ? $categories[0]->name : '';
$published_date = get_the_date('c');
$modified_date = get_the_modified_date('c');
$site_assets = get_template_directory_uri();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO Meta Tags -->
    <title><?php echo esc_html($post_title . ' | ' . $site_name); ?></title>
    <meta name="description" content="<?php echo esc_attr($post_excerpt); ?>">
    <meta name="author" content="<?php echo esc_attr($author_name); ?>">
    <link rel="canonical" href="<?php echo esc_url($post_url); ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo esc_url($post_url); ?>">
    <meta property="og:title" content="<?php echo esc_attr($post_title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($post_excerpt); ?>">
    <meta property="og:image" content="<?php echo esc_url($post_image); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>">
    <meta property="og:locale" content="pt_BR">
    <meta property="article:published_time" content="<?php echo esc_attr($published_date); ?>">
    <meta property="article:modified_time" content="<?php echo esc_attr($modified_date); ?>">
    <meta property="article:author" content="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">
    <?php if ($category_name) : ?>
        <meta property="article:section" content="<?php echo esc_attr($category_name); ?>">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo esc_url($post_url); ?>">
    <meta name="twitter:title" content="<?php echo esc_attr($post_title); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($post_excerpt); ?>">
    <meta name="twitter:image" content="<?php echo esc_url($post_image); ?>">

    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Article",
            "headline": "<?php echo esc_js($post_title); ?>",
            "description": "<?php echo esc_js($post_excerpt); ?>",
            "image": "<?php echo esc_url($post_image); ?>",
            "datePublished": "<?php echo esc_attr($published_date); ?>",
            "dateModified": "<?php echo esc_attr($modified_date); ?>",
            "author": {
                "@type": "Person",
                "name": "<?php echo esc_js($author_name); ?>",
                "url": "<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"
            },
            "publisher": {
                "@type": "Organization",
                "name": "<?php echo esc_js($site_name); ?>",
                "logo": {
                    "@type": "ImageObject",
                    "url": "<?php echo esc_url(get_template_directory_uri() . '/assets/img/logo.svg'); ?>"
                }
            },
            "mainEntityOfPage": {
                "@type": "WebPage",
                "@id": "<?php echo esc_url($post_url); ?>"
            }
        }
    </script>

    <!-- Favicon -->
    <link rel="icon" href="<?php echo $site_assets; ?>/assets/icons/favicon.svg" type="image/svg+xml">
    <link rel="alternate icon" href="<?php echo $site_assets; ?>/assets/icons/favicon.ico" sizes="any">

    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" sizes="120x120" href="<?php echo $site_assets; ?>/assets/icons/apple-touch-icon-120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo $site_assets; ?>/assets/icons/apple-touch-icon-152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $site_assets; ?>/assets/icons/apple-touch-icon-180.png">

    <!-- Manifest -->
    <link rel="manifest" href="<?php echo $site_assets; ?>/assets/icons/site.webmanifest">
    <meta name="theme-color" content="#193CB8">

    <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $site_assets; ?>/assets/css/noticias.css">

    <?php wp_head(); ?>
</head>

<body <?php body_class('bg-white page-noticia'); ?>>
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

    <?php get_template_part('parts/extra/template-parts/breadcrumb'); ?>

    <!-- Main Content -->
    <main class="py-10 lg:py-12">
        <?php while (have_posts()) : the_post(); ?>

            <article class="container-news">
                <header class="mb-6">

                    <!-- Título Principal -->
                    <h1 class="text-3xl lg:text-5xl font-bold mb-4 leading-tight">
                        <?php the_title(); ?>
                    </h1>

                    <!-- Subtítulo/Lead -->
                    <?php if (has_excerpt()) : ?>
                        <p class="text-base md:text-lg text-gray-700 leading-relaxed mb-6 font-normal">
                            <?php the_excerpt(); ?>
                        </p>
                    <?php endif; ?>

                    <!-- Metadados: Autoria e Data -->
                    <div class="text-sm text-gray-500 leading-relaxed pt-5">
                        <div class="mb-1">
                            <span class="text-gray-700 font-semibold"><?php esc_html_e('Por', 'cchla-ufrn'); ?> </span>
                            <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"
                                class="text-[#1B4D9E] hover:underline font-semibold">
                                <?php the_author(); ?>
                            </a>
                            <span class="mx-2">·</span>
                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                <?php echo esc_html(get_the_date('d/m/Y H\hi')); ?>
                            </time>
                        </div>
                    </div>

                </header>

                <!-- Barra de Compartilhamento -->
                <div class="border-y border-gray-200 mb-4">
                    <div class="max-w-screen-xl mx-auto px-4 lg:px-6 py-3">
                        <div class="flex items-center gap-3">
                            <a href="#"
                                data-share="facebook"
                                aria-label="<?php esc_attr_e('Compartilhar no Facebook', 'cchla-ufrn'); ?>"
                                class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-[#1877F2] hover:bg-[#145dbf] text-white transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <i class="fa-brands fa-facebook-f text-sm" aria-hidden="true"></i>
                            </a>
                            <a href="#"
                                data-share="whatsapp"
                                aria-label="<?php esc_attr_e('Compartilhar no WhatsApp', 'cchla-ufrn'); ?>"
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-[#25D366] hover:bg-[#1da851] text-white transition-colors focus:outline-none focus:ring-2 focus:ring-green-400">
                                <i class="fa-brands fa-whatsapp text-lg" aria-hidden="true"></i>
                            </a>
                            <a href="#"
                                data-share="copy"
                                aria-label="<?php esc_attr_e('Compartilhar link', 'cchla-ufrn'); ?>"
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-300 hover:bg-gray-400 text-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-400">
                                <i class="fa-solid fa-share-nodes text-sm" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo do Post -->
                <div class="post-content p-8 md:p-8 sm:px-8 sm:py-8">
                    <?php the_content(); ?>
                </div>

                <!-- Posts Relacionados -->
                <?php
                // Busca posts relacionados pela mesma categoria
                if (!empty($categories)) {
                    $category_ids = array();
                    foreach ($categories as $category) {
                        $category_ids[] = $category->term_id;
                    }

                    $related_args = array(
                        'post_type' => 'post',
                        'posts_per_page' => 4,
                        'post__not_in' => array(get_the_ID()),
                        'category__in' => $category_ids,
                        'orderby' => 'rand',
                    );

                    $related_query = new WP_Query($related_args);

                    if ($related_query->have_posts()) :
                ?>
                        <aside class="related-posts mt-12 pt-8 border-t border-gray-200">
                            <div class="max-w-screen-xl mx-auto px-4 lg:px-6">
                                <h2 class="text-2xl font-bold text-gray-900 mb-8">
                                    <?php esc_html_e('posts relacionados', 'cchla-ufrn'); ?>
                                </h2>

                                <div class="space-y-8">
                                    <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>

                                        <!-- Post Relacionado -->
                                        <article class="pb-6 border-b border-gray-100 last:border-0">
                                            <div class="mb-2">
                                                <span class="text-sm text-gray-500"><?php esc_html_e('por', 'cchla-ufrn'); ?> </span>
                                                <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"
                                                    class="text-sm text-blue-600 hover:underline font-medium">
                                                    <?php the_author(); ?>
                                                </a>
                                                <span class="text-sm text-gray-500"><?php esc_html_e(', publicado em', 'cchla-ufrn'); ?> </span>
                                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"
                                                    class="text-sm text-gray-500">
                                                    <?php echo esc_html(get_the_date('d.M.Y')); ?>
                                                </time>
                                            </div>
                                            <h3 class="text-xl font-semibold leading-tight">
                                                <a href="<?php the_permalink(); ?>"
                                                    class="text-blue-700 hover:text-blue-900 hover:underline transition-colors">
                                                    <?php the_title(); ?>
                                                </a>
                                            </h3>
                                        </article>

                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </aside>
                <?php
                    endif;
                    wp_reset_postdata();
                }
                ?>

            </article>

        <?php endwhile; ?>
    </main>

    <?php get_footer(); ?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const pageUrl = encodeURIComponent(window.location.href);
            const pageTitle = encodeURIComponent(document.title);

            // Facebook
            const fbShare = document.querySelector('[data-share="facebook"]');
            if (fbShare) {
                fbShare.addEventListener('click', function(e) {
                    e.preventDefault();
                    const fbUrl = `https://www.facebook.com/sharer/sharer.php?u=${pageUrl}`;
                    window.open(fbUrl, '_blank', 'width=600,height=400');
                });
            }

            // WhatsApp
            const waShare = document.querySelector('[data-share="whatsapp"]');
            if (waShare) {
                waShare.addEventListener('click', function(e) {
                    e.preventDefault();
                    const waUrl = `https://api.whatsapp.com/send?text=${pageTitle}%20${pageUrl}`;
                    window.open(waUrl, '_blank');
                });
            }

            // Copiar link
            const copyShare = document.querySelector('[data-share="copy"]');
            if (copyShare) {
                copyShare.addEventListener('click', async function(e) {
                    e.preventDefault();
                    try {
                        await navigator.clipboard.writeText(window.location.href);
                        alert("<?php esc_html_e('Link copiado para a área de transferência!', 'cchla-ufrn'); ?>");
                    } catch (err) {
                        console.error("Erro ao copiar o link:", err);
                        alert("<?php esc_html_e('Não foi possível copiar o link.', 'cchla-ufrn'); ?>");
                    }
                });
            }
        });

        // Toggle do menu mobile
        const menuBtn = document.getElementById("menu-toggle");
        const mobileMenu = document.getElementById("mobile-menu");

        if (menuBtn && mobileMenu) {
            menuBtn.addEventListener("click", () => {
                const expanded = menuBtn.getAttribute("aria-expanded") === "true";
                menuBtn.setAttribute("aria-expanded", !expanded);
                mobileMenu.classList.toggle("hidden");
                menuBtn.innerHTML = expanded ?
                    '<i class="fa-solid fa-bars"></i>' :
                    '<i class="fa-solid fa-xmark"></i>';
            });
        }

        // Toggle dos submenus no mobile
        function toggleDropdown(button) {
            const submenu = button.nextElementSibling;
            const icon = button.querySelector("i");
            submenu.classList.toggle("hidden");
            icon.classList.toggle("rotate-180");
        }
    </script>

    <?php wp_footer(); ?>

</body>

</html>