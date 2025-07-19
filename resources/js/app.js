import './bootstrap';
import './notifications'; 
import './chat';

// Atau tambahkan langsung di sini:
console.log('🚀 app.js loaded');

// Make user ID available globally for notifications
window.userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
