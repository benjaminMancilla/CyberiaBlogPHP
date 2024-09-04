document.getElementById('comment-toggle').addEventListener('click', function() {
    const form = document.getElementById('comment-form');
    form.style.display = 'block';
    this.style.display = 'none'; // Oculta el botón de "Add Comment" una vez que se despliega el formulario
});

document.querySelector('.form-cancel-button').addEventListener('click', function() {
    const form = document.getElementById('comment-form');
    form.style.display = 'none';
    document.getElementById('comment-toggle').style.display = 'block'; // Vuelve a mostrar el botón de "Add Comment"
});
