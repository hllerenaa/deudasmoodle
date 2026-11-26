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
* **Bloquear recursos**: las actividades con la condicion "Regla de deuda" (Restringir acceso) quedan cerradas; opcionalmente tambien todas las de ciertos tipos y categorias (bloqueo global).
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
$string['cachettl'] = 'Recordar la respuesta durante';
$string['cachettl_help'] = 'Tiempo durante el que se reutiliza la respuesta de la API para cada usuario. Cuando el estudiante paga, recupera el acceso al vencer este tiempo o al pulsar "Ya pague, volver a verificar". Entre 1 minuto y 1 dia; los errores se recuerdan 30 segundos.';
$string['confirmarborrar'] = 'Borrar la regla "{$a}"? Las actividades que la usen como condicion quedaran disponibles.';
$string['contrato'] = 'Contrato de la API';
$string['contrato_errores'] = 'Errores';
$string['contrato_errores_desc'] = '<p>Se considera error: respuesta distinta de HTTP 200, JSON invalido, tiempo de espera agotado o ausencia del campo de resultado. Ante un error se aplica la politica de la regla (por defecto: dejar pasar al usuario).</p>';
$string['contrato_intro'] = 'Estructura que debe cumplir la API consultada por cada regla. Las rutas de los campos (resultado, datos, mensaje) y el cuerpo enviado se pueden ajustar en cada regla; esto es lo que el plugin usa por defecto.';
$string['contrato_llamada'] = 'Llamada';
$string['contrato_llamada_desc'] = '<ul><li><code>POST</code> a la URL de la regla, con <code>Content-Type: application/json</code>.</li><li>Autenticacion segun la regla: <code>Authorization: Bearer &lt;token&gt;</code>, una cabecera propia (p.ej. <code>X-API-Key: &lt;token&gt;</code>) o ninguna.</li><li>Tiempo maximo de espera configurable (5 s por defecto). No se siguen redirecciones.</li></ul>';
$string['contrato_peticion'] = 'Cuerpo que envia Moodle';
$string['contrato_peticion_desc'] = '<p><code>identificador</code> lleva el username, idnumber o correo, segun la regla; <code>tipo_identificador</code> indica cual. <code>contexto</code> va vacio cuando la consulta no ocurre dentro de un curso.</p>';
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
$string['error_plantilla'] = 'La plantilla del cuerpo no es JSON valido.';
$string['error_resultado'] = 'La respuesta no tiene un valor true/false en "{$a}".';
$string['error_ruta'] = 'Ruta invalida: use nombres separados por puntos, p.ej. datos o respuesta.resultado.';
$string['error_sinidentificador'] = 'El usuario no tiene {$a}; no se puede consultar la API.';
$string['error_timeout'] = 'Entre 1 y 30 segundos.';
$string['error_url'] = 'Ingrese una URL http(s) valida.';
$string['errorpolitica'] = 'Si la API falla';
$string['errorpolitica_aplicar'] = 'Aplicar la accion (bloquear/avisar)';
$string['errorpolitica_help'] = 'Que hacer si la API no responde, responde con error o con un formato inesperado. Se recomienda dejar pasar: asi una caida del sistema de cobros no deja a todos sin acceso.';
$string['errorpolitica_permitir'] = 'Dejar pasar al usuario (recomendado)';
$string['estado'] = 'Estado';
$string['estilo'] = 'Estilo';
$string['estilo_danger'] = 'Peligro (rojo)';
$string['estilo_info'] = 'Informacion (azul)';
$string['estilo_warning'] = 'Advertencia (amarillo)';
$string['global'] = 'global';
$string['globalactivo'] = 'Bloqueo global';
$string['globalactivo_help'] = 'Ademas de las actividades donde el docente agregue la condicion, bloquea automaticamente las actividades de los tipos y categorias elegidos, sin configurarlas una por una. Este bloqueo no aparece en "Restringir acceso" de la actividad: el usuario ve el mensaje al intentar entrar.';
$string['globalcategorias'] = 'Categorias (incluye subcategorias)';
$string['globalcategorias_help'] = 'Vacio = todas las categorias.';
$string['globalmodulos'] = 'Tipos de actividad';
$string['habilitado'] = 'Aplicar reglas';
$string['habilitado_desc'] = 'Interruptor general. Desactivado, ninguna regla bloquea ni avisa (la pantalla Probar sigue funcionando).';
$string['hostsinternos'] = 'Permitir APIs en la red interna';
$string['hostsinternos_desc'] = 'Por defecto Moodle bloquea peticiones a hosts y puertos de la lista "cURL hosts bloqueados" (red interna, localhost). Active esto solo si la API de la institucion esta en la red privada.';
$string['identificador'] = 'Identificar al usuario por';
$string['identificador_help'] = 'Dato del usuario que se envia en {identificador}. Si el usuario no lo tiene (p.ej. sin numero de ID), se trata como error de la API.';
$string['inactiva'] = 'Inactiva';
$string['mensaje'] = 'Mensaje';
$string['ninguno'] = 'Ninguno';
$string['nombre'] = 'Nombre';
$string['nuevapestana'] = 'Abrir el enlace en una pestana nueva';
$string['nuevaregla'] = 'Nueva regla';
$string['orden'] = 'Orden';
$string['orden_help'] = 'Las reglas se evaluan de menor a mayor. Para bloquear la plataforma basta la primera regla que se active.';
$string['peticion'] = 'Peticion enviada';
$string['plantilla'] = 'Cuerpo JSON enviado';
$string['plantilla_help'] = 'JSON que se envia a la API. Las variables {username}, {idnumber}, {email}, {identificador}, {course_shortname}, {cmid}... se reemplazan dentro de los textos. Debe ser JSON valido.';
$string['plugindeshabilitado'] = 'Las reglas estan desactivadas en los ajustes generales: no se aplicara ninguna.';
$string['privacy:metadata:apiexterna'] = 'Para decidir si se aplica una regla, se envian datos del usuario a la API configurada por el administrador.';
$string['privacy:metadata:apiexterna:course'] = 'Nombre corto del curso y actividad donde ocurre la consulta.';
$string['privacy:metadata:apiexterna:email'] = 'Correo del usuario.';
$string['privacy:metadata:apiexterna:idnumber'] = 'Numero de ID del usuario.';
$string['privacy:metadata:apiexterna:username'] = 'Nombre de usuario.';
$string['probar'] = 'Probar';
$string['probar_aviso'] = 'La prueba consulta la API real con el usuario indicado (sin usar la cache) y muestra lo que veria. No bloquea ni avisa a nadie.';
$string['probar_error'] = 'Error: {$a}. Se aplicaria la politica de error de la regla.';
$string['probar_exento'] = 'Este usuario esta exento (administrador, capacidad de exento o rol exento): en la realidad la regla no se le aplicaria.';
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
$string['rolesexentos'] = 'Roles exentos';
$string['rolesexentos_help'] = 'Los usuarios con alguno de estos roles en cualquier contexto (p.ej. Profesor) nunca quedan afectados por la regla. Los administradores siempre estan exentos.';
$string['rutadatos'] = 'Campo de datos';
$string['rutamensaje'] = 'Campo de mensaje';
$string['rutaresultado'] = 'Campo de resultado';
$string['rutaresultado_help'] = 'Ruta en la respuesta JSON del valor true/false. Use puntos para objetos anidados, p.ej. respuesta.tiene_deuda.';
$string['seactivacuando'] = 'Se activa con';
$string['sinreglas'] = 'Aun no hay reglas. Cree la primera con "Nueva regla".';
$string['timeout'] = 'Tiempo maximo de espera (s)';
$string['token'] = 'Token o clave';
$string['titulo'] = 'Titulo';
$string['todas'] = 'Todas';
$string['todos'] = 'Todos';
$string['url'] = 'URL de la API';
$string['usarejemplo'] = 'Usar esta respuesta como ejemplo';
$string['valoractiva'] = 'La regla se activa cuando la API responde';
$string['valoractiva_false'] = 'false (p.ej. "matricula al dia = false")';
$string['valoractiva_help'] = 'Valor del campo de resultado que dispara la accion. Asi la API no tiene que adaptarse al plugin: si responde "tiene_deuda", elija true; si responde "al_dia", elija false.';
$string['valoractiva_true'] = 'true (p.ej. "tiene deuda = true")';
$string['var_cmid'] = 'Id de la actividad (si la consulta ocurre en una actividad)';
$string['var_course_fullname'] = 'Nombre del curso';
$string['var_course_shortname'] = 'Nombre corto del curso';
$string['var_email'] = 'Correo';
$string['var_firstname'] = 'Nombre';
$string['var_fullname'] = 'Nombre completo';
$string['var_identificador'] = 'Valor enviado como identificador';
$string['var_idnumber'] = 'Numero de ID';
$string['var_lastname'] = 'Apellido';
$string['var_regla'] = 'Nombre de la regla';
$string['var_tipo_identificador'] = 'username, idnumber o email';
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
