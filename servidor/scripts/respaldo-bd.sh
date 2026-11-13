#!/bin/bash
# ==============================================================================
# Script de Respaldo Diario de Base de Datos PostgreSQL — PuntoKampus
# ==============================================================================
set -e

# Configuración
BACKUP_DIR="/var/backups/puntokampus"
FECHA=$(date +"%Y-%m-%d_%H%M%S")
ARCHIVO="${BACKUP_DIR}/puntokampus_${FECHA}.sql.gz"
RETENCION_DIAS=30

DB_NAME="puntokampus"
DB_USER="puntokampus_user"
DB_HOST="127.0.0.1"
DB_PORT="5432"

mkdir -p "${BACKUP_DIR}"

echo "[$(date)] Iniciando respaldo de PostgreSQL: ${DB_NAME}..."

# Volcado comprimido con pg_dump
pg_dump -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USER}" -d "${DB_NAME}" --no-owner --clean | gzip > "${ARCHIVO}"

echo "[$(date)] Respaldo completado exitosamente: ${ARCHIVO}"

# Purgar respaldos más antiguos a la retención configurada
echo "[$(date)] Eliminando respaldos con más de ${RETENCION_DIAS} días..."
find "${BACKUP_DIR}" -type f -name "puntokampus_*.sql.gz" -mtime +${RETENCION_DIAS} -delete

echo "[$(date)] Proceso de respaldo finalizado."
