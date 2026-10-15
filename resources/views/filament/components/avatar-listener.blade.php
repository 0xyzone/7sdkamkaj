<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.addEventListener('avatar-updated', (event) => {
            const avatarUrl = event.detail?.url;
            
            // Update all user avatars in the topbar and user menu
            document.querySelectorAll('.fi-user-menu-trigger .fi-avatar, .fi-user-avatar').forEach((el) => {
                if (avatarUrl) {
                    if (el.tagName === 'IMG') {
                        el.src = avatarUrl;
                    } else {
                        const img = document.createElement('img');
                        img.src = avatarUrl;
                        img.alt = el.getAttribute('aria-label') || 'User Avatar';
                        img.className = el.className + ' object-cover';
                        img.loading = 'lazy';
                        el.replaceWith(img);
                    }
                } else {
                    // Avatar removed, reload to restore default initials cleanly
                    window.location.reload();
                }
            });
        });
    });
</script>
