<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>GreenCycle</title>
</head>
<body>

<h1>GreenCycle</h1>

<div id="login-section">
  <h2>Iniciar sesión</h2>
  <form id="login-form">
    <input type="email" id="login-email" placeholder="Correo" aria-label="Correo" required>
    <input type="password" id="login-password" placeholder="Contraseña" aria-label="Contraseña" required>
    <button type="submit" id="login-btn">Iniciar sesión</button>
  </form>
  <p id="login-error" role="alert"></p>
  <button id="show-register-btn" type="button">Registrarme</button>
</div>

<div id="register-section" style="display:none;">
  <h2>Registrarme</h2>
  <form id="register-form">
    <input type="text" id="reg-name" placeholder="Nombre" aria-label="Nombre" required>
    <input type="email" id="reg-email" placeholder="Correo" aria-label="Correo" required>
    <input type="password" id="reg-password" placeholder="Contraseña" aria-label="Contraseña" required>
    <input type="password" id="reg-password-confirm" placeholder="Confirmar contraseña" aria-label="Confirmar contraseña" required>
    <button type="submit" id="register-btn">Crear cuenta</button>
  </form>
  <p id="register-error" role="alert"></p>
  <button id="show-login-btn" type="button">Ya tengo cuenta</button>
</div>

<div id="trees-section" style="display:none;">
  <p id="user-info"></p>
  <button id="logout-btn" type="button">Cerrar sesión</button>

  <h2>Plantar un árbol</h2>
  <form id="plant-form">
    <input type="number" id="seed-type" placeholder="ID del tipo de semilla" aria-label="ID del tipo de semilla" value="1" min="1" required>
    <button type="submit" id="plant-btn">Plantar</button>
  </form>
  <p id="plant-error" role="alert"></p>
  <p id="plant-status" role="status"></p>

  <h2>Mis árboles</h2>
  <button id="refresh-btn" type="button">Actualizar lista</button>
  <p id="trees-status" role="status">Cargando...</p>
  <ul id="trees-list"></ul>

  <h2>Detalle del árbol</h2>
  <p id="detail-status" role="status">Selecciona un árbol para ver su detalle.</p>
  <pre id="detail-content"></pre>
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
    // Se conserva el código HTTP y el cuerpo para poder reaccionar por tipo de error.
    const error = new Error(data.message || 'Error en la petición');
    error.status = res.status;
    error.data = data;
    throw error;
  }
  return data;
}

// --- Referencias ---
const loginSection = document.getElementById('login-section');
const registerSection = document.getElementById('register-section');
const treesSection = document.getElementById('trees-section');

function showLogin() {
  registerSection.style.display = 'none';
  treesSection.style.display = 'none';
  loginSection.style.display = 'block';
}

// Deshabilita un botón mientras se procesa una solicitud y lo restaura al terminar.
async function withButtonLocked(button, busyText, action) {
  const originalText = button.textContent;
  button.disabled = true;
  button.textContent = busyText;
  try {
    return await action();
  } finally {
    button.disabled = false;
    button.textContent = originalText;
  }
}

// --- Alternar entre login y registro ---
document.getElementById('show-register-btn').addEventListener('click', () => {
  loginSection.style.display = 'none';
  registerSection.style.display = 'block';
});
document.getElementById('show-login-btn').addEventListener('click', showLogin);

// --- Login ---
document.getElementById('login-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const loginError = document.getElementById('login-error');
  loginError.textContent = '';
  const email = document.getElementById('login-email').value;
  const password = document.getElementById('login-password').value;

  await withButtonLocked(document.getElementById('login-btn'), 'Entrando...', async () => {
    try {
      const data = await apiFetch('/login', {
        method: 'POST',
        body: JSON.stringify({ email, password }),
      });
      localStorage.setItem('gc_token', data.token);
      await mostrarArboles(data.user);
    } catch (err) {
      loginError.textContent = err.message;
    }
  });
});

