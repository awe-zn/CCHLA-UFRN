// Adicione em assets/js/search-autocomplete.js

(function($) {
    'use strict';
    
    $(document).ready(function() {
        var searchInput = $('.search-form input[type="search"]');
        var suggestionsBox = $('<div class="search-suggestions"></div>');
        
        searchInput.parent().append(suggestionsBox);
        
        var searchTimeout;
        
        searchInput.on('input', function() {
            var term = $(this).val();
            
            clearTimeout(searchTimeout);
            
            if (term.length < 3) {
                suggestionsBox.hide();
                return;
            }
            
            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: cchlaSearch.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cchla_search_suggestions',
                        term: term,
                        nonce: cchlaSearch.nonce
                    },
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            var html = '<ul class="suggestion-list">';
                            
                            response.data.forEach(function(item) {
                                html += '<li><a href="' + item.url + '">';
                                html += '<span class="suggestion-type">' + item.type + '</span>';
                                html += '<span class="suggestion-title">' + item.title + '</span>';
                                html += '</a></li>';
                            });
                            
                            html += '</ul>';
                            
                            suggestionsBox.html(html).show();
                        } else {
                            suggestionsBox.hide();
                        }
                    }
                });
            }, 300);
        });
        
        // Fechar sugestões ao clicar fora
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-form').length) {
                suggestionsBox.hide();
            }
        });
    });
    
})(jQuery);