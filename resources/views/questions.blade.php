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

    {{-- IMPORT CSV ACTUALIZADO --}}
    <div id="formCSV" class="toggle-form">
        <form action="{{ route('questions.import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            {{-- Fila 1: Archivo y Modo --}}
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label">📄 Archivo CSV:</label>
                    <input type="file" name="csv" accept=".csv,text/csv" class="form-control" required>
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">
                        Delimitador: coma, punto y coma, tab o |. UTF-8.
                    </small>
                </div>
                <div class="form-col">
                    <label class="form-label">Modo de importación:</label>
                    <select name="modo" class="form-select">
                        <option value="insert">Solo insertar nuevas</option>
                        <option value="upsert" selected>Crear/Actualizar existentes</option>
                    </select>
                </div>
            </div>

            {{-- Fila 2: Selector de Motivo (NUEVO) --}}
            <div class="form-row" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem; margin-top: 1rem;">
                <div class="form-col">
                    <label class="form-label">
                        🎯 <strong>Opción 1:</strong> Forzar todas las preguntas a un motivo específico
                    </label>
                    <select name="motivo_forzado_id" id="motivoForzado" class="form-select" onchange="toggleMotivoOptions()">
                        <option value="">-- No forzar, usar motivo del CSV --</option>
                        @foreach(($motivos ?? collect()) as $motivo)
                            <option value="{{ $motivo->id }}">{{ $motivo->nombre }}</option>
                        @endforeach
                    </select>
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">
                        Si seleccionás un motivo aquí, <strong>se ignorará</strong> la columna "motivo" del CSV
                    </small>
                </div>
            </div>

            {{-- Fila 3: Opciones de creación automática --}}
            <div class="form-row" id="autoCreateOptions">
                <div class="form-col">
                    <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="crear_motivos" id="crearMotivos" value="1" checked style="margin: 0;">
                        <strong>Opción 2:</strong> Crear motivos automáticamente si vienen en el CSV
                    </label>
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem; margin-left: 1.5rem;">
                        Permite importar múltiples motivos en un solo CSV
                    </small>
                </div>
                <div class="form-col">
                    <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="crear_categorias" value="1" checked style="margin: 0;">
                        Crear categorías automáticamente
                    </label>
                </div>
            </div>
            
            {{-- Botón de importar --}}
            <div class="form-row">
                <div class="form-col">
                    <button type="submit" class="submit-btn" style="background: #4a6bff; width: 100%;">
                        📥 Importar CSV
                    </button>
                </div>
            </div>
            
            {{-- Información sobre formato CSV --}}
            <details style="margin-top: 1rem;">
                <summary class="csv-toggle" style="cursor: pointer; color: var(--accent); font-weight: 500;">
                    📋 Ver formatos de CSV aceptados
                </summary>
                <div class="csv-preview" style="background: rgba(0,0,0,0.3); padding: 1rem; border-radius: 8px; margin-top: 0.5rem;">
                    <p style="margin-bottom: 0.5rem;"><strong>Formato 1: Con columna "motivo" (múltiples motivos)</strong></p>
                    <pre style="background: rgba(0,0,0,0.5); padding: 0.75rem; border-radius: 4px; overflow-x: auto; font-size: 0.85rem;">motivo,categoria,pregunta,a,b,c,d,correcta
Anime,Dragon Ball,¿Quién es Goku?,Saiyajin,Humano,Namekiano,Android,A
Videojuegos,Mario Bros,¿Quién es Mario?,Fontanero,Chef,Carpintero,Pintor,A
Deportes,Fútbol,¿Cuántos jugadores por equipo?,11,10,12,9,A</pre>

                    <p style="margin: 1rem 0 0.5rem;"><strong>Formato 2: Sin columna "motivo" (usar selector)</strong></p>
                    <pre style="background: rgba(0,0,0,0.5); padding: 0.75rem; border-radius: 4px; overflow-x: auto; font-size: 0.85rem;">categoria,pregunta,a,b,c,d,correcta
