function updateFooterYear() {
    const currentYear = new Date();
    const yearElement = document.getElementById('footer-year');
    
    if (yearElement) {
        yearElement.textContent = currentYear.getFullYear();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    updateFooterYear();
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateFooterYear);
} else {
    updateFooterYear();
}
