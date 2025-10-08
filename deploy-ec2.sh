#!/bin/bash

# ============================================
# Script de Deployment - CuantoSabe
# ============================================

set -e

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

# Variables
IMAGE_NAME="lcebreiros/cuantosabe:latest"
COMPOSE_FILE="docker-compose.prod.yml"
BACKUP_DIR="./backups"
DATE=$(date +%Y%m%d_%H%M%S)

# Funciones de logging
log() {
    echo -e "${GREEN}✓${NC} $1"
}

warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

error() {
    echo -e "${RED}✗${NC} $1"
    exit 1
}

info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Banner
echo ""
echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   🚀 CuantoSabe Deployment            ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"
echo ""

# 1. Verificar archivos necesarios
info "Verificando configuración..."
[ -f "$COMPOSE_FILE" ] || error "No se encontró $COMPOSE_FILE"
[ -f ".env.prod" ] || error "No se encontró .env.prod"
log "Archivos de configuración OK"

# 2. Crear directorio de backups
mkdir -p "$BACKUP_DIR"

# 3. Backup de base de datos (si existe)
info "Creando backup de base de datos..."
if docker ps | grep -q laravel-db; then
    BACKUP_FILE="${BACKUP_DIR}/db_backup_${DATE}.sql"
    docker exec laravel-db mysqldump \
        -uCuantoSabe \
        -pCuantoSabe2025! \
        cuanto_sabe_db > "$BACKUP_FILE" 2>/dev/null || warning "No se pudo crear backup de BD"
    
    if [ -f "$BACKUP_FILE" ]; then
        gzip "$BACKUP_FILE"
        log "Backup creado: ${BACKUP_FILE}.gz"
        
        # Mantener solo los últimos 7 backups
        ls -t ${BACKUP_DIR}/db_backup_*.sql.gz 2>/dev/null | tail -n +8 | xargs -r rm
    fi
else
    warning "Base de datos no está corriendo, saltando backup"
fi

# 4. Pull de la última imagen
info "Descargando última imagen Docker..."
docker pull $IMAGE_NAME || error "No se pudo descargar la imagen"
log "Imagen actualizada"

# 5. Detener contenedores
info "Deteniendo contenedores actuales..."
docker compose -f $COMPOSE_FILE down || true
log "Contenedores detenidos"

# 6. Limpiar recursos Docker no utilizados
info "Limpiando recursos Docker..."
docker image prune -f > /dev/null 2>&1
docker volume prune -f > /dev/null 2>&1 || true
log "Limpieza completada"

# 7. Iniciar servicios
info "Iniciando servicios..."
docker compose -f $COMPOSE_FILE up -d
log "Servicios iniciados"

# 8. Esperar a que los servicios estén listos
info "Esperando a que los servicios estén listos..."
sleep 15

# 9. Ejecutar migraciones
info "Ejecutando migraciones de base de datos..."
docker exec laravel-app php artisan migrate --force || warning "Migraciones fallaron o no hay cambios"
log "Migraciones ejecutadas"

# 10. Optimizar aplicación
info "Optimizando aplicación..."
docker exec laravel-app php artisan config:cache
docker exec laravel-app php artisan route:cache
docker exec laravel-app php artisan view:cache
log "Optimización completada"

# 11. Verificar estado de contenedores
info "Estado de contenedores:"
docker compose -f $COMPOSE_FILE ps

# 12. Health check
echo ""
info "Verificando salud de la aplicación..."
sleep 5

# Verificar app
if docker exec laravel-app curl -f http://localhost:8080/ > /dev/null 2>&1; then
    log "✅ Aplicación Laravel respondiendo"
else
    warning "⚠️  Aplicación Laravel no responde aún"
fi

# Verificar nginx
if curl -f http://localhost:80 > /dev/null 2>&1; then
    log "✅ Nginx respondiendo"
else
    warning "⚠️  Nginx no responde aún"
fi

# 13. Mostrar logs recientes
echo ""
info "Últimos logs de la aplicación:"
echo "----------------------------------------"
docker compose -f $COMPOSE_FILE logs --tail=15 app
echo "----------------------------------------"

# 14. Resumen final
echo ""
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✅ Deployment Completado             ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}📊 Comandos útiles:${NC}"
echo ""
echo "  Ver logs en vivo:"
echo "    docker compose -f $COMPOSE_FILE logs -f app"
echo ""
echo "  Ver todos los servicios:"
echo "    docker compose -f $COMPOSE_FILE ps"
echo ""
echo "  Reiniciar aplicación:"
echo "    docker compose -f $COMPOSE_FILE restart app"
echo ""
echo "  Ejecutar comandos Artisan:"
echo "    docker exec laravel-app php artisan <comando>"
echo ""
echo "  Acceder al contenedor:"
echo "    docker exec -it laravel-app bash"
echo ""
echo "  Ver logs de Nginx:"
echo "    docker compose -f $COMPOSE_FILE logs -f nginx"
echo ""
echo -e "${YELLOW}🔗 URL de la aplicación:${NC}"
echo "  https://app.cuantosabe.com.ar"
echo ""