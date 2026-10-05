# 🚀 Contexto del Proyecto: Qhatu (POS, Tienda y Agente IA)

Este documento contiene el contexto completo técnico y de negocio de **Qhatu**, para que cualquier Inteligencia Artificial pueda entender rápidamente la arquitectura, el stack y los flujos principales del sistema antes de comenzar a programar o sugerir cambios.

---

## 🏗️ 1. Arquitectura y Stack Tecnológico

El proyecto está dividido en dos repositorios/carpetas principales:

### Frontend (Next.js PWA)
- **Framework:** Next.js (App Router) + React + TypeScript.
- **Estilos:** Tailwind CSS + variables CSS nativas (lenguaje visual propio con colores `#1F3D3A`, `#E2672A`, `#FBF7EE`).
- **Iconos:** `lucide-react`.
- **Estado y Fetching:** Context API (`AuthContext`), `localStorage`, y un wrapper centralizado de peticiones en `lib/api.ts`.
- **PWA:** Funciona como aplicación instalable (Progressive Web App) con su propio `sw.js` (Service Worker) para cachear vistas y optimizar la carga en locales con mala conexión a internet.
- **Ruta de desarrollo típica:** `c:\Users\Giuliano\Documents\adl`

### Backend (Laravel API REST)
- **Framework:** Laravel 11 (PHP 8+) + MySQL.
- **ORM:** Eloquent.
- **Arquitectura:** Patrón MVC tradicional enfocado puramente en proveer endpoints JSON en `routes/api.php`.
- **Autenticación:** Tokens Bearer (probablemente Laravel Sanctum).
- **Ruta de desarrollo típica:** `c:\laragon\www\devVentas`

---

## 🎯 2. Módulos y Funcionalidad del Sistema

Qhatu es un ecosistema 360º para negocios (farmacias, peluquerías, comercios, financieras) que unifica el cobro físico con la presencia digital y la atención por Inteligencia Artificial.

### A. Punto de Venta (POS) y Gestión Admin (`/admin`, `/pos`, `/dashboard`)
- **POS / Caja:** Permite procesar ventas rápidamente. Soporta carrito de compras, cálculo de precios dinámicos (precios por mayor/docenas), y **pagos mixtos** (ej. cobrar una parte en efectivo, otra en transferencia y otra en "cuenta corriente" / fiado).
- **Gestión de Local:** El administrador (dueño) configura su perfil (`app/perfil/page.tsx`): logo, portada, CBU/Alias bancario, horarios semanales, ubicación, redes sociales, y categorías de su negocio.
- **QR Dinámico:** El sistema genera un QR público (vía `api.qrserver.com`) para que los locales lo impriman y peguen en su mostrador.

### B. Marketplace y Tienda Pública (`/tienda`)
- **Feed Público:** Los clientes finales entran a `qhatu.app/tienda` para explorar locales de su ciudad ordenados por categorías, tendencias y cercanía.
- **Perfiles Públicos:** Cada local tiene su URL amigable (`/tienda/local/[slug]`).
- **Horarios Dinámicos:** El frontend lee el JSON de horarios del backend y cruza con la hora actual para mostrar un `StatusBadge` ("Abierto" / "Cerrado") en tiempo real.
- **Catálogo:** Muestra los productos y servicios (`Articulo` y `Servicio`) del local.

### C. Sistema de Turnos y Agenda (`Cola Virtual` y `Turnos Fijos`)
- **Creación de Servicios:** Barberías, canchas o consultorios configuran servicios con su precio y duración.
- **Tipos de reserva:**
  1. `cola_virtual`: El cliente saca número en el momento y la app le dice su "posición" y la hora estimada.
  2. `turno_fijo`: El cliente elige una fecha y un bloque de horario (grilla armada inteligentemente).
  3. `coordinacion`: Redirige al WhatsApp del local.
- **Ticket en vivo (`/tienda/turno/[token]`):** Pantalla pública en tiempo real donde el cliente ve cómo avanza la cola y recibe un aviso cuando es su turno.
- **Staff / Empleados:** Un cliente puede pedir turno con un barbero específico (pasando el `?staff=[id]` en la URL). El backend asocia automáticamente la venta al ID del recurso/empleado correspondiente.

