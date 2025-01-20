document.addEventListener("DOMContentLoaded", (event) => {
  init();
});

function init() {
  const button = document.getElementById('btn-delete');
  
  button.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();

    window.location.href = button.dataset.href;
  })
}
