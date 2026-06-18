const API_BASE = '../../ProyectoCRUDAPI/api/index.php';
let token = '';

const API_BASE = (function() {
    const origin = window.location.origin;
    const basePath = window.location.pathname.replace(/\/public\/.*$/, '');
    return origin + basePath + '/api/index.php';
})();

async function apiRequest(url, method, data = null) {
    const fullUrl = url ? `${API_BASE}${url}` : API_BASE;
    const options = {
        method,
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        }
    };
    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }
    const response = await fetch(fullUrl, options);
    const result = await response.json();
    if (!response.ok) {
        if (response.status === 401) {
            Swal.fire('Sesión expirada', 'Vuelva a iniciar sesión', 'error');
            document.getElementById('loginSection').style.display = 'block';
            document.getElementById('productosSection').style.display = 'none';
            token = '';
        }
        throw new Error(result.error || result.message || 'Error en la petición');
    }
    return result;
}

document.getElementById('btnLogin').addEventListener('click', async () => {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    if (!username || !password) {
        Swal.fire('Error', 'Ingrese usuario y contraseña', 'warning');
        return;
    }
    try {
<<<<<<< HEAD:LabAPI_DS7/public/js/script.js
        const response = await fetch(`${API_BASE}/login`, {
=======
        const response = await fetch(`${API_BASE}?login`, {
>>>>>>> parent of 08c5dea (Aicion Proyecto):ProyectoCRUDAPI/public/js/script.js
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        const data = await response.json();
        if (response.ok && data.token) {
            token = data.token;
            document.getElementById('tokenInfo').innerHTML = '✅ Token obtenido correctamente.';
            document.getElementById('loginSection').style.display = 'none';
            document.getElementById('productosSection').style.display = 'block';
            listarProductos();
        } else {
            Swal.fire('Error', data.error || 'Credenciales incorrectas', 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'No se pudo conectar al servidor. Verifique la ruta de la API.', 'error');
    }
});

async function listarProductos(buscar = '') {
    try {
<<<<<<< HEAD:LabAPI_DS7/public/js/script.js
        let url = API_BASE;
        
        if (buscar) url += '?buscar=' + encodeURIComponent(buscar);
=======
        let url = buscar ? `?buscar=${encodeURIComponent(buscar)}` : '';
>>>>>>> parent of 08c5dea (Aicion Proyecto):ProyectoCRUDAPI/public/js/script.js
        const productos = await apiRequest(url, 'GET');
        const tbody = document.getElementById('productosTable');
        tbody.innerHTML = '';
        productos.forEach(prod => {
            const row = tbody.insertRow();
            row.insertCell(0).innerText = prod.id;
            row.insertCell(1).innerText = prod.codigo;
            row.insertCell(2).innerText = prod.producto;
            row.insertCell(3).innerText = prod.precio;
            row.insertCell(4).innerText = prod.cantidad;
            row.insertCell(5).innerHTML = `
                <button class="btn btn-sm btn-warning me-2" onclick="editarProducto(${prod.id})">Editar</button>
                <button class="btn btn-sm btn-danger" onclick="eliminarProducto(${prod.id})">Eliminar</button>
            `;
        });
    } catch (err) {
        Swal.fire('Error', err.message, 'error');
    }
}

document.getElementById('productoForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('productoId').value;
    const datos = {
        codigo: document.getElementById('codigo').value,
        producto: document.getElementById('producto').value,
        precio: parseFloat(document.getElementById('precio').value),
        cantidad: parseInt(document.getElementById('cantidad').value)
    };
    try {
        let result;
        if (id) {
<<<<<<< HEAD:LabAPI_DS7/public/js/script.js
            result = await apiRequest(API_BASE, 'PUT', { ...datos, id: parseInt(id) });
        } else {
            result = await apiRequest(API_BASE, 'POST', datos);
=======
            result = await apiRequest('', 'PUT', { ...datos, id: parseInt(id) });
        } else {
            result = await apiRequest('', 'POST', datos);
>>>>>>> parent of 08c5dea (Aicion Proyecto):ProyectoCRUDAPI/public/js/script.js
        }
        Swal.fire('Éxito', result.message, 'success');
        limpiarFormulario();
        listarProductos();
    } catch (err) {
        if (err.errors) {
            const errores = Object.values(err.errors).join('\n');
            Swal.fire('Errores de validación', errores, 'warning');
        } else {
            Swal.fire('Error', err.message, 'error');
        }
    }
});

function limpiarFormulario() {
    document.getElementById('productoId').value = '';
    document.getElementById('codigo').value = '';
    document.getElementById('producto').value = '';
    document.getElementById('precio').value = '';
    document.getElementById('cantidad').value = '';
    document.querySelector('#productoForm button[type="submit"]').innerText = 'Guardar';
}

async function editarProducto(id) {
    try {
<<<<<<< HEAD:LabAPI_DS7/public/js/script.js
        const producto = await apiRequest(`${API_BASE}?id=${id}`, 'GET');
=======
        const producto = await apiRequest(`?id=${id}`, 'GET');
>>>>>>> parent of 08c5dea (Aicion Proyecto):ProyectoCRUDAPI/public/js/script.js
        document.getElementById('productoId').value = producto.id;
        document.getElementById('codigo').value = producto.codigo;
        document.getElementById('producto').value = producto.producto;
        document.getElementById('precio').value = producto.precio;
        document.getElementById('cantidad').value = producto.cantidad;
        document.querySelector('#productoForm button[type="submit"]').innerText = 'Actualizar';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (err) {
        Swal.fire('Error', err.message, 'error');
    }
}

async function eliminarProducto(id) {
    const confirm = await Swal.fire({
        title: '¿Estás seguro?',
        text: "No podrás revertir esto",
        icon: 'warning',
        showCancelButton: true
    });
    if (confirm.isConfirmed) {
        try {
<<<<<<< HEAD:LabAPI_DS7/public/js/script.js
            await apiRequest(`${API_BASE}?id=${id}`, 'DELETE');
=======
            await apiRequest(`?id=${id}`, 'DELETE');
>>>>>>> parent of 08c5dea (Aicion Proyecto):ProyectoCRUDAPI/public/js/script.js
            Swal.fire('Eliminado', 'Producto eliminado correctamente', 'success');
            listarProductos();
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        }
    }
}

document.getElementById('btnBuscar').addEventListener('click', () => {
    listarProductos(document.getElementById('searchInput').value);
});
document.getElementById('searchInput').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') listarProductos(e.target.value);
});