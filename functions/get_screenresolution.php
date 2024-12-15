<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSON screenResolution</title>
</head>
<body>
    <pre id="jsonOutput"></pre> <!-- Placeholder to show the JSON output -->
    <script>
        // Create a JavaScript object
   
            Const screenSize=${window.screen.width}x${window.screen.height}
  

        // Convert the JavaScript object to a JSON string
        const jsonString = JSON.stringify(data, null, 2); // `null` for replacer and `2` for pretty-printing with indentation

        // Print the JSON string to the console
        console.log(jsonString);

        // Display the JSON string on the webpage
        document.getElementById('jsonOutput').textContent = jsonString;
    </script>

</body>
</html>
