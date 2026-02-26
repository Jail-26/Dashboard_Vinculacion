Proyecto de ejemplo "Proyectos de Vinculación" — preparación para subir a GitHub

Pasos rápidos antes de publicar en un repo público:

- Copia `includes/config.example.php` a `includes/config.php` y configura tus credenciales localmente.
- Alternativa: exporta variables de entorno (`DB_HOST`, `DB_USER`, `DB_PASS`, `HASH_KEY`, `SMTP_*`).
- NO subir archivos con datos reales: `base_vistas_datos.sql`, `database_dashboard.sql`, `vinculacion.sql`, `vistas.sql`.
- Se han añadido copias sanitizadas: `*.sanitized.sql` para referencias públicas.
- Asegúrate de rotar cualquier contraseña que haya estado en este repositorio anteriormente.

Configuración de ejemplo (bash):
```
export DB_HOST=127.0.0.1
export DB_USER=root
export DB_PASS=mi_password_secreta
export HASH_KEY=clave_segura_y_unica
```

Si quieres, puedo:
- crear un `.env.example` y convertir la carga de config para usar `vlucas/phpdotenv`.
- limpiar más archivos sensibles o ayudarte a reescribir el historial Git para eliminar secretos.
Cómo instalar dependencias y preparar el proyecto localmente:

```
# Instalar dependencias (requiere composer instalado)
composer install

# Copiar ejemplo y configurar tu .env local
cp .env.example .env
# Edita .env y completa claves seguras

# Opcional: si ya tienes archivos sensibles en git history, revisa los pasos de limpieza abajo
```

Comandos sugeridos para limpiar historial de Git (usar con cuidado):

```
# 1) Eliminar archivos sensibles del index y crear commit que los remueve
git rm --cached includes/config.php base_vistas_datos.sql database_dashboard.sql vinculacion.sql vistas.sql
git commit -m "Remove sensitive files before publishing"

# 2) Reescribir historial para eliminar secretos (ejemplo con BFG or git-filter-repo)
# Usar `bfg` or `git filter-repo`. Ejemplo con BFG:
# java -jar bfg.jar --delete-files includes/config.php,*.sql
# git reflog expire --expire=now --all && git gc --prune=now --aggressive

# 3) Forzar push al remoto (avisar: sobrescribe remoto)
# git remote add origin git@github.com:TU_USUARIO/TU_REPO.git
# git push -u --force origin main
```

Recomendación de subida a GitHub:
- Crea el repo en GitHub (privado o público según prefieras). Si es público, asegúrate de que no quedan secretos.
- Añade el remoto y realiza `git push`.
- Si quieres, puedo preparar y ejecutar los comandos locales aquí (necesito confirmación y la URL del repositorio para añadir remoto).
