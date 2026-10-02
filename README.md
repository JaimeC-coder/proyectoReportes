# Sistema de Informes Técnicos

Aplicación Laravel para crear **informes técnicos con texto enriquecido**, imágenes y **generación automática de PDF**.
Incluye plantillas configurables (viene precargada la de **Mantenimiento de Pozo a Tierra**), vista previa en vivo y control de acceso por roles.

## Stack

| Capa | Tecnología |
|---|---|
| Backend | Laravel 12 · Jetstream (Livewire) · Fortify · Sanctum |
| UI | Livewire 3 · Alpine · **WireUI 2** (prefijo `x-w-`) · Tailwind CSS 3 · **SweetAlert2** · modo oscuro |
| Editor | **Tiptap 3** (negrita, colores, resaltado, listas, alineación, enlaces, tablas, imágenes) |
| PDF | **DomPDF** (barryvdh/laravel-dompdf) |
| Roles | spatie/laravel-permission (Administrador / Técnico) |
| BD | MySQL |

## Instalación en Laragon (Windows)

Requisitos: **PHP 8.2 o superior** (con las extensiones `gd`, `zip`, `fileinfo`, `pdo_mysql` y `mbstring` activas), Composer, Node 18+ y MySQL.
En Laragon: *Menú → PHP → Version* (elige 8.2 o superior) y *Menú → PHP → Extensions → gd*.

```bash
cd D:\PROYECT\PHP\proyectoReportes

composer install
copy .env.example .env          # si aún no existe el .env
php artisan key:generate
```

Crea la base de datos `proyecto_reportes` (desde HeidiSQL, o con *Laragon → Database*) y revisa `DB_*` en `.env`
(Laragon por defecto: usuario `root` sin contraseña). Después:

```bash
php artisan migrate --seed      # tablas, roles, usuarios, plantilla Pozo a Tierra e informe de ejemplo
php artisan storage:link        # necesario para ver las imágenes subidas
npm install
npm run build                   # o "npm run dev" mientras desarrollas
php artisan serve               # http://127.0.0.1:8000
```

> También puedes usar `composer run setup` (hace todo lo anterior después de crear la BD) y `composer run dev` (servidor + Vite).
> Si mueves el proyecto a `C:\laragon\www`, Laragon creará `http://proyectoreportes.test`; en ese caso actualiza `APP_URL` en `.env`.

### Usuarios de prueba

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@informes.test` | `password` |
| Técnico | `tecnico@informes.test` | `password` |

**Cambia estas contraseñas** en producción (Usuarios → Editar).

## Funcionalidades

- **Solo login**: no hay registro público ni recuperación de contraseña, y `/` redirige al dashboard o al login. Los usuarios desactivados no pueden ingresar.
- **Dashboard** con estadísticas y últimos informes.
- **Plantillas** (administrador): crear, editar, duplicar, activar/desactivar y eliminar. Viene precargada *Mantenimiento de Pozo a Tierra* con las 6 secciones del documento de referencia.
- **Editor de informes** con **vista previa en vivo** al costado (en móvil, con el botón *Vista previa*):
  - Se elige una plantilla o se empieza un informe en blanco.
  - Cada sección tiene **subtítulo, descripción y un bloque de contenido**, y se puede subir, bajar, duplicar, insertar debajo o eliminar.
  - El card grande con **＋** abre un **modal** para elegir el tipo de bloque:
    - **Texto enriquecido** (Tiptap, con imágenes y tablas dentro del texto)
    - **Campos clave-valor** (p. ej. Información general)
    - **Tabla editable** (p. ej. Cuadro comparativo)
    - **Galería de imágenes** (Evidencias fotográficas, 1 o 2 por fila, con título y descripción)
  - Las imágenes se optimizan al subirse (máximo 1600 px, JPG al 82 %).
- **Al guardar** se genera el PDF y el informe queda **inmutable**: solo se puede ver, descargar o **duplicar** para crear uno nuevo. El código correlativo tiene el formato `INF-AAAA-0001`.
- **Ajustes del PDF** (administrador): logo, razón social, RUC, dirección, color principal, pie de página, etiquetas y opciones para mostrar u ocultar la empresa, los datos del cliente y ubicación, la firma del técnico, el código y la numeración de páginas.
- **Firma**: cada usuario sube su firma y su cargo en *Mi perfil*. La firma se guarda "congelada" en cada informe.
- **Modo oscuro** con un botón en la barra superior y en el login (se recuerda por navegador).

## Estructura (Controller/Livewire → Service → Repository)

```
app/
├── Data/                 DTOs: ReportData, TemplateData, SectionData (normaliza y limpia cada bloque)
├── Enums/                SectionType, Role, Permission
├── Http/Controllers/     ReportPdfController (ver / descargar PDF)
├── Http/Middleware/      EnsureUserIsActive
├── Livewire/
│   ├── Concerns/ManagesSections.php   Lógica compartida del constructor de secciones
│   ├── Reports/ (Index, Create, Show) · Templates/ (Index, Editor)
│   ├── Users/Index · Settings/Edit · Profile/SignatureForm · Dashboard
├── Policies/ReportPolicy.php
├── Repositories/         ReportRepository (filtros, correlativo), TemplateRepository
├── Services/             ReportService, ReportPdfService, ReportDocumentBuilder,
│                         TemplateService, SettingService, ImageService
└── Support/              HtmlSanitizer (anti-XSS), DocumentAsset
resources/views/
├── documents/            report.blade.php + styles.blade.php  ← el MISMO diseño para la vista previa y el PDF
├── livewire/sections/    builder, preview y editors/{richtext,key_value,table,gallery}
└── pdf/report.blade.php
resources/js/editor.js    Componente Alpine de Tiptap
```

Para cambiar el diseño del informe, edita `resources/views/documents/`. Usa solo CSS compatible con DomPDF (tablas en lugar de flex o grid).

## Pruebas

```bash
php artisan test
```

Hay 27 pruebas: acceso solo con login, permisos por rol, creación completa de un informe con PDF, duplicado, plantillas y limpieza del HTML.
