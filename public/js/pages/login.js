document.addEventListener('DOMContentLoaded', function() {
    const box = document.querySelector('.box');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    
    let isTyping = false;
    let typingTimer = null;
    let closeTimer = null;
    
    const CLOSE_DELAY = 50;
    const TYPING_DELAY = 2000;

    function openBox() {
        clearTimeout(closeTimer);
        box.classList.add('open');
    }

    function tryCloseBox() {
        clearTimeout(closeTimer);
        
        if (isTyping) {
            return; 
        }

        closeTimer = setTimeout(() => {
            const isUsernameFocused = (document.activeElement === usernameInput);
            const isPasswordFocused = (document.activeElement === passwordInput);
            
            if (!box.matches(':hover') && !isUsernameFocused && !isPasswordFocused) {
                box.classList.remove('open');
            }
        }, CLOSE_DELAY);
    }

    function handleTyping() {
        isTyping = true;
        openBox();
        clearTimeout(typingTimer);

        typingTimer = setTimeout(() => {
            isTyping = false;
            tryCloseBox();
        }, TYPING_DELAY);
    }

    box.addEventListener('mouseenter', openBox);
    box.addEventListener('mouseleave', tryCloseBox);
    
    usernameInput.addEventListener('focus', openBox);
    passwordInput.addEventListener('focus', openBox);
    
    usernameInput.addEventListener('blur', tryCloseBox);
    passwordInput.addEventListener('blur', tryCloseBox);

    usernameInput.addEventListener('input', handleTyping);
    passwordInput.addEventListener('input', handleTyping);

    const eyePath = "M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z";
    const eyeOffPath = "M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21";

    const iconPath = document.getElementById('passwordToggleIconPath');
    const toggleButton = document.getElementById('passwordToggleBtn');

    if (iconPath) {
        iconPath.setAttribute('d', eyePath);
    }

    if (toggleButton) {
        toggleButton.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                if (iconPath) iconPath.setAttribute('d', eyeOffPath);
            } else {
                passwordInput.type = 'password';
                if (iconPath) iconPath.setAttribute('d', eyePath);
            }
            passwordInput.focus();
        });
    }

    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = usernameInput.value.trim();
            const password = passwordInput.value.trim();
            
            if (!username || !password) {
                if (typeof showToast === 'function') {
                    showToast('Username dan password harus diisi!', 'error');
                } else {
                    alert('Username dan password harus diisi!');
                }
                return;
            }
            
            if (typeof showLoginAnimation === 'function') {
                showLoginAnimation(() => {
                    loginForm.submit();
                });
            } else {
                document.getElementById('loginAnimation').style.display = 'flex';
                setTimeout(() => {
                    loginForm.submit();
                }, 1000);
            }
        });
    }

    const successAlert = document.getElementById('success-alert');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.transition = 'opacity 0.5s ease';
            successAlert.style.opacity = '0';
            setTimeout(() => successAlert.remove(), 500);
        }, 5000);
    }

    const fireworksContainer = document.getElementById('fireworks-container');
    if (fireworksContainer) {
        
        let cursorX = window.innerWidth / 2;
        let cursorY = window.innerHeight / 2;

        window.addEventListener('mousemove', function(e) {
            cursorX = e.clientX;
            cursorY = e.clientY;
        });

        function createFireworkExplosion(x, y) {
            const particleCount = 5;
            const particles = [];
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                particle.style.left = x + 'px';
                particle.style.top = y + 'px';
                const hue = Math.floor(Math.random() * 360);
                particle.style.background = `hsl(${hue}, 100%, 75%)`;
                fireworksContainer.appendChild(particle);
                particles.push(particle);
            }
            requestAnimationFrame(() => {
                particles.forEach(particle => {
                    const angle = Math.random() * Math.PI * 2;
                    const distance = Math.random() * 100 + 50;
                    const destX = Math.cos(angle) * distance;
                    const destY = Math.sin(angle) * distance;
                    particle.style.transform = `translate(${destX}px, ${destY}px) scale(0)`;
                    particle.style.opacity = '0';
                    setTimeout(() => {
                        particle.remove();
                    }, 1000);
                });
            });
        }
        setInterval(() => {
            createFireworkExplosion(cursorX, cursorY);
        }, 500); 
    }
});