import 'package:flutter/material.dart';
import '../theme/theme.dart';
import '../models/tienda.dart';
import '../models/producto.dart';
import 'pk_components.dart';

class StoreCard extends StatelessWidget {
  final Tienda tienda;
  final VoidCallback onTap;

  const StoreCard({
    super.key,
    required this.tienda,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: PKSpacing.md),
      decoration: BoxDecoration(
        color: PKColors.tarjeta,
        borderRadius: PKRadii.tarjetaAll,
        border: Border.all(color: PKColors.borde),
        boxShadow: PKShadows.tarjeta,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: PKRadii.tarjetaAll,
          child: Padding(
            padding: const EdgeInsets.all(PKSpacing.base),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Avatar con Logo o Inicial
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    color: PKColors.primarioSuave,
                    borderRadius: PKRadii.imagenAll,
                    border: Border.all(color: PKColors.borde),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: (tienda.logoUrl != null && tienda.logoUrl!.isNotEmpty)
                      ? Image.network(
                          tienda.logoUrl!,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => _buildInitial(),
                        )
                      : _buildInitial(),
                ),
                const SizedBox(width: PKSpacing.md),

                // Información
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              tienda.nombre,
                              style: PKTextStyles.tituloTarjeta,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          _buildRatingBadge(tienda),
                          const SizedBox(width: PKSpacing.xs),
                          if (tienda.esPremium)
                            const PKInsigniaSaco10()
                          else
                            const PKInsigniaMatriculado(),
                        ],
                      ),
                      if (tienda.descripcion != null && tienda.descripcion!.isNotEmpty) ...[
                        const SizedBox(height: PKSpacing.xs),
                        Text(
                          tienda.descripcion!,
                          style: PKTextStyles.cuerpoSecundario.copyWith(fontSize: 13),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                      const SizedBox(height: PKSpacing.sm),
                      Row(
                        children: [
                          if (tienda.puntoEntrega != null) ...[
                            const Icon(Icons.location_on_outlined, size: 14, color: PKColors.secundario),
                            const SizedBox(width: 2),
                            Flexible(
                              child: Text(
                                tienda.puntoEntrega!.nombre,
                                style: PKTextStyles.etiqueta,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            const SizedBox(width: PKSpacing.sm),
                          ],
                          if (tienda.categoria1 != null) ...[
                            const Icon(Icons.label_outline, size: 14, color: PKColors.secundario),
                            const SizedBox(width: 2),
                            Text(
                              tienda.categoria1!.nombre,
                              style: PKTextStyles.etiqueta,
                            ),
                          ],
                          // Tramo 2: badge "Pausada"
                          if (!tienda.puedeRecibirPedidos) ...[ 
                            const SizedBox(width: PKSpacing.sm),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFFEDED),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: const Text(
                                '⏸ Pausada',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: Color(0xFFBA1A1A),
                                  fontFamily: 'DMSans',
                                ),
                              ),
                            ),
                          ],
                          // Badge "Abierto" en verde si la tienda está abierta
                          if (tienda.puedeRecibirPedidos && tienda.estaAbierto) ...[
                            const SizedBox(width: PKSpacing.sm),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xFFDCFCE7),
                                border: Border.all(color: const Color(0xFFBBF7D0)),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.circle, size: 7, color: Color(0xFF16A34A)),
                                  SizedBox(width: 4),
                                  Text(
                                    'Abierto',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w700,
                                      color: Color(0xFF15803D),
                                      fontFamily: 'DMSans',
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                          // Badge "Cerrado" cuando está fuera de horario
                          if (tienda.puedeRecibirPedidos && !tienda.estaAbierto) ...[
                            const SizedBox(width: PKSpacing.sm),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFEE2E2),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: const Text(
                                '🔴 Cerrado',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: Color(0xFF991B1B),
                                  fontFamily: 'DMSans',
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildRatingBadge(Tienda t) {
    if (t.estrellasPromedio != null && t.estrellasTotal > 0) {
      return Container(
        margin: const EdgeInsets.only(right: 6),
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
        decoration: BoxDecoration(
          color: const Color(0xFFFEF3C7),
          borderRadius: BorderRadius.circular(6),
          border: Border.all(color: const Color(0xFFFDE68A)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.star_rounded, size: 14, color: Color(0xFFF59E0B)),
            const SizedBox(width: 2),
            Text(
              t.estrellasPromedio!.toStringAsFixed(1),
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w800,
                color: Color(0xFF92400E),
                fontFamily: 'DMSans',
              ),
            ),
            const SizedBox(width: 2),
            Text(
              '(${t.estrellasTotal})',
              style: const TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w600,
                color: Color(0xFFB45309),
                fontFamily: 'DMSans',
              ),
            ),
          ],
        ),
      );
    }
    return const Padding(
      padding: EdgeInsets.only(right: 6),
      child: Text(
        '✨ Nueva',
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: Color(0xFF9CA3AF),
          fontStyle: FontStyle.italic,
          fontFamily: 'DMSans',
        ),
      ),
    );
  }

