const carrossel = document.getElementById('carrossel');
const card = carrossel.firstElementChild;
const slides = carrossel.children.length;
let index = 0;

function getCardWidth() {
  let card_perc = window.getComputedStyle(card).minWidth;
  return parseFloat(card_perc.replace('%', ''));
}

function getRowGap() {
  let gap_px = window.getComputedStyle(carrossel).rowGap;
  return parseFloat(gap_px.replace('px', ''));
}

function moverCarrossel(direcao) {
  let card_width = getCardWidth();
  let gap_px = getRowGap();
  let containerWidth = carrossel.clientWidth;

  let gap_perc = (gap_px / containerWidth) * 100;

  let cardsPorVez = card_width >= 100 ? 1 : 2;

  index += direcao * cardsPorVez;

  if (index < 0) index = 0;
  if (index > slides - cardsPorVez) index = slides - cardsPorVez;

  let deslocamento = index * (card_width + gap_perc);

  carrossel.style.transform = `translateX(-${deslocamento}%)`;
}

document.getElementById('nextbtn').addEventListener('click', () => moverCarrossel(1));
document.getElementById('prevbtn').addEventListener('click', () => moverCarrossel(-1));
