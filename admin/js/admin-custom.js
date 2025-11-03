/**
 * ============================================
 * JAVASCRIPT CUSTOMIZADO - ADMIN CCHLA
 * ============================================
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // ===== ADICIONA LOGO NO MENU LATERAL =====
        if ($('#adminmenu').length) {
            var logoHtml = '<li class="cchla-admin-logo" style="padding: 20px; text-align: center; border-bottom: 2px solid rgba(255,255,255,0.1);">';
            logoHtml += '<img src="' + cchlaAdmin.themeUrl + '/assets/img/logo-admin.svg" alt="CCHLA" style="max-width: 120px; height: auto;">';
            logoHtml += '</li>';
            
            $('#adminmenu').prepend(logoHtml);
        }
        
        // ===== MENSAGEM DE BOAS-VINDAS PERSONALIZADA =====
        if ($('.welcome-panel').length && cchlaAdmin.showWelcome) {
            var welcomeMessage = '<div class="cchla-welcome-message" style="background: white; padding: 20px; border-radius: 8px; margin-top: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">';
            welcomeMessage += '<h3 style="color: #2E3CB9; margin-top: 0;">👋 Bem-vindo ao Painel CCHLA!</h3>';
            welcomeMessage += '<p>Gerencie o conteúdo do site do Centro de Ciências Humanas, Letras e Artes.</p>';
            welcomeMessage += '<div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px;">';
            welcomeMessage += '<a href="' + cchlaAdmin.adminUrl + 'post-new.php?post_type=noticias" class="button button-primary">Nova Notícia</a>';
            welcomeMessage += '<a href="' + cchlaAdmin.adminUrl + 'post-new.php?post_type=publicacoes" class="button button-primary">Nova Publicação</a>';
            welcomeMessage += '<a href="' + cchlaAdmin.adminUrl + 'post-new.php?post_type=especiais" class="button button-primary">Novo Especial</a>';
            welcomeMessage += '<a href="' + cchlaAdmin.siteUrl + '" class="button button-secondary" target="_blank">Ver Site</a>';
            welcomeMessage += '</div>';
            welcomeMessage += '</div>';
            
            $('.welcome-panel').after(welcomeMessage);
        }
        
        // ===== SMOOTH SCROLL NAS ÂNCORAS =====
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.getAttribute('href'));
            if(target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        });
        
        // ===== CONFIRMAÇÃO DE EXCLUSÃO MELHORADA =====
        $('.submitdelete').on('click', function(e) {
            if (!$(this).hasClass('cchla-confirmed')) {
                e.preventDefault();
                var $this = $(this);
                
                if (confirm('⚠️ Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.')) {
                    $this.addClass('cchla-confirmed');
                    $this[0].click();
                }
            }
        });
        
        // ===== PREVIEW DE IMAGENS NOS METABOXES =====
        $('.cchla-image-upload').each(function() {
            var $button = $(this);
            var $input = $button.prev('input');
            var $preview = $button.next('.cchla-image-preview');
            
            $button.on('click', function(e) {
                e.preventDefault();
                
                var mediaUploader = wp.media({
                    title: 'Escolher Imagem',
                    button: {
                        text: 'Usar esta imagem'
                    },
                    multiple: false
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $input.val(attachment.id);
                    $preview.html('<img src="' + attachment.url + '" style="max-width: 200px; height: auto; border-radius: 8px; margin-top: 10px;">');
                });
                
                mediaUploader.open();
            });
        });
        
        // ===== AUTO-SAVE INDICATOR =====
        $(document).on('heartbeat-tick.autosave', function() {
            var $indicator = $('.cchla-autosave-indicator');
            if (!$indicator.length) {
                $indicator = $('<div class="cchla-autosave-indicator" style="position: fixed; bottom: 20px; right: 20px; background: #00a32a; color: white; padding: 10px 20px; border-radius: 50px; font-size: 14px; z-index: 9999; display: none;">✓ Salvo automaticamente</div>');
                $('body').append($indicator);
            }
            
            $indicator.fadeIn().delay(2000).fadeOut();
        });
        
        // ===== TABS MELHORADOS =====
        $('.nav-tab').on('click', function(e) {
            var $tab = $(this);
            if (!$tab.hasClass('nav-tab-active')) {
                $tab.css('transform', 'scale(0.95)');
                setTimeout(function() {
                    $tab.css('transform', 'scale(1)');
                }, 100);
            }
        });
        
        // ===== HIGHLIGHT DE CAMPOS OBRIGATÓRIOS VAZIOS =====
        $('form').on('submit', function() {
            var hasEmpty = false;
            
            $(this).find('[required]').each(function() {
                if (!$(this).val()) {
                    $(this).css('border-color', '#dc3232');
                    hasEmpty = true;
                } else {
                    $(this).css('border-color', '');
                }
            });
            
            if (hasEmpty) {
                alert('⚠️ Por favor, preencha todos os campos obrigatórios.');
            }
        });
        
        // ===== COPY TO CLIPBOARD =====
        $('.cchla-copy-btn').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var text = $btn.data('copy');
            
            navigator.clipboard.writeText(text).then(function() {
                var originalText = $btn.text();
                $btn.text('✓ Copiado!');
                setTimeout(function() {
                    $btn.text(originalText);
                }, 2000);
            });
        });
        
        // ===== DARK MODE TOGGLE (OPCIONAL) =====
        if (localStorage.getItem('cchla-dark-mode') === 'enabled') {
            $('body').addClass('cchla-dark-mode');
        }
        
        $('#cchla-toggle-dark-mode').on('click', function(e) {
            e.preventDefault();
            $('body').toggleClass('cchla-dark-mode');
            
            if ($('body').hasClass('cchla-dark-mode')) {
                localStorage.setItem('cchla-dark-mode', 'enabled');
            } else {
                localStorage.removeItem('cchla-dark-mode');
            }
        });
        
    });
    
})(jQuery);