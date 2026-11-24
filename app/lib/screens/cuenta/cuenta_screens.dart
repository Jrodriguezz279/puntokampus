import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../theme/theme.dart';
import '../../constants/app_strings.dart';
import '../../providers/auth_provider.dart';
import '../../services/auth_service.dart';
import '../../widgets/pk_components.dart';
import '../auth/auth_screens.dart';

// ══════════════════════════════════════════════════════════════════════════════
// CUENTA — P13 Mi Cuenta / P21 Política Privacidad / P22 Borrar Cuenta
// Cumple con docs/DISENO-VISUAL.md
// ══════════════════════════════════════════════════════════════════════════════

// ─── P13: Mi Cuenta ───────────────────────────────────────────────────────────
class CuentaScreen extends StatelessWidget {
  const CuentaScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final u = auth.usuario;

    if (!auth.tieneSesion || u == null) {
      return Scaffold(
        backgroundColor: PKColors.fondo,
        appBar: AppBar(title: const Text('Mi Cuenta')),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(PKSpacing.xl),
            child: PKEstadoVacio(
              icono: Icons.person_outline_rounded,
              titulo: 'Inicia sesión',
              subtitulo: 'Entra con tu correo institucional UTB para ver tus datos, gestionar tu tienda y tus pedidos.',
              botonTexto: 'Iniciar Sesión',
              onBoton: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const BienvenidaScreen())),
            ),
          ),
        ),
      );
    }

    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(title: const Text('Mi Cuenta')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(PKSpacing.base),
        child: Column(
          children: [
            // Tarjeta de Perfil
            PKTarjeta(
              child: Row(
                children: [
                  Container(
                    width: 60,
                    height: 60,
                    decoration: const BoxDecoration(
                      color: PKColors.primarioSuave,
                      shape: BoxShape.circle,
                    ),
                    child: Center(
                      child: Text(
                        u.nombres?.isNotEmpty == true ? u.nombres![0].toUpperCase() : 'U',
                        style: const TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                          color: PKColors.primario,
                          fontFamily: 'DMSans',
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: PKSpacing.base),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(u.nombres ?? 'Estudiante UTB', style: PKTextStyles.tituloTarjeta),
                        const SizedBox(height: 2),
                        Text(u.correo, style: PKTextStyles.etiqueta),
                        if (u.carrera != null) ...[
                          const SizedBox(height: 2),
                          Text('🎓 ${u.carrera}', style: PKTextStyles.etiqueta),
                        ],
                        const SizedBox(height: PKSpacing.xs),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: PKColors.fondo,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: PKColors.borde),
                          ),
                          child: Text(
                            (u.estrellasPromedio != null && u.estrellasTotal > 0)
                                ? '⭐ ${u.estrellasPromedio!.toStringAsFixed(1)} (${u.estrellasTotal} calificaciones)'
                                : '⭐ Todavía no tienes calificaciones',
                            style: PKTextStyles.etiqueta.copyWith(
                              color: (u.estrellasPromedio != null && u.estrellasTotal > 0)
                                  ? const Color(0xFFB45309)
                                  : PKColors.secundario,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: PKSpacing.base),

            // Opciones de Configuración
            PKTarjeta(
              padding: EdgeInsets.zero,
              child: Column(
                children: [
                  SwitchListTile(
                    title: Text('Mostrar mi WhatsApp en mi tienda', style: PKTextStyles.cuerpo.copyWith(fontSize: 15)),
                    value: u.mostrarWhatsapp,
                    onChanged: (val) {
                      auth.actualizarPerfil(mostrarWhatsapp: val);
                    },
                  ),
                  const Divider(height: 1),
                  ListTile(
                    leading: const Icon(Icons.privacy_tip_outlined, color: PKColors.primario),
                    title: Text('Política de Privacidad', style: PKTextStyles.cuerpo.copyWith(fontSize: 15)),
                    trailing: const Icon(Icons.chevron_right, color: PKColors.secundario),
                    onTap: () {
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const PrivacidadScreen()));
                    },
                  ),
                  const Divider(height: 1),
                  ListTile(
                    leading: const Icon(Icons.exit_to_app, color: PKColors.error),
                    title: Text(
                      'Cerrar Sesión',
                      style: PKTextStyles.cuerpo.copyWith(
                        color: PKColors.error,
                        fontWeight: FontWeight.bold,
                        fontSize: 15,
                      ),
                    ),
                    onTap: () => auth.salir(),
                  ),
                ],
              ),
            ),

            const SizedBox(height: PKSpacing.base),

            // Zona Peligrosa (Borrar Cuenta)
            PKTarjeta(
              padding: EdgeInsets.zero,
              child: ListTile(
                leading: const Icon(Icons.delete_forever, color: PKColors.error),
                title: Text(
                  'Borrar mi cuenta',
                  style: PKTextStyles.cuerpo.copyWith(
                    color: PKColors.error,
                    fontWeight: FontWeight.bold,
                    fontSize: 15,
                  ),
                ),
                subtitle: Text('Elimina definitivamente tu cuenta, tienda y productos.', style: PKTextStyles.etiqueta),
                onTap: () {
                  Navigator.push(context, MaterialPageRoute(builder: (_) => const BorrarCuentaScreen()));
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── P21: Política de Privacidad ──────────────────────────────────────────────
class PrivacidadScreen extends StatelessWidget {
  const PrivacidadScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(title: const Text('Política de Privacidad')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(PKSpacing.base),
        child: PKTarjeta(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Privacidad en PuntoKampus UTB', style: PKTextStyles.tituloSeccion),
              const SizedBox(height: PKSpacing.base),
              Text(
                'En PuntoKampus nos tomamos muy en serio la privacidad y seguridad de los estudiantes de la Universidad Técnica de Babahoyo.\n\n'
                '1. Datos recopilados: únicamente tu correo institucional (@utb.edu.ec o @est.utb.edu.ec), nombres y número de WhatsApp para coordinar pedidos.\n\n'
                '2. Sin contraseñas: no guardamos contraseñas. Cada inicio de sesión utiliza un código temporal de 6 dígitos que expira en 10 minutos.\n\n'
                '3. Derecho al olvido: puedes solicitar en cualquier momento la eliminación total de tu cuenta, tienda y productos desde la app.',
                style: PKTextStyles.cuerpo,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── P22: Borrar Cuenta ───────────────────────────────────────────────────────
class BorrarCuentaScreen extends StatefulWidget {
  const BorrarCuentaScreen({super.key});

  @override
  State<BorrarCuentaScreen> createState() => _BorrarCuentaScreenState();
}

class _BorrarCuentaScreenState extends State<BorrarCuentaScreen> {
  final _codigoController = TextEditingController();
  bool _codigoEnviado = false;
  bool _procesando = false;

  @override
  void dispose() {
    _codigoController.dispose();
    super.dispose();
  }

  void _solicitarCodigo() async {
    setState(() => _procesando = true);
    try {
      await AuthService.solicitarCodigoBorrado();
      setState(() {
        _codigoEnviado = true;
        _procesando = false;
      });
      if (!mounted) return;
      pkSnackOk(context, 'Código de confirmación enviado a tu correo.');
    } catch (e) {
      setState(() => _procesando = false);
      if (!mounted) return;
      pkSnackError(context, e.toString());
    }
  }

  void _confirmarBorrado() async {
    if (_codigoController.text.trim().length != 6) {
      pkSnackError(context, 'Ingresa el código completo de 6 dígitos.');
      return;
    }

    setState(() => _procesando = true);
    try {
      await AuthService.borrarCuenta(_codigoController.text.trim());
      if (!mounted) return;
      Navigator.popUntil(context, (route) => route.isFirst);
    } catch (e) {
      setState(() => _procesando = false);
      if (!mounted) return;
      pkSnackError(context, e.toString());
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: PKColors.fondo,
      appBar: AppBar(title: const Text('Borrar mi cuenta')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(PKSpacing.base),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            PKTarjeta(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.warning_amber_rounded, color: PKColors.error, size: 28),
                      const SizedBox(width: PKSpacing.sm),
                      Text('Acción Irreversible', style: PKTextStyles.tituloTarjeta.copyWith(color: PKColors.error)),
                    ],
                  ),
                  const SizedBox(height: PKSpacing.md),
                  Text(
                    'Se borrarán definitivamente tu cuenta, tu tienda, tus productos y tus pedidos asociados.',
                    style: PKTextStyles.cuerpo,
                  ),
                ],
              ),
            ),
            const SizedBox(height: PKSpacing.lg),
            if (!_codigoEnviado)
              ElevatedButton(
                onPressed: _procesando ? null : _solicitarCodigo,
                style: ElevatedButton.styleFrom(
                  backgroundColor: PKColors.error,
                  foregroundColor: PKColors.sobrePrimario,
                ),
                child: _procesando
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(color: PKColors.sobrePrimario, strokeWidth: 2),
                      )
                    : const Text('Pedir código de confirmación'),
              )
            else ...[
              PKCampoEtiquetado(
                etiqueta: 'Código de 6 dígitos recibido',
                controller: _codigoController,
                keyboardType: TextInputType.number,
                maxLength: 6,
                autofocus: true,
                style: const TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 6,
                  fontFamily: 'DMSans',
                ),
              ),
              const SizedBox(height: PKSpacing.base),
              ElevatedButton(
                onPressed: _procesando ? null : _confirmarBorrado,
                style: ElevatedButton.styleFrom(
                  backgroundColor: PKColors.error,
                  foregroundColor: PKColors.sobrePrimario,
                ),
                child: _procesando
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(color: PKColors.sobrePrimario, strokeWidth: 2),
                      )
                    : const Text(AppStrings.btnBorrarCuenta),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
