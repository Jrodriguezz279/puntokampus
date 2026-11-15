@extends('app.layout')

@section('titulo', 'Mi Tienda — PuntoKampus')

@section('styles')
<style>
    .store-summary {
        background: white;
        border-radius: var(--radius);
        padding: 1.25rem;
        border: 1px solid var(--border);
        margin-bottom: 1.25rem;
    }

    .summary-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .store-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .order-card {
        background: white;
        border-radius: var(--radius);
        padding: 1rem;
        border: 1px solid var(--border);
        margin-bottom: 0.85rem;
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .form-group { margin-bottom: 0.85rem; }
    .form-group label { display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 0.3rem; }
    .form-control { width: 100%; padding: 0.6rem 0.8rem; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; }
</style>
@endsection

@section('content')

    <!-- Resumen de Mi Tienda -->
    <div class="store-summary">
        <div class="summary-header">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <div style="width:52px; height:52px; border-radius:12px; background:var(--bg); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; overflow:hidden; font-size:1.5rem; font-weight:800; color:var(--primary); flex-shrink:0;">
                    @if ($tienda->logo_url)
                        <img src="{{ $tienda->logo_url }}" alt="{{ $tienda->nombre }}" style="width:100%; height:100%; object-fit:cover;" onerror="this.onerror=null;this.parentElement.innerHTML='{{ mb_substr($tienda->nombre, 0, 1) }}';">
                    @else
                        {{ mb_substr($tienda->nombre, 0, 1) }}
                    @endif
                </div>
                <div>
                    <h1 style="font-size: 1.25rem; font-weight: 800; margin:0;">{{ $tienda->nombre }}</h1>
                    <span style="font-size: 0.82rem; color: var(--text-muted);">
                        📍 {{ $tienda->puntoEntrega->nombre ?? 'Campus UTB' }} • 🏷️ {{ $tienda->categoria1->nombre }}
                    </span>
                </div>
            </div>
            @if ($tienda->plan === 'premium')
                <span class="badge badge-saco10">⭐ Sacó 10</span>
            @else
                <span class="badge badge-matriculado">Matriculado</span>
            @endif
        </div>

        <p style="font-size: 0.88rem; color: var(--text);">{{ $tienda->descripcion }}</p>

        <!-- Cambiar logo de la tienda -->
        <details style="margin: 0.75rem 0 0.5rem; font-size: 0.83rem;">
            <summary style="cursor:pointer; color:var(--primary); font-weight:600;">📷 Actualizar logo de la tienda</summary>
            <form method="POST" action="/app/mi-tienda/logo" enctype="multipart/form-data" style="margin-top:0.5rem; display:flex; gap:0.5rem; align-items:center;">
                @csrf
                <input type="file" name="logo" accept="image/png,image/jpeg,image/jpg" required class="form-control" style="font-size:0.8rem; padding:0.35rem;">
                <button type="submit" class="btn btn-secondary" style="font-size:0.8rem; white-space:nowrap; padding:0.4rem 0.8rem; min-height:36px;">Subir logo</button>
            </form>
        </details>

        <div class="store-actions">
            <a href="/app/tienda/{{ $tienda->slug }}" class="btn btn-secondary" style="flex:1; min-height:40px; font-size:0.85rem;">
                👁️ Ver cómo la ven los demás
            </a>
            @if ($tienda->plan !== 'premium')
                <a href="#plan-premium" class="btn btn-accent" style="flex:1; min-height:40px; font-size:0.85rem; text-align:center;">
                    ⭐ Mejorar a Sacó 10
                </a>
            @endif
        </div>
    </div>

    <!-- Agregar Nuevo Producto -->
    <div class="card" style="margin-bottom: 1.25rem;">
        <h2 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.85rem;">
            ➕ Publicar nuevo producto
        </h2>
        <form method="POST" action="/app/mi-tienda/producto" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="nombre">Nombre del Producto</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ej: Pastel de Zanahoria" class="form-control" required>
            </div>

            <div style="display:flex; gap:0.75rem;">
                <div class="form-group" style="flex:1;">
                    <label for="precio">Precio ($)</label>
                    <input type="number" id="precio" name="precio" step="0.05" min="0.25" max="50" placeholder="1.25" class="form-control" required>
                </div>
                <div class="form-group" style="flex:1.5;">
                    <label for="foto">Foto del Producto (opcional)</label>
                    <input type="file" id="foto" name="foto" accept="image/*" class="form-control" style="padding:0.4rem;">
                </div>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción o ingredientes</label>
                <input type="text" id="descripcion" name="descripcion" placeholder="Casero, recién horneado..." class="form-control">
            </div>

            <button type="submit" class="btn btn-primary" style="min-height:42px;">
                Publicar en mi Tienda
            </button>
        </form>
    </div>

    <!-- Pestañas de Gestión -->
    <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.85rem;">
        📦 Pedidos recibidos de compradores ({{ $pedidos->count() }})
    </h2>

    @forelse ($pedidos as $pedido)
        <div class="order-card">
            <div class="order-header">
                <div>
                    <strong style="color:var(--primary); font-size:1rem;">{{ $pedido->codigo }}</strong>
                    <span style="font-size:0.8rem; color:var(--text-muted); margin-left:0.4rem;">
                        {{ $pedido->creado_en ? $pedido->creado_en->format('d/m H:i') : '' }}
                    </span>
                </div>
                @if ($pedido->estado === 'entregado')
                    <span class="badge badge-entregado">Entregado</span>
                @elseif ($pedido->estado === 'pendiente')
                    <span class="badge badge-pendiente">Pendiente</span>
                @else
                    <span class="badge badge-cancelado">{{ ucfirst($pedido->estado) }}</span>
                @endif
            </div>

            <div style="font-size:0.88rem; margin:0.4rem 0;">
                👤 <strong>{{ $pedido->comprador->nombres }}</strong> ({{ $pedido->comprador->correo }})
            </div>

            <div style="background:#f9fafb; padding:0.65rem 0.8rem; border-radius:8px; font-size:0.85rem; margin:0.5rem 0;">
                @foreach ($pedido->items as $item)
                    <div style="display:flex; justify-content:space-between;">
                        <span>{{ $item->cantidad }}x {{ $item->nombre_producto }}</span>
                        <strong>${{ number_format($item->subtotal, 2) }}</strong>
                    </div>
                @endforeach
                <div style="border-top:1px dashed #e5e7eb; margin-top:0.4rem; padding-top:0.4rem; display:flex; justify-content:space-between; font-weight:700;">
                    <span>Total:</span>
                    <span style="color:#16a34a;">${{ number_format($pedido->total, 2) }} ({{ ucfirst($pedido->forma_pago) }})</span>
                </div>
            </div>

            <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:0.75rem;">
                📍 Entrega: <strong>{{ $pedido->puntoEntrega->nombre ?? 'Campus' }}</strong> • ⏰ {{ date('H:i', strtotime($pedido->hora_entrega)) }}
                @if ($pedido->nota)
                    <br>💬 <em>"{{ $pedido->nota }}"</em>
                @endif
            </div>

            @if ($pedido->estado === 'pendiente')
                <div style="background:#FFF8F5; border:1px solid #fed7aa; border-radius:8px; padding:0.75rem; margin-top:0.5rem;">
                    <div style="font-size:0.8rem; font-weight:700; color:#A73C00; margin-bottom:0.4rem;">
                        🔑 Confirmar entrega con código del comprador
                    </div>
                    @if ($pedido->codigo_bloqueado)
                        <div style="font-size:0.82rem; color:#dc2626; font-weight:600; padding:0.3rem 0;">
                            ⚠️ Código bloqueado tras 5 intentos fallidos. Contacta al administrador para desbloquear.
                        </div>
                    @else
                        <form method="POST" action="/app/mi-tienda/pedidos/{{ $pedido->codigo }}/confirmar-entrega" style="display:flex; gap:0.4rem; align-items:center; margin-bottom:0.5rem;">
                            @csrf
                            <input type="text" name="codigo_entrega" maxlength="4" pattern="[0-9]{4}" placeholder="0000" required
                                   style="width:90px; text-align:center; font-size:1.15rem; font-weight:800; letter-spacing:4px; padding:0.35rem; border:1px solid var(--border); border-radius:6px;"
                                   title="Código de 4 dígitos dado por el comprador">
                            <button type="submit" class="btn btn-primary" style="background:#16a34a; flex:1; min-height:38px; font-size:0.85rem; padding:0.4rem 0.6rem;">
                                ✓ Confirmar entrega
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="/app/mi-tienda/pedidos/{{ $pedido->codigo }}/cancelar">
                        @csrf
                        <button type="submit" class="btn btn-secondary" style="width:100%; min-height:30px; font-size:0.78rem; color:#dc2626; padding:0.25rem;" onclick="return confirm('¿Estás seguro de cancelar este pedido?');">
                            ✕ Cancelar pedido
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <div class="card" style="text-align:center; color:var(--text-muted); padding:2rem;">
            Aún no tienes pedidos recibidos.
        </div>
    @endforelse

    <!-- Mis Productos -->
    <h2 style="font-size: 1.1rem; font-weight: 700; margin: 1.5rem 0 0.85rem;">
        Mis productos a la venta ({{ $productos->count() }})
    </h2>

    @foreach ($productos as $prod)
        <div style="background:white; border-radius:10px; padding:0.85rem; border:1px solid var(--border); margin-bottom:0.6rem; display:flex; justify-content:space-between; align-items:center; gap:0.5rem;">
            <div style="display:flex; align-items:center; gap:0.75rem; flex:1; min-width:0;">
                <div style="width:48px; height:48px; border-radius:8px; background:#f3f4f6; border:1px solid var(--border); display:flex; align-items:center; justify-content:center; overflow:hidden; font-size:1.4rem; flex-shrink:0;">
                    @if ($prod->fotos->isNotEmpty() && $prod->fotos->first()->url)
                        <img src="{{ $prod->fotos->first()->url }}" alt="{{ $prod->nombre }}" style="width:100%; height:100%; object-fit:cover;" onerror="this.onerror=null;this.parentElement.innerHTML='🍪';">
                    @else
                        🍪
                    @endif
                </div>
                <div style="min-width:0;">
                    <strong style="display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $prod->nombre }}</strong>
                    <span style="font-size:0.8rem; color:var(--text-muted); display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $prod->descripcion ?: 'Sin descripción' }}</span>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:0.5rem; flex-shrink:0;">
                <strong style="color:#16a34a; font-size:1.05rem;">${{ number_format($prod->precio, 2) }}</strong>
                <button type="button" class="btn btn-secondary" style="min-height:32px; font-size:0.78rem; padding:0.25rem 0.6rem;"
                        onclick="abrirEditarProducto({{ $prod->id }}, '{{ addslashes($prod->nombre) }}', '{{ addslashes($prod->descripcion ?? '') }}', {{ $prod->precio }}, '{{ $prod->fotos->first()?->url ?? '' }}')">
                    ✏️ Editar
                </button>
                <form method="POST" action="/app/mi-tienda/producto/{{ $prod->id }}/eliminar" onsubmit="return confirm('¿Eliminar {{ addslashes($prod->nombre) }}?');" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="min-height:32px; font-size:0.78rem; padding:0.25rem 0.55rem; color:#dc2626;" title="Eliminar producto">
                        🗑️
                    </button>
                </form>
            </div>
        </div>
    @endforeach

    <!-- P18: Plan Premium (Sacó 10) -->
    <div id="plan-premium" class="card" style="margin-top: 1.75rem; border: 1.5px solid #FCD34D; background: linear-gradient(180deg, #FFFDF5 0%, #FFFFFF 100%);">
        <div style="display:flex; align-items:center; gap:0.6rem; margin-bottom:0.75rem;">
            <span style="font-size:1.8rem;">⭐</span>
            <div>
                <h2 style="font-size: 1.15rem; font-weight: 800; color: #92400E; margin:0;">
                    Plan Premium "Sacó 10"
                </h2>
                <span style="font-size:0.82rem; color:var(--text-muted);">
                    Destaca tu tienda ante toda la universidad por ${{ number_format($config->precio_premium ?? 5.00, 2) }} / semestre
                </span>
            </div>
        </div>

        <ul style="font-size: 0.85rem; line-height: 1.5; color: var(--text); padding-left: 1.25rem; margin-bottom: 1rem;">
            <li>Insignia dorada oficial <strong>⭐ Sacó 10</strong> visible en el catálogo y tu perfil.</li>
            <li>Posición prioritaria destacada en búsquedas y categorías.</li>
            <li>Recepción ilimitada de pedidos sin restricciones.</li>
        </ul>

        @if ($tienda->plan === 'premium')
            <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:8px; padding:0.85rem; color:#065f46; font-weight:700; font-size:0.9rem; text-align:center;">
                🎉 ¡Tu tienda ya cuenta con el Plan Sacó 10 activo!
            </div>
        @elseif ($ultimaSolicitudPremium && $ultimaSolicitudPremium->estado === 'pendiente')
            <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:0.85rem; color:#92400e; font-size:0.88rem;">
                ⏳ <strong>Solicitud en revisión</strong><br>
                Hemos recibido tu comprobante (N° {{ $ultimaSolicitudPremium->numero_comprobante }}). Nuestro equipo lo verificará en un plazo máximo de 24 horas.
            </div>
        @else
            @if ($ultimaSolicitudPremium && $ultimaSolicitudPremium->estado === 'rechazada')
                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:0.85rem; color:#991b1b; font-size:0.85rem; margin-bottom:1rem;">
                    ❌ <strong>Solicitud anterior rechazada:</strong> {{ $ultimaSolicitudPremium->motivo_rechazo ?? 'Comprobante no legible o datos no coincidentes.' }}<br>
                    Puedes corregir los datos y enviar un nuevo comprobante abajo.
                </div>
            @endif

            <div style="background:#f9fafb; border:1px solid var(--border); border-radius:8px; padding:0.85rem; font-size:0.82rem; margin-bottom:1rem;">
                <div style="font-weight:700; margin-bottom:0.4rem; color:var(--primary);">🏦 Datos de transferencia bancaria / Deuna:</div>
                <div><strong>Banco:</strong> {{ $config->banco ?? 'Banco Pichincha' }} ({{ $config->tipo_cuenta ?? 'Ahorros' }})</div>
                <div><strong>N° Cuenta:</strong> {{ $config->numero_cuenta ?? '2200123456' }}</div>
                <div><strong>Titular:</strong> {{ $config->titular_cuenta ?? 'PuntoKampus UTB' }}</div>
                @if ($config->usuario_deuna ?? '@puntokampus')
                    <div><strong>Deuna!:</strong> {{ $config->usuario_deuna ?? '@puntokampus' }}</div>
                @endif
            </div>

            <form method="POST" action="/app/mi-tienda/premium" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="numero_comprobante">Número de Comprobante / Transacción</label>
                    <input type="text" id="numero_comprobante" name="numero_comprobante" placeholder="Ej: 12345678" required class="form-control" maxlength="50" value="{{ old('numero_comprobante') }}">
                </div>

                <div style="display:flex; gap:0.75rem;">
                    <div class="form-group" style="flex:1;">
                        <label for="monto">Monto transferido ($)</label>
                        <input type="number" id="monto" name="monto" step="0.01" min="0.01" max="1000" class="form-control" required value="{{ old('monto', $config->precio_premium ?? '5.00') }}">
                    </div>
                    <div class="form-group" style="flex:1.5;">
                        <label for="foto_comprobante">Foto del Comprobante</label>
                        <input type="file" id="foto_comprobante" name="foto_comprobante" accept="image/png,image/jpeg,image/jpg" required class="form-control" style="padding:0.4rem;">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="background:#D97706; border-color:#B45309; min-height:42px; font-weight:700;">
                    ⭐ Enviar Comprobante para Activación
                </button>
            </form>
        @endif
    </div>

    <!-- Modal: Editar Producto -->
    <div id="modalEditarProducto" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:flex-end; justify-content:center;">
        <div style="background:white; width:100%; max-width:600px; border-radius:20px 20px 0 0; padding:1.5rem; max-height:88vh; overflow-y:auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="font-size:1.1rem; font-weight:700; margin:0;">✏️ Editar Producto</h3>
                <button onclick="cerrarEditarProducto()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:var(--text-muted);">✕</button>
            </div>
            <form id="formEditarProducto" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="edit_nombre">Nombre del Producto *</label>
                    <input type="text" id="edit_nombre" name="nombre" class="form-control" required maxlength="80">
                </div>
                <div class="form-group">
                    <label for="edit_descripcion">Descripción (opcional)</label>
                    <input type="text" id="edit_descripcion" name="descripcion" class="form-control" maxlength="300">
                </div>
                <div style="display:flex; gap:0.75rem;">
                    <div class="form-group" style="flex:1;">
                        <label for="edit_precio">Precio ($) *</label>
                        <input type="number" id="edit_precio" name="precio" step="0.05" min="0.25" max="50" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex:1.5;">
                        <label for="edit_foto">Cambiar Foto (opcional)</label>
                        <input type="file" id="edit_foto" name="foto" accept="image/png,image/jpeg,image/jpg" class="form-control" style="padding:0.4rem;">
                    </div>
                </div>
                <!-- Vista previa de la foto actual -->
                <div id="edit_foto_preview" style="display:none; align-items:center; gap:0.6rem; margin-bottom:0.75rem;">
                    <img id="edit_foto_img" src="" alt="Foto actual" style="width:70px; height:70px; object-fit:cover; border-radius:8px; border:1px solid var(--border);">
                    <span style="font-size:0.78rem; color:var(--text-muted);">Foto actual (selecciona un archivo para reemplazarla)</span>
                </div>
                <button type="submit" class="btn btn-primary" style="min-height:42px; width:100%;">
                    💾 Guardar Cambios
                </button>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    function abrirEditarProducto(id, nombre, descripcion, precio, fotoUrl) {
        document.getElementById('formEditarProducto').action = '/app/mi-tienda/producto/' + id;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_descripcion').value = descripcion;
        document.getElementById('edit_precio').value = precio;

        const preview = document.getElementById('edit_foto_preview');
        const img = document.getElementById('edit_foto_img');
        if (fotoUrl && fotoUrl.trim() !== '') {
            img.src = fotoUrl;
            preview.style.display = 'flex';
        } else {
            preview.style.display = 'none';
        }

        // Limpiar el input de archivo
        document.getElementById('edit_foto').value = '';

        const modal = document.getElementById('modalEditarProducto');
        modal.style.display = 'flex';
    }

    function cerrarEditarProducto() {
        document.getElementById('modalEditarProducto').style.display = 'none';
    }

    // Cerrar al hacer clic en el backdrop
    document.getElementById('modalEditarProducto').addEventListener('click', function(e) {
        if (e.target === this) cerrarEditarProducto();
    });
</script>
@endsection
