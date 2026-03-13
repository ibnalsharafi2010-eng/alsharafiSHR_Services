<?php
// Simple logo generation
header('Content-Type: image/svg+xml');
header('Cache-Control: no-cache');

?>
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="60" viewBox="0 0 200 60">
  <defs>
    <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#2a67ac;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#1e4d7a;stop-opacity:1" />
    </linearGradient>
  </defs>
  
  <rect width="200" height="60" rx="8" fill="url(#logoGradient)" />
  
  <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="24" font-weight="bold" fill="white" text-anchor="middle">
    SHR
  </text>
  
  <text x="50%" y="75%" font-family="Arial, sans-serif" font-size="12" fill="white" text-anchor="middle">
    Support
  </text>
</svg>
