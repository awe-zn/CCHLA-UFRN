/**
 * ============================================
 * JAVASCRIPT DA PÁGINA DE LOGIN - CCHLA
 * ============================================
 */

(function() {
    'use strict';
    
    // Aguarda o carregamento do DOM
    document.addEventListener('DOMContentLoaded', function() {
        
        // ===== ADICIONA TÍTULO E SUBTÍTULO =====
        addLoginTitle();
        
        // ===== ADICIONA FORMA DECORATIVA =====
        addDecorativeShape();
        
        // ===== ADICIONA RODAPÉ =====
        addLoginFooter();
        
        // ===== MELHORA O FEEDBACK DO BOTÃO =====
        enhanceSubmitButton();
        
        // ===== ADICIONA ÍCONE DE USUÁRIO NO INPUT =====
        addUserIcon();
        
        // ===== ANIMAÇÃO DE FOCO NOS INPUTS =====
        enhanceInputFocus();
        
        // ===== TECLA ENTER PARA SUBMIT =====
        enhanceEnterKey();
        
    });
    
    /**
     * Adiciona título acima do formulário
     */
    function addLoginTitle() {
        var loginH1 = document.querySelector('#login h1');
        if (!loginH1) return;
        
        var titleDiv = document.createElement('div');
        titleDiv.className = 'cchla-login-title';
        titleDiv.innerHTML = '<h2>Bem-vindo ao CCHLA</h2><p>Faça login para acessar o painel administrativo</p>';
        
        loginH1.parentNode.insertBefore(titleDiv, loginH1.nextSibling);
    }
    
    /**
     * Adiciona forma decorativa
     */
    function addDecorativeShape() {
        var body = document.querySelector('body.login');
        if (!body) return;
        
        var shape = document.createElement('div');
        shape.className = 'cchla-shape-decoration';
        body.appendChild(shape);
    }
    
    /**
     * Adiciona rodapé
     */
    function addLoginFooter() {
        var body = document.querySelector('body.login');
        if (!body) return;
        
        var footer = document.createElement('div');
        footer.className = 'cchla-login-footer';
        footer.innerHTML = '© ' + new Date().getFullYear() + ' CCHLA - UFRN | <a href="https://cchla.ufrn.br" target="_blank">cchla.ufrn.br</a>';
        body.appendChild(footer);
    }
    
    /**
     * Melhora o feedback do botão de submit
     */
    function enhanceSubmitButton() {
        var submitBtn = document.querySelector('#wp-submit');
        if (!submitBtn) return;
        
        var form = submitBtn.closest('form');
        if (!form) return;
        
        form.addEventListener('submit', function() {
            submitBtn.classList.add('loading');
            submitBtn.value = 'Entrando...';
        });
    }
    
    /**
     * Adiciona ícone de usuário no input
     */
    function addUserIcon() {
        var userInput = document.querySelector('#user_login');
        if (!userInput) return;
        
        var wrapper = userInput.parentElement;
        if (!wrapper.classList.contains('user-email-wrap')) {
            wrapper.classList.add('user-email-wrap');
        }
        
        // Adiciona ícone via CSS ::before
        var style = document.createElement('style');
        style.textContent = `
            .user-email-wrap::before {
                content: '👤';
                position: absolute;
                left: 18px;
                top: 50%;
                transform: translateY(-50%);
                font-size: 16px;
                pointer-events: none;
                z-index: 1;
            }
            #user_login {
                padding-left: 48px !important;
            }
        `;
        document.head.appendChild(style);
    }
    
    /**
     * Animação de foco nos inputs
     */
    function enhanceInputFocus() {
        var inputs = document.querySelectorAll('input[type="text"], input[type="password"], input[type="email"]');
        
        inputs.forEach(function(input) {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    }
    
    /**
     * Enter para submit
     */
    function enhanceEnterKey() {
        var inputs = document.querySelectorAll('#loginform input, #registerform input, #lostpasswordform input');
        
        inputs.forEach(function(input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && input.type !== 'submit') {
                    e.preventDefault();
                    var submitBtn = input.closest('form').querySelector('input[type="submit"]');
                    if (submitBtn) {
                        submitBtn.click();
                    }
                }
            });
        });
    }
    
    /**
     * Particles.js (Opcional - efeito de partículas)
     */
    function addParticles() {
        // Implementação opcional de partículas flutuantes
        // Requer biblioteca particles.js
    }
    
})();