// --- Registro ---
document.getElementById('register-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const registerError = document.getElementById('register-error');
  registerError.textContent = '';
  const name = document.getElementById('reg-name').value;
  const email = document.getElementById('reg-email').value;
  const password = document.getElementById('reg-password').value;
  const password_confirmation = document.getElementById('reg-password-confirm').value;

  await withButtonLocked(document.getElementById('register-btn'), 'Creando...', async () => {
    try {
      const data = await apiFetch('/register', {
        method: 'POST',
        body: JSON.stringify({ name, email, password, password_confirmation }),
      });
      localStorage.setItem('gc_token', data.token);
      await mostrarArboles(data.user);
    } catch (err) {
      registerError.textContent = err.message;
    }
  });
});

// --- Cerrar sesión ---
document.getElementById('logout-btn').addEventListener('click', async () => {
  try {
    await apiFetch('/logout', { method: 'POST' });
  } catch (_) {
    // aunque falle en el servidor, igual limpiamos localmente
  }
  localStorage.removeItem('gc_token');
  showLogin();
});

// --- Plantar árbol ---
document.getElementById('plant-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const plantError = document.getElementById('plant-error');
  const plantStatus = document.getElementById('plant-status');
  plantError.textContent = '';
  plantStatus.textContent = '';
  const seed_type_id = Number(document.getElementById('seed-type').value);

  await withButtonLocked(document.getElementById('plant-btn'), 'Plantando...', async () => {
    try {
      await apiFetch('/trees', {
        method: 'POST',
        body: JSON.stringify({ seed_type_id }),
      });
      plantStatus.textContent = 'Árbol plantado.';
      await cargarArboles();
    } catch (err) {
      if (err.status === 401) {
        return sesionExpirada();
      }
      plantError.textContent = err.message;
    }
  });
});

// --- Refrescar lista manualmente ---
document.getElementById('refresh-btn').addEventListener('click', cargarArboles);

function sesionExpirada() {
  localStorage.removeItem('gc_token');
  showLogin();
  document.getElementById('login-error').textContent = 'Tu sesión expiró. Inicia sesión de nuevo.';
}

// --- Listado: estados cargando / vacío / éxito / error ---
async function cargarArboles() {
  const status = document.getElementById('trees-status');
  const list = document.getElementById('trees-list');
  status.textContent = 'Cargando...';
  list.innerHTML = '';

  try {
    const trees = await apiFetch('/trees');
    status.textContent = trees.length === 0 ? 'Aún no tienes árboles.' : '';
    trees.forEach((t) => {
      const li = document.createElement('li');
      li.textContent = `${t.seed_type?.name ?? 'Árbol'} — Nivel ${t.level} — Salud ${t.health} — Estado ${t.status} `;

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = 'Ver detalle';
      btn.addEventListener('click', () => verDetalle(t.id));
      li.appendChild(btn);

      list.appendChild(li);
    });
  } catch (err) {
    if (err.status === 401) {
      return sesionExpirada();
    }
    status.textContent = 'Error: ' + err.message;
  }
}

// --- Detalle: estados cargando / éxito / error (401, 403, 404) ---
async function verDetalle(id) {
  const status = document.getElementById('detail-status');
  const content = document.getElementById('detail-content');
  status.textContent = 'Cargando detalle...';
  content.textContent = '';

  try {
    const t = await apiFetch('/trees/' + id);
    status.textContent = '';
    content.textContent =
      `Tipo: ${t.seed_type?.name ?? '-'}\n` +
      `Nivel: ${t.level}\n` +
      `Salud: ${t.health}\n` +
      `Progreso: ${t.progress}\n` +
      `Estado: ${t.status}`;
  } catch (err) {
    if (err.status === 401) {
      return sesionExpirada();
    }
    status.textContent =
      err.status === 403 ? 'No tienes permiso para ver este árbol.' :
      err.status === 404 ? 'El árbol no existe.' :
      'No se pudo cargar el detalle.';
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
    await mostrarArboles(user);
  } catch (_) {
    localStorage.removeItem('gc_token');
  }
})();
</script>

</body>
</html>
