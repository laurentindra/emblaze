const menuToggle = document.getElementById('menuToggle');
const mobileMenu = document.getElementById('mobileMenu');
menuToggle.addEventListener('click', () => mobileMenu.classList.toggle('show'));

const tabLogin = document.getElementById('tabLogin');
const tabRegister = document.getElementById('tabRegister');
const registerFields = document.querySelectorAll('.register-fields');
const loginOnlyRow = document.querySelector('.login-only-row');
const submitBtn = document.getElementById('submitBtn');
const loginHeading = document.querySelector('.login-heading');
const loginSub = document.querySelector('.login-sub');
const switchPrompt = document.getElementById('switchPrompt');
const switchLink = document.getElementById('switchLink');
const formMessage = document.getElementById('formMessage');

let isLogin = true;

function switchToLogin() {
    isLogin = true;
    tabLogin.classList.add('active');
    tabRegister.classList.remove('active');
    registerFields.forEach(f => f.classList.remove('show'));
    loginOnlyRow.style.display = 'flex';
    submitBtn.textContent = 'Sign In';
    loginHeading.innerHTML = 'Welcome <em>back.</em>';
    loginSub.textContent = 'Sign in to your account to continue.';
    switchPrompt.innerHTML = 'Don\'t have an account? <a href="#" id="switchLink">Create one</a>';
    document.getElementById('switchLink').addEventListener('click', (e) => { e.preventDefault(); switchToRegister(); });
    hideMessage();
}

function switchToRegister() {
    isLogin = false;
    tabRegister.classList.add('active');
    tabLogin.classList.remove('active');
    registerFields.forEach(f => f.classList.add('show'));
    loginOnlyRow.style.display = 'none';
    submitBtn.textContent = 'Create Account';
    loginHeading.innerHTML = 'Join <em>Emblaze.</em>';
    loginSub.textContent = 'Create your account and join the circle.';
    switchPrompt.innerHTML = 'Already have an account? <a href="#" id="switchLink">Sign in</a>';
    document.getElementById('switchLink').addEventListener('click', (e) => { e.preventDefault(); switchToLogin(); });
    hideMessage();
}

tabLogin.addEventListener('click', switchToLogin);
tabRegister.addEventListener('click', switchToRegister);
switchLink.addEventListener('click', (e) => { e.preventDefault(); switchToRegister(); });

document.getElementById('togglePassword').addEventListener('click', () => {
    const pwd = document.getElementById('password');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
});

function showMessage(text, type) {
    formMessage.textContent = text;
    formMessage.className = `form-message ${type}`;
}

function hideMessage() {
    formMessage.className = 'form-message';
}

document.getElementById('authForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const username = document.getElementById('username').value.trim();

    if (!email || !password) {
        showMessage('Please fill in all required fields.', 'error');
        return;
    }

    const form = new FormData();
    form.append('email', email);
    form.append('password', password);
    form.append('username', username);

    if (!isLogin) {
        //register cihuy
        const confirm = document.getElementById('confirmPassword').value;
        const fullName = document.getElementById('fullName').value.trim();

        if (password !== confirm) {
            showMessage('Passwords do not match.', 'error');
            return;
        }

        form.append('action', 'register');
        form.append('nama', fullName);

        fetch('login.php', { method: 'POST', body: form })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showMessage('Account created! Welcome to Emblaze.', 'success');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1800);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(() => showMessage('Server error. Try again.', 'error'));

    } else {
        //login cihuy
        form.append('action', 'login');

        fetch('login.php', { method: 'POST', body: form })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showMessage('Signed in successfully. Redirecting…', 'success');
                    setTimeout(() => {
                        // Gunakan redirect dari server (admin → admin panel, user → index)
                        window.location.href = data.redirect || 'index.php';
                    }, 1500);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(() => showMessage('Server error. Try again.', 'error'));
    }
});

async function loginUser(email, password) {
    try {
        // IP ADDRESS CHANGED!! change it when ur in campus cerr
        const response = await fetch('http://192.168.1.15:3000/api/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        const result = await response.json();
        if (response.ok) {
            alert('Login Berhasil!');
            window.location.href = 'index.php';
        } else {
            alert('Login Gagal: ' + result.message);
        }
    } catch (err) {
        console.log('Server API mati atau IP berubah!');
    }
}