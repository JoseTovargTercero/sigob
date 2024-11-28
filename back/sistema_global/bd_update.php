<?php

require_once 'conexion.php';
require_once 'session.php';
require_once 'errores.php';
require_once 'DatabaseHandler.php';
$db = new DatabaseHandler($conexion);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Actualización de BD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.27.0/themes/prism-okaidia.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.27.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.27.0/components/prism-sql.min.js"></script>
    <script src="../../src/assets/js/sweetalert2.all.min.js"></script>

    <style>
        .loader {
            display: inline-block;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .vista_flotante {
            position: fixed;
            bottom: 0;
            color: gray;
        }

        .hide {
            display: none
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <!-- Input llave -->
        <div class="mb-3">
            <label for="llave" class="form-label">Llave de verificación</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-key"></i></span>
                <input type="text" value="4F1DAB34F6B9065EBAE074B5599A869914DB11D9A596B3C493A3A9AB9" class="form-control" id="llave" placeholder="Ingrese la llave">
                <button id="btn-validar" class="btn btn-primary">Validar</button>

            </div>
        </div>

        <!-- Textarea para consulta -->
        <div class="mb-3">
            <label for="consulta" class="form-label">Consulta SQL</label>
            <textarea disabled class="form-control" id="consulta" rows="6" placeholder="Ingrese la consulta SQL"></textarea>
        </div>

        <!-- Botón para enviar consulta -->
        <button disabled id="enviarConsulta" class="btn btn-primary">Enviar actualización</button>

        <!-- Tabla de actualizaciones -->
        <div class="mt-5">
            <h5>Historial de Actualizaciones</h5>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Consulta</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="historialActualizaciones">
                </tbody>
            </table>

            <div class="mt-3" id="msg">
            </div>
        </div>
    </div>


    <div class="vista_flotante hide">
        <h2>
            <i class="bi bi-arrow-repeat loader"></i>
            Cargando
        </h2>
    </div>

    <script>
        let registros_locales = []

        function obtener_registros_locales() {
            $.ajax({
                url: '_DBH-select.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    table: 'system_bd'
                }),
                success: function(response) {

                    let jsonResponse;
                    try {
                        jsonResponse = typeof response === 'string' ? JSON.parse(response) : response;
                    } catch (e) {
                        alert('Error al interpretar la respuesta del servidor.');
                        return;
                    }

                    if (jsonResponse.success) {
                        const datos = jsonResponse.success;

                        for (const key in datos) {
                            if (Object.prototype.hasOwnProperty.call(datos, key)) {
                                const element = datos[key];
                                registros_locales.push(element.actualizacion)
                            }
                        }

                    } else if (jsonResponse.error) {
                        alert('Error: ' + jsonResponse.error);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error al enviar la consulta: ' + error);
                    $('.vista_flotante').addClass('hide');
                }
            });
        }
        obtener_registros_locales() // Obtener registros locales

        function registrarCambio(id) {
            $.ajax({
                url: 'bd_update_back.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    accion: 'salvar_ejecutado',
                    id: id
                }),
                success: function(response) {

                    console.log(response)


                    let jsonResponse;
                    try {
                        jsonResponse = typeof response === 'string' ? JSON.parse(response) : response;
                    } catch (e) {
                        alert('Error al interpretar la respuesta del servidor.');
                        return;
                    }

                    if (jsonResponse.success) {
                        console.log('Guardado')
                    } else if (jsonResponse.error) {
                        console.log('error')
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText)
                }
            });
        }


        $(document).ready(function() {
            // Enviar consulta
            $('#enviarConsulta').click(function() {
                const llave = $('#llave').val().trim();
                const consulta = $('#consulta').val().trim();

                if (llave && consulta) {
                    $.ajax({
                        url: 'https://sigep-amazonas.com/gestor_bd_sigob/manejador.php',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            accion: 'send_alter',
                            llave: llave,
                            consulta: consulta
                        }),
                        success: function(response) {

                            let jsonResponse;
                            try {
                                jsonResponse = typeof response === 'string' ? JSON.parse(response) : response;
                            } catch (e) {
                                alert('Error al interpretar la respuesta del servidor.');
                                return;
                            }

                            if (jsonResponse.success) {
                                toast_s('success', 'Consulta enviada correctamente')

                                $('#consulta').val('')
                                registrarCambio(jsonResponse.success)
                                // almacenar registro segun el id enviado
                            } else {
                                toast_s('error', 'error' + jsonResponse.error)
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('Error al enviar la consulta: ' + error);
                        }
                    });
                } else {
                    toast_s('error', 'error' + jsonResponse.error)


                }
            });

            // Ejecutar consulta desde el historial
            $(document).on('click', '.btn-ejecutar', function() {
                const id = $(this).data('id');
                const qry = $(this).data('qry');
                $.ajax({
                    url: 'bd_update_back.php',
                    para: 'ejecutar',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        id: id,
                        qry: qry,
                        accion: 'ejecutar'
                    }),
                    success: function(response) {
                        console.log(response)
                        if (response.success) {
                            toast_s('success', 'Consulta ejecutada correctamente.')

                            $('#row_' + id).remove()
                        } else {
                            toast_s('error', 'error: ' + response.error)
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error al ejecutar la consulta: ' + error);
                    }
                });
            });

            // validar llave
            $(document).on('click', '#btn-validar', function() {
                const llave = $('#llave').val();
                $('.vista_flotante').removeClass('hide');

                if (llave) {
                    $.ajax({
                        url: 'https://sigep-amazonas.com/gestor_bd_sigob/manejador.php',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            llave: llave,
                            accion: 'validar'
                        }),
                        success: function(response) {
                            $('.vista_flotante').addClass('hide');

                            let jsonResponse;
                            try {
                                jsonResponse = typeof response === 'string' ? JSON.parse(response) : response;
                            } catch (e) {
                                alert('Error al interpretar la respuesta del servidor.');
                                return;
                            }

                            if (jsonResponse.success) {
                                $('#consulta').attr('disabled', false)
                                $('#enviarConsulta').attr('disabled', false)
                                $('#historialActualizaciones').html('');
                                let cambios = false;


                                const datos = jsonResponse.success;

                                if (datos != 'No hay registros para mostrar') {
                                    for (const key in datos) {
                                        if (Object.prototype.hasOwnProperty.call(datos, key)) {
                                            const element = datos[key];

                                            if (registros_locales.indexOf(element.id) == -1) {
                                                cambios = true
                                                $('#historialActualizaciones').append(`
                                                    <tr id="row_${element.id}">
                                                    <td>  <pre style="width: 70vw;"><code class="language-sql">${element.qry}</code></pre></td>
                                                    <td class="text-center">
                                                    <br>
                                                    <b>Información de la actualización</b>
                                                    <br>
                                                    <br>

                                                    User: <b>${element.user}</b> <br>
                                                    Fecha: <b>${element.fecha}</b> <br>
                                                    Id registro: <b>${element.id}</b>
                                                    <br>
                                                    <br>
                                                    <button class="btn btn-warning w-100 btn-ejecutar" data-qry="${element.qry}" data-id="${element.id}">Ejecutar</button>
                                                    </td>
                                                    </tr>`);
                                            }
                                        }
                                    }
                                }

                                Prism.highlightAll();

                                if (!cambios) {

                                    $('#msg').html(`<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Respuesta exitosa. </strong> No hay cambios pendientes.</div>`)
                                }


                            } else if (jsonResponse.error) {

                                if (jsonResponse.error == 'No tiene permisos de acceso') {
                                    toast_s('error', jsonResponse.error)
                                } else {
                                    $('#msg').html(`<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Respuesta exitosa. </strong>${jsonResponse.error}</div>`)
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('Error al enviar la consulta: ' + error);
                            $('#msg').html(``)
                            $('.vista_flotante').addClass('hide');
                        }
                    });
                } else {
                    toast_s('error', 'Por favor, completa ambos campos.')

                }
            })
        });
        /*
        alter table `system_bd` add `nc` int not null after `actualizacion`;
        */
    </script>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</body>

</html>