import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Kích hoạt trạm thu sóng WebSockets
import './echo';
