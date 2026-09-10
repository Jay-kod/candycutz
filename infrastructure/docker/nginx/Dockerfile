# Production Nginx Container for Candycutz Single-VPS Topology
FROM nginx:1.25-alpine

# Remove default server definition
RUN rm -rf /etc/nginx/conf.d/default.conf

# Copy custom production nginx configuration
COPY infrastructure/docker/nginx/nginx.conf /etc/nginx/nginx.conf

# Create web root and public storage directories
RUN mkdir -p /var/www/web/dist /var/www/html/public /var/www/html/storage/app/public \
    && chown -R nginx:nginx /var/www/web /var/www/html

# Copy pre-compiled Vue 3 web distribution
COPY barbing-saloon-web/dist /var/www/web/dist

# Expose HTTP and HTTPS
EXPOSE 80 443

# Health check using the internal /health-nginx probe
HEALTHCHECK --interval=15s --timeout=5s --retries=3 \
    CMD wget -qO- http://localhost/health-nginx || exit 1

CMD ["nginx", "-g", "daemon off;"]
