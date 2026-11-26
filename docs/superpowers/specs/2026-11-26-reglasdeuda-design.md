# local_reglasdeuda — Diseño

Fecha: 2026-11-26

## Intención

La universidad necesita que Moodle reaccione a la situación financiera del
estudiante: una API institucional indica si una persona tiene deuda (sí/no) y,
según eso, Moodle bloquea la plataforma, restringe recursos o solo avisa, con
un mensaje personalizado que incluye el total adeudado y un enlace de pago.

Debe ser dinámico: Tesorería/TI registran **reglas** (cada una con su API y su
acción) sin tocar código, y pueden **probarlas** desde la administración.

## Objetivos

- Varias reglas, cada una con: API (URL, autenticación, plantilla JSON),
  identificador del usuario (`username`, `idnumber` o `email`), valor que la
  activa (`true` o `false`), acción y mensaje.
- Tres acciones por regla:
  1. **Bloquear plataforma**: tras iniciar sesión, página con el mensaje; no
     se puede navegar.
  2. **Bloquear recursos**: (a) condición de disponibilidad por actividad
     (`availability_reglasdeuda`) y (b) opcional, bloqueo global por tipo de
     actividad y categoría de curso configurado en la regla.
  3. **Solo mensaje**: aviso en el Área personal y al entrar a cada curso.
- Mensaje editable (título, cuerpo HTML, botón con texto y enlace, estilo) con
  variables del usuario, del curso y de la respuesta de la API, seleccionables
  con un clic (sin memorizar la sintaxis).
- Pantalla **Probar**: consulta real con un usuario, muestra petición,
  respuesta, decisión y el mensaje renderizado, sin bloquear a nadie.
- Página de **contrato** con la estructura que debe cumplir la API.
- Botón "Ya pagué, volver a verificar" que limpia la caché del usuario.

## No objetivos

- No se consultan rubros individuales: la API manda el total.
- No se procesan pagos dentro de Moodle; solo se enlaza a la pasarela.
- No hay reportes históricos de consultas (solo registro de errores).

## Anti-objetivos (fallo aunque "funcione")

- Que una caída de la API deje a toda la universidad sin acceso
  (por defecto, si la API falla, se deja pasar; configurable por regla).
- Que administradores o docentes queden bloqueados (exentos por capacidad
  `local/reglasdeuda:exento` y por roles exentos configurables por regla).
- Que cada clic del estudiante genere una consulta a la API (caché con TTL).
- Que datos de la API inyecten HTML/JS en el mensaje (todo valor se escapa).

## Restricciones

- Moodle 4.1 → 5.1 (en 5.1 los plugins viven en `public/local/` y
  `public/availability/condition/`).
- Mismo estilo que `local_mod`: código y comentarios en español, clases en
  `classes/local/`, cadenas `en` y `es`, `build.py` que genera los ZIP en
  `dist/` renombrando la carpeta raíz.

## Contrato de la API (por defecto)

Petición: `POST <url>`, `Content-Type: application/json`, autenticación
configurable (ninguna, `Authorization: Bearer <token>` o cabecera propia).
Cuerpo (plantilla editable, se validan como JSON):

```json
{
  "regla": "{regla}",
  "identificador": "{identificador}",
  "tipo_identificador": "{tipo_identificador}",
  "usuario": {"username": "{username}", "email": "{email}", "idnumber": "{idnumber}"}
}
```

Respuesta esperada (HTTP 200):

```json
{
  "resultado": true,
  "mensaje": "opcional, reemplaza el mensaje configurado",
  "datos": {"total": "235.50", "url_pago": "https://..."}
}
```

- La ruta del campo de resultado es configurable (por defecto `resultado`).
- `true`/`"true"`/`1`/`"1"`/`"si"`/`"sí"` se interpretan como verdadero.
- Error = HTTP ≠ 200, JSON inválido, tiempo agotado o campo ausente → se
  aplica la política de error de la regla (por defecto: dejar pasar).

## Enfoques considerados

1. **Una sola API en la configuración del plugin** — más simple, pero cada
   caso nuevo (documentos, encuestas) exigiría otro plugin. Descartado.
2. **Reglas en tabla propia + plugin de disponibilidad** — elegido. La
   restricción por actividad es nativa de Moodle; el bloqueo global cubre el
   caso "Tesorería controla sin depender de docentes".
3. **Solo plugin de disponibilidad** — no cubre bloqueo de plataforma ni
   avisos. Descartado.

## Decisiones del constructor (propuestas, corregibles)

- Caché MUC por (regla, usuario) con TTL por regla (15 min por defecto).
- Variables de la API: se descubren de una **respuesta de ejemplo** guardada
  en la regla; la pantalla Probar permite "usar esta respuesta como ejemplo".
  Bajo el editor aparecen chips por grupo (Usuario, Curso, API); un clic
  inserta la variable donde está el cursor (TinyMCE/Atto/textarea) o, si no
  se puede, la copia al portapapeles. La vista previa del mensaje está en la
  pantalla Probar (con datos reales), no en vivo en el formulario.
- El mensaje se arma con `html_writer` (no mustache) porque se genera dentro
  de `require_login`, antes de inicializar el tema; los avisos usan
  `\core\notification` (zona de contenido del Área personal y del curso).
- Callback `local_reglasdeuda_after_require_login` (verificado: vigente de
  4.1 a 5.1, sin hook de reemplazo). Formulario de disponibilidad en YUI
  (verificado: sigue siendo YUI en 5.1).
- El enlace del botón admite variables; si queda vacío o no es URL válida se
  usa el enlace de respaldo de la regla; si tampoco hay, no se muestra botón.
- Hosts internos (API en red privada) solo si el admin activa
  "Permitir hosts internos" en los ajustes generales.

## Cambio 2026-11-26: un solo plugin

Se pidió un único ZIP. Se eliminó `availability_reglasdeuda` (Moodle instala un
plugin por ZIP y la condición de "Restringir acceso" exige su propio tipo de
plugin). El bloqueo de recursos ahora se define solo en la regla: actividades
concretas por ID (`actividades`) y/o filtros por tipo, categoría y curso
(`globalactivo`, `globalmodulos`, `globalcategorias`, `globalcursos`). Los
docentes ya no configuran la restricción por actividad; la actividad sigue
visible y el usuario ve el mensaje al entrar.
