<?php
function sendError($code, $message) {
    http_response_code($code);
    echo "$code $message: Parameters or method not allowed.";
}

?>