(function () {
  var pw = document.getElementById('password');
  var box = document.getElementById('strength');
  if (!pw || !box) return;
  var text = box.querySelector('.strength-text');
    var labels = ['Enter a password', 'Very weak', 'Weak', 'Medium', 'Strong'];

  pw.addEventListener('input', function () {
    var v = pw.value;
    var score = 0;
    if (v.length >= 8) score++;
    if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^a-zA-Z0-9]/.test(v)) score++;
    var level = v.length === 0 ? 0 : Math.max(1, score);
    box.className = 'strength strength-' + level;
    text.textContent = labels[level];
  });
})();