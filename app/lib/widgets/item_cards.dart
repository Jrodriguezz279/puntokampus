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
                  child: tienda.logoUrl != null
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
                  child: producto.primeraFotoUrl != null
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
                      Text(
                        '\$${producto.precio.toStringAsFixed(2)}',
                        style: PKTextStyles.precio,
                      ),
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
