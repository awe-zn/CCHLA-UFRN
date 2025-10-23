

const carrossel = document.getElementById('carrossel');
const slides = carrossel.children.length;
let index = 0;

document.getElementById('nextbtn').addEventListener('click', function() {
    index = (index + 1) % slides;
    carrossel.style.transform = `translateX(-${index * 100}%)`;
})
document.getElementById('prevbtn').addEventListener('click', function() {
    index = (index - 1 + slides) % slides;
    carrossel.style.transform = `translateX(-${index * 100}%)`;
})