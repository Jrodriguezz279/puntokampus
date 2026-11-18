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
use App\Services\QrService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

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

        // Premium primero en su categoría / vitrina
        $tiendas = $query->orderByRaw("CASE WHEN plan = 'premium' THEN 0 ELSE 1 END")
            ->orderBy('creado_en', 'desc')
            ->paginate(20);
        $categorias = Categoria::orderBy('orden')->get();

        // Tramo 2: Tiendas destacadas (Premium) y ofertas para el Carrusel de la Vitrina
        $carruselTiendas = Tienda::where('estado', 'activa')
            ->where('plan', 'premium')
            ->with(['categoria1', 'productos' => function ($q) {
                $q->whereNull('eliminado_en')->where('disponible', true);
            }])
            ->take(6)
            ->get();

        $carruselOfertas = Producto::whereNull('eliminado_en')
            ->where('disponible', true)
            ->where('descuento_activo', true)
            ->whereHas('tienda', fn($q) => $q->where('estado', 'activa'))
            ->with(['tienda.puntoEntrega', 'fotos'])
            ->take(6)
            ->get();

        return view('app.vitrina', compact(
            'tiendas',
            'categorias',
            'categoriaId',
            'busqueda',
            'carruselTiendas',
            'carruselOfertas'
        ));
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
     * P01 / P02: Pantalla de Login con Correo y Contraseña
     */
    public function loginView(): View
    {
        return view('app.login');
    }

    /**
     * Procesar inicio de sesión web con correo institucional y contraseña
     */
    public function entrarWeb(Request $request): RedirectResponse
    {
        $request->validate([
            'correo'     => ['required', 'email'],
            'contrasena' => ['required', 'string'],
        ]);

        $correo = strtolower(trim($request->input('correo')));
        $contrasena = $request->input('contrasena');

        $usuario = Usuario::where('correo', $correo)->first();
        if ($usuario && $usuario->estado === 'desactivada') {
            return back()->withErrors(['correo' => 'Tu cuenta está desactivada. Escribe al equipo para más información.'])->withInput();
        }

        $valida = false;
        if ($usuario && !empty($usuario->password)) {
            $valida = Hash::check($contrasena, $usuario->password);
        }

        // Acceso de prueba / demostración
        if (!$valida && ($contrasena === 'PuntoKampus2026!' || $contrasena === '123456')) {
            if ($usuario && in_array($correo, ['maria.compradora@est.utb.edu.ec', 'carlos.vendedor@est.utb.edu.ec', 'admin@utb.edu.ec'])) {
                $valida = true;
            }
        }

        if (!$usuario || !$valida) {
            return back()->withErrors(['contrasena' => 'El correo o la contraseña son incorrectos.'])->withInput();
        }

        Auth::login($usuario);

        return redirect('/app')->with('mensaje', "¡Bienvenido/a de nuevo, {$usuario->nombres}!");
    }

    /**
     * Pantalla de Registro
     */
    public function registroView(): View
    {
        return view('app.registro');
    }

    /**
     * Solicitar código para registro de cuenta nueva
     */
    public function solicitarCodigoRegistro(Request $request): RedirectResponse
    {
        $request->validate(['correo' => ['required', 'email']]);
        $correo = strtolower(trim($request->input('correo')));

        if (!$this->authService->esCorreoUtbValido($correo)) {
            return back()->withErrors(['correo' => 'Solo se puede registrar con un correo de la UTB (termina en utb.edu.ec).'])->withInput();
        }

        try {
            $this->authService->solicitarCodigo($correo, 'acceso');
        } catch (\Throwable $e) {
            return back()->withErrors(['correo' => $e->getMessage()])->withInput();
        }

        session([
            'registro_correo' => $correo,
            'codigo_demo' => '123456',
        ]);

        return redirect('/app/registro/verificar');
    }

    /**
     * Pantalla de verificación para registro
     */
    public function verificarRegistroView(): View
    {
        $correo = session('registro_correo');
        if (!$correo) {
            return redirect('/app/registro');
        }
        $codigoDemo = session('codigo_demo');
        return view('app.registro_verificar', compact('correo', 'codigoDemo'));
    }

    /**
     * Completar registro con código y contraseña
     */
    public function completarRegistroWeb(Request $request): RedirectResponse
    {
        $request->validate([
            'codigo'     => ['required', 'string', 'size:6'],
            'contrasena' => ['required', 'string', 'min:6'],
            'nombres'    => ['nullable', 'string', 'max:150'],
        ]);

        $correo = session('registro_correo');
        if (!$correo) {
            return redirect('/app/registro');
        }

        try {
            $resultado = $this->authService->verificarCodigo(
                $correo,
                $request->input('codigo'),
                'web',
                $request->input('contrasena'),
                'acceso'
            );

            $usuario = $resultado['usuario'];
            if ($request->filled('nombres')) {
                $usuario->nombres = trim($request->input('nombres'));
                $usuario->save();
            }

            Auth::login($usuario);
            session()->forget(['registro_correo', 'codigo_demo']);

            return redirect('/app')->with('mensaje', "¡Cuenta creada exitosamente! Bienvenido/a, {$usuario->nombres}.");
        } catch (\Throwable $e) {
            return back()->withErrors(['codigo' => $e->getMessage()]);
        }
    }

    /**
     * Pantalla de Olvidé mi Contraseña
     */
    public function olvideContrasenaView(): View
    {
        return view('app.olvide_contrasena');
    }

    /**
     * Solicitar código para restablecer contraseña
     */
    public function solicitarCodigoRecuperacionWeb(Request $request): RedirectResponse
    {
        $request->validate(['correo' => ['required', 'email']]);
        $correo = strtolower(trim($request->input('correo')));

        if (!$this->authService->esCorreoUtbValido($correo)) {
            return back()->withErrors(['correo' => 'Solo se puede solicitar con un correo de la UTB (termina en utb.edu.ec).'])->withInput();
        }

        $usuario = Usuario::where('correo', $correo)->first();
        if (!$usuario || $usuario->estado === 'desactivada') {
            return back()->withErrors(['correo' => 'No encontramos una cuenta activa con este correo institucional.'])->withInput();
        }

        try {
            $this->authService->solicitarCodigoRecuperacion($correo);
        } catch (\Throwable $e) {
            return back()->withErrors(['correo' => $e->getMessage()])->withInput();
        }

        session([
            'recuperar_correo' => $correo,
            'codigo_demo' => '123456',
        ]);

        return redirect('/app/olvide-contrasena/recuperar');
    }

    /**
     * Pantalla para ingresar código y nueva contraseña
     */
    public function recuperarContrasenaView(): View
    {
        $correo = session('recuperar_correo');
        if (!$correo) {
            return redirect('/app/olvide-contrasena');
        }
        $codigoDemo = session('codigo_demo');
        return view('app.recuperar_contrasena', compact('correo', 'codigoDemo'));
    }

    /**
     * Procesar cambio de contraseña con código
     */
    public function procesarRecuperacionWeb(Request $request): RedirectResponse
    {
        $request->validate([
            'codigo'     => ['required', 'string', 'size:6'],
            'contrasena' => ['required', 'string', 'min:6'],
        ]);

        $correo = session('recuperar_correo');
        if (!$correo) {
            return redirect('/app/olvide-contrasena');
        }

        try {
            $this->authService->recuperarContrasena(
                $correo,
                $request->input('codigo'),
                $request->input('contrasena')
            );

            session()->forget(['recuperar_correo', 'codigo_demo']);

            return redirect('/app/login')->with('mensaje', '¡Tu contraseña ha sido actualizada con éxito! Inicia sesión con tu nueva contraseña.');
        } catch (\Throwable $e) {
            return back()->withErrors(['codigo' => $e->getMessage()]);
        }
    }

    /**
     * Procesar solicitud de código de 6 dígitos (modo compatible legacy)
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
            'nombre'             => ['required', 'string', 'max:40'],
            'descripcion'        => ['nullable', 'string', 'max:300'],
            'categoria_1_id'     => ['required', 'exists:categorias,id'],
            'categoria_2_id'     => ['nullable', 'different:categoria_1_id', 'exists:categorias,id'],
            'punto_entrega_id'   => ['nullable', 'exists:puntos_entrega,id'],
            'horario'            => ['nullable', 'string', 'max:100'],
            'dias_atencion'      => ['nullable', 'string', 'max:100'],
            'hora_apertura'      => ['nullable', 'string', 'max:10'],
            'hora_cierre'        => ['nullable', 'string', 'max:10'],
            'recibiendo_pedidos' => ['nullable', 'boolean'],
            'whatsapp'           => ['required', 'string', 'max:20'],
            'logo'               => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
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
        $tienda->dias_atencion = $datos['dias_atencion'] ?? null;
        $tienda->hora_apertura = $datos['hora_apertura'] ?? null;
        $tienda->hora_cierre = $datos['hora_cierre'] ?? null;
        $tienda->recibiendo_pedidos = $request->has('recibiendo_pedidos') ? (bool) $request->input('recibiendo_pedidos') : true;
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
            'nombre'             => ['required', 'string', 'max:40'],
            'descripcion'        => ['nullable', 'string', 'max:300'],
            'categoria_1_id'     => ['required', 'exists:categorias,id'],
            'categoria_2_id'     => ['nullable', 'different:categoria_1_id', 'exists:categorias,id'],
            'punto_entrega_id'   => ['nullable', 'exists:puntos_entrega,id'],
            'horario'            => ['nullable', 'string', 'max:100'],
            'dias_atencion'      => ['nullable', 'string', 'max:100'],
            'hora_apertura'      => ['nullable', 'string', 'max:10'],
            'hora_cierre'        => ['nullable', 'string', 'max:10'],
            'recibiendo_pedidos' => ['nullable', 'boolean'],
            'whatsapp'           => ['required', 'string', 'max:20'],
            'logo'               => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
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
            'usuario_id'         => $usuario->id,
            'nombre'             => $datos['nombre'],
            'slug'               => $slug,
            'descripcion'        => $datos['descripcion'] ?? null,
            'categoria_1_id'     => $datos['categoria_1_id'],
            'categoria_2_id'     => $datos['categoria_2_id'] ?? null,
            'punto_entrega_id'   => $datos['punto_entrega_id'] ?? null,
            'horario'            => $datos['horario'] ?? null,
            'dias_atencion'      => $datos['dias_atencion'] ?? null,
            'hora_apertura'      => $datos['hora_apertura'] ?? null,
            'hora_cierre'        => $datos['hora_cierre'] ?? null,
            'recibiendo_pedidos' => $request->has('recibiendo_pedidos') ? (bool) $request->input('recibiendo_pedidos') : true,
            'logo_url'           => $logoUrl,
            'plan'               => 'gratis',
            'estado'             => 'activa',
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
            'nombre'           => ['required', 'string', 'max:80'],
            'descripcion'      => ['nullable', 'string', 'max:300'],
            'precio'           => ['required', 'numeric', 'min:0.25', 'max:50'],
            'precio_descuento' => ['nullable', 'numeric', 'min:0.01', 'lt:precio'],
            'foto'             => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ], [
            'precio_descuento.lt' => 'El precio con descuento debe ser menor al precio normal.',
        ]);

        $tieneDescuento = !empty($datos['precio_descuento']) && (float) $datos['precio_descuento'] > 0;

        $producto = Producto::create([
            'tienda_id'        => $tienda->id,
            'nombre'           => $datos['nombre'],
            'descripcion'      => $datos['descripcion'] ?? null,
            'precio'           => $datos['precio'],
            'precio_descuento' => $tieneDescuento ? $datos['precio_descuento'] : null,
            'descuento_activo' => $tieneDescuento,
            'disponible'       => true,
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
     * P16: Actualizar producto existente (incluyendo foto, disponibilidad y descuento)
     */
    public function actualizarProducto(Request $request, int $id): RedirectResponse
    {
        $usuario = Auth::user();
        $tienda = Tienda::where('usuario_id', $usuario->id)->firstOrFail();
        $producto = Producto::where('tienda_id', $tienda->id)->whereNull('eliminado_en')->findOrFail($id);

        $datos = $request->validate([
            'nombre'           => ['required', 'string', 'max:80'],
            'descripcion'      => ['nullable', 'string', 'max:300'],
            'precio'           => ['required', 'numeric', 'min:0.25', 'max:50'],
            'precio_descuento' => ['nullable', 'numeric', 'min:0.01', 'lt:precio'],
            'disponible'       => ['nullable', 'boolean'],
            'foto'             => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ], [
            'precio_descuento.lt' => 'El precio con descuento debe ser menor al precio normal.',
        ]);

        $tieneDescuento = !empty($datos['precio_descuento']) && (float) $datos['precio_descuento'] > 0;

        $producto->update([
            'nombre'           => $datos['nombre'],
            'descripcion'      => $datos['descripcion'] ?? null,
            'precio'           => $datos['precio'],
            'precio_descuento' => $tieneDescuento ? $datos['precio_descuento'] : null,
            'descuento_activo' => $tieneDescuento,
            'disponible'       => $request->has('disponible') ? (bool) $request->input('disponible') : true,
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

        $minima = now('America/Guayaquil')->addMinutes(25)->toDateTimeString();
        $maxima = now('America/Guayaquil')->addDays(7)->toDateTimeString();

        $datos = $request->validate([
            'tienda_id'        => ['required', 'exists:tiendas,id'],
            'producto_id'      => ['required', 'exists:productos,id'],
            'cantidad'         => ['required', 'integer', 'min:1', 'max:20'],
            'punto_entrega_id' => ['required', 'exists:puntos_entrega,id'],
            'forma_pago'       => ['required', 'in:efectivo,transferencia,deuna'],
            'hora_entrega'     => ['required', 'date', "after:{$minima}", "before:{$maxima}"],
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

        // Tramo 2: Tienda no recibiendo pedidos
        if (!$tienda->recibiendo_pedidos) {
            $msg = 'La tienda ha pausado la recepción de pedidos temporalmente.';
            $proxima = $tienda->siguienteAperturaTexto();
            if ($proxima) {
                $msg .= " {$proxima}.";
            }
            return back()->withErrors(['pedido' => $msg]);
        }

        // Tramo 2: Validar franja horaria de entrega si está configurada
        if (!empty($tienda->hora_apertura) && !empty($tienda->hora_cierre)) {
            $horaEntregaStr = Carbon::parse($datos['hora_entrega'])->format('H:i');
            if ($horaEntregaStr < $tienda->hora_apertura || $horaEntregaStr > $tienda->hora_cierre) {
                return back()->withErrors([
                    'hora_entrega' => "El horario de atención es de {$tienda->hora_apertura} a {$tienda->hora_cierre}" .
                        ($tienda->dias_atencion ? " ({$tienda->diasAtencionLegible()})" : '') .
                        ". Por favor selecciona una hora dentro del horario o desde la siguiente apertura."
                ]);
            }
        }

        $producto = Producto::where('id', $datos['producto_id'])
            ->where('tienda_id', $tienda->id)
            ->whereNull('eliminado_en')
            ->where('disponible', true)
            ->firstOrFail();

        // Tramo 2: Descuentos para Premium / Productos con descuento activo
        $precioUnitario = $producto->precio_efectivo;
        $subtotal = round($precioUnitario * $datos['cantidad'], 2);
        $total = $subtotal;

        // Generar código único PK-XXXXXX
        $codigo = 'PK-' . strtoupper(Str::random(6));
        // R17: Generar código numérico de 4 dígitos para entrega
        $codigoEntrega = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $pedido = DB::transaction(function () use ($usuario, $tienda, $datos, $total, $codigo, $producto, $subtotal, $precioUnitario, $codigoEntrega) {
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
                'precio_unitario' => $precioUnitario,
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
     * R17: Confirmar entrega con código o escaneo QR por el vendedor
     */
    public function confirmarEntrega(Request $request, string $codigo): RedirectResponse
    {
        $usuario = Auth::user();
        $tienda = Tienda::where('usuario_id', $usuario->id)->firstOrFail();
        $pedido = Pedido::where('codigo', $codigo)->where('tienda_id', $tienda->id)->firstOrFail();

        $codigoRaw = trim((string) $request->input('codigo_entrega', ''));

        // Tramo 2: Soporta URL con ?c=1234, payload escaneado del QR "PK:<codigo>:<pin>" o 4 dígitos directos
        if (preg_match('/[?&]c=(\d{4})/', $codigoRaw, $matches)) {
            $codigoEntrega = $matches[1];
        } elseif (preg_match('/PK:[^:]+:(\d{4})/', $codigoRaw, $matches)) {
            $codigoEntrega = $matches[1];
        } elseif (preg_match('/^(\d{4})$/', $codigoRaw, $matches)) {
            $codigoEntrega = $matches[1];
        } else {
            $codigoEntrega = $codigoRaw;
        }

        if (strlen($codigoEntrega) !== 4 || !ctype_digit($codigoEntrega)) {
            return back()->withErrors(['general' => 'El código de entrega debe ser de 4 dígitos numéricos o un código QR válido.']);
        }

        if ($pedido->estado !== 'pendiente') {
            return back()->withErrors(['general' => "Solo se puede confirmar entrega de pedidos pendientes. Estado: '{$pedido->estado}'."]);
        }

        if ($pedido->codigo_bloqueado) {
            return back()->withErrors(['general' => 'Código bloqueado tras 5 intentos fallidos. Contacta al administrador para desbloquear.']);
        }

        if (!$pedido->verificarCodigoEntrega($codigoEntrega)) {
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
     * Tramo 2: Ver código QR del pedido (solo para el comprador)
     */
    public function verQrWeb(string $codigo): Response
    {
        $usuario = Auth::user();
        $pedido = Pedido::where('codigo', $codigo)
            ->where('comprador_id', $usuario->id)
            ->firstOrFail();

        $payload = $pedido->qr_payload;
        if (!$payload) {
            abort(404, 'Código QR no disponible o pedido ya entregado.');
        }

        $png = QrService::generarPng($payload, 280);

        return response($png, 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Tramo 2: Confirmación directa por escaneo de código QR desde la cámara del vendedor
     */
    public function confirmarEntregaWeb(Request $request, string $codigo): RedirectResponse
    {
        $pedido = Pedido::where('codigo', $codigo)->firstOrFail();
        $usuario = Auth::user();

        // Si no ha iniciado sesión, guardar URL de destino y redirigir al login
        if (!$usuario) {
            session(['url.intended' => $request->fullUrl()]);
            return redirect('/app/login')->with('mensaje', 'Inicia sesión como vendedor para confirmar la entrega del pedido.');
        }

        // Verificar que el usuario sea el dueño de la tienda del pedido
        if ($pedido->tienda->usuario_id !== $usuario->id) {
            return redirect('/app')->withErrors(['general' => 'No tienes permiso para confirmar este pedido. Solo el vendedor de la tienda puede hacerlo.']);
        }

        if ($pedido->estado === 'entregado') {
            return redirect('/app/mi-tienda')->with('mensaje', "El pedido #{$codigo} ya fue marcado como entregado.");
        }

        $codigoIngresado = trim((string) $request->query('c', ''));
        $codigoReal = $pedido->codigo_entrega_descifrado;

        if (empty($codigoIngresado) || $codigoIngresado !== $codigoReal) {
            return redirect('/app/mi-tienda')->withErrors(['general' => 'Código de entrega QR incorrecto o inválido.']);
        }

        $pedido->update([
            'estado'             => 'entregado',
            'estado_cambiado_en' => now(),
        ]);

        return redirect('/app/mi-tienda')->with('mensaje', "🎉 ¡Excelente! Pedido #{$pedido->codigo} confirmado y entregado con éxito mediante el escaneo de código QR.");
    }

    /**
     * Tramo 2: Alternar rápidamente interruptor "recibiendo pedidos"
     */
    public function toggleRecibiendoPedidos(): RedirectResponse
    {
        $usuario = Auth::user();
        $tienda = Tienda::where('usuario_id', $usuario->id)->firstOrFail();
        $tienda->recibiendo_pedidos = !$tienda->recibiendo_pedidos;
        $tienda->save();

        $estado = $tienda->recibiendo_pedidos ? 'activada (recibiendo pedidos con normalidad)' : 'pausada (no estás recibiendo pedidos)';
        return back()->with('mensaje', "Tu tienda ahora está {$estado}.");
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
