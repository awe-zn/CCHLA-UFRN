jQuery(function($){
  const btn = $('#load-more');
  if (!btn.length) return;

  let nextPage = parseInt(btn.data('next-page'), 10) || 2;
  const max     = parseInt(btn.data('max-pages'), 10) || 1;
  const exclude = (btn.data('exclude') || '').toString();
  const cat     = parseInt(btn.data('cat'), 10) || 0;
  const tag     = parseInt(btn.data('tag'), 10) || 0;
  const author  = parseInt(btn.data('author'), 10) || 0;

  let busy = false;

  btn.on('click', function(){
    if (busy) return;
    if (nextPage > max) { btn.hide(); return; }

    busy = true;
    btn.find('i').addClass('animate-spin');

    $.post(loadmore.ajaxurl, {
      action: 'mytheme_load_more',
      nonce:  loadmore.nonce,
      page:   nextPage,
      exclude: exclude,
      cat:    cat,
      tag:    tag,
      author: author
    })
    .done(function(html){
      html = (html || '').trim();
      if (html.length) {
        $('#post-container').append(html);
        nextPage++;
        if (nextPage > max) btn.hide();
      } else {
        btn.hide();
      }
    })
    .always(function(){
      btn.find('i').removeClass('animate-spin');
      busy = false;
    });
  });
});
