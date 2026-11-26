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
 * Selector de variables del mensaje: chips que insertan {variable} en el campo enfocado.
 *
 * Las variables de la API se descubren de la "respuesta de ejemplo" y se actualizan al editarla.
 * Escrito en ES5 sin dependencias para que amd/build funcione sin compilar con grunt.
 *
 * @module     local_reglasdeuda/variables
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    'use strict';

    var cfg = null;
    var objetivo = null; // Id del ultimo campo enfocado; por defecto el editor.

    /**
     * Aplana un objeto a claves con punto (igual que plantilla::aplanar en PHP).
     *
     * @param {*} valor
     * @param {String} prefijo
     * @param {Array} salida
     * @return {Array}
     */
    var aplanar = function(valor, prefijo, salida) {
        if (valor !== null && typeof valor === 'object') {
            Object.keys(valor).forEach(function(clave) {
                if (/^[a-zA-Z0-9_]+$/.test(clave)) {
                    aplanar(valor[clave], prefijo + '.' + clave, salida);
                }
            });
        } else {
            salida.push(prefijo);
        }
        return salida;
    };

    /**
     * Variables de la API segun la respuesta de ejemplo y la ruta de datos.
     *
     * @return {Array|null} null si el ejemplo no es JSON valido
     */
    var variablesApi = function() {
        var ejemplo = document.getElementById(cfg.ejemplo);
        var ruta = document.getElementById(cfg.rutadatos);
        if (!ejemplo || !ejemplo.value.trim()) {
            return [];
        }
        var json;
        try {
            json = JSON.parse(ejemplo.value);
        } catch (e) {
            return null;
        }
        var datos = json;
        (ruta && ruta.value ? ruta.value : 'datos').split('.').forEach(function(parte) {
            datos = (datos && typeof datos === 'object') ? datos[parte] : undefined;
        });
        if (datos === undefined) {
            return [];
        }
        return aplanar(datos, 'datos', []);
    };

    var insertarEnCampo = function(campo, texto) {
        var inicio = campo.selectionStart || 0;
        var fin = campo.selectionEnd || inicio;
        campo.value = campo.value.slice(0, inicio) + texto + campo.value.slice(fin);
        campo.focus();
        campo.selectionStart = campo.selectionEnd = inicio + texto.length;
        campo.dispatchEvent(new Event('change', {bubbles: true}));
    };

    var copiar = function(texto) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(texto);
        }
        window.console.info(cfg.textos.copiado + ' ' + texto);
    };

    /**
     * Inserta en el campo objetivo: TinyMCE (4.3+), Atto o el campo de texto.
     *
     * @param {String} texto
     */
    var insertar = function(texto) {
        var id = objetivo || cfg.editor;
        if (id === cfg.editor) {
            if (window.tinymce && window.tinymce.get(cfg.editor)) {
                window.tinymce.get(cfg.editor).insertContent(texto);
                return;
            }
            var atto = document.getElementById(cfg.editor + 'editable');
            if (atto) {
                atto.focus();
                if (document.execCommand('insertText', false, texto)) {
                    return;
                }
            }
        }
        var campo = document.getElementById(id);
        if (campo && (campo.tagName === 'INPUT' || campo.tagName === 'TEXTAREA') && campo.offsetParent !== null) {
            insertarEnCampo(campo, texto);
            return;
        }
        copiar(texto);
    };

    var chip = function(nombre, ayuda) {
        var boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'btn btn-sm btn-outline-primary me-1 mr-1 mb-1 font-monospace text-monospace';
        boton.textContent = '{' + nombre + '}';
        if (ayuda) {
            boton.title = ayuda;
        }
        // Mousedown con preventDefault: no robar el foco al campo donde se va a insertar.
        boton.addEventListener('mousedown', function(e) {
            e.preventDefault();
        });
        boton.addEventListener('click', function() {
            insertar('{' + nombre + '}');
        });
        return boton;
    };

    var grupo = function(titulo, contenido) {
        var div = document.createElement('div');
        div.className = 'mb-2';
        var etiqueta = document.createElement('div');
        etiqueta.className = 'small fw-bold font-weight-bold mb-1';
        etiqueta.textContent = titulo;
        div.appendChild(etiqueta);
        contenido.forEach(function(nodo) {
            div.appendChild(nodo);
        });
        return div;
    };

    var pintar = function() {
        var contenedor = document.getElementById(cfg.contenedor);
        if (!contenedor) {
            return;
        }
        contenedor.innerHTML = '';
        cfg.grupos.forEach(function(g) {
            contenedor.appendChild(grupo(g.nombre, g.variables.map(function(v) {
                return chip(v.nombre, v.ayuda);
            })));
        });
        var api = variablesApi();
        var nodos;
        if (api && api.length) {
            nodos = api.map(function(n) {
                return chip(n, '');
            });
        } else {
            var aviso = document.createElement('span');
            aviso.className = 'small text-muted';
            aviso.textContent = cfg.textos.sinapi;
            nodos = [aviso];
        }
        contenedor.appendChild(grupo(cfg.textos.api, nodos));
    };

    /**
     * TinyMCE vive en un iframe: su foco no burbujea al documento, se escucha en la instancia.
     */
    var escucharTiny = function(intentos) {
        if (window.tinymce && window.tinymce.get(cfg.editor)) {
            window.tinymce.get(cfg.editor).on('focus', function() {
                objetivo = cfg.editor;
            });
        } else if (intentos > 0) {
            setTimeout(function() {
                escucharTiny(intentos - 1);
            }, 500);
        }
    };

    return {
        /**
         * @param {String} contenedorid div con data-config (ver regla_form::config_variables)
         */
        init: function(contenedorid) {
            var contenedor = document.getElementById(contenedorid);
            if (!contenedor) {
                return;
            }
            cfg = JSON.parse(contenedor.getAttribute('data-config'));
            cfg.contenedor = contenedorid;
            pintar();

            document.addEventListener('focusin', function(e) {
                var id = e.target && e.target.id;
                if (cfg.campos.indexOf(id) !== -1) {
                    objetivo = id;
                } else if (id === cfg.editor + 'editable' || id === cfg.editor) {
                    objetivo = cfg.editor;
                }
            });
            escucharTiny(20);

            [cfg.ejemplo, cfg.rutadatos].forEach(function(id) {
                var campo = document.getElementById(id);
                if (campo) {
                    campo.addEventListener('input', pintar);
                }
            });
        }
    };
});
