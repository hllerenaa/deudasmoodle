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
 * English strings.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Debt rules (API)';
$string['reglasdeuda:gestionar'] = 'Manage debt rules';
$string['reglasdeuda:exento'] = 'Exempt from debt rules';

$string['accesorestringido'] = 'Access restricted';
$string['accion'] = 'Action';
$string['accion_avisar'] = 'Only show a message (Dashboard and courses)';
$string['accion_bloquear'] = 'Block access to the site';
$string['accion_help'] = 'What happens when the rule triggers:

* **Block site**: after logging in the user sees the message and cannot navigate.
* **Block resources**: when trying to enter the selected activities (by ID, or every activity of selected types, categories and courses) the user sees the message and a button back to the course.
* **Only message**: full access; the notice is shown on the Dashboard and when entering each course.';
$string['accion_restringir'] = 'Block resources';
$string['activa'] = 'Enabled';
$string['activar'] = 'Enable';
$string['ajustesgenerales'] = 'General settings';
$string['api'] = 'API';
$string['autenticacion'] = 'Authentication';
$string['autenticacion_bearer'] = 'Authorization: Bearer <token>';
$string['autenticacion_cabecera'] = 'Custom header (e.g. X-API-Key)';
$string['autenticacion_ninguna'] = 'None';
$string['botontexto'] = 'Button text';
$string['botonurl'] = 'Button link';
$string['botonurl_help'] = 'Accepts variables, e.g. {datos.url_pago}. If empty or not a valid http(s) URL, the fallback link is used; if there is none, no button is shown.';
$string['botonurlrespaldo'] = 'Fallback link';
$string['cabeceranombre'] = 'Header name';
$string['cachettl'] = 'Remember the response for';
$string['cachettl_help'] = 'How long the API response is reused for each user. After paying, the student regains access when this time expires or by clicking "I have paid, check again". Between 1 minute and 1 day; errors are remembered for 30 seconds.';
$string['confirmarborrar'] = 'Delete the rule "{$a}"? Activities using it as a condition will become available.';
$string['contrato'] = 'API contract';
$string['contrato_errores'] = 'Errors';
$string['contrato_errores_desc'] = '<p>An error is: a response other than HTTP 200, invalid JSON, a timeout or a missing result field. On error the rule\'s policy applies (default: let the user through).</p>';
$string['contrato_intro'] = 'Structure the API queried by each rule must follow. Field paths (result, data, message) and the request body can be adjusted per rule; this is what the plugin uses by default.';
$string['contrato_llamada'] = 'Call';
$string['contrato_llamada_desc'] = '<ul><li><code>POST</code> to the rule URL, with <code>Content-Type: application/json</code>.</li><li>Authentication per rule: <code>Authorization: Bearer &lt;token&gt;</code>, a custom header (e.g. <code>X-API-Key: &lt;token&gt;</code>) or none.</li><li>Configurable timeout (5 s by default). Redirects are not followed.</li></ul>';
$string['contrato_peticion'] = 'Body sent by Moodle';
$string['contrato_peticion_desc'] = '<p><code>identificador</code> carries the username, idnumber or email, depending on the rule; <code>tipo_identificador</code> says which. <code>contexto</code> is empty when the check does not happen inside a course.</p>';
$string['contrato_respuesta'] = 'Expected response';
$string['contrato_respuesta_desc'] = '<ul><li><code>resultado</code> (required): <code>true</code>/<code>false</code> (1/0 and "si"/"no" are also accepted). The rule decides whether it acts on true or on false.</li><li><code>mensaje</code> (optional): plain text that replaces the configured message.</li><li><code>datos</code> (optional): any field, used in the message as <code>{datos.total}</code>, <code>{datos.url_pago}</code>, etc. Nested objects use dots: <code>{datos.cliente.nombre}</code>.</li></ul>';
$string['decision'] = 'Decision';
$string['decision_aplica'] = 'The rule triggers: {$a}';
$string['decision_detalle'] = 'The API returned {$a->resultado}; the rule acts when it returns {$a->valoractiva}.';
$string['decision_noaplica'] = 'The rule does not trigger: the user continues normally.';
$string['desactivar'] = 'Disable';
$string['editarregla'] = 'Edit rule';
$string['ejemploguardado'] = 'Response saved as example: its variables now appear in the picker.';
$string['error_cabecera'] = 'Invalid header name (letters, digits and hyphens only).';
$string['error_cachettl'] = 'Between 1 minute and 1 day.';
$string['error_http'] = 'The API returned HTTP {$a}.';
$string['error_json'] = 'The response is not valid JSON.';
$string['error_plantilla'] = 'The body template is not valid JSON.';
$string['error_resultado'] = 'The response has no true/false value at "{$a}".';
$string['error_ruta'] = 'Invalid path: use names separated by dots, e.g. datos or response.result.';
$string['error_sinidentificador'] = 'The user has no {$a}; the API cannot be queried.';
$string['error_timeout'] = 'Between 1 and 30 seconds.';
$string['error_url'] = 'Enter a valid http(s) URL.';
$string['errorpolitica'] = 'If the API fails';
$string['errorpolitica_aplicar'] = 'Apply the action (block/notify)';
$string['errorpolitica_help'] = 'What to do if the API does not respond, returns an error or an unexpected format. Letting users through is recommended, so an outage of the billing system does not lock everyone out.';
$string['errorpolitica_permitir'] = 'Let the user through (recommended)';
$string['estado'] = 'Status';
$string['estilo'] = 'Style';
$string['estilo_danger'] = 'Danger (red)';
$string['estilo_info'] = 'Information (blue)';
$string['estilo_warning'] = 'Warning (yellow)';
$string['global'] = 'global';
$string['globalactivo'] = 'Also block by type, category or course';
$string['globalactivo_help'] = 'Besides the activities by ID, block every activity matching the activity type, category and course filters (an empty filter = all). The activity is still listed in the course; the user sees the message when trying to enter.';
$string['globalcategorias'] = 'Categories (including subcategories)';
$string['globalcategorias_help'] = 'Empty = all categories.';
$string['globalmodulos'] = 'Activity types';
$string['habilitado'] = 'Apply rules';
$string['habilitado_desc'] = 'Master switch. When off, no rule blocks or notifies (the Test page still works).';
$string['hostsinternos'] = 'Allow APIs on the internal network';
$string['hostsinternos_desc'] = 'By default Moodle blocks requests to hosts and ports in the "cURL blocked hosts" list (internal network, localhost). Enable this only if the institution\'s API is on the private network.';
$string['identificador'] = 'Identify the user by';
$string['identificador_help'] = 'User field sent as {identificador}. If the user does not have it (e.g. no ID number), it is treated as an API error.';
$string['inactiva'] = 'Disabled';
$string['mensaje'] = 'Message';
$string['ninguno'] = 'None';
$string['nombre'] = 'Name';
$string['nuevapestana'] = 'Open the link in a new tab';
$string['nuevaregla'] = 'New rule';
$string['orden'] = 'Order';
$string['orden_help'] = 'Rules are evaluated from lowest to highest. For site blocking, the first rule that triggers is enough.';
$string['peticion'] = 'Request sent';
$string['plantilla'] = 'JSON body sent';
$string['plantilla_help'] = 'JSON sent to the API. Variables {username}, {idnumber}, {email}, {identificador}, {course_shortname}, {cmid}... are replaced inside strings. Must be valid JSON.';
$string['plugindeshabilitado'] = 'Rules are disabled in the general settings: none will be applied.';
$string['privacy:metadata:apiexterna'] = 'To decide whether a rule applies, user data is sent to the API configured by the administrator.';
$string['privacy:metadata:apiexterna:course'] = 'Short name of the course and activity where the check happens.';
$string['privacy:metadata:apiexterna:email'] = 'User email.';
$string['privacy:metadata:apiexterna:idnumber'] = 'User ID number.';
$string['privacy:metadata:apiexterna:username'] = 'Username.';
$string['probar'] = 'Test';
$string['probar_aviso'] = 'The test queries the real API with the given user (bypassing the cache) and shows what they would see. Nobody is blocked or notified.';
$string['probar_error'] = 'Error: {$a}. The rule\'s error policy would apply.';
$string['probar_exento'] = 'This user is exempt (administrator, exempt capability or exempt role): in practice the rule would not apply to them.';
$string['probar_nousuario'] = 'No user found with that username, email or ID number.';
$string['probar_usuario'] = 'User';
$string['probar_usuario_ph'] = 'username, email or ID number';
$string['probarregla'] = 'Test rule: {$a}';
$string['reglaborrada'] = 'Rule deleted.';
$string['reglaguardada'] = 'Rule saved. Test it with a user.';
$string['reglas'] = 'Rules';
$string['respuesta'] = 'Response';
$string['respuestaejemplo'] = 'Example response';
$string['respuestaejemplo_help'] = 'A typical API response. Its data fields appear as variables in the message picker. Filled automatically from the Test page with "Use this response as example".';
$string['resultadoprueba'] = 'Result for {$a}';
$string['rolesexentos'] = 'Exempt roles';
$string['rolesexentos_help'] = 'Users with any of these roles in any context (e.g. Teacher) are never affected by the rule. Site administrators are always exempt.';
$string['rutadatos'] = 'Data field';
$string['rutamensaje'] = 'Message field';
$string['rutaresultado'] = 'Result field';
$string['rutaresultado_help'] = 'Path in the JSON response of the true/false value. Use dots for nested objects, e.g. response.has_debt.';
$string['seactivacuando'] = 'Triggers on';
$string['sinreglas'] = 'No rules yet. Create the first one with "New rule".';
$string['timeout'] = 'Timeout (s)';
$string['token'] = 'Token or key';
$string['titulo'] = 'Title';
$string['todas'] = 'All';
$string['todos'] = 'All';
$string['url'] = 'API URL';
$string['usarejemplo'] = 'Use this response as example';
$string['valoractiva'] = 'The rule triggers when the API returns';
$string['valoractiva_false'] = 'false (e.g. "enrolment up to date = false")';
$string['valoractiva_help'] = 'Value of the result field that triggers the action, so the API does not have to adapt to the plugin: if it returns "has_debt", choose true; if it returns "up_to_date", choose false.';
$string['valoractiva_true'] = 'true (e.g. "has debt = true")';
$string['var_cmid'] = 'Activity id (if the check happens in an activity)';
$string['var_course_fullname'] = 'Course name';
$string['var_course_shortname'] = 'Course short name';
$string['var_email'] = 'Email';
$string['var_firstname'] = 'First name';
$string['var_fullname'] = 'Full name';
$string['var_identificador'] = 'Value sent as identifier';
$string['var_idnumber'] = 'ID number';
$string['var_lastname'] = 'Last name';
$string['var_regla'] = 'Rule name';
$string['var_tipo_identificador'] = 'username, idnumber or email';
$string['var_username'] = 'Username';
$string['variables'] = 'Variables';
$string['variables_api'] = 'API response';
$string['variables_ayuda'] = 'Click a field (title, message, button) and then a variable to insert it.';
$string['variables_copiado'] = 'Variable copied to clipboard:';
$string['variables_curso'] = 'Course';
$string['variables_regla'] = 'Rule';
$string['variables_sinapi'] = 'Paste an example response (Response section) or use "Use this response as example" on the Test page to see the API variables here.';
$string['variables_usuario'] = 'User';
$string['variablesrecibidas'] = 'Variables received';
$string['verificar'] = 'I have paid, check again';
$string['vistaprevia'] = 'What the user would see';
$string['volvercurso'] = 'Back to course';
$string['cachedef_reglas'] = 'Active debt rules';
$string['cachedef_resultados'] = 'API results per rule and user';
$string['error_token'] = 'The token cannot contain line breaks.';
$string['privacy:metadata:apiexterna:fullname'] = 'First and last name of the user.';
$string['privacy:metadata:apiexterna:userid'] = 'Moodle user id.';
$string['actividades'] = 'Activities (IDs)';
$string['actividades_help'] = 'IDs of the activities to block, separated by commas. The ID is in the activity address: .../mod/quiz/view.php?id=42.';
$string['error_actividades'] = 'Only numbers separated by commas.';
$string['error_actividadesnoexisten'] = 'Some ID does not match an existing activity.';
$string['error_sinalcance'] = 'Enter at least one activity or tick blocking by type, category or course.';
$string['globalcursos'] = 'Courses';
$string['globalcursos_help'] = 'Empty = all courses.';
$string['nactividades'] = '{$a} activity(ies)';