Dragon Ball,¿Quién es Goku?,Saiyajin,Humano,Namekiano,Android,A
Naruto,¿Quién es Naruto?,Ninja,Samurai,Pirata,Caballero,A</pre>

                    <p style="margin: 1rem 0 0.5rem;"><strong>Columnas aceptadas (alias):</strong></p>
                    <ul style="list-style: disc; margin-left: 1.5rem; font-size: 0.9rem; color: var(--text-secondary);">
                        <li><code>motivo</code>: motivo, motivo_nombre, reason, tema</li>
                        <li><code>categoria</code>: categoria, category, cat, categoria_nombre</li>
                        <li><code>pregunta</code>: pregunta, texto, enunciado, question</li>
                        <li><code>correcta</code>: correcta, respuesta, opcion_correcta, correct, answer (acepta: A/B/C/D, 1-4, o texto exacto)</li>
                    </ul>
                </div>
            </details>
        </form>
    </div>

    <!-- Listados -->
    <div class="categories-section">
        <div class="category-card">
            <h3 class="category-title">Motivos existentes ({{ ($motivos ?? collect())->count() }})</h3>
            <form action="{{ route('motivo.bulkDelete') }}" method="POST">
                @csrf
                <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem; margin-bottom:0.75rem;">
                    <label style="display:flex; align-items:center; gap:0.5rem; color: var(--text-secondary); font-size:0.9rem;">
                        <input type="checkbox" id="selectAllMotivos" onclick="toggleSelectAll('motivos')">
                        Seleccionar todo
                    </label>
                    <button type="submit" class="action-btn" style="padding:0.5rem 0.9rem; background: rgba(120, 20, 20, 0.6); border-color: rgba(180, 60, 60, 0.9);"
                        onclick="return confirm('¿Eliminar los motivos seleccionados? Se eliminarán también sus categorías y sesiones relacionadas.');">
                        🗑️ Eliminar seleccionados
                    </button>
                </div>
                <ul class="category-list">
                    @forelse(($motivos ?? collect()) as $m)
                        <li class="category-item">
                            <span style="display:flex; align-items:center; gap:0.5rem;">
                                <input type="checkbox" class="chk-motivo" name="ids[]" value="{{ $m->id }}" data-group="motivos">
                                <span class="category-name">{{ $m->nombre }}</span>
                            </span>
                            <span class="category-motive" style="font-size: 0.85rem; opacity: 0.7;">
                                {{ $m->categorias->count() }} categorías
                            </span>
                        </li>
                    @empty
                        <li style="opacity: 0.5; font-style: italic;">No hay motivos creados</li>
                    @endforelse
                </ul>
            </form>
        </div>
        
        <div class="category-card">
            <h3 class="category-title">Categorías existentes ({{ ($categorias ?? collect())->count() }})</h3>
            <form action="{{ route('categoria.bulkDelete') }}" method="POST">
                @csrf
                <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem; margin-bottom:0.75rem;">
                    <label style="display:flex; align-items:center; gap:0.5rem; color: var(--text-secondary); font-size:0.9rem;">
                        <input type="checkbox" id="selectAllCategorias" onclick="toggleSelectAll('categorias')">
                        Seleccionar todo
                    </label>
                    <button type="submit" class="action-btn" style="padding:0.5rem 0.9rem; background: rgba(120, 20, 20, 0.6); border-color: rgba(180, 60, 60, 0.9);"
                        onclick="return confirm('¿Eliminar las categorías seleccionadas? Las preguntas quedarán sin categoría.');">
                        🗑️ Eliminar seleccionadas
                    </button>
                </div>
                <ul class="category-list">
                    @forelse(($categorias ?? collect()) as $c)
                        <li class="category-item">
                            <span style="display:flex; align-items:center; gap:0.5rem;">
                                <input type="checkbox" class="chk-categoria" name="ids[]" value="{{ $c->id }}" data-group="categorias">
                                <span class="category-name">{{ $c->nombre }}</span>
                            </span>
                            <span class="category-motive">{{ $c->motivo->nombre ?? '—' }}</span>
                        </li>
                    @empty
                        <li style="opacity: 0.5; font-style: italic;">No hay categorías creadas</li>
                    @endforelse
                </ul>
            </form>
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

    // Mostrar/ocultar opciones según si hay motivo forzado
    function toggleMotivoOptions() {
        const motivoForzado = document.getElementById('motivoForzado');
        const autoCreateOptions = document.getElementById('autoCreateOptions');
        const crearMotivosCheckbox = document.getElementById('crearMotivos');
        
        if (motivoForzado.value) {
            // Si hay motivo forzado, deshabilitar creación de motivos
            crearMotivosCheckbox.checked = false;
            crearMotivosCheckbox.disabled = true;
            autoCreateOptions.style.opacity = '0.5';
        } else {
            // Si no hay motivo forzado, habilitar creación de motivos
            crearMotivosCheckbox.disabled = false;
            crearMotivosCheckbox.checked = true;
            autoCreateOptions.style.opacity = '1';
        }
    }
    
    // Inicializar al cargar
    document.addEventListener('DOMContentLoaded', toggleMotivoOptions);

    // Select-all y sincronización de checkboxes
    function toggleSelectAll(group) {
        const master = group === 'motivos' ? document.getElementById('selectAllMotivos') : document.getElementById('selectAllCategorias');
        const checkboxes = document.querySelectorAll(`input[type="checkbox"][data-group="${group}"]`);
        checkboxes.forEach(chk => chk.checked = !!master.checked);
    }

    // Mantener estado del "seleccionar todo" cuando se tocan individuales
    ['motivos','categorias'].forEach(group => {
        document.addEventListener('change', (e) => {
            const target = e.target;
            if (target.matches(`input[type="checkbox"][data-group="${group}"]`)) {
                const all = Array.from(document.querySelectorAll(`input[type="checkbox"][data-group="${group}"]`));
                const allChecked = all.length > 0 && all.every(chk => chk.checked);
                const master = group === 'motivos' ? document.getElementById('selectAllMotivos') : document.getElementById('selectAllCategorias');
                if (master) master.checked = allChecked;
            }
        });
    });
</script>
