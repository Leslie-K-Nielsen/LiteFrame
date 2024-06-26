document.getElementById('hamburger').addEventListener('click', function() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('close-btn').style.display = 'block'; // Show close button
});

document.getElementById('close-btn').addEventListener('click', function() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('close-btn').style.display = 'none'; // Hide close button
});
