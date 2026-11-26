<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Cadenas en espanol.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Reglas de deuda (API)';
$string['reglasdeuda:gestionar'] = 'Gestionar reglas de deuda';
$string['reglasdeuda:exento'] = 'Exento de las reglas de deuda';

$string['accesorestringido'] = 'Acceso restringido';
$string['accion'] = 'Accion';
$string['accion_avisar'] = 'Solo mostrar mensaje (Area personal y cursos)';
$string['accion_bloquear'] = 'Bloquear acceso a la plataforma';
$string['accion_help'] = 'Que ocurre cuando la regla se activa:

* **Bloquear plataforma**: al iniciar sesion el usuario ve el mensaje y no puede navegar.
* **Bloquear recursos**: al intentar entrar a las actividades elegidas (por ID, o todas las de ciertos tipos, categorias y cursos) ve el mensaje y un boton para volver al curso.
* **Solo mensaje**: puede entrar a todo; ve el aviso en el Area personal y al entrar a cada curso.';
$string['accion_restringir'] = 'Bloquear recursos';
$string['activa'] = 'Activa';
$string['activar'] = 'Activar';
$string['ajustesgenerales'] = 'Ajustes generales';
$string['api'] = 'API';
$string['autenticacion'] = 'Autenticacion';
$string['autenticacion_bearer'] = 'Authorization: Bearer <token>';
$string['autenticacion_cabecera'] = 'Cabecera propia (p.ej. X-API-Key)';
$string['autenticacion_ninguna'] = 'Ninguna';
$string['botontexto'] = 'Texto del boton';
$string['botonurl'] = 'Enlace del boton';
$string['botonurl_help'] = 'Admite variables, por ejemplo {datos.url_pago}. Si queda vacio o no es una URL http(s) valida, se usa el enlace de respaldo; si tampoco hay, no se muestra el boton.';
$string['botonurlrespaldo'] = 'Enlace de respaldo';
$string['cabeceranombre'] = 'Nombre de la cabecera';
$string['cachettl'] = 'Volver a consultar cada';
$string['cachettl_help'] = 'Durante este tiempo se reutiliza la ultima respuesta de cada estudiante. Si paga, se desbloquea al pasar este tiempo o al pulsar "Ya pague, volver a verificar". Entre 1 minuto y 1 dia.';
$string['confirmarborrar'] = 'Borrar la regla "{$a}"? Las actividades que la usen como condicion quedaran disponibles.';
$string['contrato'] = 'Contrato de la API';
$string['contrato_errores'] = 'Errores';
$string['contrato_errores_desc'] = '<p>Se considera error: respuesta distinta de HTTP 200, JSON invalido, tiempo de espera agotado o ausencia del campo de resultado. Ante un error se aplica la politica de la regla (por defecto: dejar pasar al usuario).</p>';
$string['contrato_intro'] = 'Lo que Moodle envia a la API de cada regla y lo que espera recibir. Entregue esta pagina al equipo que desarrolla la API.';
$string['contrato_llamada'] = 'Llamada';
$string['contrato_llamada_desc'] = '<ul><li>Metodo y parametros segun cada regla (abajo).</li><li>Autenticacion segun la regla: <code>Authorization: Bearer &lt;token&gt;</code>, una cabecera propia (p.ej. <code>X-API-Key: &lt;token&gt;</code>) o ninguna.</li><li>Tiempo maximo de espera configurable (5 s por defecto). No se siguen redirecciones.</li></ul>';
$string['contrato_peticion'] = 'Peticion de cada regla';
$string['contrato_peticion_desc'] = '<p>Ejemplo con un usuario ficticio. Los valores reales salen del perfil de cada usuario.</p>';
$string['contrato_respuesta'] = 'Respuesta esperada';
$string['contrato_respuesta_desc'] = '<ul><li><code>resultado</code> (obligatorio): <code>true</code>/<code>false</code> (tambien se aceptan 1/0, "si"/"no"). La regla decide si actua con true o con false.</li><li><code>mensaje</code> (opcional): texto plano que reemplaza el mensaje configurado.</li><li><code>datos</code> (opcional): cualquier campo, se usa en el mensaje como <code>{datos.total}</code>, <code>{datos.url_pago}</code>, etc. Los objetos anidados se leen con puntos: <code>{datos.cliente.nombre}</code>.</li></ul>';
$string['decision'] = 'Decision';
$string['decision_aplica'] = 'La regla se activa: {$a}';
$string['decision_detalle'] = 'La API respondio {$a->resultado}; la regla actua cuando responde {$a->valoractiva}.';
$string['decision_noaplica'] = 'La regla no se activa: el usuario sigue con normalidad.';
$string['desactivar'] = 'Desactivar';
$string['editarregla'] = 'Editar regla';
$string['ejemploguardado'] = 'Respuesta guardada como ejemplo: sus variables ya aparecen en el selector.';
$string['error_cabecera'] = 'Nombre de cabecera invalido (solo letras, numeros y guiones).';
$string['error_cachettl'] = 'Entre 1 minuto y 1 dia.';
$string['error_http'] = 'La API respondio HTTP {$a}.';
$string['error_json'] = 'La respuesta no es JSON valido.';
$string['error_resultado'] = 'La respuesta no tiene un valor true/false en "{$a}".';
$string['error_ruta'] = 'Ruta invalida: use nombres separados por puntos, p.ej. datos o respuesta.resultado.';
$string['error_timeout'] = 'Entre 1 y 30 segundos.';
$string['error_url'] = 'Ingrese una URL http(s) valida.';
$string['errorpolitica'] = 'Si la API no responde';
$string['errorpolitica_aplicar'] = 'Aplicar la accion igual';
$string['errorpolitica_help'] = 'Que hacer si la API esta caida o responde algo inesperado. Recomendado: dejar pasar, asi una falla del sistema de cobros no bloquea a todos.';
$string['errorpolitica_permitir'] = 'Dejar pasar al estudiante (recomendado)';
$string['estado'] = 'Estado';
$string['estilo'] = 'Estilo';
$string['estilo_danger'] = 'Peligro (rojo)';
$string['estilo_info'] = 'Informacion (azul)';
$string['estilo_warning'] = 'Advertencia (amarillo)';
$string['global'] = 'global';
$string['globalactivo'] = 'Bloquear tambien por tipo, categoria o curso';
$string['globalactivo_help'] = 'Ademas de las actividades por ID, bloquea todas las que cumplan los filtros de tipo de actividad, categoria y curso (un filtro vacio = todos). La actividad se sigue viendo en el curso; el usuario ve el mensaje al intentar entrar.';
$string['globalcategorias'] = 'Categorias (incluye subcategorias)';
$string['globalcategorias_help'] = 'Vacio = todas las categorias.';
$string['globalmodulos'] = 'Tipos de actividad';
$string['habilitado'] = 'Aplicar reglas';
$string['habilitado_desc'] = 'Interruptor general. Desactivado, ninguna regla bloquea ni avisa (la pantalla Probar sigue funcionando).';
$string['hostsinternos'] = 'Permitir APIs en la red interna';
$string['hostsinternos_desc'] = 'Por defecto Moodle bloquea peticiones a hosts y puertos de la lista "cURL hosts bloqueados" (red interna, localhost). Active esto solo si la API de la institucion esta en la red privada.';
$string['inactiva'] = 'Inactiva';
$string['mensaje'] = 'Mensaje';
$string['nombre'] = 'Nombre';
$string['nuevapestana'] = 'Abrir el enlace en una pestana nueva';
$string['nuevaregla'] = 'Nueva regla';
$string['orden'] = 'Orden';
$string['orden_help'] = 'Las reglas se evaluan de menor a mayor. Para bloquear la plataforma basta la primera regla que se active.';
$string['peticion'] = 'Peticion enviada';
$string['plugindeshabilitado'] = 'Las reglas estan desactivadas (pestana Ajustes): no se aplicara ninguna.';
$string['privacy:metadata:apiexterna'] = 'Para decidir si se aplica una regla, se envian datos del usuario a la API configurada por el administrador.';
$string['privacy:metadata:apiexterna:course'] = 'Nombre corto del curso y actividad donde ocurre la consulta.';
$string['privacy:metadata:apiexterna:email'] = 'Correo del usuario.';
$string['privacy:metadata:apiexterna:idnumber'] = 'Numero de ID del usuario.';
$string['privacy:metadata:apiexterna:username'] = 'Nombre de usuario.';
$string['probar'] = 'Probar';
$string['probar_aviso'] = 'La prueba consulta la API real con el usuario indicado (sin usar la cache) y muestra lo que veria. No bloquea ni avisa a nadie.';
$string['probar_error'] = 'Error: {$a}. Se aplicaria la politica de error de la regla.';
$string['probar_exento'] = 'La regla no aplicaria a este usuario: es administrador, tiene la capacidad de exento o no tiene ninguno de los roles a los que aplica la regla.';
$string['probar_nousuario'] = 'No se encontro un usuario con ese nombre de usuario, correo o numero de ID.';
$string['probar_usuario'] = 'Usuario';
$string['probar_usuario_ph'] = 'usuario, correo o numero de ID';
$string['probarregla'] = 'Probar regla: {$a}';
$string['reglaborrada'] = 'Regla borrada.';
$string['reglaguardada'] = 'Regla guardada. Pruebela con un usuario.';
$string['reglas'] = 'Reglas';
$string['respuesta'] = 'Respuesta';
$string['respuestaejemplo'] = 'Respuesta de ejemplo';
$string['respuestaejemplo_help'] = 'Una respuesta tipica de la API. Sus campos de datos aparecen como variables en el selector del mensaje. Se completa sola desde la pantalla Probar con "Usar esta respuesta como ejemplo".';
$string['resultadoprueba'] = 'Resultado para {$a}';
$string['rutadatos'] = 'Campo con los datos para el mensaje';
$string['rutamensaje'] = 'Campo con un mensaje propio de la API';
$string['rutaresultado'] = 'Campo que dice si tiene deuda';
$string['rutaresultado_help'] = 'Nombre del campo de la respuesta que trae true o false. Si la API responde {"resultado": true}, escriba: resultado. Si responde {"tiene_deuda": true}, escriba: tiene_deuda.';
$string['seactivacuando'] = 'Se activa con';
$string['sinreglas'] = 'Aun no hay reglas. Cree la primera con "Nueva regla".';
$string['timeout'] = 'Tiempo maximo de espera (s)';
$string['token'] = 'Token o clave';
$string['titulo'] = 'Titulo';
$string['todas'] = 'Todas';
$string['todos'] = 'Todos';
$string['url'] = 'URL de la API';
$string['usarejemplo'] = 'Usar esta respuesta como ejemplo';
$string['valoractiva'] = 'Actuar cuando ese campo sea';
$string['valoractiva_false'] = 'false';
$string['valoractiva_help'] = 'Casi siempre: true. Elija false solo si la API responde al reves, por ejemplo {"al_dia": false} cuando el estudiante debe.';
$string['valoractiva_true'] = 'true (si tiene deuda)';
$string['var_cmid'] = 'Id de la actividad (si la consulta ocurre en una actividad)';
$string['var_course_fullname'] = 'Nombre del curso';
$string['var_course_shortname'] = 'Nombre corto del curso';
$string['var_email'] = 'Correo';
$string['var_firstname'] = 'Nombre';
$string['var_fullname'] = 'Nombre completo';
$string['var_idnumber'] = 'Numero de ID';
$string['var_lastname'] = 'Apellido';
$string['var_regla'] = 'Nombre de la regla';
$string['var_username'] = 'Nombre de usuario';
$string['variables'] = 'Variables';
$string['variables_api'] = 'Respuesta de la API';
$string['variables_ayuda'] = 'Haga clic en un campo (titulo, mensaje, boton) y luego en una variable para insertarla.';
$string['variables_copiado'] = 'Variable copiada al portapapeles:';
$string['variables_curso'] = 'Curso';
$string['variables_regla'] = 'Regla';
$string['variables_sinapi'] = 'Pegue una respuesta de ejemplo (seccion Respuesta) o use "Usar esta respuesta como ejemplo" en Probar para ver aqui las variables de la API.';
$string['variables_usuario'] = 'Usuario';
$string['variablesrecibidas'] = 'Variables recibidas';
$string['verificar'] = 'Ya pague, volver a verificar';
$string['vistaprevia'] = 'Asi lo veria el usuario';
$string['volvercurso'] = 'Volver al curso';
$string['cachedef_reglas'] = 'Reglas de deuda activas';
$string['cachedef_resultados'] = 'Resultados de la API por regla y usuario';
$string['error_token'] = 'El token no puede contener saltos de linea.';
$string['privacy:metadata:apiexterna:fullname'] = 'Nombre y apellido del usuario.';
$string['privacy:metadata:apiexterna:userid'] = 'Id del usuario en Moodle.';
$string['actividades'] = 'Actividades (IDs)';
$string['actividades_help'] = 'IDs de las actividades a bloquear, separados por comas. El ID aparece en la direccion de la actividad: .../mod/quiz/view.php?id=42.';
$string['error_actividades'] = 'Solo numeros separados por comas.';
$string['error_actividadesnoexisten'] = 'Alguno de los IDs no corresponde a una actividad existente.';
$string['error_sinalcance'] = 'Indique al menos una actividad o marque el bloqueo por tipo, categoria o curso.';
$string['globalcursos'] = 'Cursos';
$string['globalcursos_help'] = 'Vacio = todos los cursos.';
$string['nactividades'] = '{$a} actividad(es)';
$string['agregarparametro'] = 'Agregar parametro';
$string['campo_fijo'] = 'Valor fijo';
$string['campo_id'] = 'Id interno de Moodle';
$string['campo_perfil'] = 'Perfil: {$a}';
$string['error_param_nombre'] = 'Nombre invalido: letras, numeros, _ . - (sin espacios).';
$string['error_param_repetido'] = 'Nombre repetido.';
$string['error_sinparametros'] = 'Agregue al menos un parametro.';
$string['error_sinvalor'] = 'El usuario no tiene "{$a}"; no se puede consultar la API.';
$string['metodo'] = 'Metodo';
$string['metodo_get'] = 'GET (parametros en la URL)';
$string['metodo_post_form'] = 'POST formulario (application/x-www-form-urlencoded)';
$string['metodo_post_json'] = 'POST JSON (application/json)';
$string['param_campo'] = 'Campo de Moodle';
$string['param_nombre'] = 'Nombre';
$string['param_valor'] = 'Valor';
$string['param_valor_ph'] = 'fijo, o por defecto';
$string['parametro'] = 'Parametro {no}';
$string['parametro_help'] = 'Nombre con el que la API recibe el dato y que se envia:<ul><li><b>Solo campo de Moodle</b>: el dato del usuario (p.ej. cedula <- Numero de ID).</li><li><b>Solo valor</b> (campo en "Elegir..."): se envia ese valor siempre (p.ej. deuda_maxima = 50).</li><li><b>Campo y valor</b>: el dato del usuario; si no lo tiene, el valor por defecto.</li></ul>Con GET viaja en la URL (?cedula=...); con POST, en el cuerpo.';
$string['parametros'] = 'Parametros enviados';
$string['contrato_sinreglas'] = 'Aun no hay reglas: cree una para ver aqui su peticion.';
$string['privacy:metadata:apiexterna:profile'] = 'Campos de perfil personalizados que el administrador elija enviar.';
$string['error_param_sinvalor'] = 'Elija un campo de Moodle o escriba un valor.';
$string['pordefecto'] = '(por defecto: {$a})';
$string['rolesaplica'] = 'Roles a los que aplica';
$string['rolesaplica_help'] = 'La regla solo afecta a usuarios con alguno de estos roles en cualquier curso o contexto (p.ej. Estudiante). Vacio = aplica a todos. Los administradores y quien tenga la capacidad de exento nunca quedan afectados.';
$string['paso_api'] = '1. ¿A que API se consulta y que se le envia?';
$string['paso_respuesta'] = '2. ¿Como se lee la respuesta?';
$string['paso_accion'] = '3. ¿Que se hace con el estudiante?';
$string['paso_mensaje'] = '4. ¿Que mensaje ve el estudiante?';
$string['respuesta_esperada'] = 'La API debe responder algo asi';
$string['rutadatos_help'] = 'Campo de la respuesta con los valores para el mensaje (total, enlace de pago...). Por defecto: datos. Se usan como {datos.total}.';
$string['rutamensaje_help'] = 'Si la API envia este campo con texto, se muestra ese texto en lugar del mensaje configurado. Por defecto: mensaje.';
