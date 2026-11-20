<!-- Panel de Activación de Audio (solo sale la primera vez) -->
<div id="panel-activacion" style="display: none; position: fixed; bottom: 20px; right: 20px; background: #f59e0b; color: #1e293b; padding: 15px 20px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); z-index: 9999; text-align: center; font-family: sans-serif;">
    <p style="margin: 0 0 8px 0; font-weight: bold; font-size: 13px;">🔊 El sonido de cocina está desactivado</p>
    <button type="button" onclick="activarAudioCocina()" style="background: #0f172a; color: white; border: none; padding: 8px 16px; font-size: 13px; border-radius: 8px; cursor: pointer; font-weight: bold;">
        Activar Sonido de Cocina
    </button>
</div>

<script>
    let audioCtx = null;
    let audioHabilitado = localStorage.getItem('sonidoCocinaActivado') === 'true';
    let ultimoPedidoId = null;

    // Inicializa el motor de audio en silencio si ya fue activado antes
    function iniciarMotorAudio() {
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (!audioHabilitado) {
            // Solo muestra el cartel amarillo si NUNCA se ha activado antes
            document.getElementById('panel-activacion').style.display = 'block';
        } else {
            iniciarMotorAudio();
        }
    });

    // Si ya estaba activado antes, cualquier clic en la página desbloquea el audio en Chrome sin mostrar el cartel
    document.addEventListener('click', () => {
        if (audioHabilitado) {
            iniciarMotorAudio();
        }
    });

    function tocarTimbreCocina() {
        iniciarMotorAudio();
        if (!audioCtx || audioCtx.state !== 'running') return;

        const ahora = audioCtx.currentTime;
        [523.25, 659.25].forEach((frecuencia, index) => {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(frecuencia, ahora + index * 0.18);

            gain.gain.setValueAtTime(0.4, ahora + index * 0.18);
            gain.gain.exponentialRampToValueAtTime(0.001, ahora + index * 0.18 + 0.6);

            osc.connect(gain);
            gain.connect(audioCtx.destination);

            osc.start(ahora + index * 0.18);
            osc.stop(ahora + index * 0.18 + 0.6);
        });
    }

    function activarAudioCocina() {
        localStorage.setItem('sonidoCocinaActivado', 'true');
        audioHabilitado = true;
        document.getElementById('panel-activacion').style.display = 'none';
        tocarTimbreCocina();
    }

    setInterval(async () => {
        try {
            const response = await fetch('/conteo-pedidos', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();

            if (ultimoPedidoId === null) {
                ultimoPedidoId = data.ultimo_id;
            } else if (data.ultimo_id > ultimoPedidoId) {
                ultimoPedidoId = data.ultimo_id;

                if (audioHabilitado) {
                    tocarTimbreCocina();
                }

                // Solo recarga automáticamente si estás en el Monitor de Pedidos
                if (window.location.pathname.includes('monitor-pedidos')) {
                    setTimeout(() => location.reload(), 1200);
                }
            }
        } catch (error) {
            console.log("Verificando nuevos pedidos...");
        }
    }, 3000);
</script>