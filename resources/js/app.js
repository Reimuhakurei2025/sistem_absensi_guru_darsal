import './bootstrap';

// ====== ALPINE.JS (untuk flash message auto-dismiss & UI kecil) ======
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// ====== SIDEBAR TOGGLE (mobile-first) ======
document.addEventListener('DOMContentLoaded', () => {
    const sidebar     = document.getElementById('sidebar');
    const overlay     = document.getElementById('sidebar-overlay');
    const btnOpen     = document.getElementById('btn-sidebar-open');
    const btnClose    = document.getElementById('btn-sidebar-close');

    const openSidebar = () => {
        sidebar?.classList.remove('-translate-x-full');
        overlay?.classList.remove('hidden');
    };

    const closeSidebar = () => {
        sidebar?.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
    };

    btnOpen?.addEventListener('click', openSidebar);
    btnClose?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // Tutup sidebar saat user klik link nav (mobile)
    document.querySelectorAll('.sidebar-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) closeSidebar();
        });
    });
});