### D. Agente IA (n8n + WhatsApp)
- **Integración WhatsApp:** Qhatu está conectado a un Agente IA desplegado en flujos de **n8n**.
- **Acceso a datos:** La IA consulta la base de datos de Qhatu (stock, catálogo, precios actualizados, disponibilidad de turnos).
- **Asistente del Comercio:** Los clientes pueden chatear con el número de WhatsApp de la plataforma, preguntar si la farmacia "X" está de turno, cuánto cuesta un producto o pedir directamente un turno. La IA usa el contexto de la base de datos de Qhatu para responder de forma asertiva y ejecutar acciones vía API.

---

## 🛠️ 3. Particularidades del Código (Reglas para la IA)

Si vas a modificar el código de Qhatu, tené en cuenta estos estándares descubiertos durante el desarrollo:

1. **Variables Overload (Polimorfismo en Turnos):** En el flujo de creación de turnos (`TurnoController::store`), el frontend suele mandar el ID del empleado en el campo `recurso_id`. El backend se encarga de discriminar si ese ID pertenece a la tabla `personas` (un empleado) o a `recursos` (una instalación física).
2. **Pagos Mixtos Estrictos:** Las funciones que completan turnos o ventas en el backend siempre esperan poder recibir múltiples variables de monto simultáneas (`monto_efectivo`, `monto_transferencia`, `monto_cuenta_corriente`) para dividir el cobro.
3. **Manejo de Imágenes de Frontend:** No confíes ciegamente en que las variables de imagen siempre se llaman igual; la base de datos mezcla formatos legacy. Usá siempre fallbacks. Ej: `const portada = store?.foto_portada_url || store?.foto_portada || store?.portada`.
4. **Caché del Frontend:** El proyecto usa un `sw.js` (Service Worker). Al hacer cambios grandes en CSS o UI, recordá indicarle al desarrollador que suba la versión del `CACHE_NAME` en el archivo `/public/sw.js`.
5. **No ejecutar migraciones auto:** El desarrollador a cargo prefiere correr los comandos de migración de base de datos de forma manual. Limitate a darle las instrucciones SQL o el código del archivo PHP, no lances comandos automatizados por consola.

---

## 🛑 4. Reglas Estrictas de Consola y Ejecución (Workflow del Dev)

Para alinearse con el flujo de trabajo del desarrollador principal, la IA debe respetar SIEMPRE las siguientes restricciones al interactuar con la terminal:

1. **Migraciones y Base de Datos:** NUNCA ejecutes `php artisan migrate`, `db:seed` ni ningún comando destructivo o que altere la estructura de la base de datos de Laravel. Solo generá el archivo de migración y pedile al usuario que lo ejecute.
2. **Instalación de Dependencias:** NUNCA ejecutes comandos como `npm install`, `composer require` o `npm run build`. Si necesitamos una nueva librería o probar un build, dale el comando exacto al desarrollador para que él lo corra en su consola cuando lo considere oportuno.
3. **Manejo de Servidores de Desarrollo:** No intentes iniciar, detener o reiniciar los servidores de desarrollo (`php artisan serve`, `npm run dev`). Asumí que el entorno ya está corriendo.
4. **Git y Control de Versiones:** La IA no debe realizar commits ni pushear código. Todo el control de ramas, merges y subidas a repositorios es responsabilidad exclusiva del desarrollador.

---

## 💬 5. Estilo de Trabajo y Comunicación (Iterativo)

1. **Cero Asunciones y Planificación Previa:** Nunca asumas requerimientos técnicos ni de negocio si falta información. Si algo es ambiguo, preguntá. Ante una tarea nueva o compleja, **siempre presentá un plan de acción** paso a paso para que el desarrollador lo valide antes de empezar a modificar código.
2. **Pasos Cortos y Testeables:** Dividí el desarrollo en partes pequeñas y comprobables de manera independiente (ej: primero armar el endpoint y probarlo; después conectar el componente frontend).
3. **Pausar y Esperar Feedback:** Después de implementar y entregar un paso lógico, **detené la ejecución**. Esperá siempre la confirmación del desarrollador de que esa parte funciona correctamente en su entorno local antes de continuar con el siguiente paso del plan.
