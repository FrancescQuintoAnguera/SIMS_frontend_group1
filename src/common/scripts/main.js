document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('my-sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => {
            sidebar.abrir();
        });
    }

    document.addEventListener('menu-action', (event) => {
        const { action } = event.detail;
        console.log('Acción de menú:', action);
    });

    document.addEventListener('footer-action', (event) => {
        const { action } = event.detail;
        console.log('Acción de footer:', action);
    });
});
