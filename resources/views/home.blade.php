<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>GreenCycle</title>
</head>
<body>

<h1>GreenCycle</h1>

<div id="login-section">
  <h2>Iniciar sesión</h2>
  <form id="login-form">
    <input type="email" id="login-email" placeholder="Correo" required>
    <input type="password" id="login-password" placeholder="Contraseña" required>
    <button type="submit">Iniciar sesión</button>
  </form>
  <p id="login-error"></p>
  <button id="show-register-btn" type="button">Registrarme</button>
</div>

<div id="register-section" style="display:none;">
  <h2>Registrarme</h2>
  <form id="register-form">
    <input type="text" id="reg-name" placeholder="Nombre" required>
    <input type="email" id="reg-email" placeholder="Correo" required>
    <input type="password" id="reg-password" placeholder="Contraseña" required>
    <input type="password" id="reg-password-confirm" placeholder="Confirmar contraseña" required>
    <button type="submit">Crear cuenta</button>
  </form>
  <p id="register-error"></p>
  <button id="show-login-btn" type="button">Ya tengo cuenta</button>
</div>

<div id="trees-section" style="display:none;">
  <p id="user-info"></p>
  <button id="logout-btn" type="button">Cerrar sesión</button>

  <h2>Plantar un árbol</h2>
  <form id="plant-form">
    <input type="number" id="seed-type" placeholder="ID del tipo de semilla" value="1" min="1" required>
    <button type="submit">Plantar</button>
  </form>
  <p id="plant-error"></p>

  <h2>Mis árboles</h2>
  <button id="refresh-btn" type="button">Actualizar lista</button>
  <p id="trees-status">Cargando...</p>
  <ul id="trees-list"></ul>
</div>

<script>
const API_BASE = '/api';

async function apiFetch(path, options = {}) {
  const token = localStorage.getItem('gc_token');
  const res = await fetch(API_BASE + path, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    throw new Error(data.message || 'Error en la petición');
  }
  return data;
}

// --- Referencias ---
const loginSection = document.getElementById('login-section');
const registerSection = document.getElementById('register-section');
const treesSection = document.getElementById('trees-section');

// --- Alternar entre login y registro ---
document.getElementById('show-register-btn').addEventListener('click', () => {
  loginSection.style.display = 'none';
  registerSection.style.display = 'block';
});
document.getElementById('show-login-btn').addEventListener('click', () => {
  registerSection.style.display = 'none';
  loginSection.style.display = 'block';
});

// --- Login ---
document.getElementById('login-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const email = document.getElementById('login-email').value;
  const password = document.getElementById('login-password').value;

  try {
    const data = await apiFetch('/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });
    localStorage.setItem('gc_token', data.token);
    mostrarArboles(data.user);
  } catch (err) {
    document.getElementById('login-error').textContent = err.message;
  }
});

// --- Registro ---
document.getElementById('register-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const name = document.getElementById('reg-name').value;
  const email = document.getElementById('reg-email').value;
  const password = document.getElementById('reg-password').value;
  const password_confirmation = document.getElementById('reg-password-confirm').value;

  try {
    const data = await apiFetch('/register', {
      method: 'POST',
      body: JSON.stringify({ name, email, password, password_confirmation }),
    });
    localStorage.setItem('gc_token', data.token);
    mostrarArboles(data.user);
  } catch (err) {
    document.getElementById('register-error').textContent = err.message;
  }
});

// --- Cerrar sesión ---
document.getElementById('logout-btn').addEventListener('click', async () => {
  try {
    await apiFetch('/logout', { method: 'POST' });
  } catch (_) {
    // aunque falle en el servidor, igual limpiamos localmente
  }
  localStorage.removeItem('gc_token');
  treesSection.style.display = 'none';
  loginSection.style.display = 'block';
});

// --- Plantar árbol ---
document.getElementById('plant-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const plantError = document.getElementById('plant-error');
  plantError.textContent = '';

  try {
    const seed_type_id = Number(document.getElementById('seed-type').value);
    await apiFetch('/trees', {
      method: 'POST',
      body: JSON.stringify({ seed_type_id }),
    });
    await cargarArboles();
  } catch (err) {
    plantError.textContent = err.message;
  }
});

// --- Refrescar lista manualmente ---
document.getElementById('refresh-btn').addEventListener('click', cargarArboles);

async function cargarArboles() {
  const status = document.getElementById('trees-status');
  const list = document.getElementById('trees-list');
  status.textContent = 'Cargando...';
  list.innerHTML = '';

  try {
    const trees = await apiFetch('/trees');
    console.log('Respuesta de /trees:', trees);
    status.textContent = trees.length === 0 ? 'No tienes árboles.' : '';
    trees.forEach(t => {
      const li = document.createElement('li');
      li.textContent = `${t.seed_type?.name ?? 'Árbol'} — Nivel ${t.nivel} — Salud ${t.salud} — Estado ${t.estado}`;
      list.appendChild(li);
    });
  } catch (err) {
    status.textContent = 'Error: ' + err.message;
  }
}

async function mostrarArboles(user) {
  loginSection.style.display = 'none';
  registerSection.style.display = 'none';
  treesSection.style.display = 'block';
  document.getElementById('user-info').textContent = 'Sesión: ' + user.name;
  await cargarArboles();
}

(async function init() {
  const token = localStorage.getItem('gc_token');
  if (!token) return;
  try {
    const user = await apiFetch('/me');
    mostrarArboles(user);
  } catch (_) {
    localStorage.removeItem('gc_token');
  }
})();
</script>

</body>
</html>