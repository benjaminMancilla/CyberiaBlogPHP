function createWave(x, y) {
    const wave = document.createElement('div');
    wave.className = 'wave';
    wave.style.left = `${x}px`;
    wave.style.top = `${y}px`;
    document.body.appendChild(wave);

    setTimeout(() => {
        wave.remove();
    }, 5000);
}

function randomWave() {
    const x = Math.random() * window.innerWidth*0.9;
    const y = Math.random() * window.innerHeight*0.9;
    createWave(x, y);
}


setInterval(randomWave, 5000);