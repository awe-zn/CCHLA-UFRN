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