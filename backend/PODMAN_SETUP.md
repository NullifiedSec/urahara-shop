# Podman Setup Guide for UraharaShop

This guide explains how to set up and run the database containers using Podman.

## Prerequisites

- Podman installed on your system
- `podman-compose` installed (optional, for easier management)

### Install Podman

**Arch Linux:**
```bash
sudo pacman -S podman podman-compose
```

**Ubuntu/Debian:**
```bash
sudo apt-get install podman podman-compose
```

**Fedora:**
```bash
sudo dnf install podman podman-compose
```

## Quick Start

### 1. Start the Database Containers

Navigate to the backend directory:
```bash
cd backend
```

Using `podman-compose` (recommended):
```bash
podman-compose up -d
```

Or using `podman` directly:
```bash
podman-compose -f podman-compose.yml up -d
```

### 2. Check Container Status

```bash
podman ps
```

You should see:
- `uraharashop-postgres` (PostgreSQL database)
- `uraharashop-redis` (Redis cache)

### 3. Configure Backend Environment

Copy the example environment file (if it exists) or create `.env`:
```bash
# If .env.podman.example exists
cp .env.podman.example .env

# Or create .env manually
```

Update the database credentials in `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=uraharashop
DB_USERNAME=uraharashop
DB_PASSWORD=uraharashop123
```

### 4. Run Migrations

```bash
php artisan migrate
```

## Container Management

### Start Containers
```bash
podman-compose up -d
```

### Stop Containers
```bash
podman-compose down
```

### View Logs
```bash
# All containers
podman-compose logs

# Specific container
podman-compose logs postgres
podman-compose logs redis
```

### Restart Containers
```bash
podman-compose restart
```

### Remove Containers and Volumes
```bash
# Remove containers but keep volumes
podman-compose down

# Remove containers and volumes (WARNING: deletes all data)
podman-compose down -v
```

## Port Configuration

Default ports (can be changed in `podman-compose.yml`):
- **PostgreSQL**: `5432`
- **MySQL**: `3306` (if enabled)
- **Redis**: `6379`

To change ports, edit `podman-compose.yml` or set environment variables:
```bash
DB_PORT=5433 podman-compose up -d
```

## Database Options

### Using PostgreSQL (Default)

The default configuration uses PostgreSQL. No changes needed.

### Using MySQL

1. Comment out the PostgreSQL service in `podman-compose.yml`
2. Uncomment the MySQL service
3. Update `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uraharashop
DB_USERNAME=uraharashop
DB_PASSWORD=uraharashop123
```

4. Restart containers:
```bash
podman-compose down
podman-compose up -d
```

## Connecting to the Database

### PostgreSQL

```bash
# Using psql
podman exec -it uraharashop-postgres psql -U uraharashop -d uraharashop

# Or from host (if psql is installed)
psql -h 127.0.0.1 -p 5432 -U uraharashop -d uraharashop
```

### MySQL

```bash
# Using mysql client
podman exec -it uraharashop-mysql mysql -u uraharashop -p uraharashop

# Or from host (if mysql client is installed)
mysql -h 127.0.0.1 -P 3306 -u uraharashop -p uraharashop
```

## Data Persistence

Data is stored in Podman volumes:
- `uraharashop_postgres_data` - PostgreSQL data
- `uraharashop_mysql_data` - MySQL data (if used)
- `uraharashop_redis_data` - Redis data

To backup data:
```bash
# PostgreSQL
podman run --rm -v uraharashop_postgres_data:/data -v $(pwd):/backup alpine tar czf /backup/postgres_backup.tar.gz /data

# MySQL
podman run --rm -v uraharashop_mysql_data:/data -v $(pwd):/backup alpine tar czf /backup/mysql_backup.tar.gz /data
```

## Troubleshooting

### Container won't start

Check logs:
```bash
podman-compose logs postgres
```

### Port already in use

Change the port in `podman-compose.yml`:
```yaml
ports:
  - "5433:5432"  # Use 5433 on host instead of 5432
```

### Permission denied

If you get permission errors, you may need to run with sudo or configure rootless Podman:
```bash
# Run with sudo (not recommended)
sudo podman-compose up -d

# Or configure rootless Podman (recommended)
podman system migrate
```

### Database connection refused

1. Check if container is running:
   ```bash
   podman ps
   ```

2. Check container logs:
   ```bash
   podman-compose logs postgres
   ```

3. Verify port forwarding:
   ```bash
   podman port uraharashop-postgres
   ```

4. Test connection:
   ```bash
   podman exec -it uraharashop-postgres pg_isready -U uraharashop
   ```

## Environment Variables

You can override default values by setting environment variables:

```bash
cd backend
DB_DATABASE=myapp \
DB_USERNAME=myuser \
DB_PASSWORD=mypassword \
DB_PORT=5433 \
podman-compose up -d
```

Or create a `.env` file in the backend directory:
```env
DB_DATABASE=uraharashop
DB_USERNAME=uraharashop
DB_PASSWORD=uraharashop123
DB_PORT=5432
REDIS_PORT=6379
```

## Health Checks

Containers include health checks. View status:
```bash
podman inspect uraharashop-postgres | grep -A 10 Health
```

## Network Configuration

Containers are connected via a bridge network `uraharashop-network`. To connect additional containers:

```bash
podman network connect uraharashop-network <container-name>
```

## Production Considerations

For production, consider:
1. Using stronger passwords
2. Setting up SSL/TLS for database connections
3. Configuring firewall rules
4. Setting up regular backups
5. Using secrets management instead of environment variables
6. Configuring resource limits in `podman-compose.yml`

Example resource limits:
```yaml
services:
  postgres:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
        reservations:
          cpus: '1'
          memory: 1G
```

