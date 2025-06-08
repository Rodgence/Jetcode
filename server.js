const express = require('express');
const path = require('path');
const fs = require('fs');

const app = express();
const PORT = 3000;

// Serve static files
app.use(express.static('.'));

// Handle clean URLs - redirect to .html files
app.get('*', (req, res) => {
  const requestedPath = req.path;
  
  // Handle root path
  if (requestedPath === '/') {
    return res.sendFile(path.join(__dirname, 'index.html'));
  }
  
  // Check if the clean URL corresponds to an HTML file
  const htmlFile = requestedPath + '.html';
  const filePath = path.join(__dirname, htmlFile);
  
  // Check if the HTML file exists
  if (fs.existsSync(filePath)) {
    return res.sendFile(filePath);
  }
  
  // If no HTML file found, try to serve as static file
  const staticFilePath = path.join(__dirname, requestedPath);
  if (fs.existsSync(staticFilePath)) {
    return res.sendFile(staticFilePath);
  }
  
  // If nothing found, send 404
  res.status(404).send('Page not found');
});

app.listen(PORT, () => {
  console.log(`Server running at http://localhost:${PORT}`);
  console.log('Clean URLs are working! Try:');
  console.log('- http://localhost:3000/');
  console.log('- http://localhost:3000/privacy-policy');
  console.log('- http://localhost:3000/terms-and-conditions');
}); 