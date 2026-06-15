let token = '';

async function apiRequest(url, method, data = null) {
    const options = {
        method: method,
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        }
    };
    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }
    const response = await fetch(url, options);
    const result = await response.json();
    if (!response.ok) {
        if (response.status === 401) {
            Swal.fire('Sesión expirada', 'Vuelve a iniciar sesión', 'error');
            document.getElementById('loginSection').style.display = 'block';
            document.getElementById('productosSection').style.display = 'none';
            token = '';
        }
        throw new Error(result.error || result.message || 'Error en la petición');
    }
    return result;
}

// Login
document.getElementById('btnLogin').addEventListener('click', async () => {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    if (!username || !password) {
        Swal.fire('Error', 'Ingrese usuario y contraseña', 'warning');
        return;
    }
    try {
        const response = await fetch('/PHP-Proyects/LabAPI_DS7/api/index.php/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        const data = await response.json();
        if (response.ok && data.token) {
            token = data.token;
            document.getElementById('tokenInfo').innerHTML = `✅ Token obtenido correctamente.`;
            document.getElementById('loginSection').style.display = 'none';
            document.getElementById('productosSection').style.display = 'block';
            listarProductos();
        } else {
            Swal.fire('Error', data.error || 'Credenciales incorrectas', 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'No se pudo conectar al servidor', 'error');
    }
});

async function listarProductos(buscar = '') {
    try {
        let url = '/PHP-Proyects/LabAPI_DS7/api/index.php';
        if (buscar) url += '?buscar=' + encodeURIComponent(buscar);
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
            const btnCell = row.insertCell(5);
            btnCell.innerHTML = `
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
            result = await apiRequest('/PHP-Proyects/LabAPI_DS7/api/index.php', 'PUT', { ...datos, id: parseInt(id) });
        } else {
            result = await apiRequest('/PHP-Proyects/LabAPI_DS7/api/index.php', 'POST', datos);
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
        const producto = await apiRequest(`/PHP-Proyects/LabAPI_DS7/api/index.php?id=${id}`, 'GET');
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
            await apiRequest(`/PHP-Proyects/LabAPI_DS7/api/index.php?id=${id}`, 'DELETE');
            Swal.fire('Eliminado', 'Producto eliminado correctamente', 'success');
            listarProductos();
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        }
    }
}

document.getElementById('btnBuscar').addEventListener('click', () => {
    const busqueda = document.getElementById('searchInput').value;
    listarProductos(busqueda);
});