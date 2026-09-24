// Central API Configuration for COGNOS 2K26
// If frontend and backend are hosted together (e.g. inside /innovex2026/), 
// this automatically determines the correct backend path without hardcoding.
// If using a custom external backend URL, set window.COGNOS_API_BASE_URL before this script runs.
(function() {
    if (!window.COGNOS_API_BASE_URL) {
        const path = window.location.pathname;
        if (path.includes('/frontend')) {
            // E.g. /innovex2026/frontend/index.html -> /innovex2026/backend
            window.COGNOS_API_BASE_URL = path.substring(0, path.lastIndexOf('/frontend')) + '/backend';
        } else if (path.includes('/TECH_FEST')) {
            window.COGNOS_API_BASE_URL = '/TECH_FEST/backend';
        } else {
            window.COGNOS_API_BASE_URL = '../backend';
        }
    }
    if (!window.COGNOS_API_URL) {
        window.COGNOS_API_URL = window.COGNOS_API_BASE_URL + '/register.php';
    }
})();
