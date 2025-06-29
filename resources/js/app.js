import './bootstrap';
import './simple-notifications';
import './notifications';

// Make user ID available globally for notifications
window.userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
