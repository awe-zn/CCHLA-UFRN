$('.drop-down').click(function() {
    const drop = $(this).siblings('.drop');

    drop.toggleClass('hidden')
});

function show_menu() {
    const menu = document.querySelector('.menu-mobile')

    menu.classList.toggle('hidden')
}
document.addEventListener('DOMContentLoaded', () => {
    const carousel = document.getElementById('carousel');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    let currentIndex = 0;
    const totalCards = carousel.children.length;

    function updateCarousel() {
        carousel.style.transform = `translateX(-${currentIndex * 100}%)`;
    }

    nextBtn.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % totalCards;
        updateCarousel();
    });

    prevBtn.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + totalCards) % totalCards;
        updateCarousel();
    });

            // Swipe
    let startX = 0;
    const container = carousel.parentElement;
    container.addEventListener('touchstart', e => startX = e.touches[0].clientX);
    container.addEventListener('touchend', e => {
        const endX = e.changedTouches[0].clientX;
        const diff = startX - endX;
        if (Math.abs(diff) > 50) {
            diff > 0 ? nextBtn.click() : prevBtn.click();
        }
    });

    updateCarousel();
});