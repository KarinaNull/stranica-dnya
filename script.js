let timer = document.getElementById('timer');

if (timer) {
    let left = Number(timer.dataset.left);

    function showTime() {
        if (left <= 0) {
            location.reload();
            return;
        }

        let h = Math.floor(left / 3600);
        let m = Math.floor(left % 3600 / 60);
        let s = left % 60;

        timer.textContent = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        left--;
    }

    showTime();
    setInterval(showTime, 1000);
}
