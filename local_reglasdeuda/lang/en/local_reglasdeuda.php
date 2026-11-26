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
$string['cachettl'] = 'Check again every';
$string['cachettl_help'] = 'During this time the last response for each student is reused. After paying, the student is unblocked when it expires or by clicking "I have paid, check again". Between 1 minute and 1 day.';
$string['confirmarborrar'] = 'Delete the rule "{$a}"? Activities using it as a condition will become available.';
$string['contrato'] = 'API contract';
$string['contrato_errores'] = 'Errors';
$string['contrato_errores_desc'] = '<p>An error is: a response other than HTTP 200, invalid JSON, a timeout or a missing result field. On error the rule\'s policy applies (default: let the user through).</p>';
$string['contrato_intro'] = 'What Moodle sends to each rule\'s API and what it expects back. Hand this page to the team building the API.';
$string['contrato_llamada'] = 'Call';
$string['contrato_llamada_desc'] = '<ul><li>Method and parameters per rule (below).</li><li>Authentication per rule: <code>Authorization: Bearer &lt;token&gt;</code>, a custom header (e.g. <code>X-API-Key: &lt;token&gt;</code>) or none.</li><li>Configurable timeout (5 s by default). Redirects are not followed.</li></ul>';
$string['contrato_peticion'] = 'Request of each rule';
$string['contrato_peticion_desc'] = '<p>Example with a fictitious user. Real values come from each user\'s profile.</p>';
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
$string['error_resultado'] = 'The response has no true/false value at "{$a}".';
$string['error_ruta'] = 'Invalid path: use names separated by dots, e.g. datos or response.result.';
$string['error_timeout'] = 'Between 1 and 30 seconds.';
$string['error_url'] = 'Enter a valid http(s) URL.';
$string['errorpolitica'] = 'If the API does not respond';
$string['errorpolitica_aplicar'] = 'Apply the action anyway';
$string['errorpolitica_help'] = 'What to do if the API is down or returns something unexpected. Recommended: let through, so a billing system failure does not block everyone.';
$string['errorpolitica_permitir'] = 'Let the student through (recommended)';
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
$string['inactiva'] = 'Disabled';
$string['mensaje'] = 'Message';
$string['nombre'] = 'Name';
$string['nuevapestana'] = 'Open the link in a new tab';
$string['nuevaregla'] = 'New rule';
$string['orden'] = 'Order';
$string['orden_help'] = 'Rules are evaluated from lowest to highest. For site blocking, the first rule that triggers is enough.';
$string['peticion'] = 'Request sent';
$string['plugindeshabilitado'] = 'Rules are disabled (Settings tab): none will be applied.';
$string['privacy:metadata:apiexterna'] = 'To decide whether a rule applies, user data is sent to the API configured by the administrator.';
$string['privacy:metadata:apiexterna:course'] = 'Short name of the course and activity where the check happens.';
$string['privacy:metadata:apiexterna:email'] = 'User email.';
$string['privacy:metadata:apiexterna:idnumber'] = 'User ID number.';
$string['privacy:metadata:apiexterna:username'] = 'Username.';
$string['probar'] = 'Test';
$string['probar_aviso'] = 'The test queries the real API with the given user (bypassing the cache) and shows what they would see. Nobody is blocked or notified.';
$string['probar_error'] = 'Error: {$a}. The rule\'s error policy would apply.';
$string['probar_exento'] = 'This user is an administrator, manager or teacher: site blocking would not apply (they can always log in).';
$string['probar_nousuario'] = 'No people found with that text.';
$string['probar_usuario'] = 'User';
$string['probar_usuario_ph'] = 'first name, last name, username, email or ID';
$string['probarregla'] = 'Test rule: {$a}';
$string['reglaborrada'] = 'Rule deleted.';
$string['reglaguardada'] = 'Rule saved. Test it with a user.';
$string['reglas'] = 'Rules';
$string['respuesta'] = 'Response';
$string['respuestaejemplo'] = 'Example response';
$string['respuestaejemplo_help'] = 'A typical API response. Its data fields appear as variables in the message picker. Filled automatically from the Test page with "Use this response as example".';
$string['resultadoprueba'] = 'Result for {$a}';
$string['rutadatos'] = 'Field with data for the message';
$string['rutamensaje'] = 'Field with the API\'s own message';
$string['rutaresultado'] = 'Field that says whether there is debt';
$string['rutaresultado_help'] = 'Name of the response field carrying true or false. If the API returns {"resultado": true}, enter: resultado. If it returns {"has_debt": true}, enter: has_debt.';
$string['seactivacuando'] = 'Triggers on';
$string['sinreglas'] = 'No rules yet. Create the first one with "New rule".';
$string['timeout'] = 'Timeout (s)';
$string['token'] = 'Token or key';
$string['titulo'] = 'Title';
$string['todas'] = 'All';
$string['todos'] = 'All';
$string['url'] = 'API URL';
$string['usarejemplo'] = 'Use this response as example';
$string['valoractiva'] = 'Act when that field is';
$string['valoractiva_false'] = 'false';
$string['valoractiva_help'] = 'Almost always: true. Choose false only if the API answers the other way round, e.g. {"up_to_date": false} when the student owes.';
$string['valoractiva_true'] = 'true (has debt)';
$string['var_cmid'] = 'Activity id (if the check happens in an activity)';
$string['var_course_fullname'] = 'Course name';
$string['var_course_shortname'] = 'Course short name';
$string['var_email'] = 'Email';
$string['var_firstname'] = 'First name';
$string['var_fullname'] = 'Full name';
$string['var_idnumber'] = 'ID number';
$string['var_lastname'] = 'Last name';
$string['var_regla'] = 'Rule name';
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
$string['agregarparametro'] = 'Add parameter';
$string['campo_fijo'] = 'Fixed value';
$string['campo_id'] = 'Moodle internal id';
$string['campo_perfil'] = 'Profile: {$a}';
$string['error_param_nombre'] = 'Invalid name: letters, digits, _ . - (no spaces).';
$string['error_param_repetido'] = 'Duplicate name.';
$string['error_sinparametros'] = 'Add at least one parameter.';
$string['error_sinvalor'] = 'The user has no "{$a}"; the API cannot be queried.';
$string['metodo'] = 'Method';
$string['metodo_get'] = 'GET (parameters in the URL)';
$string['metodo_post_form'] = 'POST form (application/x-www-form-urlencoded)';
$string['metodo_post_json'] = 'POST JSON (application/json)';
$string['param_campo'] = 'Moodle field';
$string['param_nombre'] = 'Name';
$string['param_valor'] = 'Value';
$string['param_valor_ph'] = 'fixed, or default';
$string['parametro'] = 'Parameter {no}';
$string['parametro_help'] = 'Name under which the API receives the value, and what is sent:<ul><li><b>Moodle field only</b>: the user\'s data (e.g. cedula <- ID number).</li><li><b>Value only</b> (field on "Choose..."): that value is always sent (e.g. deuda_maxima = 50).</li><li><b>Field and value</b>: the user\'s data; if missing, the default value.</li></ul>With GET it goes in the URL (?cedula=...); with POST, in the body.';
$string['parametros'] = 'Parameters sent';
$string['contrato_sinreglas'] = 'No rules yet: create one to see its request here.';
$string['privacy:metadata:apiexterna:profile'] = 'Custom profile fields the administrator chooses to send.';
$string['error_param_sinvalor'] = 'Choose a Moodle field or enter a value.';
$string['pordefecto'] = '(default: {$a})';
$string['rolesaplica'] = 'Roles it applies to';
$string['rolesaplica_help'] = 'The rule only affects users with any of these roles in any course or context (e.g. Student). Empty = applies to everyone. Site administrators and users with the exempt capability are never affected.';
$string['paso_api'] = '1. Which API is queried and what is sent?';
$string['paso_respuesta'] = '2. How is the response read?';
$string['paso_accion'] = '3. What happens to the student?';
$string['paso_mensaje'] = '4. What message does the student see?';
$string['respuesta_esperada'] = 'The API should respond like this';
$string['rutadatos_help'] = 'Response field with values for the message (total, payment link...). Default: datos. Used as {datos.total}.';
$string['rutamensaje_help'] = 'If the API sends text in this field, it is shown instead of the configured message. Default: mensaje.';
$string['param_quitar'] = 'Remove';
$string['archivofaltante'] = 'The plugin file "{$a}" is missing. It was probably removed by the server antivirus. Reinstall the ZIP and check Settings > File check.';
$string['verificacion'] = 'File check';
$string['verificacion_ok'] = 'All plugin files are present and unchanged.';
$string['verificacion_problema'] = '{$a->faltan} plugin files are missing and {$a->cambiados} changed. Usually the server antivirus: reinstall the ZIP and, if it happens again, ask the hosting to exclude local/reglasdeuda from scanning.';
$string['verificacion_falta'] = 'Missing: {$a}';
$string['verificacion_cambiado'] = 'Modified: {$a}';
$string['quienes'] = 'Who does it apply to?';
$string['quienes_desc'] = 'Every user whose API check indicates debt, regardless of role. Exception: <b>site blocking</b> never applies to administrators, managers or teachers (they can always log in).';
$string['buscar'] = 'Search';
$string['datosenviados'] = 'Data sent to the API';
$string['masresultados'] = 'Showing the first 25; type something more specific.';
