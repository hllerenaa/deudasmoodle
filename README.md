# deudasmoodle — Reglas de deuda por API para Moodle

Moodle consulta una API institucional (p. ej. "¿tiene deuda?") y, según la
respuesta, **bloquea la plataforma**, **bloquea recursos** o **solo avisa**, con
un mensaje personalizable (total adeudado, enlace de pago, nombre del
estudiante...).

| Plugin | Carpeta en Moodle | Para qué |
|---|---|---|
| `local_reglasdeuda` | `local/reglasdeuda` | Reglas, administración, bloqueo de plataforma, bloqueo global, avisos |
| `availability_reglasdeuda` | `availability/condition/reglasdeuda` | Condición "Regla de deuda" en *Restringir acceso* de cada actividad |

Compatible con **Moodle 4.1 a 5.1**. En 5.1 las carpetas van bajo `public/`
(`public/local/reglasdeuda`, `public/availability/condition/reglasdeuda`).

> El enlace del botón admite variables, pero no las codifica: si la URL lleva
> parámetros, conviene que la API devuelva la URL completa (`{datos.url_pago}`).

## Instalación

```bash
py build.py            # genera dist/local_reglasdeuda_<release>.zip y dist/availability_reglasdeuda_<release>.zip
py build.py --bump     # sube $plugin->version en ambos (y la dependencia)
```

Instalar en *Administración del sitio > Extensiones > Instalar extensiones*,
**primero** `local_reglasdeuda` y luego `availability_reglasdeuda`.

## Uso

*Administración del sitio > Extensiones > Extensiones locales > Reglas de deuda (API)*

1. **Reglas > Nueva regla**: URL de la API, token, dato que identifica al usuario
   (`idnumber`, `username` o `email`), cuerpo JSON, con qué valor se activa
   (`true`/`false`) y la acción.
2. **Probar**: consulta la API real con un usuario, muestra petición, respuesta,
   decisión y el mensaje tal como lo vería. "Usar esta respuesta como ejemplo"
   guarda la respuesta para que sus variables aparezcan en el selector.
3. **Mensaje**: título, cuerpo (editor), botón con enlace y estilo. Bajo
   *Variables* hay botones con cada variable: clic en el campo y luego en la
   variable para insertarla.
4. **Contrato de la API**: página con la estructura que debe cumplir la API,
   para entregar al equipo que la desarrolla.

### Acciones

| Acción | Efecto |
|---|---|
| Bloquear acceso a la plataforma | Tras iniciar sesión, página con el mensaje y botón *Cerrar sesión* |
| Bloquear recursos | Actividades con la condición *Regla de deuda*; opcionalmente bloqueo global por tipo de actividad y categoría |
| Solo mostrar mensaje | Aviso en el Área personal y al entrar a cada curso |

Nunca se aplica a administradores, a quien tenga `local/reglasdeuda:exento`
(por defecto Gestor) ni a los *roles exentos* de cada regla (en reglas nuevas
vienen marcados Profesor y Profesor sin permiso de edición). El bloqueo global
tampoco afecta a quien tenga `moodle/course:ignoreavailabilityrestrictions`.

El bloqueo de plataforma y el global también cortan la app móvil, los servicios
web y AJAX (con un error "Acceso restringido"), porque ahí no se puede redirigir.
Nunca se bloquean los flujos obligatorios de Moodle (políticas del sitio, MFA,
cambio de contraseña, completar perfil) para no generar bucles de redirección.

## Contrato por defecto

Petición `POST` JSON (plantilla editable por regla):

```json
{
  "regla": "Deuda",
  "identificador": "0912345678",
  "tipo_identificador": "idnumber",
  "usuario": {"username": "jperez", "email": "jperez@uni.edu.ec", "idnumber": "0912345678"},
  "contexto": {"curso": "MAT101", "actividad": "42"}
}
```

Respuesta esperada (HTTP 200):

```json
{
  "resultado": true,
  "mensaje": "",
  "datos": {"total": "235.50", "url_pago": "https://pagos.uni.edu.ec/?ref=0912345678"}
}
```

- `resultado`: true/false (también 1/0, "si"/"no"). Ruta configurable.
- `mensaje` (opcional): texto plano que reemplaza el mensaje configurado.
- `datos`: cualquier campo; en el mensaje se usa como `{datos.total}`.
- Error (HTTP ≠ 200, JSON inválido, timeout, sin resultado): por defecto se deja
  pasar al usuario (configurable por regla). Los errores se cachean 1 minuto.

## Detalles técnicos

- Cache MUC `local_reglasdeuda/resultados` por (regla, usuario), TTL por regla
  (15 min por defecto, entre 1 min y 1 día) y memo por petición. Editar una
  regla solo invalida sus resultados. Botón *Ya pagué, volver a verificar* la
  limpia; al borrar un usuario se borran sus resultados.
- La condición de disponibilidad solo consulta la API para el usuario actual en
  una petición web; cron e informes usan la cache (sin dato, no restringe).
- Tras restaurar un curso en **otro** sitio, la condición apunta a un id de
  regla de este sitio: revise las restricciones de las actividades restauradas.
- Acciones en el callback `local_reglasdeuda_after_require_login` (vigente de 4.1
  a 5.1). Avisos con `\core\notification`.
- Las peticiones respetan la lista de hosts bloqueados de cURL de Moodle; para
  APIs en la red interna, activar *Permitir APIs en la red interna*.
- Variables: `{firstname}`, `{lastname}`, `{fullname}`, `{username}`,
  `{idnumber}`, `{email}`, `{identificador}`, `{course_fullname}`,
  `{course_shortname}`, `{cmid}`, `{regla}`, `{datos.*}`. Los valores se escapan
  al insertarse en el HTML; los enlaces solo aceptan `http(s)`.
- Pruebas: `vendor/bin/phpunit local/reglasdeuda/tests/plantilla_test.php`.
