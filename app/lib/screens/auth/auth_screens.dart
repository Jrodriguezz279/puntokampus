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

// ─── P02: Ingreso de Correo ───────────────────────────────────────────────────
class CorreoScreen extends StatefulWidget {
  const CorreoScreen({super.key});

  @override
  State<CorreoScreen> createState() => _CorreoScreenState();
}

class _CorreoScreenState extends State<CorreoScreen> {
  final _correoController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  @override
  void dispose() {
    _correoController.dispose();
    super.dispose();
  }

  Future<void> _enviarCodigo() async {
    if (!_formKey.currentState!.validate()) return;

    final correo = _correoController.text.trim().toLowerCase();
    final auth = Provider.of<AuthProvider>(context, listen: false);

    final ok = await auth.solicitarCodigo(correo);
    if (!mounted) return;

    if (ok) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => CodigoScreen(correo: correo)),
      );
    } else {
      pkSnackError(context, auth.error ?? AppStrings.errServidor);
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
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: PKSpacing.sm),

                Text('Ingresa tu correo\ninstitucional', style: PKTextStyles.tituloPantalla),
                const SizedBox(height: PKSpacing.sm),
                Text(
                  'Te enviamos un código de 6 dígitos para entrar sin contraseña.',
                  style: PKTextStyles.cuerpoSecundario,
                ),

                const SizedBox(height: PKSpacing.xl),

                PKCampoEtiquetado(
                  etiqueta: 'Correo electrónico UTB',
                  hint: 'ejemplo@utb.edu.ec',
                  controller: _correoController,
                  keyboardType: TextInputType.emailAddress,
                  autofocus: true,
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
                  texto: AppStrings.btnEnviarCodigo,
                  onPressed: _enviarCodigo,
                  cargando: auth.cargando,
                ),

                const SizedBox(height: PKSpacing.xl),

                // ── Acceso rápido demo ────────────────────────────────────────
                Center(
                  child: Text(
                    'Acceso rápido de evaluación:',
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
                          _enviarCodigo();
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
                          _enviarCodigo();
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
