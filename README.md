# Reglas de deuda (API) — plugin de bloqueo para Moodle

`local_reglasdeuda` consulta una API de la institución (por ejemplo, el ERP
de cobros: *"¿este estudiante tiene deuda?"*) y, según la respuesta,
**bloquea la plataforma**, **bloquea recursos** o **solo muestra un aviso**.
El mensaje que ve el estudiante se personaliza con sus datos y con lo que
devuelve la API (total adeudado, enlace de pago...).

- Un solo plugin, un solo ZIP. Compatible con **Moodle 4.1 a 5.1**.
- Se pueden tener **varias reglas** (deuda, documentos pendientes, encuestas...),
  cada una con su API, su acción y su mensaje.

---

## 1. Instalación

1. Generar el ZIP:
   ```bash
   py build.py          # crea dist/local_reglasdeuda_<versión>.zip
   ```
2. En Moodle: **Administración del sitio › Extensiones › Instalar extensiones**,
   subir el ZIP y seguir los pasos.
3. Al terminar, Moodle lleva automáticamente al administrador a la pantalla
   del plugin. Esto ocurre una sola vez después de instalar o actualizar.

> Instalación manual: copiar la carpeta `local_reglasdeuda` como
> `local/reglasdeuda` (en Moodle 5.1: `public/local/reglasdeuda`) y visitar
> *Administración del sitio › Notificaciones*.

## 2. Dónde está

| Cómo llegar | Ruta |
|---|---|
| Menú | **Administración del sitio › Extensiones › Extensiones locales › Reglas de deuda (API)** |
| Dirección directa | `https://<su-moodle>/local/reglasdeuda/index.php` |

Todo está en una sola pantalla con pestañas:

| Pestaña | Para qué |
|---|---|
| **Reglas** | Lista de reglas: activar/desactivar, editar, **Probar**, borrar |
| **Nueva regla** | Crear una regla |
| **Contrato de la API** | Qué envía Moodle a la API de cada regla y qué espera recibir. Para entregar al equipo que desarrolla la API |
| **Ajustes** | Interruptor general y permiso para APIs en la red interna (solo administradores) |

Permisos: `local/reglasdeuda:gestionar` (gestionar reglas; por defecto, Gestor)
y `local/reglasdeuda:exento` (nunca se le aplican reglas; por defecto, Gestor).

## 3. Configurar una regla

### General
- **Nombre** (p. ej. "Deuda"), **Activa**, **Orden** (las reglas se evalúan de menor a mayor).

### API: qué se envía
- **URL de la API** (`https://...`).
- **Método**:
  - `GET`: los parámetros van en la URL → `GET https://erp/api/deuda?cedula=0912345678`
  - `POST JSON`: cuerpo `{"cedula": "0912345678"}`
  - `POST formulario`: cuerpo `cedula=0912345678`
- **Parámetros enviados**: una fila por parámetro, con el **Nombre** que espera la API
  y el **Campo de Moodle** que se envía:

  | Nombre | ← Campo de Moodle |
  |---|---|
  | `cedula` | Número de ID |
  | `correo` | Correo |
  | `sistema` | Valor fijo: `MOODLE` |

  Campos disponibles: Número de ID, Nombre de usuario, Correo, Nombre, Apellido,
  Id interno de Moodle, **cualquier campo de perfil personalizado** y *Valor fijo*.
  Si a un estudiante le falta el dato (p. ej. no tiene número de ID), no se
  consulta la API y se aplica la política de error.
- **Autenticación**: ninguna, `Authorization: Bearer <token>` o una cabecera propia
  (p. ej. `X-API-Key`). **Tiempo máximo de espera** (5 s por defecto).

### Respuesta: cómo se interpreta
- **Campo de resultado** (por defecto `resultado`): el valor `true`/`false` que decide.
  Admite rutas con puntos (`respuesta.tiene_deuda`) y también 1/0 o "si"/"no".
