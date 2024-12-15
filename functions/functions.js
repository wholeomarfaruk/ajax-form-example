  // copy To Clipboard
  const copyToClipboard = async (selector) => {
    try {
      const element = document.querySelector(selector);
      await navigator.clipboard.writeText(element.value);
      console.log("Text copied to clipboard!");
      // Optional: Display a success message to the user
    } catch (error) {
      console.error("Failed to copy to clipboard:", error);
      // Optional: Display an error message to the user
    }
  };
  const pasteFromClipboard = async (selector) => {
    try {
        const text = await navigator.clipboard.readText(); // Read text from the clipboard
        const element = document.querySelector(selector); // Use the provided selector
        element.value = text; // Paste the text into the input or textarea
        console.log("Text pasted from clipboard!");
        // Optional: Display a success message to the user
    } catch (error) {
        console.error("Failed to paste from clipboard:", error);
        // Optional: Display an error message to the user
    }
};


// Show/Hide Spinner
function showSpinner() {
  document.getElementById('dynamicspinner').style.display = 'block';
}

function hideSpinner() {
  document.getElementById('dynamicspinner').style.display = 'none';
}
// <!-- Spinner (hidden by default) -->
// <div id="dynamicspinner" class="spinner-border text-primary" role="status" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1051;">
//   <span class="visually-hidden">Loading...</span>
// </div>

// Show/Hide Alert Modal
function showAlert(isSuccess, message, delay = 3000) {
  const alertModal = document.getElementById('alertModal');
  const alertMessage = document.getElementById('alertMessage');

  // Check if elements exist
  if (!alertModal || !alertMessage) {
    console.error("Alert elements not found in the DOM.");
    return;
  }

  // Set the appropriate Bootstrap class for success or danger
  alertModal.className = `alert ${isSuccess ? 'alert-success' : 'alert-danger'} alert-dismissible fade show`;
  
  // Set the alert message
  alertMessage.textContent = message;

  // Display the alert
  alertModal.style.display = 'block';

  // Automatically hide the alert after the specified delay
  setTimeout(hideAlert, delay);
}

function hideAlert() {
  const alertModal = document.getElementById('alertModal');
  
  // Hide the alert modal without removing it from the DOM
  if (alertModal) {
    alertModal.style.display = 'none';
    alertModal.classList.remove('show'); // Reset Bootstrap `show` class
  }
}


// <!-- Alert Modal (hidden by default) -->
// <div id="alertModal" class="alert alert-dismissible fade" role="alert" style="display: none; position: fixed; top: 10%; right: 10%; z-index: 1051;">
//   <span id="alertMessage"></span>
//   <button type="button" class="btn-close" aria-label="Close" onclick="hideAlert()"></button>
// </div>