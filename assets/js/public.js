(function(){
  function mount() {
    var root = document.getElementById('medical-booking-root');
    if (!root) return;
    
    // Check if React component is already loaded
    if (root.children.length > 0) {
      // React component is already mounted, don't interfere
      return;
    }
    
    // Make root full-bleed (escape theme content width)
    root.style.width = '100vw';
    root.style.maxWidth = '100vw';
    root.style.marginLeft = 'calc(50% - 50vw)';
    root.style.marginRight = 'calc(50% - 50vw)';
    root.style.boxSizing = 'border-box';
    
    // Show loading message while React component loads
    root.innerHTML = '<div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">Se încarcă formularul de programare...</div>';
  }
  
  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    mount();
  } else {
    document.addEventListener('DOMContentLoaded', mount);
  }
})();
