import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../theme/theme.dart';
import '../../widgets/pk_components.dart';
import '../../constants/app_strings.dart';
import '../../providers/auth_provider.dart';
import '../../providers/tienda_provider.dart';

// ══════════════════════════════════════════════════════════════════════════════
// PANTALLAS DE AUTENTICACIÓN — P01 Bienvenida / P02 Correo / P03 Código / P04 Perfil
// Todos los colores y estilos vienen de app/lib/theme/ únicamente.
// ══════════════════════════════════════════════════════════════════════════════

// ─── P01: Pantalla de Bienvenida ─────────────────────────────────────────────
class BienvenidaScreen extends StatelessWidget {
  const BienvenidaScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: PKColors.fondo,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: PKSpacing.base,
            vertical: PKSpacing.xl,
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const SizedBox(height: PKSpacing.lg),

              // ── Área central: logo + ilustración + descripción ──────────────
              Column(
                children: [
                  // Logo visual (ilustración lineal)
                  Container(
                    width: 110,
                    height: 110,
                    decoration: BoxDecoration(
                      color: PKColors.primarioSuave,
                      borderRadius: PKRadii.tarjetaAll,
                      border: Border.all(color: PKColors.borde),
                    ),
                    child: Center(
                      child: Icon(
                        Icons.storefront_outlined,
                        size: 56,
                        color: PKColors.primario,
                      ),
                    ),
                  ),

                  const SizedBox(height: PKSpacing.lg),

                  // Nombre de la app
                  const PKLogo(fontSize: 32),

                  const SizedBox(height: PKSpacing.base),

                  // Descripción editorial
                  Text(
                    'El marketplace universitario\nde la UTB.',
                    textAlign: TextAlign.center,
                    style: PKTextStyles.tituloPantalla.copyWith(
                      fontSize: 22,
                      fontWeight: FontWeight.w700,
                      color: PKColors.titulo,
                    ),
                  ),

                  const SizedBox(height: PKSpacing.sm),

                  Text(
                    'Compra y vende entre estudiantes\ndentro del campus.',
                    textAlign: TextAlign.center,
                    style: PKTextStyles.cuerpoSecundario,
                  ),
                ],
              ),