- **La regla se activa cuando la API responde**: `true` o `false`. Así la API no
  tiene que adaptarse: si responde "tiene_deuda", elija true; si responde
  "al_dia", elija false.
- **Campo de datos** (por defecto `datos`) y **campo de mensaje** (por defecto `mensaje`).
- **Si la API falla**: *dejar pasar* (recomendado) o *aplicar la acción*.
- **Recordar la respuesta durante**: de 1 minuto a 1 día (15 min por defecto).
  Los errores se recuerdan 30 segundos.
- **Respuesta de ejemplo**: sus campos aparecen como variables en el mensaje (ver Probar).

### Acción
| Acción | Qué ve el estudiante |
|---|---|
| **Bloquear acceso a la plataforma** | Al entrar, una página con el mensaje y *Cerrar sesión*. No puede navegar |
| **Bloquear recursos** | Al abrir las actividades elegidas, el mensaje y *Volver al curso*. El resto del curso funciona |
| **Solo mostrar mensaje** | Entra a todo; ve el aviso en el *Área personal*, *Mis cursos* y al entrar a cada curso |

Para **Bloquear recursos** se eligen:
- **Actividades (IDs)**: separados por comas. El ID está en la dirección de la
  actividad: `.../mod/quiz/view.php?id=`**`42`**.
- y/o **Bloquear también por tipo, categoría o curso**: p. ej. todos los
  *Cuestionarios* de la categoría *Pregrado* (un filtro vacío = todos).

**Roles exentos**: en reglas nuevas vienen marcados *Profesor* y *Profesor sin
permiso de edición*. Los administradores siempre están exentos.

### Mensaje
- **Título**, **Mensaje** (editor con formato), **Texto del botón**, **Enlace del
  botón** (p. ej. `{datos.url_pago}`), **Enlace de respaldo** (si la API no manda
  enlace), **Abrir en pestaña nueva** y **Estilo** (información, advertencia, peligro).
- **Variables**: debajo de *Mensaje* hay botones con cada variable. Haga clic en
  un campo (título, mensaje, botón) y luego en la variable para insertarla:
  - Usuario: `{firstname}` `{lastname}` `{fullname}` `{username}` `{idnumber}` `{email}`
  - Curso: `{course_fullname}` `{course_shortname}` `{cmid}`
  - Regla: `{regla}`
  - API: `{datos.total}`, `{datos.url_pago}`... (salen de la *Respuesta de ejemplo*)
- Si la API envía `mensaje`, ese texto reemplaza al configurado.

Ejemplo:
> **Tienes valores pendientes**
> Hola {firstname}, registras un saldo pendiente de **${datos.total}**.
> Regulariza tu pago para continuar con normalidad. **[Pagar ahora]**

## 4. Probar una regla

En **Reglas › Probar** se escribe un usuario (nombre de usuario, correo o número
de ID) y el plugin consulta la API real, **sin bloquear ni avisar a nadie**. Muestra:

1. La **petición** enviada (método, URL, cabeceras con el token oculto, cuerpo).
2. La **respuesta** (código HTTP, tiempo y JSON).
3. La **decisión** (si la regla se activaría).
4. Las **variables recibidas** y una **vista previa del mensaje**.

El botón **Usar esta respuesta como ejemplo** guarda esa respuesta en la regla
para que sus campos aparezcan como variables al escribir el mensaje.

## 5. Contrato de la API

La pestaña **Contrato de la API** muestra, para cada regla, sus parámetros y un
ejemplo de la petición. La API debe responder con HTTP 200 y JSON:

```json
{
  "resultado": true,
  "mensaje": "",
  "datos": {"total": "235.50", "url_pago": "https://pagos.example.edu/?ref=0912345678"}
}
```

- `resultado` (obligatorio): true/false.
- `mensaje` (opcional): texto plano que reemplaza el mensaje configurado.
- `datos` (opcional): cualquier campo; en el mensaje se usa como `{datos.total}`.
  Los objetos anidados se leen con puntos: `{datos.cliente.nombre}`.
