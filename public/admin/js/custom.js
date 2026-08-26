document.addEventListener('DOMContentLoaded', function () {

    // Sidebar toggle
    // const toggleBtn = document.getElementById('toggleSidebar');
    // if (toggleBtn) {
    //     toggleBtn.addEventListener('click', function () {
    //         document.querySelector('.sidebar').classList.toggle('active');
    //     });
    // }
    const toggleBtn = document.getElementById('toggleSidebar');
    const toggleBtnMobile = document.getElementById('toggleSidebarMobile');
    const closeBtn = document.getElementById('closeSidebar');
    const sidebar = document.querySelector('.sidebar');

    // apply saved state
    sidebar.classList.toggle(
        'active',
        localStorage.getItem('sidebarState') === 'active'
    );

    const handleToggleClick = () => {
        sidebar.classList.toggle('active');
        localStorage.setItem(
            'sidebarState',
            sidebar.classList.contains('active') ? 'active' : 'inactive'
        );
    };

    toggleBtn?.addEventListener('click', handleToggleClick);
    toggleBtnMobile?.addEventListener('click', handleToggleClick);

    closeBtn?.addEventListener('click', () => {
        sidebar.classList.remove('active');
        localStorage.setItem('sidebarState', 'inactive');
    });

    // // Menu logic

    const menuLinks = document.querySelectorAll('.menu-list a');

    menuLinks.forEach(link => {
        link.addEventListener('click', function (e) {

            const isParent = this.classList.contains('menu-link');
            const parentLi = this.closest('.has-submenu');

            if (isParent) {
                e.preventDefault();

                // Close other submenus at the same level only — with nested
                // submenus (e.g. Permission Templates under Roles &
                // Permissions, itself under Settings), closing every
                // .has-submenu on the page would collapse this one's own
                // ancestors too.
                const siblingScope = parentLi.parentElement;
                if (siblingScope) {
                    Array.from(siblingScope.children).forEach(el => {
                        if (el !== parentLi && el.classList.contains('has-submenu')) {
                            el.classList.remove('open');
                        }
                    });
                }

                parentLi.classList.toggle('open');
                return;
            }

            // Active state
            menuLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');

            const submenu = this.closest('.submenu');

            if (submenu) {
                const parent = submenu.closest('.has-submenu');
                const parentLink = parent.querySelector('.menu-link');

                // Some parents (e.g. Roles & Permissions) are real links,
                // not a pure toggle — no .menu-link to mark active there.
                if (parentLink) parentLink.classList.add('active');
                parent.classList.add('open');
            }

        });
    });

});


function showToast(message, type = 'success') {
    const toastBox = document.getElementById('toastBox');
    if (!toastBox) return;

    const normalizedType = type === 'danger' ? 'danger' : 'success';
    const iconClass = normalizedType === 'success'
        ? 'fa-solid fa-circle-check'
        : 'fa-solid fa-circle-exclamation';
    const title = normalizedType === 'success' ? 'Success' : 'Error';

    const toast = document.createElement('div');
    toast.className = `custom-toast custom-toast--${normalizedType}`;
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    toast.innerHTML = `
        <div class="custom-toast__icon"><i class="${iconClass}"></i></div>
        <div class="custom-toast__body">
            <div class="custom-toast__title">${title}</div>
            <div class="custom-toast__message">${message}</div>
        </div>
        <button type="button" class="custom-toast__close" aria-label="Close notification">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="custom-toast__progress"></div>
    `;

    toastBox.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.add('is-visible');
    });

    const dismissToast = () => {
        toast.classList.remove('is-visible');
        toast.classList.add('is-hiding');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 240);
    };

    toast.querySelector('.custom-toast__close')?.addEventListener('click', dismissToast);
    setTimeout(dismissToast, 3600);

    const maxToasts = 4;
    while (toastBox.children.length > maxToasts) {
        toastBox.removeChild(toastBox.firstElementChild);
    }
}