              // ── Área inferior: botones ──────────────────────────────────────
              Column(
                children: [
                  PKBotonPrimario(
                    texto: AppStrings.btnEntrarUtb,
                    icono: Icons.mail_outline_rounded,
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const CorreoScreen()),
                      );
                    },
                  ),
                  const SizedBox(height: PKSpacing.sm),
                  PKBotonSecundario(
                    texto: 'Ver la vitrina sin sesión',
                    icono: Icons.storefront_outlined,
                    onPressed: () => Navigator.pop(context),
                  ),
                  const SizedBox(height: PKSpacing.base),
                  Text(
                    'Solo para estudiantes y docentes UTB.',
                    style: PKTextStyles.etiqueta,
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── P02: Ingreso de Correo y Contraseña ───────────────────────────────────────
class CorreoScreen extends StatefulWidget {
  const CorreoScreen({super.key});

  @override
  State<CorreoScreen> createState() => _CorreoScreenState();
}

class _CorreoScreenState extends State<CorreoScreen> {
  final _correoController = TextEditingController();
  final _passwordController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  bool _ocultarPassword = true;

  @override
  void dispose() {
    _correoController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _iniciarSesion() async {
    if (!_formKey.currentState!.validate()) return;

    final correo = _correoController.text.trim().toLowerCase();
    final password = _passwordController.text;
    final auth = Provider.of<AuthProvider>(context, listen: false);

    final ok = await auth.login(correo, password);
    if (!mounted) return;

    if (ok) {
      Provider.of<TiendaProvider>(context, listen: false).cargarMiTienda();
      if (auth.usuario?.nombres == null || auth.usuario!.nombres!.isEmpty) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (_) => const PerfilScreen()),
        );
      } else {
        Navigator.popUntil(context, (route) => route.isFirst);
      }
    } else {
      pkSnackError(context, auth.error ?? 'El correo o la contraseña son incorrectos.');
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(
        title: const Text('Acceso con correo UTB'),
        backgroundColor: Colors.transparent,
        elevation: 0,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(PKSpacing.base),
          child: AutofillGroup(
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: PKSpacing.sm),

                  Text('Iniciar sesión', style: PKTextStyles.tituloPantalla),
                  const SizedBox(height: PKSpacing.sm),
                  Text(
                    'Ingresa tu correo institucional y contraseña de PuntoKampus.',
                    style: PKTextStyles.cuerpoSecundario,
                  ),

                  const SizedBox(height: PKSpacing.xl),

                  PKCampoEtiquetado(
                    etiqueta: 'Correo electrónico UTB',
                    hint: 'ejemplo@utb.edu.ec',
                    controller: _correoController,
                    keyboardType: TextInputType.emailAddress,
                    textInputAction: TextInputAction.next,
                    autofocus: true,
                    autofillHints: const [AutofillHints.username, AutofillHints.email],
                    prefixIcon: const Icon(Icons.mail_outline_rounded, size: 20, color: PKColors.secundario),
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return 'Por favor ingresa tu correo.';
                      }
                      if (!value.trim().toLowerCase().endsWith('utb.edu.ec')) {
                        return AppStrings.errCorreoNoUtb;
                      }
                      return null;
                    },
                  ),

                  const SizedBox(height: PKSpacing.base),

                  PKCampoEtiquetado(
                    etiqueta: 'Contraseña',
                    hint: '••••••••',
                    controller: _passwordController,
                    obscureText: _ocultarPassword,
                    textInputAction: TextInputAction.done,
                    autofillHints: const [AutofillHints.password],
                    prefixIcon: const Icon(Icons.lock_outline_rounded, size: 20, color: PKColors.secundario),
                    suffixIcon: IconButton(
                      icon: Icon(
                        _ocultarPassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                        size: 20,
                        color: PKColors.secundario,
                      ),
                      onPressed: () => setState(() => _ocultarPassword = !_ocultarPassword),
                    ),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'Ingresa tu contraseña.';
                      }
                      return null;
                    },
                  ),

                  Align(
                    alignment: Alignment.centerRight,
                    child: TextButton(
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => OlvidePasswordScreen(
                              correoInicial: _correoController.text.trim(),
                            ),
                          ),
                        );
                      },
                      child: Text(
                        '¿Olvidaste tu contraseña?',
                        style: PKTextStyles.etiqueta.copyWith(
                          color: PKColors.primario,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ),

                  const SizedBox(height: PKSpacing.sm),

                  PKBotonPrimario(
                    texto: 'Entrar',
                    onPressed: _iniciarSesion,
                    cargando: auth.cargando,
                  ),

                  const SizedBox(height: PKSpacing.base),

                  Center(
                    child: TextButton(
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => RegistroScreen(
                              correoInicial: _correoController.text.trim(),
                            ),
                          ),
                        );
                      },
                      child: RichText(
                        text: TextSpan(
                          text: '¿Aún no tienes cuenta? ',
                          style: PKTextStyles.cuerpoSecundario,
                          children: [
                            TextSpan(
                              text: 'Regístrate aquí',
                              style: PKTextStyles.cuerpoSecundario.copyWith(
                                color: PKColors.primario,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),

                  const SizedBox(height: PKSpacing.xl),

                  // ── Acceso rápido demo ────────────────────────────────────────
                  Center(
                    child: Text(
                      'Acceso rápido de evaluación (con contraseña guardada):',
                      style: PKTextStyles.etiqueta,
                    ),
                  ),
                  const SizedBox(height: PKSpacing.sm),
                  Row(
                    children: [
                      Expanded(
                        child: _BotonAccesoRapido(
                          label: 'María\nCompradora',
                          icono: Icons.shopping_bag_outlined,
                          onTap: () {
                            _correoController.text = 'maria.compradora@est.utb.edu.ec';
                            _passwordController.text = 'Compradora2026!';
                            _iniciarSesion();
                          },
                        ),
                      ),
                      const SizedBox(width: PKSpacing.sm),
                      Expanded(
                        child: _BotonAccesoRapido(
                          label: 'Carlos\nVendedor',
                          icono: Icons.storefront_outlined,
                          onTap: () {
                            _correoController.text = 'carlos.vendedor@est.utb.edu.ec';
                            _passwordController.text = 'Vendedor2026!';
                            _iniciarSesion();
                          },
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _BotonAccesoRapido extends StatelessWidget {
  final String label;
  final IconData icono;
  final VoidCallback onTap;

  const _BotonAccesoRapido({
    required this.label,
    required this.icono,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: PKSpacing.md, horizontal: PKSpacing.sm),
        decoration: BoxDecoration(
          color: PKColors.primarioSuave,
          borderRadius: PKRadii.botonAll,
          border: Border.all(color: PKColors.primarioSuaveTexto.withAlpha(50)),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icono, size: 18, color: PKColors.primarioSuaveTexto),
            const SizedBox(width: PKSpacing.xs),
            Text(
              label,
              textAlign: TextAlign.center,
              style: PKTextStyles.etiqueta.copyWith(
                color: PKColors.primarioSuaveTexto,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── RegistroScreen: Código al correo + crear contraseña ───────────────────────
class RegistroScreen extends StatefulWidget {
  final String? correoInicial;
  const RegistroScreen({super.key, this.correoInicial});

  @override
  State<RegistroScreen> createState() => _RegistroScreenState();
}

class _RegistroScreenState extends State<RegistroScreen> {
  final _correoController = TextEditingController();
  final _codigoController = TextEditingController();
  final _nombreController = TextEditingController();
  final _passwordController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  bool _codigoEnviado = false;
  bool _ocultarPassword = true;

  @override
  void initState() {
    super.initState();
    if (widget.correoInicial != null && widget.correoInicial!.isNotEmpty) {
      _correoController.text = widget.correoInicial!;
    }
  }

  @override
  void dispose() {
    _correoController.dispose();
    _codigoController.dispose();
    _nombreController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _solicitarCodigo() async {
    if (!_formKey.currentState!.validate()) return;
    final correo = _correoController.text.trim().toLowerCase();
    final auth = Provider.of<AuthProvider>(context, listen: false);

    final ok = await auth.solicitarCodigo(correo);
    if (!mounted) return;

    if (ok) {
      setState(() => _codigoEnviado = true);
      pkSnackOk(context, 'Código enviado a $correo');
    } else {
      pkSnackError(context, auth.error ?? 'No pudimos enviar el código.');
    }
  }

  Future<void> _completarRegistro() async {
    if (!_formKey.currentState!.validate()) return;

    final correo = _correoController.text.trim().toLowerCase();
    final codigo = _codigoController.text.trim();
    final password = _passwordController.text;
    final nombres = _nombreController.text.trim();

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final ok = await auth.verificarCodigo(correo, codigo, contrasena: password);
    if (!mounted) return;

    if (ok) {
      if (nombres.isNotEmpty) {
        await auth.actualizarPerfil(nombres: nombres);
      }
      if (!mounted) return;
      Provider.of<TiendaProvider>(context, listen: false).cargarMiTienda();
      if (auth.usuario?.nombres == null || auth.usuario!.nombres!.isEmpty) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (_) => const PerfilScreen()),
        );
      } else {
        Navigator.popUntil(context, (route) => route.isFirst);
      }
    } else {
      pkSnackError(context, auth.error ?? 'Código incorrecto o vencido.');
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(
        title: const Text('Crear cuenta UTB'),
        backgroundColor: Colors.transparent,
        elevation: 0,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(PKSpacing.base),
          child: AutofillGroup(
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: PKSpacing.sm),
                  Text(
                    _codigoEnviado ? 'Crea tu contraseña' : 'Registro con correo UTB',
                    style: PKTextStyles.tituloPantalla,
                  ),
                  const SizedBox(height: PKSpacing.sm),
                  Text(
                    _codigoEnviado
                        ? 'Ingresa el código enviado a ${_correoController.text} y define tu contraseña.'
                        : 'Te enviaremos un código de 6 dígitos a tu correo institucional para activar tu cuenta.',
                    style: PKTextStyles.cuerpoSecundario,
                  ),
                  const SizedBox(height: PKSpacing.xl),

                  if (!_codigoEnviado) ...[
                    PKCampoEtiquetado(
                      etiqueta: 'Correo institucional UTB',
                      hint: 'ejemplo@est.utb.edu.ec',
                      controller: _correoController,
                      keyboardType: TextInputType.emailAddress,
                      autofocus: true,
                      autofillHints: const [AutofillHints.email, AutofillHints.username],
                      prefixIcon: const Icon(Icons.mail_outline_rounded, size: 20, color: PKColors.secundario),
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Por favor ingresa tu correo.';
                        }
                        if (!value.trim().toLowerCase().endsWith('utb.edu.ec')) {
                          return AppStrings.errCorreoNoUtb;
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: PKSpacing.lg),
                    PKBotonPrimario(
                      texto: 'Enviar código de verificación',
                      onPressed: _solicitarCodigo,
                      cargando: auth.cargando,
                    ),
                  ] else ...[
                    PKCampoEtiquetado(
                      etiqueta: 'Código de 6 dígitos',
                      hint: '123456',
                      controller: _codigoController,
                      keyboardType: TextInputType.number,
                      autofocus: true,
                      maxLength: 6,
                      autofillHints: const [AutofillHints.oneTimeCode],
                      prefixIcon: const Icon(Icons.pin_outlined, size: 20, color: PKColors.secundario),
                      validator: (value) {
                        if (value == null || value.trim().length != 6) {
                          return 'Ingresa los 6 dígitos del código.';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: PKSpacing.base),
                    PKCampoEtiquetado(
                      etiqueta: 'Tu Nombre y Apellido (opcional)',
                      hint: 'Ej. Juan Pérez',
                      controller: _nombreController,
                      textInputAction: TextInputAction.next,
                      prefixIcon: const Icon(Icons.person_outline_rounded, size: 20, color: PKColors.secundario),
                    ),
                    const SizedBox(height: PKSpacing.base),
                    PKCampoEtiquetado(
                      etiqueta: 'Crear Contraseña',
                      hint: 'Mínimo 6 caracteres',
                      controller: _passwordController,
                      obscureText: _ocultarPassword,
                      textInputAction: TextInputAction.done,
                      autofillHints: const [AutofillHints.newPassword],
                      prefixIcon: const Icon(Icons.lock_outline_rounded, size: 20, color: PKColors.secundario),
                      suffixIcon: IconButton(
                        icon: Icon(
                          _ocultarPassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                          size: 20,
                          color: PKColors.secundario,
                        ),
                        onPressed: () => setState(() => _ocultarPassword = !_ocultarPassword),
                      ),
                      validator: (value) {
                        if (value == null || value.length < 6) {
                          return 'La contraseña debe tener al menos 6 caracteres.';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: PKSpacing.lg),
                    PKBotonPrimario(
                      texto: 'Crear cuenta y entrar',
                      onPressed: _completarRegistro,
                      cargando: auth.cargando,
                    ),
                    const SizedBox(height: PKSpacing.sm),
                    Center(
                      child: TextButton(
                        onPressed: () => setState(() => _codigoEnviado = false),
                        child: const Text('← Cambiar correo o reenviar código'),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// ─── OlvidePasswordScreen: Código al correo + restablecer contraseña ──────────
class OlvidePasswordScreen extends StatefulWidget {
  final String? correoInicial;
  const OlvidePasswordScreen({super.key, this.correoInicial});

  @override
  State<OlvidePasswordScreen> createState() => _OlvidePasswordScreenState();
}

class _OlvidePasswordScreenState extends State<OlvidePasswordScreen> {
  final _correoController = TextEditingController();
  final _codigoController = TextEditingController();
  final _passwordController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  bool _codigoEnviado = false;
  bool _ocultarPassword = true;

  @override
  void initState() {
    super.initState();
    if (widget.correoInicial != null && widget.correoInicial!.isNotEmpty) {
      _correoController.text = widget.correoInicial!;
    }
  }

  @override
  void dispose() {
    _correoController.dispose();
    _codigoController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _solicitarCodigo() async {
    if (!_formKey.currentState!.validate()) return;
    final correo = _correoController.text.trim().toLowerCase();
    final auth = Provider.of<AuthProvider>(context, listen: false);

    final ok = await auth.solicitarRecuperacion(correo);
    if (!mounted) return;

    if (ok) {
      setState(() => _codigoEnviado = true);
      pkSnackOk(context, 'Código de recuperación enviado a $correo');
    } else {
      pkSnackError(context, auth.error ?? 'No pudimos enviar el código de recuperación.');
    }
  }

  Future<void> _guardarNuevaContrasena() async {
    if (!_formKey.currentState!.validate()) return;

    final correo = _correoController.text.trim().toLowerCase();
    final codigo = _codigoController.text.trim();
    final password = _passwordController.text;

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final ok = await auth.recuperarContrasena(correo, codigo, password);
    if (!mounted) return;

    if (ok) {
      pkSnackOk(context, '¡Contraseña actualizada! Ya puedes iniciar sesión.');
      Navigator.pop(context);
    } else {
      pkSnackError(context, auth.error ?? 'Código incorrecto o no se pudo actualizar.');
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(
        title: const Text('Recuperar contraseña'),
        backgroundColor: Colors.transparent,
        elevation: 0,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(PKSpacing.base),
          child: AutofillGroup(
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: PKSpacing.sm),
                  Text(
                    _codigoEnviado ? 'Nueva contraseña' : 'Olvidé mi contraseña',
                    style: PKTextStyles.tituloPantalla,
                  ),
                  const SizedBox(height: PKSpacing.sm),
                  Text(
                    _codigoEnviado
                        ? 'Escribe el código recibido y tu nueva contraseña.'
                        : 'Ingresa tu correo institucional y te enviaremos un código para restablecerla.',
                    style: PKTextStyles.cuerpoSecundario,
                  ),
                  const SizedBox(height: PKSpacing.xl),

                  if (!_codigoEnviado) ...[
                    PKCampoEtiquetado(
                      etiqueta: 'Correo institucional UTB',
                      hint: 'ejemplo@est.utb.edu.ec',
                      controller: _correoController,
                      keyboardType: TextInputType.emailAddress,
                      autofocus: true,
                      autofillHints: const [AutofillHints.email, AutofillHints.username],
                      prefixIcon: const Icon(Icons.mail_outline_rounded, size: 20, color: PKColors.secundario),
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return 'Por favor ingresa tu correo.';
                        }
                        if (!value.trim().toLowerCase().endsWith('utb.edu.ec')) {
                          return AppStrings.errCorreoNoUtb;
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: PKSpacing.lg),
                    PKBotonPrimario(
                      texto: 'Enviar código de recuperación',
                      onPressed: _solicitarCodigo,
                      cargando: auth.cargando,
                    ),
                  ] else ...[
                    PKCampoEtiquetado(
                      etiqueta: 'Código de 6 dígitos',
                      hint: '123456',
                      controller: _codigoController,
                      keyboardType: TextInputType.number,
                      autofocus: true,
                      maxLength: 6,
                      autofillHints: const [AutofillHints.oneTimeCode],
                      prefixIcon: const Icon(Icons.pin_outlined, size: 20, color: PKColors.secundario),
                      validator: (value) {
                        if (value == null || value.trim().length != 6) {
                          return 'Ingresa los 6 dígitos del código.';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: PKSpacing.base),
                    PKCampoEtiquetado(
                      etiqueta: 'Nueva Contraseña',
                      hint: 'Mínimo 6 caracteres',
                      controller: _passwordController,
                      obscureText: _ocultarPassword,
                      textInputAction: TextInputAction.done,
                      autofillHints: const [AutofillHints.newPassword],
                      prefixIcon: const Icon(Icons.lock_outline_rounded, size: 20, color: PKColors.secundario),
                      suffixIcon: IconButton(
                        icon: Icon(
                          _ocultarPassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                          size: 20,
                          color: PKColors.secundario,
                        ),
                        onPressed: () => setState(() => _ocultarPassword = !_ocultarPassword),
                      ),
                      validator: (value) {
                        if (value == null || value.length < 6) {
                          return 'La contraseña debe tener al menos 6 caracteres.';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: PKSpacing.lg),
                    PKBotonPrimario(
                      texto: 'Guardar nueva contraseña',
                      onPressed: _guardarNuevaContrasena,
                      cargando: auth.cargando,
                    ),
                    const SizedBox(height: PKSpacing.sm),
                    Center(
                      child: TextButton(
                        onPressed: () => setState(() => _codigoEnviado = false),
                        child: const Text('← Reenviar código o cambiar correo'),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// ─── P03: Verificación de Código ─────────────────────────────────────────────
class CodigoScreen extends StatefulWidget {
  final String correo;
  const CodigoScreen({super.key, required this.correo});

  @override
  State<CodigoScreen> createState() => _CodigoScreenState();
}

class _CodigoScreenState extends State<CodigoScreen> {
  final _codigoController = TextEditingController();

  Future<void> _verificar() async {
    final codigo = _codigoController.text.trim();
    if (codigo.length != 6) {
      pkSnackError(context, 'Ingresa el código completo de 6 dígitos.');
      return;
    }

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final ok = await auth.verificarCodigo(widget.correo, codigo);
    if (!mounted) return;

    if (ok) {
      Provider.of<TiendaProvider>(context, listen: false).cargarMiTienda();
      if (auth.usuario?.nombres == null || auth.usuario!.nombres!.isEmpty) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (_) => const PerfilScreen()),
        );
      } else {
        Navigator.popUntil(context, (route) => route.isFirst);
      }
    } else {
      pkSnackError(context, auth.error ?? 'Código incorrecto. Intenta de nuevo.');
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(
        title: const Text('Verificar código'),
        backgroundColor: Colors.transparent,
        elevation: 0,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(PKSpacing.base),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              const SizedBox(height: PKSpacing.lg),

              // Icono
              Container(
                width: 80,
                height: 80,
                decoration: const BoxDecoration(
                  color: PKColors.primarioSuave,
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.mark_email_unread_outlined,
                  size: 40,
                  color: PKColors.primario,
                ),
              ),

              const SizedBox(height: PKSpacing.base),

              Text(
                'Revisa tu bandeja',
                style: PKTextStyles.tituloSeccion,
                textAlign: TextAlign.center,
              ),

              const SizedBox(height: PKSpacing.sm),

              Text(
                'Enviamos un código a:\n${widget.correo}',
                textAlign: TextAlign.center,
                style: PKTextStyles.cuerpoSecundario,
              ),

              const SizedBox(height: PKSpacing.lg),

              // Banner demo
              PKTarjeta(
                padding: const EdgeInsets.symmetric(horizontal: PKSpacing.base, vertical: PKSpacing.sm),
                child: Row(
                  children: [
                    const Icon(Icons.verified_user_outlined, color: PKColors.primario, size: 20),
                    const SizedBox(width: PKSpacing.sm),
                    Expanded(
                      child: Text(
                        'Código demo de evaluación: 123456',
                        style: PKTextStyles.etiqueta.copyWith(
                          color: PKColors.primarioSuaveTexto,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    TextButton(
                      onPressed: () => setState(() => _codigoController.text = '123456'),
                      child: const Text('Usar'),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: PKSpacing.lg),

              // Campo de código grande
              TextField(
                controller: _codigoController,
                keyboardType: TextInputType.number,
                maxLength: 6,
                textAlign: TextAlign.center,
                autofocus: true,
                style: const TextStyle(
                  fontSize: 32,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 10,
                  color: PKColors.titulo,
                  fontFamily: 'DMSans',
                ),
                decoration: const InputDecoration(
                  hintText: '·  ·  ·  ·  ·  ·',
                  counterText: '',
                ),
              ),

              const SizedBox(height: PKSpacing.lg),

              PKBotonPrimario(
                texto: AppStrings.btnEntrar,
                onPressed: _verificar,
                cargando: auth.cargando,
              ),

              const SizedBox(height: PKSpacing.base),

              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('Cambiar correo'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── P04: Completar Perfil ────────────────────────────────────────────────────
class PerfilScreen extends StatefulWidget {
  const PerfilScreen({super.key});

  @override
  State<PerfilScreen> createState() => _PerfilScreenState();
}

class _PerfilScreenState extends State<PerfilScreen> {
  final _nombresController = TextEditingController();
  final _whatsappController = TextEditingController();
  bool _aceptoPrivacidad = false;

  @override
  void dispose() {
    _nombresController.dispose();
    _whatsappController.dispose();
    super.dispose();
  }

  Future<void> _guardar() async {
    if (_nombresController.text.trim().isEmpty) {
      pkSnackError(context, 'Por favor escribe tus nombres y apellidos.');
      return;
    }
    if (!_aceptoPrivacidad) {
      pkSnackError(context, 'Debes aceptar la política de privacidad para continuar.');
      return;
    }

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final ok = await auth.actualizarPerfil(
      nombres: _nombresController.text.trim(),
      whatsapp: _whatsappController.text.trim(),
      aceptoPrivacidad: true,
    );

    if (!mounted) return;
    if (ok) Navigator.popUntil(context, (route) => route.isFirst);
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(title: const Text('Completa tu perfil')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(PKSpacing.base),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: PKSpacing.sm),
              Text('¡Casi listo! 👋', style: PKTextStyles.tituloPantalla),
              const SizedBox(height: PKSpacing.sm),
              Text(
                'Indícanos tus datos para identificarte al comprar y coordinar entregas.',
                style: PKTextStyles.cuerpoSecundario,
              ),
              const SizedBox(height: PKSpacing.xl),

              PKTarjeta(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Datos personales', style: PKTextStyles.tituloTarjeta),
                    const SizedBox(height: PKSpacing.base),
                    PKCampoEtiquetado(
                      etiqueta: 'Nombres y Apellidos *',
                      hint: 'Ej. María García López',
                      controller: _nombresController,
                      prefixIcon: const Icon(Icons.person_outline, size: 20, color: PKColors.secundario),
                    ),
                    const SizedBox(height: PKSpacing.base),
                    PKCampoEtiquetado(
                      etiqueta: 'WhatsApp (opcional)',
                      hint: 'Ej. 0987654321',
                      controller: _whatsappController,
                      keyboardType: TextInputType.phone,
                      prefixIcon: const Icon(Icons.phone_outlined, size: 20, color: PKColors.secundario),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: PKSpacing.base),

              // Checkbox de privacidad
              PKTarjeta(
                padding: const EdgeInsets.symmetric(horizontal: PKSpacing.sm, vertical: PKSpacing.xs),
                child: CheckboxListTile(
                  value: _aceptoPrivacidad,
                  onChanged: (val) => setState(() => _aceptoPrivacidad = val ?? false),
                  title: Text(
                    'Acepto la política de privacidad y tratamiento de datos de PuntoKampus.',
                    style: PKTextStyles.etiqueta.copyWith(color: PKColors.titulo),
                  ),
                  controlAffinity: ListTileControlAffinity.leading,
                  contentPadding: EdgeInsets.zero,
                  activeColor: PKColors.primario,
                ),
              ),

              const SizedBox(height: PKSpacing.xl),

              PKBotonPrimario(
                texto: AppStrings.btnContinuar,
                onPressed: _guardar,
                cargando: auth.cargando,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