- Es error: HTTP distinto de 200, JSON inválido, tiempo agotado o falta `resultado`.

## 6. Cómo funciona

```
Estudiante abre una página
        │
        ▼
 ¿Está exento? (admin, Gestor, rol exento) ──sí──► sigue normal
        │ no
        ▼
 ¿Hay respuesta guardada y vigente? ──no──► consulta la API (con los parámetros de la regla)
        │                                         y guarda la respuesta (15 min por defecto)
        ▼
 ¿resultado == valor que activa la regla?
        │ sí
        ├── Bloquear plataforma → página de bloqueo con el mensaje
        ├── Bloquear recursos   → al abrir la actividad: mensaje + Volver al curso
        └── Solo mensaje        → aviso en Área personal, Mis cursos y cursos
```

- **"Ya pagué, volver a verificar"**: botón en el mensaje que borra la respuesta
  guardada y vuelve a consultar al instante.
- **App móvil y servicios web**: el bloqueo de plataforma y el de recursos
  también aplican y responden con el error *"Acceso restringido"*.
- **Nunca se bloquean** las páginas obligatorias de Moodle (aceptar políticas, MFA,
  cambio de contraseña, completar perfil), para no generar bucles.
- **Si la API se cae**, por defecto se deja pasar a todos (configurable por regla).

## 7. Ajustes (pestaña Ajustes)

- **Aplicar reglas**: interruptor general. Apagado, ninguna regla actúa, pero
  *Probar* sigue funcionando.
- **Permitir APIs en la red interna**: Moodle bloquea por defecto las peticiones
  a la red privada y a localhost (lista *cURL hosts bloqueados*). Actívelo
  solo si la API está en la red interna de la institución.

## 8. Problemas frecuentes

| Síntoma | Solución |
|---|---|
| `Class "local_reglasdeuda\...\" not found` tras copiar archivos nuevos | Subir de versión (`py build.py --bump`) y reinstalar el ZIP, o *Administración › Desarrollo › Purgar todas las cachés* |
| Probar dice que la URL está bloqueada | Activar *Permitir APIs en la red interna* (pestaña Ajustes) |
| Probar funciona pero al estudiante no le aparece nada | Revisar: regla **Activa**, *Aplicar reglas* encendido, que el estudiante no tenga un rol exento, y esperar a que venza la respuesta guardada (o usar "Ya pagué, volver a verificar") |
| "El usuario no tiene ..." | Al estudiante le falta el campo que se envía (p. ej. número de ID) |
| Las actividades por ID dejaron de bloquearse tras duplicar/restaurar un curso | Las copias tienen IDs nuevos: agréguelos o use el filtro por curso/categoría |

## 9. Desarrollo

```
local_reglasdeuda/
├── index.php, editar.php, probar.php, contrato.php, ajustes.php   pantallas (pestañas)
├── bloqueado.php, verificar.php                                    lo que ve el estudiante
├── lib.php                                       callback after_require_login (4.1–5.1)
├── classes/local/aplicador.php                   decide y ejecuta la acción en cada página
├── classes/local/evaluador.php                   consulta + caché + exenciones
├── classes/local/parametros.php, cliente_api.php qué se envía y cómo (GET/POST)
├── classes/local/mensaje.php, plantilla.php      mensaje y variables (valores escapados)
├── db/  install.xml, upgrade.php, access.php, caches.php, events.php
└── amd/src/variables.js                          botones de variables del mensaje
```

- `py build.py --bump` sube `$plugin->version`; `--release 0.3.0` cambia la versión visible.
- Pruebas: `vendor/bin/phpunit local/reglasdeuda/tests/plantilla_test.php`.
- El enlace del botón no codifica variables: si la URL lleva parámetros, conviene
  que la API devuelva la URL completa (`{datos.url_pago}`).
