YUI.add('moodle-availability_reglasdeuda-form', function (Y, NAME) {

/**
 * Formulario de la condicion "Regla de deuda" en Restringir acceso.
 *
 * @module moodle-availability_reglasdeuda-form
 */
M.availability_reglasdeuda = M.availability_reglasdeuda || {};

/**
 * @class M.availability_reglasdeuda.form
 * @extends M.core_availability.plugin
 */
M.availability_reglasdeuda.form = Y.Object(M.core_availability.plugin);

/**
 * Reglas disponibles: [{id: 1, nombre: 'Deuda'}].
 *
 * @property reglas
 * @type Array
 */
M.availability_reglasdeuda.form.reglas = null;

M.availability_reglasdeuda.form.initInner = function(reglas) {
    this.reglas = reglas;
};

M.availability_reglasdeuda.form.getNode = function(json) {
    var html = '<label><span class="pe-3 pr-3">' + M.util.get_string('title', 'availability_reglasdeuda') + '</span> ' +
        '<span class="availability-group">' +
        '<select name="id" class="custom-select form-select">' +
        '<option value="choose">' + M.util.get_string('choosedots', 'moodle') + '</option>';
    for (var i = 0; i < this.reglas.length; i++) {
        var regla = this.reglas[i];
        // El nombre llega ya formateado (format_string) desde PHP.
        html += '<option value="' + regla.id + '">' + regla.nombre + '</option>';
    }
    html += '</select></span></label>';
    var node = Y.Node.create('<span>' + html + '</span>');

    if (json.id !== undefined && node.one('select[name=id] > option[value=' + json.id + ']')) {
        node.one('select[name=id]').set('value', '' + json.id);
    }

    if (!M.availability_reglasdeuda.form.addedEvents) {
        M.availability_reglasdeuda.form.addedEvents = true;
        var root = Y.one('.availability-field');
        root.delegate('change', function() {
            M.core_availability.form.update();
        }, '.availability_reglasdeuda select');
    }
    return node;
};

M.availability_reglasdeuda.form.fillValue = function(value, node) {
    var seleccion = node.one('select[name=id]').get('value');
    value.id = seleccion === 'choose' ? 'choose' : parseInt(seleccion, 10);
};

M.availability_reglasdeuda.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);
    if (value.id === 'choose') {
        errors.push('availability_reglasdeuda:error_seleccionar');
    }
};

}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
