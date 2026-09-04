// Main JavaScript entry point
import './bootstrap';

// jQuery is loaded from CDN in the layout, so we just ensure it's available
if (typeof window.$ === 'undefined' && typeof jQuery !== 'undefined') {
    window.$ = window.jQuery = jQuery;
}

// Import lodash and make it globally available
import _ from 'lodash';
window._ = _;

// Import axios and make it globally available
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add any custom JavaScript here
import swal from 'sweetalert2';
window.Swal = swal;

// froiden-helper attaches $.easyAjax to jQuery; keep $ on the same instance for legacy Blade scripts
// that run after this module (e.g. Update Application Swal.then → $.easyAjax).
if (typeof window.jQuery !== 'undefined') {
    window.$ = window.jQuery;
}
