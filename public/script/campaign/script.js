document.addEventListener("DOMContentLoaded", (event) => {
  init();
});

function init() {
  const buttons = document.getElementsByClassName('btn-delete');

  for(let button of buttons) {
    button.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();

      window.location.href = button.dataset.href;
    })
  }
}
