// Configuración de Pusher
Pusher.logToConsole = true;

const pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {
    cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
    encrypted: true
});

const echo = new Echo({
    broadcaster: 'pusher',
    key: '{{ env("PUSHER_APP_KEY") }}',
    cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
    forceTLS: true
});

const activeSessionId = {{ $activeSession ? $activeSession->id : 'null' }};

// ===============================
// Actualizar lista de participantes en tiempo real
// ===============================
if (activeSessionId) {
    echo.channel(`game-session.${activeSessionId}`)
        .listen('ParticipantUpdated', (e) => {
            const container = document.getElementById('queue-container');
            if (e.participants.length > 0) {
                container.innerHTML = '';
                e.participants.forEach(p => {
                    const div = document.createElement('div');
                    div.classList.add('participant-item');
                    div.textContent = p.name; // Ajusta según tu modelo de usuario
                    container.appendChild(div);
                });
            } else {
                container.innerHTML = `<div class="empty-queue-message">
                    La lista de participantes aparecerá aquí cuando inicie una sesión
                </div>`;
            }
        });
}

// ===============================
// Función para girar la ruleta
// ===============================
const spinButton = document.querySelector('#spin-button');
if (spinButton) {
    spinButton.addEventListener('click', async () => {
        spinButton.disabled = true;
        try {
            const response = await fetch(`/game/${activeSessionId}/spin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
            });
            const data = await response.json();
            alert(`La ruleta eligió: ${data.result}`);
        } catch (error) {
            console.error(error);
            alert('Ocurrió un error al girar la ruleta');
        } finally {
            spinButton.disabled = false;
        }
    });
}

// ===============================
// Formulario pregunta random
// ===============================
const randomForm = document.querySelector('#random-question-form');
if (randomForm) {
    randomForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(randomForm);
        try {
            const response = await fetch(randomForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: formData
            });
            const data = await response.json();
            alert(`Pregunta enviada: ${data.question}`);
        } catch (error) {
            console.error(error);
            alert('Error enviando la pregunta random');
        }
    });
}

// ===============================
// Escuchar eventos de nueva pregunta
// ===============================
if (activeSessionId) {
    echo.channel(`game-session.${activeSessionId}`)
        .listen('NewQuestion', (e) => {
            const questionPanel = document.querySelector('#question-panel');
            if (questionPanel) {
                questionPanel.textContent = e.question.text;
            }
        });
}
