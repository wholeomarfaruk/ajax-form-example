// script.js

function showToast(message, type = 'success') {
    // Get the toast container
    const container = document.getElementById('toast-container');

    // Create a new toast element
    const toast = document.createElement('div');
    toast.className = `toaster ${type}`;
    toast.textContent = message;

    // Append the toast to the container
    container.appendChild(toast);

    // Set a timeout to fade out and remove the toast after 100 seconds
    const timeout = setTimeout(() => {
        toast.classList.add('fadeOut'); // Add the fadeOut class
        toast.addEventListener('animationend', () => toast.remove());
    }, 3000);

    // Remove the toast when clicked and clear the timeout
    toast.onclick = () => {
        clearTimeout(timeout); // Clear the timeout to prevent auto-removal
        toast.classList.add('fadeOut');
        toast.addEventListener('animationend', () => toast.remove());
    };
}
