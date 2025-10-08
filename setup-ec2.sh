#!/bin/bash

# ============================================
# Setup Inicial EC2 - CuantoSabe
# Ejecutar con: sudo bash setup-ec2.sh
# ============================================

set -e

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

log() { echo -e "${GREEN}✓${NC} $1"; }
warning() { echo -e "${YELLOW}⚠${NC} $1"; }
error() { echo -e "${RED}✗${NC} $1"; exit 1; }
info() { echo -e "${BLUE}ℹ${NC} $1"; }

# Verificar que se ejecuta como root
if [ "$EUID" -ne 0 ]; then 
    error "Por favor ejecuta este script con sudo"
fi

echo ""
echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   🚀 Setup EC2 - CuantoSabe            ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"
echo ""

# 1. Actualizar sistema
info "Actualizando sistema..."
apt update && apt upgrade -y
log "Sistema actualizado"

# 2. Instalar dependencias
info "Instalando dependencias..."
apt install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    lsb-release \
    git \
    htop \
    vim \
    unzip \
    ufw
log "Dependencias instaladas"

# 3. Instalar Docker
info "Instalando Docker..."
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com | sh
    systemctl enable docker
    systemctl start docker
    usermod -aG docker ubuntu
    log "Docker instalado"
else
    log "Docker ya está instalado"
fi

# 4. Instalar Docker Compose
info "Instalando Docker Compose..."
if ! docker compose version &> /dev/null; then
    mkdir -p /usr/local/lib/docker/cli-plugins
    curl -SL "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" \
        -o /usr/local/lib/docker/cli-plugins/docker-compose
    chmod +x /usr/local/lib/docker/cli-plugins/docker-compose
    log "Docker Compose instalado"
else
    log "Docker Compose ya está instalado"
fi

# 5. Configurar firewall
info "Configurando firewall UFW..."
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable
log "Firewall configurado"

# 6. Crear estructura de directorios
info "Creando directorios del proyecto..."
mkdir -p ~/cuantosabe/{certbot/{conf,www},backups}
cd ~/cuantosabe
log "Directorios creados"

# 7. Configurar límites del sistema
info "Optimizando sistema..."
cat >> /etc/sysctl.conf <<EOF

# Optimizaciones Docker
net.core.somaxconn = 1024
net.ipv4.tcp_max_syn_backlog = 2048
vm.swappiness = 10
EOF
sysctl -p > /dev/null
log "Sistema optimizado"

# 8. Configurar logrotate
info "Configurando rotación de logs..."
cat > /etc/logrotate.d/cuantosabe <<EOF
/home/ubuntu/cuantosabe/storage/logs/*.log {
    daily
    rotate 7
    compress
    delaycompress
    notifempty
    missingok
}
EOF
log "Logrotate configurado"

# 9. Crear alias útiles
info "Creando alias útiles..."
cat >> /home/ubuntu/.bashrc <<'EOF'

# CuantoSabe Aliases
alias cs='cd ~/cuantosabe'
alias cs-logs='docker compose -f ~/cuantosabe/docker-compose.prod.yml logs -f'
alias cs-ps='docker compose -f ~/cuantosabe/docker-compose.prod.yml ps'
alias cs-restart='docker compose -f ~/cuantosabe/docker-compose.prod.yml restart'
alias cs-exec='docker exec -it laravel-app bash'
alias cs-artisan='docker exec laravel-app php artisan'
alias cs-deploy='cd ~/cuantosabe && ./deploy.sh'
EOF
log "Aliases creados"

# 10. Resumen final
echo ""
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✅ Setup Completado                  ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
echo ""
echo -e "${YELLOW}📋 Próximos pasos:${NC}"
echo ""
echo "1. Salir y volver a conectar para aplicar cambios:"
echo "   exit"
echo "   ssh -i tu-clave.pem ubuntu@tu-ip"
echo ""
echo "2. Ir al directorio del proyecto:"
echo "   cd ~/cuantosabe"
echo ""
echo "3. Subir tus archivos:"
echo "   - docker-compose.prod.yml"
echo "   - .env.prod"
echo "   - nginx.conf"
echo "   - deploy.sh"
echo ""
echo "4. Obtener certificado SSL:"
echo "   docker compose -f docker-compose.prod.yml run --rm certbot certonly --webroot \\"
echo "     --webroot-path=/var/www/certbot \\"
echo "     --email tu@email.com \\"
echo "     --agree-tos \\"
echo "     -d app.cuantosabe.com.ar"
echo ""
echo "5. Hacer el primer deployment:"
echo "   chmod +x deploy.sh"
echo "   ./deploy.sh"
echo ""
echo -e "${BLUE}🎉 ¡Todo listo para deployar!${NC}"
echo ""