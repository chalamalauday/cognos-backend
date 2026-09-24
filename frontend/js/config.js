// Central API Configuration for COGNOS 2K26
const RENDER_BACKEND_URL = "https://cognos-backend.onrender.com";

(function() {
    const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    
    if (!window.COGNOS_API_BASE_URL) {
        if (isLocal) {
            // Local preview on XAMPP
            const path = window.location.pathname;
            window.COGNOS_API_BASE_URL = path.includes('/TECH_FEST') ? '/TECH_FEST/backend' : '../backend';
        } else {
            // Live college frontend (rvrjcce.ac.in) -> Send registration requests to Render backend
            window.COGNOS_API_BASE_URL = `${RENDER_BACKEND_URL}/backend`;
        }
    }

    if (!window.COGNOS_API_URL) {
        window.COGNOS_API_URL = window.COGNOS_API_BASE_URL + '/register.php';
    }
})();
