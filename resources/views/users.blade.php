@extends('layouts.app')

@section('content')
<div class="user-panel-container">
    <h2 class="panel-title">Gestión de Usuarios</h2>

    <div class="overflow-x-auto mt-6">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>DNI</th>
                    <th>Registrado</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr data-user-id="{{ $user->id }}">
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->dni_ultimo4 }}</td>
                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                    <td>
                        <select class="role-select" data-user-id="{{ $user->id }}" data-current-role="{{ $user->role }}">
                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                            <option value="moderator" {{ $user->role === 'moderator' ? 'selected' : '' }}>Moderator</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </td>
                    <td>
                        @if(auth()->id() !== $user->id)
                        <button class="delete-btn" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}">Eliminar</button>
                        @else
                        <span class="text-xs text-gray-500 italic">Tú</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Modal --}}
<div id="deleteModal" class="modal hidden">
    <div class="modal-content">
        <h3>¿Eliminar usuario <span id="deleteUserName" class="font-bold text-[#ff0055]"></span>?</h3>
        <div class="modal-actions">
            <button id="confirmDelete" class="btn btn-danger">Eliminar</button>
            <button id="cancelDelete" class="btn">Cancelar</button>
        </div>
    </div>
</div>

<style>
    .user-panel-container {
        background: rgba(5, 5, 20, 0.9);
        padding: 2rem;
        border-radius: 15px;
        max-width: 960px;
        margin: 0 auto;
        box-shadow: 0 0 25px #00f0ff44;
    }

    .panel-title {
        font-size: 2rem;
        text-align: center;
        color: #00f0ff;
        margin-bottom: 1.5rem;
        text-shadow: 0 0 8px #00f0ff;
    }

    .user-table {
        width: 100%;
        border-collapse: collapse;
        color: white;
    }

    .user-table th,
    .user-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #00f0ff33;
    }

    .user-table th {
        background-color: #0a0a20;
        color: #00f0ff;
    }

    .user-table tr:hover {
        background-color: #111132;
    }

    select.role-select {
        background-color: #0a0a20;
        color: white;
        border: 1px solid #00f0ff;
        padding: 0.4rem;
        border-radius: 4px;
    }

    .delete-btn {
        background-color: #ff0055;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        cursor: pointer;
        transition: 0.3s ease;
    }

    .delete-btn:hover {
        background-color: #cc0044;
    }

    .modal {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 50;
    }

    .modal-content {
        background: #111;
        padding: 2rem;
        border-radius: 10px;
        color: white;
        max-width: 400px;
        text-align: center;
        box-shadow: 0 0 20px #ff005588;
    }

    .modal-actions {
        margin-top: 1rem;
        display: flex;
        justify-content: center;
        gap: 1rem;
    }

    .btn {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        border: 1px solid #00f0ff;
        color: #00f0ff;
        background: transparent;
        cursor: pointer;
        transition: 0.3s ease;
    }

    .btn:hover {
        background-color: #00f0ff22;
    }

    .btn-danger {
        background-color: #ff0055;
        color: white;
        border: none;
    }

    .btn-danger:hover {
        background-color: #cc0044;
    }

    .hidden {
    display: none !important;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let deleteModal = document.getElementById('deleteModal');
    let deleteUserName = document.getElementById('deleteUserName');
    let confirmBtn = document.getElementById('confirmDelete');
    let cancelBtn = document.getElementById('cancelDelete');
    let currentDeleteId = null;

    document.querySelectorAll('.role-select').forEach(select => {
        select.addEventListener('change', function() {
            const userId = this.dataset.userId;
            const role = this.value;
            const currentRole = this.dataset.currentRole;
            if (role === currentRole) return;

            fetch(`/users/${userId}/role`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ role })
            })
            .then(res => res.json())
            .then(data => {
                console.log('Respuesta updateRole:', data);
                if (data.success) {
                    this.dataset.currentRole = role;
                    alert('Rol actualizado');
                } else {
                    this.value = currentRole;
                    alert(data.message || 'Error');
                }
            })
            .catch(error => {
                console.error('Error updateRole:', error);
                this.value = currentRole;
                alert('Error de conexión');
            });
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            currentDeleteId = btn.dataset.userId;
            deleteUserName.textContent = btn.dataset.userName;
            deleteModal.classList.remove('hidden');
        });
    });

    cancelBtn.addEventListener('click', () => {
        deleteModal.classList.add('hidden');
        currentDeleteId = null;
    });

    confirmBtn.addEventListener('click', () => {
        if (!currentDeleteId) return;
        fetch(`/users/${currentDeleteId}`, {  // <--- corregido: quitar /admin
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`[data-user-id="${currentDeleteId}"]`).remove();
                alert('Usuario eliminado');
            } else {
                alert(data.message || 'Error');
            }
            deleteModal.classList.add('hidden');
            currentDeleteId = null;
        })
        .catch(() => {
            alert('Error de conexión');
            deleteModal.classList.add('hidden');
            currentDeleteId = null;
        });
    });
});

</script>

@endsection
