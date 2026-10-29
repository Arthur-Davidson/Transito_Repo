document.addEventListener('DOMContentLoaded', () => {
  const boton = document.getElementById('abrirMenu');
  const menu = document.getElementById('menuPrincipal');
  if (boton && menu) boton.addEventListener('click', () => {
    const abierto = boton.getAttribute('aria-expanded') !== 'true';
    boton.setAttribute('aria-expanded', String(abierto));
    menu.classList.toggle('show', abierto);
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.querySelectorAll('details.gestion').forEach(d => d.open = false);
      if (boton && menu) { menu.classList.remove('show'); boton.setAttribute('aria-expanded', 'false'); }
    }
  });
});
