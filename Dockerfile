# Simple PHP Dockerfile for Render.com
FROM php:8.3-cli

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Set working directory
WORKDIR /app

# Copy application
COPY . /app/

# Create data directory for SQLite
RUN mkdir -p /app/data && chmod 755 /app/data
RUN mkdir -p /app/log && chmod 755 /app/log

# Expose port
EXPOSE 10000

# Start PHP built-in server (Render uses port 10000)
CMD ["php", "-S", "0.0.0.0:10000", "-t", "/app"]