#!/bin/bash

# Script de despliegue para EC2
# Uso: ./deploy-ec2.sh

set -e

echo "🚀 Iniciando despliegue en EC2..."

# Variables
IMAGE_NAME="lcebreiros/cuantosabe:latest"
COMPOSE_FILE="docker-compose.prod.yml"

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Función para imprimir con color
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# 1. Verificar que estamos en el servidor correcto
echo "📍 Verificando servidor..."
if [ ! -f "$COMPOSE_FILE" ]; then
    print_error "No se encontró $COMPOSE_FILE"
    exit 1
fi
print_success "Archivo de configuración encontrado"

# 2. Detener contenedores actuales
echo "🛑 Deteniendo contenedores actuales..."
docker-compose -f $COMPOSE_FILE down || true
print_success "Contenedores detenidos"

# 3. Pull de la última imagen
echo "📥 Descargando última imagen..."
docker pull $IMAGE_NAME
print_success "Imagen descargada"

# 4. Limpiar imágenes antiguas
echo "🧹 Limpiando imágenes antiguas..."
docker image prune -f
print_success "Limpieza completada"

# 5. Iniciar servicios
echo "🚀 Iniciando servicios..."
docker-compose -f $COMPOSE_FILE up -d
print_success "Servicios iniciados"

# 6. Esperar a que la aplicación esté lista
echo "⏳ Esperando que la aplicación esté lista..."
sleep 10

# 7. Verificar estado de los contenedores
echo "🔍 Verificando estado de contenedores..."
docker-compose -f $COMPOSE_FILE ps

# 8. Verificar logs
echo "📋 Últimos logs de la aplicación:"
docker-compose -f $COMPOSE_FILE logs --tail=20 app

# 9. Health check
echo "🏥 Verificando salud de la aplicación..."
if curl -f http://localhost:80 > /dev/null 2>&1; then
    print_success "✅ Aplicación funcionando correctamente"
else
    print_warning "⚠️  La aplicación puede no estar respondiendo aún"
fi

# 10. Mostrar información útil
echo ""
echo "================================================"
print_success "🎉 Despliegue completado"
echo "================================================"
echo ""
echo "📊 Comandos útiles:"
echo "  Ver logs:        docker-compose -f $COMPOSE_FILE logs -f app"
echo "  Ver estado:      docker-compose -f $COMPOSE_FILE ps"
echo "  Reiniciar:       docker-compose -f $COMPOSE_FILE restart"
echo "  Detener:         docker-compose -f $COMPOSE_FILE down"
echo "  Entrar al shell: docker-compose -f $COMPOSE_FILE exec app bash"
echo ""