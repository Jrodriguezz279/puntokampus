@extends('app.layout')

@section('titulo', 'Crear mi Tienda — PuntoKampus')

@section('content')

    <div style="max-width: 640px; margin: 1rem auto;">
        <div class="card" style="padding: 1.75rem;">
            <div style="font-size: 2.5rem; text-align: center; margin-bottom: 0.5rem;">🏪</div>
            <h1 style="font-size: 1.35rem; font-weight: 800; text-align: center; color: var(--primary); margin:0;">
                Empezar a vender en PuntoKampus
            </h1>
            <p style="font-size: 0.88rem; color: var(--text-muted); text-align: center; margin: 0.4rem 0 1.5rem;">
                Publica tus productos ante toda la comunidad de la UTB. No cobramos comisiones y los cobros van directo a ti.
            </p>

            <form method="POST" action="/app/mi-tienda/crear" enctype="multipart/form-data">
                @csrf

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="logo" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.3rem;">
                        Logo de tu Tienda (opcional)
                    </label>
                    <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/jpg"
                           class="form-control" style="width:100%; padding:0.4rem; border:1px solid var(--border); border-radius:8px;">
                    <span style="font-size:0.75rem; color:var(--text-muted);">Foto o logo para tu perfil (máx. 5MB).</span>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="nombre" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.3rem;">
                        Nombre de tu Tienda *
                    </label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: Dulces de Babahoyo, Papelería Facu..." required
                           class="form-control" style="width:100%; padding:0.65rem 0.85rem; border:1px solid var(--border); border-radius:8px;"
                           value="{{ old('nombre') }}" maxlength="40">
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="whatsapp" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.3rem;">
                        Número de WhatsApp para recibir pedidos *
                    </label>
                    <input type="text" id="whatsapp" name="whatsapp" placeholder="Ej: 0991234567" required
                           class="form-control" style="width:100%; padding:0.65rem 0.85rem; border:1px solid var(--border); border-radius:8px;"
                           value="{{ old('whatsapp', Auth::user()->whatsapp) }}">
                    <span style="font-size:0.75rem; color:var(--text-muted);">Los compradores te enviarán los pedidos aquí.</span>
                </div>

                <div style="display:flex; gap:0.75rem; margin-bottom: 1rem; flex-wrap:wrap;">
                    <div style="flex:1; min-width:200px;">
                        <label for="categoria_1_id" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.3rem;">
                            Categoría Principal *
                        </label>
                        <select name="categoria_1_id" id="categoria_1_id" class="form-control" style="width:100%; padding:0.65rem 0.85rem; border:1px solid var(--border); border-radius:8px;" required>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="flex:1; min-width:200px;">
                        <label for="categoria_2_id" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.3rem;">
                            Segunda Categoría (opcional)
                        </label>
                        <select name="categoria_2_id" id="categoria_2_id" class="form-control" style="width:100%; padding:0.65rem 0.85rem; border:1px solid var(--border); border-radius:8px;">
                            <option value="">Ninguna</option>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="punto_entrega_id" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.3rem;">
                        Punto de Entrega habitual en el Campus *
                    </label>
                    <select name="punto_entrega_id" id="punto_entrega_id" class="form-control" style="width:100%; padding:0.65rem 0.85rem; border:1px solid var(--border); border-radius:8px;">
                        @foreach ($puntos as $punto)
                            <option value="{{ $punto->id }}">{{ $punto->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="horario" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.3rem;">
                        Horario de Atención (opcional)
                    </label>
                    <input type="text" id="horario" name="horario" placeholder="Ej: Lunes a Viernes de 10:00 a 14:00"
                           class="form-control" style="width:100%; padding:0.65rem 0.85rem; border:1px solid var(--border); border-radius:8px;"
                           value="{{ old('horario') }}" maxlength="100">
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="descripcion" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.3rem;">
                        Descripción breve de tu tienda
                    </label>
                    <textarea id="descripcion" name="descripcion" rows="3" placeholder="Cuéntale a la universidad qué vendes o preparas..."
                              class="form-control" style="width:100%; padding:0.65rem 0.85rem; border:1px solid var(--border); border-radius:8px;" maxlength="300">{{ old('descripcion') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="font-size:1rem; min-height:46px;">
                    Crear mi Tienda en PuntoKampus 🎉
                </button>
            </form>
        </div>
    </div>

@endsection
                Crear y Publicar mi Tienda 🚀
            </button>
        </form>
    </div>

@endsection
