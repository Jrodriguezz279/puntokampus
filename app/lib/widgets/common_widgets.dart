import 'package:flutter/material.dart';
import '../theme/theme.dart';
import 'pk_components.dart';

class LoadingSkeleton extends StatelessWidget {
  final double height;
  final double width;
  final double borderRadius;

  const LoadingSkeleton({
    super.key,
    this.height = 80,
    this.width = double.infinity,
    this.borderRadius = PKRadii.boton,
  });

  @override
  Widget build(BuildContext context) {
    return PKEsqueleto(
      height: height,
      width: width,
      borderRadius: borderRadius,
    );
  }
}

class EmptyStateWidget extends StatelessWidget {
  final String icon;
  final String message;
  final String? buttonText;
  final VoidCallback? onButtonPressed;

  const EmptyStateWidget({
    super.key,
    this.icon = '📦',
    required this.message,
    this.buttonText,
    this.onButtonPressed,
  });

  @override
  Widget build(BuildContext context) {
    return PKEstadoVacio(
      icono: Icons.inbox_outlined,
      titulo: message,
      botonTexto: buttonText,
      onBoton: onButtonPressed,
    );
  }
}

class ErrorViewWidget extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;

  const ErrorViewWidget({
    super.key,
    required this.message,
    required this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    return PKEstadoError(
      mensaje: message,
      onReintentar: onRetry,
    );
  }
}
