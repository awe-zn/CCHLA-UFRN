$('.drop-down').click(function() {
    const drop = $(this).siblings('.drop');

    drop.toggleClass('hidden')
});

function show_menu() {
    const menu = document.querySelector('.menu-mobile')

    menu.classList.toggle('hidden')
}