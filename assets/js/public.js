(function(){
  function mount() {
    var root = document.getElementById('medical-booking-root');
    if (!root) return;
    // Make root full-bleed (escape theme content width)
    root.style.width = '100vw';
    root.style.maxWidth = '100vw';
    root.style.marginLeft = 'calc(50% - 50vw)';
    root.style.marginRight = 'calc(50% - 50vw)';
    root.style.boxSizing = 'border-box';

    // Clear
    root.innerHTML = '';
    var iframe = document.createElement('iframe');
    iframe.setAttribute('title', 'Medical Booking');
    iframe.style.width = '100%';
    iframe.style.minHeight = '1000px';
    iframe.style.border = '0';
    // Load the existing interactive demo page
    // Assumes WordPress is served from /react
    iframe.src = (window.location.origin || '') + '/react/test-fixed.html';
    iframe.onload = function(){
      try {
        var doc = iframe.contentWindow.document;
        var h = Math.max(doc.body.scrollHeight, doc.documentElement.scrollHeight);
        iframe.style.height = (h + 50) + 'px';
      } catch(e) {}
    };
    window.addEventListener('resize', function(){
      try {
        var doc = iframe.contentWindow.document;
        var h = Math.max(doc.body.scrollHeight, doc.documentElement.scrollHeight);
        iframe.style.height = (h + 50) + 'px';
      } catch(e) {}
    });
    root.appendChild(iframe);
  }
  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    mount();
  } else {
    document.addEventListener('DOMContentLoaded', mount);
  }
})();
