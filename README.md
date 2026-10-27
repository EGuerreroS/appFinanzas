# 💰 appFinanzas — Finanzas personales

App web para controlar ingresos, gastos, categorías (esencial / no esencial / ahorro) y presupuestos mensuales,
con gráficas y comparación contra metodologías de ahorro (50/30/20, 70/20/10 o personalizada).

**Stack:** Laravel 13 + MySQL · Vue 3 + Inertia.js · Quasar · Chart.js · Docker. La API REST con Sanctum (`/api/...`) sigue disponible.

Sigue estos pasos después de clonar el repositorio para levantar el proyecto localmente.

---

## 📋 Requisitos previos

Asegúrate de tener instalado:

| Herramienta | Verificar con |
|---|---|
| Docker | `docker --version` |
| Docker Compose v2 | `docker compose version` |

---

## ▶️ Instalación en un solo comando

```bash
bash setup.sh
```

Eso es todo. El script hace automáticamente:

1. Copia `.env.example` → `.env` *(si no existe)*
2. Levanta los contenedores Docker
3. Ejecuta `composer install`
4. Genera el `APP_KEY`
5. Ejecuta migraciones y seeders

---

## 🌐 URLs disponibles al terminar

| Servicio | URL |
|---|---|
| App web (y API en `/api`) | http://localhost:8000 |
| phpMyAdmin | http://localhost:8080 |

Usuario de prueba (del seeder): `superusuario@live.es` / `123456`. Cambia la contraseña si vas a usarla con datos reales.

> `setup.sh` no compila el frontend: después de correrlo ejecuta `npm install && npm run build`.

---

## ⚙️ Variables de entorno

Si necesitas cambiar algo (nombre de BD, credenciales, puertos), edita el archivo `.env` **antes** de correr el script:

```bash
cp .env.example .env
# edita .env con tu editor
bash setup.sh
```

---

## 🛠 Comandos útiles

```bash
# Ver logs en tiempo real
docker compose logs -f

# Compilar el frontend (en el host, requiere Node 20+)
npm install
npm run build      # o `npm run dev` mientras desarrollas

# Cargar datos de prueba (2 ingresos + un gasto por categoría en el mes actual)
docker compose exec app php artisan db:seed --class=DemoDataSeeder

# Respaldar la base (los .sql están en .gitignore para no subir datos personales)
docker compose exec db mysqldump -u root -proot appFinanzas > respaldo.sql

# Entrar al contenedor
docker compose exec app bash

# Detener contenedores
docker compose down

# Reiniciar desde cero (borra datos)
docker compose exec app php artisan migrate:fresh --seed
```

---

## ❗ Problemas comunes

**El script dice "El archivo .env ya existe"**
Normal si ya corriste el script antes. Tu `.env` actual se conserva sin cambios.

**Error de permisos al ejecutar el script**
```bash
chmod +x setup.sh
bash setup.sh
```

**Los contenedores tardan en iniciar**
Si ves errores de conexión a la base de datos justo al correr, espera unos segundos y vuelve a ejecutar solo las migraciones:
```bash
docker compose exec app php artisan migrate --seed --force
```