  Widget _buildInitial() {
    return Center(
      child: Text(
        tienda.nombre.isNotEmpty ? tienda.nombre[0].toUpperCase() : 'T',
        style: const TextStyle(
          fontSize: 22,
          fontWeight: FontWeight.bold,
          color: PKColors.primario,
          fontFamily: 'DMSans',
        ),
      ),
    );
  }
}

class ProductCard extends StatelessWidget {
  final Producto producto;
  final VoidCallback onTap;
  final VoidCallback onAddToCart;

  const ProductCard({
    super.key,
    required this.producto,
    required this.onTap,
    required this.onAddToCart,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: PKSpacing.md),
      decoration: BoxDecoration(
        color: PKColors.tarjeta,
        borderRadius: PKRadii.tarjetaAll,
        border: Border.all(color: PKColors.borde),
        boxShadow: PKShadows.tarjeta,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: PKRadii.tarjetaAll,
          child: Padding(
            padding: const EdgeInsets.all(PKSpacing.md),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                // Foto de producto (radio 12)
                Container(
                  width: 72,
                  height: 72,
                  decoration: BoxDecoration(
                    color: PKColors.neutroFondo,
                    borderRadius: PKRadii.imagenAll,
                    border: Border.all(color: PKColors.borde),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: (producto.primeraFotoUrl != null && producto.primeraFotoUrl!.isNotEmpty)
                      ? Image.network(
                          producto.primeraFotoUrl!,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => const Icon(Icons.fastfood_outlined, color: PKColors.secundario),
                        )
                      : const Icon(Icons.fastfood_outlined, color: PKColors.secundario),
                ),
                const SizedBox(width: PKSpacing.md),

                // Detalle
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        producto.nombre,
                        style: PKTextStyles.tituloTarjeta.copyWith(fontSize: 15),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      if (producto.descripcion != null && producto.descripcion!.isNotEmpty) ...[
                        const SizedBox(height: 2),
                        Text(
                          producto.descripcion!,
                          style: PKTextStyles.cuerpoSecundario.copyWith(fontSize: 12),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                      const SizedBox(height: PKSpacing.xs),
                      // Tramo 2: precio con descuento
                      if (producto.tieneDescuento) ...[
                        Row(
                          children: [
                            Text(
                              '\$${producto.precioEfectivo.toStringAsFixed(2)}',
                              style: PKTextStyles.precio,
                            ),
                            const SizedBox(width: PKSpacing.sm),
                            Text(
                              '\$${producto.precio.toStringAsFixed(2)}',
                              style: PKTextStyles.etiqueta.copyWith(
                                decoration: TextDecoration.lineThrough,
                                color: PKColors.secundario,
                              ),
                            ),
                            const SizedBox(width: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                              decoration: BoxDecoration(
                                color: const Color(0xFFCC4E01),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                '-${producto.porcentajeDescuento.toStringAsFixed(0)}%',
                                style: const TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.white,
                                  fontFamily: 'DMSans',
                                ),
                              ),
                            ),
                          ],
                        ),
                      ] else ...[
                        Text(
                          '\$${producto.precio.toStringAsFixed(2)}',
                          style: PKTextStyles.precio,
                        ),
                      ],
                    ],
                  ),
                ),

                // Botón añadir o pastilla Agotado
                if (producto.disponible)
                  IconButton(
                    onPressed: onAddToCart,
                    icon: const Icon(Icons.add_shopping_cart, color: PKColors.primario),
                    tooltip: 'Agregar al carrito',
                  )
                else
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: PKColors.neutroFondo,
                      borderRadius: PKRadii.chipAll,
                    ),
                    child: Text(
                      'Agotado',
                      style: PKTextStyles.etiqueta.copyWith(
                        color: PKColors.secundario,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
