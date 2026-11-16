<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\CodigoAcceso;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\PuntoEntrega;
use App\Models\Reporte;
use App\Models\SolicitudPremium;
use App\Models\Tienda;
use App\Models\Usuario;
use App\Services\AuthService;
use App\Services\ImagenService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AppEstudianteController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected ImagenService $imagenService,
    ) {}

    /**
     * P05: Vitrina Principal
     */
    public function vitrina(Request $request): View
    {
        $categoriaId = $request->query('categoria');
        $busqueda = trim((string) $request->query('q', ''));

        $query = Tienda::where('estado', 'activa')
            ->with(['categoria1', 'categoria2', 'puntoEntrega', 'usuario']);

        if ($categoriaId) {
            $query->where(function ($q) use ($categoriaId) {
                $q->where('categoria_1_id', $categoriaId)
                  ->orWhere('categoria_2_id', $categoriaId);
            });
        }

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->whereRaw('unaccent(lower(nombre)) LIKE unaccent(lower(?))', ["%{$busqueda}%"])
                  ->orWhereRaw('unaccent(lower(descripcion)) LIKE unaccent(lower(?))', ["%{$busqueda}%"])
                  ->orWhereHas('productos', function ($pq) use ($busqueda) {
                      $pq->whereNull('eliminado_en')
                         ->where(function ($sub) use ($busqueda) {
                             $sub->whereRaw('unaccent(lower(nombre)) LIKE unaccent(lower(?))', ["%{$busqueda}%"])
                                 ->orWhereRaw('unaccent(lower(descripcion)) LIKE unaccent(lower(?))', ["%{$busqueda}%"]);
                         });
                  });
            });
        }

        $tiendas = $query->orderByRaw("plan = 'premium' DESC")->paginate(20);
        $categorias = Categoria::orderBy('orden')->get();

        return view('app.vitrina', compact('tiendas', 'categorias', 'categoriaId', 'busqueda'));
    }

    /**
     * P07: Ver Tienda Pública y sus Productos
     */
    public function verTienda(string $slug): View
    {
        $tienda = Tienda::where('slug', $slug)
            ->where('estado', 'activa')
            ->with(['categoria1', 'categoria2', 'puntoEntrega', 'usuario'])
            ->firstOrFail();

        $productos = Producto::where('tienda_id', $tienda->id)
            ->whereNull('eliminado_en')
            ->where('disponible', true)
            ->with('fotos')
            ->get();

        $puntosEntrega = PuntoEntrega::where('activa', true)->orderBy('orden')->get();

        return view('app.tienda', compact('tienda', 'productos', 'puntosEntrega'));
    }

    /**
     * P01 / P02: Pantalla de Login con Correo
     */
    public function loginView(): View
    {
        return view('app.login');
    }

    /**
     * Procesar solicitud de código de 6 dígitos
     */
    public function solicitarCodigo(Request $request): RedirectResponse
    {
        $request->validate([
            'correo' => ['required', 'email'],
        ]);

        $correo = strtolower(trim($request->input('correo')));

        if (!$this->authService->esCorreoUtbValido($correo)) {
            return back()->withErrors(['correo' => 'Solo se puede entrar con un correo de la UTB (termina en utb.edu.ec).'])->withInput();
        }

        // Generar código de 6 dígitos
        $codigoPlano = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        CodigoAcceso::create([
            'correo'      => $correo,
            'codigo_hash' => Hash::make($codigoPlano),
            'expira_en'   => now()->addMinutes(10),
            'intentos'    => 0,
            'usado_en'    => null,
        ]);

        // Guardar correo y código en sesión para conveniencia de pruebas
        session([
            'login_correo' => $correo,
            'codigo_demo'  => $codigoPlano,
        ]);

        return redirect('/app/login/verificar');
    }

    /**
     * P03: Pantalla de Verificación de Código de 6 Dígitos
     */
    public function verificarView(): View
    {
        $correo = session('login_correo');
        if (!$correo) {
            return redirect('/app/login');
        }

        $codigoDemo = session('codigo_demo');

        return view('app.verificar', compact('correo', 'codigoDemo'));
    }

    /**
     * Procesar verificación de código
     */
    public function verificarCodigo(Request $request): RedirectResponse
    {
        $request->validate([
            'codigo' => ['required', 'string', 'size:6'],
        ]);

        $correo = session('login_correo');
        $codigo = trim($request->input('codigo'));

        $registroCodigo = CodigoAcceso::where('correo', $correo)
            ->whereNull('usado_en')
            ->orderBy('creado_en', 'desc')
            ->first();

        if (!$registroCodigo || $registroCodigo->expira_en->isPast()) {
            return back()->withErrors(['codigo' => 'Este código ya venció. Pide uno nuevo.']);
        }

        if (!Hash::check($codigo, $registroCodigo->codigo_hash)) {
            $registroCodigo->increment('intentos');
            $restantes = 5 - $registroCodigo->intentos;
            return back()->withErrors(['codigo' => "Ese código no es correcto. Te quedan {$restantes} intentos."]);
        }

        $registroCodigo->update(['usado_en' => now()]);

        // Obtener o crear usuario
        $usuario = Usuario::firstOrCreate(
            ['correo' => $correo],
            [
                'nombres'              => explode('@', $correo)[0],
                'estado'               => 'activa',
                'acepto_privacidad_en' => now(),
            ]
        );

        Auth::login($usuario);

        session()->forget(['login_correo', 'codigo_demo']);

        return redirect('/app')->with('mensaje', "¡Bienvenido/a, {$usuario->nombres}!");
    }

    /**
     * P14 / P15: Mi Tienda (Espacio del Vendedor)
     */
    public function miTienda(Request $request): View
    {
        $usuario = Auth::user();
        $tienda = Tienda::where('usuario_id', $usuario->id)->with(['categoria1', 'categoria2', 'puntoEntrega'])->first();

        if (!$tienda) {
            $categorias = Categoria::orderBy('orden')->get();
            $puntos = PuntoEntrega::where('activa', true)->orderBy('orden')->get();
            return view('app.tienda_crear', compact('categorias', 'puntos'));
        }

        $productos = Producto::where('tienda_id', $tienda->id)
            ->whereNull('eliminado_en')
            ->with('fotos')
            ->orderBy('id', 'desc')
            ->get();

        $pedidos = Pedido::where('tienda_id', $tienda->id)
            ->with(['comprador', 'puntoEntrega', 'items'])
            ->orderBy('id', 'desc')
            ->get();

        $config = Configuracion::first();
        $ultimaSolicitudPremium = SolicitudPremium::where('tienda_id', $tienda->id)
            ->latest('id')
            ->first();

        $categorias = Categoria::orderBy('orden')->get();
        $puntos = PuntoEntrega::where('activa', true)->orderBy('orden')->get();

        return view('app.mi_tienda', compact('tienda', 'productos', 'pedidos', 'config', 'ultimaSolicitudPremium', 'categorias', 'puntos'));
    }

    /**
     * Actualizar información de la tienda
     */
    public function actualizarTienda(Request $request): RedirectResponse
    {
        $usuario = Auth::user();
        $tienda = Tienda::where('usuario_id', $usuario->id)->first();

        if (!$tienda) {
            return back()->withErrors(['general' => 'No tienes una tienda creada.']);
        }

        $datos = $request->validate([
            'nombre'           => ['required', 'string', 'max:40'],
            'descripcion'      => ['nullable', 'string', 'max:300'],
            'categoria_1_id'   => ['required', 'exists:categorias,id'],
            'categoria_2_id'   => ['nullable', 'different:categoria_1_id', 'exists:categorias,id'],
            'punto_entrega_id' => ['nullable', 'exists:puntos_entrega,id'],
            'horario'          => ['nullable', 'string', 'max:100'],
            'whatsapp'         => ['required', 'string', 'max:20'],
            'logo'             => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        // Formatear WhatsApp
        $rawWhatsapp = preg_replace('/[^\d]/', '', $datos['whatsapp']);
        if (str_starts_with($rawWhatsapp, '09') && strlen($rawWhatsapp) === 10) {
            $rawWhatsapp = '593' . substr($rawWhatsapp, 1);
        }
        $usuario->update([
            'whatsapp' => $rawWhatsapp,
            'mostrar_whatsapp' => true,
        ]);

        // Actualizar slug si cambió el nombre
        if ($tienda->nombre !== $datos['nombre']) {
            $slugBase = Str::slug($datos['nombre']);
            $slug = $slugBase;
            $counter = 1;
            while (Tienda::where('slug', $slug)->where('id', '!=', $tienda->id)->exists()) {
                $slug = "{$slugBase}-{$counter}";
                $counter++;
            }
            $tienda->slug = $slug;
        }

        // Subir logo si se adjuntó
        if ($request->hasFile('logo')) {
            $url = $this->imagenService->procesarYGuardar($request->file('logo'), 'logos');
            $tienda->logo_url = $url;
        }

        $tienda->nombre = $datos['nombre'];
        $tienda->descripcion = $datos['descripcion'] ?? null;
        $tienda->categoria_1_id = $datos['categoria_1_id'];
        $tienda->categoria_2_id = $datos['categoria_2_id'] ?? null;
        $tienda->punto_entrega_id = $datos['punto_entrega_id'] ?? null;
        $tienda->horario = $datos['horario'] ?? null;
        $tienda->save();

        return redirect('/app/mi-tienda')->with('mensaje', 'Información de la tienda actualizada con éxito.');
    }

    /**
     * Guardar nueva tienda del vendedor
     */
    public function guardarTienda(Request $request): RedirectResponse
    {
        $usuario = Auth::user();

        if (Tienda::where('usuario_id', $usuario->id)->exists()) {
            return back()->withErrors(['general' => 'Ya tienes una tienda creada.']);
        }

        $datos = $request->validate([
            'nombre'           => ['required', 'string', 'max:40'],
            'descripcion'      => ['nullable', 'string', 'max:300'],
            'categoria_1_id'   => ['required', 'exists:categorias,id'],
            'categoria_2_id'   => ['nullable', 'different:categoria_1_id', 'exists:categorias,id'],
            'punto_entrega_id' => ['nullable', 'exists:puntos_entrega,id'],
            'horario'          => ['nullable', 'string', 'max:100'],
            'whatsapp'         => ['required', 'string', 'max:20'],
            'logo'             => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        // Formatear WhatsApp
        $rawWhatsapp = preg_replace('/[^\d]/', '', $datos['whatsapp']);
        if (str_starts_with($rawWhatsapp, '09') && strlen($rawWhatsapp) === 10) {
            $rawWhatsapp = '593' . substr($rawWhatsapp, 1);
        }
        $usuario->update([
            'whatsapp' => $rawWhatsapp,
            'mostrar_whatsapp' => true,
        ]);

        $slugBase = Str::slug($datos['nombre']);
        $slug = $slugBase;
        $counter = 1;
        while (Tienda::where('slug', $slug)->exists()) {
            $slug = "{$slugBase}-{$counter}";
            $counter++;
        }

        $logoUrl = null;
        if ($request->hasFile('logo')) {
            $logoUrl = $this->imagenService->procesarYGuardar($request->file('logo'), 'logos');
        }

        Tienda::create([
            'usuario_id'       => $usuario->id,
            'nombre'           => $datos['nombre'],
            'slug'             => $slug,
            'descripcion'      => $datos['descripcion'] ?? null,
            'categoria_1_id'   => $datos['categoria_1_id'],
            'categoria_2_id'   => $datos['categoria_2_id'] ?? null,
            'punto_entrega_id' => $datos['punto_entrega_id'] ?? null,
            'horario'          => $datos['horario'] ?? null,
            'logo_url'         => $logoUrl,
            'plan'             => 'gratis',
            'estado'           => 'activa',
        ]);

        return redirect('/app/mi-tienda')->with('mensaje', '¡Tu tienda ha sido creada y publicada con éxito!');
    }

    /**
     * Subir o actualizar logo de la tienda
     */
    public function subirLogo(Request $request): RedirectResponse
    {
        $usuario = Auth::user();
        $tienda = Tienda::where('usuario_id', $usuario->id)->firstOrFail();

        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ], [
            'logo.required' => 'Selecciona una imagen de logo.',
            'logo.image'    => 'El archivo debe ser una imagen.',
            'logo.max'      => 'El logo no puede pesar más de 5MB.',
        ]);

        $url = $this->imagenService->procesarYGuardar($request->file('logo'), 'logos');
        $tienda->update(['logo_url' => $url]);

        return back()->with('mensaje', '¡Logo de la tienda actualizado con éxito!');
    }

    /**
     * P16: Publicar nuevo producto en Mi Tienda
     */
    public function guardarProducto(Request $request): RedirectResponse
    {
        $usuario = Auth::user();
        $tienda = Tienda::where('usuario_id', $usuario->id)->firstOrFail();

        $datos = $request->validate([
            'nombre'      => ['required', 'string', 'max:80'],
            'descripcion' => ['nullable', 'string', 'max:300'],
            'precio'      => ['required', 'numeric', 'min:0.25', 'max:50'],
            'foto'        => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $producto = Producto::create([
            'tienda_id'   => $tienda->id,
            'nombre'      => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'precio'      => $datos['precio'],
            'disponible'  => true,
        ]);

        if ($request->hasFile('foto')) {
            $url = $this->imagenService->procesarYGuardar($request->file('foto'), 'productos');
            $producto->fotos()->create([
                'url'   => $url,
                'orden' => 1,
            ]);
        }

        return redirect('/app/mi-tienda')->with('mensaje', '¡Producto añadido exitosamente a tu tienda!');
    }

    /**
     * P16: Actualizar producto existente (incluyendo foto y disponibilidad)
     */
    public function actualizarProducto(Request $request, int $id): RedirectResponse
    {
        $usuario = Auth::user();
        $tienda = Tienda::where('usuario_id', $usuario->id)->firstOrFail();
        $producto = Producto::where('tienda_id', $tienda->id)->whereNull('eliminado_en')->findOrFail($id);

        $datos = $request->validate([
            'nombre'      => ['required', 'string', 'max:80'],
            'descripcion' => ['nullable', 'string', 'max:300'],
            'precio'      => ['required', 'numeric', 'min:0.25', 'max:50'],
            'disponible'  => ['nullable', 'boolean'],
            'foto'        => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $producto->update([
            'nombre'      => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'precio'      => $datos['precio'],
            'disponible'  => $request->has('disponible') ? (bool) $request->input('disponible') : true,
        ]);

        if ($request->hasFile('foto')) {
            $url = $this->imagenService->procesarYGuardar($request->file('foto'), 'productos');
            $primeraFoto = $producto->fotos()->orderBy('orden')->first();
            if ($primeraFoto) {
                $primeraFoto->update(['url' => $url]);
            } else {
                $producto->fotos()->create([
                    'url'   => $url,
                    'orden' => 1,
                ]);
            }
        }

        return redirect('/app/mi-tienda')->with('mensaje', "¡Producto '{$producto->nombre}' actualizado con éxito!");
    }

    /**
     * P16: Eliminar producto desde la web
     */
    public function eliminarProducto(int $id): RedirectResponse
    {
        $usuario = Auth::user();
        $tienda = Tienda::where('usuario_id', $usuario->id)->firstOrFail();
        $producto = Producto::where('tienda_id', $tienda->id)->whereNull('eliminado_en')->findOrFail($id);

        $producto->update(['eliminado_en' => now()]);

        return redirect('/app/mi-tienda')->with('mensaje', "Producto '{$producto->nombre}' eliminado.");
    }

    /**
     * P10: Realizar un Pedido como Comprador
     */
    public function crearPedido(Request $request): RedirectResponse
    {
        $usuario = Auth::user();

        $datos = $request->validate([
            'tienda_id'        => ['required', 'exists:tiendas,id'],
            'producto_id'      => ['required', 'exists:productos,id'],
            'cantidad'         => ['required', 'integer', 'min:1', 'max:20'],
            'punto_entrega_id' => ['required', 'exists:puntos_entrega,id'],
            'forma_pago'       => ['required', 'in:efectivo,transferencia,deuna'],
            'hora_entrega'     => ['required', 'date', 'after:+29 minutes', 'before:+7 days'],
            'nota'             => ['nullable', 'string', 'max:200'],
        ], [
            'hora_entrega.after'  => 'La hora de entrega debe ser de al menos 30 minutos en el futuro.',
            'hora_entrega.before' => 'La hora de entrega no puede superar los 7 días en el futuro.',
        ]);

        $tienda = Tienda::findOrFail($datos['tienda_id']);

        // R12: No autoregalo
        if ($tienda->usuario_id === $usuario->id) {
            return back()->withErrors(['pedido' => 'No puedes hacer pedidos a tu propia tienda.']);
        }

        $producto = Producto::where('id', $datos['producto_id'])
            ->where('tienda_id', $tienda->id)
            ->whereNull('eliminado_en')
            ->where('disponible', true)
            ->firstOrFail();

        $subtotal = round($producto->precio * $datos['cantidad'], 2);
        $total = $subtotal;

        // Generar código único PK-XXXXXX
        $codigo = 'PK-' . strtoupper(Str::random(6));
        // R17: Generar código numérico de 4 dígitos para entrega
        $codigoEntrega = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $pedido = DB::transaction(function () use ($usuario, $tienda, $datos, $total, $codigo, $producto, $subtotal, $codigoEntrega) {
            $nuevoPedido = Pedido::create([
                'codigo'           => $codigo,
                'comprador_id'     => $usuario->id,
                'tienda_id'        => $tienda->id,
                'punto_entrega_id' => $datos['punto_entrega_id'],
                'hora_entrega'     => $datos['hora_entrega'],
                'forma_pago'       => $datos['forma_pago'],
                'nota'             => $datos['nota'] ?? null,
                'total'            => $total,
                'estado'           => 'pendiente',
                'codigo_entrega'   => Crypt::encryptString($codigoEntrega),
                'intentos_codigo'  => 0,
                'codigo_bloqueado' => false,
            ]);

            PedidoItem::create([
                'pedido_id'       => $nuevoPedido->id,
                'producto_id'     => $producto->id,
                'nombre_producto' => $producto->nombre,
                'precio_unitario' => $producto->precio,
                'cantidad'        => $datos['cantidad'],
                'subtotal'        => $subtotal,
            ]);

            return $nuevoPedido;
        });

        // Generar URL de WhatsApp oficial del PRD
        $punto = PuntoEntrega::find($datos['punto_entrega_id']);
        $horaFormato = date('H:i', strtotime($datos['hora_entrega']));
        $textoWhatsApp = "Hola {$tienda->nombre}, hice el pedido {$pedido->codigo} en PuntoKampus:\n"
            . "- {$datos['cantidad']}x {$producto->nombre} (\${$subtotal})\n"
            . "Total: \${$total}. Entrega: {$punto->nombre}, {$horaFormato}. Pago: " . ucfirst($datos['forma_pago']) . ".";

        $urlWhatsApp = "https://wa.me/{$tienda->usuario->whatsapp}?text=" . urlencode($textoWhatsApp);

        session(['url_whatsapp_pedido' => $urlWhatsApp, 'ultimo_pedido_codigo' => $pedido->codigo]);

        return redirect('/app/mis-pedidos')->with('pedido_creado', true);
    }

    /**
     * P12: Mis Pedidos (Comprador)
     */
    public function misPedidos(): View
    {
        $usuario = Auth::user();
        $pedidos = Pedido::where('comprador_id', $usuario->id)
            ->with(['tienda', 'puntoEntrega', 'items'])
            ->orderBy('id', 'desc')
            ->get();

        // R17: Descifrar código de entrega solo para el comprador y solo si está pendiente
        foreach ($pedidos as $pedido) {
            $pedido->codigo_entrega_descifrado = null;
            if ($pedido->estado === 'pendiente' && !$pedido->codigo_bloqueado && !empty($pedido->codigo_entrega)) {
                try {
                    $pedido->codigo_entrega_descifrado = Crypt::decryptString($pedido->codigo_entrega);
                } catch (\Throwable $e) {
                    $pedido->codigo_entrega_descifrado = null;
                }
            }
        }

        return view('app.mis_pedidos', compact('pedidos'));
    }

    /**
     * R17: Confirmar entrega con código por el vendedor
     */
    public function confirmarEntrega(Request $request, string $codigo): RedirectResponse
    {
        $usuario = Auth::user();
        $tienda = Tienda::where('usuario_id', $usuario->id)->firstOrFail();
        $pedido = Pedido::where('codigo', $codigo)->where('tienda_id', $tienda->id)->firstOrFail();

        $request->validate([
            'codigo_entrega' => ['required', 'string', 'size:4'],
        ], [
            'codigo_entrega.required' => 'Ingresa el código de 4 dígitos.',
            'codigo_entrega.size'     => 'El código debe tener exactamente 4 dígitos.',
        ]);

        if ($pedido->estado !== 'pendiente') {
            return back()->withErrors(['general' => "Solo se puede confirmar entrega de pedidos pendientes. Estado: '{$pedido->estado}'."]);
        }

        if ($pedido->codigo_bloqueado) {
            return back()->withErrors(['general' => 'Código bloqueado tras 5 intentos fallidos. Contacta al administrador para desbloquear.']);
        }

        if (!$pedido->verificarCodigoEntrega($request->input('codigo_entrega'))) {
            $pedido->refresh();
            if ($pedido->codigo_bloqueado) {
                return back()->withErrors(['general' => 'Código bloqueado tras 5 intentos fallidos. Contacta al administrador.']);
            }
            $restantes = 5 - $pedido->intentos_codigo;
            return back()->withErrors(['general' => "Código incorrecto. Intentos restantes: {$restantes}."]);
        }

        $pedido->update([
            'estado'             => 'entregado',
            'estado_cambiado_en' => now(),
            'codigo_entrega'     => null,
            'intentos_codigo'    => 0,
        ]);

        return back()->with('mensaje', "¡Pedido {$codigo} marcado como Entregado con éxito!");
    }

    /**
     * P18: Solicitar Plan Premium (Sacó 10) desde la web
     */
    public function solicitarPremium(Request $request): RedirectResponse
    {
        $usuario = Auth::user();
        $tienda = Tienda::where('usuario_id', $usuario->id)->firstOrFail();

        // R21: No permite otra solicitud mientras haya una pendiente
        $tienePendiente = SolicitudPremium::where('tienda_id', $tienda->id)
            ->where('estado', 'pendiente')
            ->exists();

        if ($tienePendiente) {
            return back()->withErrors(['general' => 'Ya tienes una solicitud en revisión.']);
        }

        $datos = $request->validate([
            'numero_comprobante' => ['required', 'string', 'min:4', 'max:50'],
            'monto'              => ['required', 'numeric', 'min:0.01', 'max:1000'],
            'foto_comprobante'   => ['required', 'file', 'image', 'max:5120'],
        ], [
            'numero_comprobante.required' => 'El número de comprobante es obligatorio.',
            'numero_comprobante.min'      => 'El número de comprobante debe tener al menos 4 caracteres.',
            'monto.required'              => 'El monto transferido es obligatorio.',
            'foto_comprobante.required'   => 'Debes adjuntar la foto del comprobante de pago.',
            'foto_comprobante.max'        => 'La foto del comprobante no puede pesar más de 5MB.',
        ]);

        $ruta = $request->file('foto_comprobante')->store('comprobantes', 'local');

        SolicitudPremium::create([
            'tienda_id'          => $tienda->id,
            'numero_comprobante' => $datos['numero_comprobante'],
            'monto'              => $datos['monto'],
            'foto_comprobante'   => $ruta,
            'estado'             => 'pendiente',
        ]);

        return back()->with('mensaje', '¡Solicitud de Plan Premium enviada! El equipo la revisará pronto.');
    }

    /**
     * P19: Reportar una tienda desde la web
     */
    public function reportarTienda(Request $request, string $slug): RedirectResponse
    {
        $usuario = Auth::user();
        $tienda = Tienda::where('slug', $slug)->firstOrFail();

        // R20: Prohibido autoreporte
        if ($tienda->usuario_id === $usuario->id) {
            return back()->withErrors(['general' => 'No puedes reportar tu propia tienda.']);
        }

        // R20: Límite de 1 reporte por tienda al día por usuario
        $yaReportoHoy = Reporte::where('tienda_id', $tienda->id)
            ->where('reportado_por', $usuario->id)
            ->whereDate('creado_en', Carbon::today())
            ->exists();

        if ($yaReportoHoy) {
            return back()->withErrors(['general' => 'Ya reportaste esta tienda hoy.']);
        }

        $datos = $request->validate([
            'motivo'  => ['required', 'string', 'in:prohibido,no_entrego,informacion_falsa,trato_inadecuado,otro'],
            'detalle' => [
                'nullable',
                'string',
                'max:300',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('motivo') === 'otro' && (!is_string($value) || mb_strlen(trim($value)) < 10)) {
                        $fail('Si el motivo es "otro", debes detallar la razón con al menos 10 caracteres.');
                    }
                },
            ],
        ], [
            'motivo.required' => 'Selecciona un motivo para el reporte.',
            'motivo.in'       => 'El motivo seleccionado no es válido.',
            'detalle.max'     => 'El detalle no puede superar los 300 caracteres.',
        ]);

        Reporte::create([
            'tienda_id'     => $tienda->id,
            'reportado_por' => $usuario->id,
            'motivo'        => $datos['motivo'],
            'detalle'       => $datos['detalle'] ?? null,
            'estado'        => 'nuevo',
        ]);

        return back()->with('mensaje', 'Gracias. El equipo lo revisará en 24 horas.');
    }

    /**
     * Cambiar estado a entregado por el vendedor
     */
    public function marcarEntregado(string $codigo): RedirectResponse
    {
        $usuario = Auth::user();
        $tienda = Tienda::where('usuario_id', $usuario->id)->firstOrFail();
        $pedido = Pedido::where('codigo', $codigo)->where('tienda_id', $tienda->id)->firstOrFail();

        $pedido->update([
            'estado'             => 'entregado',
            'estado_cambiado_en' => now(),
        ]);

        return back()->with('mensaje', "¡Pedido {$codigo} marcado como Entregado!");
    }

    /**
     * Cancelar pedido por vendedor
     */
    public function marcarCancelado(string $codigo): RedirectResponse
    {
        $usuario = Auth::user();
        $tienda = Tienda::where('usuario_id', $usuario->id)->firstOrFail();
        $pedido = Pedido::where('codigo', $codigo)->where('tienda_id', $tienda->id)->firstOrFail();

        $pedido->update([
            'estado'             => 'cancelado',
            'cancelado_por'      => 'vendedor',
            'estado_cambiado_en' => now(),
        ]);

        return back()->with('mensaje', "El pedido {$codigo} ha sido cancelado.");
    }

    /**
     * P13: Cuenta del Estudiante
     */
    public function cuenta(): View
    {
        $usuario = Auth::user();
        return view('app.cuenta', compact('usuario'));
    }

    /**
     * Cerrar Sesión
     */
    public function salir(): RedirectResponse
    {
        Auth::logout();
        return redirect('/app')->with('mensaje', 'Sesión cerrada correctamente.');
    }

    /**
     * Atajo de prueba: Iniciar sesión con un clic como un usuario específico
     */
    public function loginRapido(string $tipo): RedirectResponse
    {
        $correo = match ($tipo) {
            'vendedor'  => 'carlos.vendedor@est.utb.edu.ec',
            'comprador' => 'maria.compradora@est.utb.edu.ec',
            default     => 'carlos.vendedor@est.utb.edu.ec',
        };

        $usuario = Usuario::where('correo', $correo)->first();
        if ($usuario) {
            Auth::login($usuario);
            return redirect('/app')->with('mensaje', "Has iniciado sesión como {$usuario->nombres}");
        }

        return redirect('/app');
    }
}
