import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../constants/app_colors.dart';
import '../../constants/app_strings.dart';
import '../../providers/auth_provider.dart';
import '../../services/auth_service.dart';
import '../auth/auth_screens.dart';

// P13: Mi Cuenta
class CuentaScreen extends StatelessWidget {
  const CuentaScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final u = auth.usuario;

    if (!auth.tieneSesion || u == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Mi Cuenta')),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Text('👤', style: TextStyle(fontSize: 48)),
                const SizedBox(height: 16),
                const Text(
                  'Inicia sesión con tu correo de la UTB para ver tus datos, gestionar tu tienda y tus pedidos.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: AppColors.textMuted),
                ),
                const SizedBox(height: 24),
                ElevatedButton(
                  onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const BienvenidaScreen())),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: Colors.white,
                    minimumSize: const Size(180, 48),
                  ),
                  child: const Text('Iniciar Sesión'),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(title: const Text('Mi Cuenta')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // Tarjeta de Perfil
            Card(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 30,
                      backgroundColor: AppColors.primaryLight,
                      child: Text(
                        u.nombres?.isNotEmpty == true ? u.nombres![0].toUpperCase() : 'U',
                        style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: AppColors.primary),
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(u.nombres ?? 'Estudiante UTB', style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
                          const SizedBox(height: 4),
                          Text(u.correo, style: const TextStyle(color: AppColors.textMuted, fontSize: 13)),
                          if (u.carrera != null)
                            Text('🎓 ${u.carrera}', style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 20),

            // Opciones de Configuración
            Card(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              child: Column(
                children: [
                  SwitchListTile(
                    title: const Text('Mostrar mi WhatsApp en mi tienda'),
                    value: u.mostrarWhatsapp,
                    onChanged: (val) {
                      auth.actualizarPerfil(mostrarWhatsapp: val);
                    },
                  ),
                  const Divider(height: 1),
                  ListTile(
                    leading: const Icon(Icons.privacy_tip_outlined, color: AppColors.primary),
                    title: const Text('Política de Privacidad'),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: () {
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const PrivacidadScreen()));
                    },
                  ),
                  const Divider(height: 1),
                  ListTile(
                    leading: const Icon(Icons.exit_to_app, color: AppColors.error),
                    title: const Text('Cerrar Sesión', style: TextStyle(color: AppColors.error, fontWeight: FontWeight.bold)),
                    onTap: () => auth.salir(),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // Zona Peligrosa (Borrar Cuenta)
            Card(
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
                side: const BorderSide(color: AppColors.errorLight),
              ),
              child: ListTile(
                leading: const Icon(Icons.delete_forever, color: AppColors.error),
                title: const Text('Borrar mi cuenta', style: TextStyle(color: AppColors.error, fontWeight: FontWeight.bold)),
                subtitle: const Text('Elimina definitivamente tu cuenta, tienda y productos.'),
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

// P21: Política de Privacidad
class PrivacidadScreen extends StatelessWidget {
  const PrivacidadScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Política de Privacidad')),
      body: const SingleChildScrollView(
        padding: EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Privacidad en PuntoKampus UTB', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
            SizedBox(height: 12),
            Text(
              'En PuntoKampus nos tomamos muy en serio la privacidad y seguridad de los estudiantes de la Universidad Técnica de Babahoyo.\n\n'
              '1. Datos recopilados: únicamente tu correo institucional (@utb.edu.ec), nombres y número de WhatsApp para coordinar pedidos.\n\n'
              '2. Sin contraseñas: no guardamos contraseñas. Cada inicio de sesión utiliza un código temporal de 6 dígitos que expira en 10 minutos.\n\n'
              '3. Derecho al olvido: puedes solicitar en cualquier momento la eliminación total de tu cuenta, tienda y productos desde la app.',
              style: TextStyle(fontSize: 14, height: 1.6, color: AppColors.text),
            ),
          ],
        ),
      ),
    );
  }
}

// P22: Borrar Cuenta
class BorrarCuentaScreen extends StatefulWidget {
  const BorrarCuentaScreen({super.key});

  @override
  State<BorrarCuentaScreen> createState() => _BorrarCuentaScreenState();
}

class _BorrarCuentaScreenState extends State<BorrarCuentaScreen> {
  final _codigoController = TextEditingController();
  bool _codigoEnviado = false;
  bool _procesando = false;

  void _solicitarCodigo() async {
    setState(() => _procesando = true);
    try {
      await AuthService.solicitarCodigoBorrado();
      setState(() {
        _codigoEnviado = true;
        _procesando = false;
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Código de confirmación enviado a tu correo.')),
      );
    } catch (e) {
      setState(() => _procesando = false);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  void _confirmarBorrado() async {
    if (_codigoController.text.trim().length != 6) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Ingresa el código de 6 dígitos.')));
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
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString()), backgroundColor: AppColors.error));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Borrar mi cuenta')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('⚠️ Acción Irreversible', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppColors.error)),
            const SizedBox(height: 12),
            const Text(
              'Se borrarán tu cuenta, tu tienda, tus productos y tus fotos. No se puede deshacer.',
              style: TextStyle(fontSize: 15, height: 1.5),
            ),
            const SizedBox(height: 24),
            if (!_codigoEnviado)
              ElevatedButton(
                onPressed: _procesando ? null : _solicitarCodigo,
                style: ElevatedButton.styleFrom(backgroundColor: AppColors.error, foregroundColor: Colors.white, minimumSize: const Size(double.infinity, 50)),
                child: const Text('Pedir código de confirmación'),
              )
            else ...[
              TextField(
                controller: _codigoController,
                keyboardType: TextInputType.number,
                maxLength: 6,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, letterSpacing: 6),
                decoration: const InputDecoration(labelText: 'Código de 6 dígitos recibido', border: OutlineInputBorder()),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: _procesando ? null : _confirmarBorrado,
                style: ElevatedButton.styleFrom(backgroundColor: AppColors.error, foregroundColor: Colors.white, minimumSize: const Size(double.infinity, 50)),
                child: const Text(AppStrings.btnBorrarCuenta),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
