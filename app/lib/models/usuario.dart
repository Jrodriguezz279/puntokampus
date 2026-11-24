class Usuario {
  final int id;
  final String correo;
  final String? nombres;
  final String? codigoEstudiante;
  final String? facultad;
  final String? carrera;
  final int? semestre;
  final String? whatsapp;
  final bool mostrarWhatsapp;
  final bool esAdmin;
  final String estado;
  // Fase 3: Calificaciones como comprador
  final double? estrellasPromedio;
  final int estrellasTotal;

  Usuario({
    required this.id,
    required this.correo,
    this.nombres,
    this.codigoEstudiante,
    this.facultad,
    this.carrera,
    this.semestre,
    this.whatsapp,
    this.mostrarWhatsapp = true,
    this.esAdmin = false,
    required this.estado,
    this.estrellasPromedio,
    this.estrellasTotal = 0,
  });

  factory Usuario.fromJson(Map<String, dynamic> json) {
    return Usuario(
      id: json['id'] as int,
      correo: json['correo'] as String? ?? '',
      nombres: json['nombres'] as String?,
      codigoEstudiante: json['codigo_estudiante'] as String?,
      facultad: json['facultad'] as String?,
      carrera: json['carrera'] as String?,
      semestre: json['semestre'] != null ? int.tryParse(json['semestre'].toString()) : null,
      whatsapp: json['whatsapp'] as String?,
      mostrarWhatsapp: json['mostrar_whatsapp'] == true || json['mostrar_whatsapp'] == 1,
      esAdmin: json['es_admin'] == true || json['es_admin'] == 1,
      estado: json['estado'] as String? ?? 'activa',
      estrellasPromedio: json['estrellas_promedio'] != null
          ? double.tryParse(json['estrellas_promedio'].toString())
          : null,
      estrellasTotal: json['estrellas_total'] is int
          ? json['estrellas_total'] as int
          : int.tryParse(json['estrellas_total']?.toString() ?? '0') ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'correo': correo,
      'nombres': nombres,
      'codigo_estudiante': codigoEstudiante,
      'facultad': facultad,
      'carrera': carrera,
      'semestre': semestre,
      'whatsapp': whatsapp,
      'mostrar_whatsapp': mostrarWhatsapp,
      'es_admin': esAdmin,
      'estado': estado,
    };
  }
}
