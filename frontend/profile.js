// ─── Mobile Menu Toggle ───
const menuToggle = document.getElementById('menuToggle');
const mobileMenu = document.getElementById('mobileMenu');
menuToggle.addEventListener('click', () => mobileMenu.classList.toggle('show'));

// ─── Profile Tabs ───
const tabs = document.querySelectorAll('.profile-tab');
const tabContents = document.querySelectorAll('.tab-content');

tabs.forEach(tab => {
  tab.addEventListener('click', () => {
    const target = tab.dataset.tab;

    tabs.forEach(t => t.classList.remove('active'));
    tab.classList.add('active');

    tabContents.forEach(tc => tc.classList.remove('active'));
    document.getElementById(`tab-${target}`).classList.add('active');

    hideMessage();
  });
});

// ─── Form Messages ───
const formMessage = document.getElementById('formMessage');

function showMessage(text, type) {
  formMessage.textContent = text;
  formMessage.className = `form-message ${type}`;
  formMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function hideMessage() {
  formMessage.className = 'form-message';
}

// ─── Update Profile ───
document.getElementById('profileForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const username = document.getElementById('profileUsername').value.trim();
  const email = document.getElementById('profileEmail').value.trim();

  if (!username || !email) {
    showMessage('Please fill in all fields.', 'error');
    return;
  }

  const btn = document.getElementById('saveProfileBtn');
  btn.disabled = true;
  btn.textContent = 'Saving…';

  const form = new FormData();
  form.append('action', 'update_profile');
  form.append('username', username);
  form.append('email', email);

  fetch('profile.php', { method: 'POST', body: form })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showMessage(data.message, 'success');
        // Update displayed name
        document.querySelector('.profile-name').textContent = username;
        document.querySelector('.profile-email').textContent = email;
        document.getElementById('avatarInitial').textContent = username.charAt(0).toUpperCase();
      } else {
        showMessage(data.message, 'error');
      }
    })
    .catch(() => showMessage('Server error. Please try again.', 'error'))
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Save Changes`;
    });
});

// ─── Change Password ───
document.getElementById('passwordForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const currentPwd = document.getElementById('currentPassword').value;
  const newPwd = document.getElementById('newPassword').value;
  const confirmPwd = document.getElementById('confirmNewPassword').value;

  if (!currentPwd || !newPwd || !confirmPwd) {
    showMessage('Please fill in all password fields.', 'error');
    return;
  }

  if (newPwd !== confirmPwd) {
    showMessage('New passwords do not match.', 'error');
    return;
  }

  if (newPwd.length < 6) {
    showMessage('Password must be at least 6 characters.', 'error');
    return;
  }

  const btn = document.getElementById('savePasswordBtn');
  btn.disabled = true;
  btn.textContent = 'Updating…';

  const form = new FormData();
  form.append('action', 'change_password');
  form.append('current_password', currentPwd);
  form.append('new_password', newPwd);
  form.append('confirm_password', confirmPwd);

  fetch('profile.php', { method: 'POST', body: form })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showMessage(data.message, 'success');
        document.getElementById('passwordForm').reset();
      } else {
        showMessage(data.message, 'error');
      }
    })
    .catch(() => showMessage('Server error. Please try again.', 'error'))
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Update Password`;
    });
});

// ─── Toggle Password Visibility ───
document.querySelectorAll('.toggle-pwd').forEach(toggle => {
  toggle.addEventListener('click', () => {
    const targetId = toggle.dataset.target;
    const input = document.getElementById(targetId);
    input.type = input.type === 'password' ? 'text' : 'password';
  });
});


