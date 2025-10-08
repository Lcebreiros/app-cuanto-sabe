@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary-color: #00f0ff;
        --secondary-color: #ff00ff;
        --dark-bg: #0a0e23;
        --card-bg: rgba(15, 18, 42, 0.92);
        --input-bg: rgba(23, 28, 51, 0.8);
        --success-color: #19ff8c;
        --warning-color: #ffcc00;
        --error-color: #ff4444;
        --text-primary: #ffffff;
        --text-secondary: #b8c7ff;
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    /* --- BASE STYLES --- */
    .questions-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }

    /* --- HEADER --- */
    .page-header {
        margin-bottom: 2.5rem;
        position: relative;
        padding-bottom: 1rem;
    }

    .page-title {
        color: var(--primary-color);
        text-shadow: 0 0 12px var(--primary-color);
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .page-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100px;
        height: 3px;
        background: var(--primary-color);
        box-shadow: 0 0 8px var(--primary-color);
    }

    /* --- ACTION BUTTONS --- */
    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
    }

    .action-btn {
        background: rgba(14, 23, 56, 0.7);
        color: var(--text-primary);
        border: 1.5px solid rgba(38, 43, 57, 0.8);
        border-radius: 12px;
        padding: 0.8rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .action-btn:hover {
        background: rgba(21, 31, 57, 0.9);
        border-color: var(--primary-color);
        box-shadow: 0 0 15px rgba(0, 240, 255, 0.5);
        transform: translateY(-2px);
    }

    /* --- TOGGLE FORMS --- */
    .toggle-form {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s;
        margin-bottom: 1.5rem;
        background: var(--card-bg);
        border-radius: 12px;
        padding: 0 1.5rem;
        border: 1px solid rgba(37, 45, 67, 0.6);
    }

    .toggle-form.show {
        max-height: 800px;
        opacity: 1;
        padding: 1.5rem;
        box-shadow: 0 0 20px rgba(0, 240, 255, 0.1);
    }

    /* --- FORM STYLES --- */
    .form-label {
        color: var(--primary-color);
        font-size: 0.95rem;
        margin-bottom: 0.5rem;
        display: block;
        font-weight: 500;
    }

    .form-control, .form-select {
        background: var(--input-bg);
        color: var(--text-primary);
        border: 1px solid rgba(37, 45, 67, 0.8);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        width: 100%;
        transition: var(--transition);
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        background: rgba(25, 31, 57, 0.9);
        box-shadow: 0 0 15px rgba(0, 240, 255, 0.3);
        outline: none;
    }

    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
        align-items: flex-end;
    }

    .form-col {
        flex: 1;
        min-width: 200px;
    }

    .submit-btn {
        background: var(--success-color);
        color: #00361e;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
    }

    .submit-btn:hover {
        background: #2affb3;
        box-shadow: 0 0 15px rgba(25, 255, 158, 0.5);
    }

    /* --- ALERTS --- */
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        border: 1px solid;
    }

    .alert-error {
        background: rgba(42, 18, 18, 0.7);
        color: #ffbdbd;
        border-color: #aa3333;
    }

    .alert-success {
        background: rgba(14, 42, 29, 0.7);
        color: #baf7d2;
        border-color: #1f6f4c;
    }

    .alert-warning {
        background: rgba(42, 33, 14, 0.7);
        color: #f7e0ba;
        border-color: #6f5a1f;
    }

    .alert-list {
        margin-left: 1.5rem;
        list-style-type: disc;
    }

    /* --- LISTS --- */
    .categories-section {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 3rem;
    }

    .category-card {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid rgba(37, 45, 67, 0.6);
    }

    .category-title {
        color: var(--primary-color);
        font-size: 1.2rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid rgba(0, 240, 255, 0.2);
    }

    .category-list {
        list-style: none;
        padding: 0;
    }

    .category-item {
        padding: 0.5rem 0;
        border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
        display: flex;
        justify-content: space-between;
    }

    .category-item:last-child {
        border-bottom: none;
    }

    .category-name {
        color: var(--text-primary);
    }

    .category-motive {
        color: var(--text-secondary);
        font-size: 0.85rem;
    }

    /* --- CSV PREVIEW --- */
    .csv-preview {
        background: rgba(12, 21, 42, 0.8);
        border: 1px solid rgba(37, 45, 67, 0.8);
        border-radius: 8px;
        color: #cfe9ff;
        padding: 1rem;
        margin-top: 1rem;
        overflow: auto;
        font-family: monospace;
        white-space: pre;
    }

    .csv-toggle {
        color: var(--primary-color);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1rem;
        font-size: 0.95rem;
    }

    .csv-toggle::after {
        content: '▶';
        font-size: 0.7rem;
        transition: transform 0.2s;
    }

    details[open] .csv-toggle::after {
        transform: rotate(90deg);
    }

    /* --- RESPONSIVE ADJUSTMENTS --- */
    @media (max-width: 768px) {
        .questions-container {
            padding: 1.5rem;
        }
        
        .form-col {
            min-width: 100%;
        }
        
        .categories-section {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="questions-container">
    <div class="page-header">
        <h1 class="page-title">Gestión de Preguntas</h1>
        
        <div class="action-buttons">
            <button type="button" class="action-btn" onclick="toggleForm('formMotivo')">
                <span>+</span> Crear motivo
            </button>
            <button type="button" class="action-btn" onclick="toggleForm('formCategoria')">
                <span>+</span> Crear categoría
            </button>
            <button type="button" class="action-btn" onclick="toggleForm('formPregunta')">
                <span>+</span> Crear pregunta
            </button>
            <button type="button" class="action-btn" onclick="toggleForm('formCSV')">
                <span>⭳</span> Importar CSV
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if ($errors->any())
        <div class="alert alert-error">
            <ul class="alert-list">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif
    
    @if (session('import_errors'))
        <div class="alert alert-warning">
            <strong>Advertencias/errores:</strong>
            <ul class="alert-list">
                @foreach (session('import_errors') as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- FORMULARIOS -->
    <div id="formMotivo" class="toggle-form">
        <form action="{{ route('motivo.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label">Nuevo motivo:</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej: Programación" required>
                </div>
                <div class="form-col">
                    <button type="submit" class="submit-btn">Agregar Motivo</button>
                </div>
            </div>
        </form>
    </div>

    <div id="formCategoria" class="toggle-form">
        <form action="{{ route('categoria.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label">Nueva categoría:</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej: JavaScript" required>
                </div>
                <div class="form-col">
                    <label class="form-label">Motivo:</label>
                    <select name="motivo_id" class="form-select" required>
                        <option value="">Elegí un motivo</option>
                        @foreach(($motivos ?? collect()) as $motivo)
                            <option value="{{ $motivo->id }}">{{ $motivo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-col">
                    <button type="submit" class="submit-btn">Agregar Categoría</button>
                </div>
            </div>
        </form>
    </div>

    <div id="formPregunta" class="toggle-form">
        <form action="{{ route('pregunta.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label">Nueva pregunta:</label>
                    <input type="text" name="texto" class="form-control" placeholder="Pregunta..." required>
                </div>
                <div class="form-col">
                    <label class="form-label">Categoría:</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Elegí una categoría</option>
                        @foreach(($categorias ?? collect()) as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }} ({{ $cat->motivo->nombre ?? '—' }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label">Opción correcta:</label>
                    <input type="text" name="opcion_correcta" class="form-control" required>
                </div>
                <div class="form-col">
                    <label class="form-label">Opción incorrecta 1:</label>
                    <input type="text" name="opcion_1" class="form-control" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label">Opción incorrecta 2:</label>
                    <input type="text" name="opcion_2" class="form-control" required>
                </div>
                <div class="form-col">
                    <label class="form-label">Opción incorrecta 3:</label>
                    <input type="text" name="opcion_3" class="form-control" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <button type="submit" class="submit-btn">Agregar Pregunta</button>
                </div>
            </div>
        </form>
    </div>

    {{-- IMPORT CSV --}}
    <div id="formCSV" class="toggle-form">
        <form action="{{ route('questions.import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label">Importar preguntas desde CSV:</label>
                    <input type="file" name="csv" accept=".csv,text/csv" class="form-control" required>
                    <small style="color: var(--text-secondary)">Delimitador: coma, punto y coma, tab o |. Codificación UTF-8.</small>
                </div>
                <div class="form-col">
                    <label class="form-label">Modo:</label>
                    <select name="modo" class="form-select">
                        <option value="insert">Solo insertar</option>
                        <option value="upsert" selected>Crear/Actualizar</option>
                    </select>
                </div>
                <div class="form-col">
                    <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="crear_categorias" value="1" style="margin: 0;">
                        Crear categorías
                    </label>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <button type="submit" class="submit-btn" style="background: #4a6bff;">Importar</button>
                </div>
            </div>
            
            <details>
                <summary class="csv-toggle">Ver formato CSV</summary>
                <div class="csv-preview">
categoria,texto,a,b,c,d,correcta
Ciencia,¿Cuál es el planeta más grande?,Júpiter,Saturno,Neptuno,Urano,A
Historia,¿En qué año fue 1810?,1810,1816,1806,1853,A
Deportes,¿Cuántos jugadores tiene un equipo de fútbol?,9,10,11,12,C
                </div>
            </details>
        </form>
    </div>

    <!-- Listados -->
    <div class="categories-section">
        <div class="category-card">
            <h3 class="category-title">Motivos existentes</h3>
            <ul class="category-list">
                @foreach(($motivos ?? collect()) as $m)
                    <li class="category-item">
                        <span class="category-name">{{ $m->nombre }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        
        <div class="category-card">
            <h3 class="category-title">Categorías existentes</h3>
            <ul class="category-list">
                @foreach(($categorias ?? collect()) as $c)
                    <li class="category-item">
                        <span class="category-name">{{ $c->nombre }}</span>
                        <span class="category-motive">{{ $c->motivo->nombre ?? '—' }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<script>
    function toggleForm(id) {
        document.querySelectorAll('.toggle-form').forEach(f => { 
            if(f.id !== id) f.classList.remove('show'); 
        });
        const el = document.getElementById(id);
        if (el) el.classList.toggle('show');
    }
</script>
@endsection