/**
 * Represents an AJAX request.
 * @class
 */
class AjaxRequest {
  /**
   * Creates an instance of AjaxRequest.
   * @constructor
   * @param {string} contentType - The content type of the request.
   * @param {object} data - The data to be sent with the request.
   * @param {string} url - The URL to send the request to.
   */
  constructor(contentType, data, url) {
    this.contentType = contentType;
    this.data = data;
    this.url = url;
  }

  /**
   * Sends the AJAX request.
   * @param {function} onSuccess - The success callback function.
   * @param {function} onError - The error callback function.
   * @returns {Promise} - A promise that resolves with the response data.
   */
  send(onSuccess, onError) {
    return $.ajax({
      url: this.url,
      type: 'POST',
      contentType: this.contentType,
      data: JSON.stringify(this.data),
      success: function(response) {
       //console.log(response);
        try {
          if (typeof response !== 'object') {
            response = JSON.parse(response);
          }
        } catch (e) {
          console.error('Error al parsear la respuesta JSON:', e);
          toast_s('error', 'Respuesta del servidor no válida');
          return;
        }

        if (response.status === 'ok') {
          if (typeof onSuccess === 'function') {
            onSuccess(response);
          }
        } else {
          console.log(response.message);
          toast_s('error', 'Error: ' + response.message);
          if (typeof onError === 'function') {
            onError(response);
          }
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
        try {
          const response = JSON.parse(jqXHR.responseText);
          console.error('Error del servidor:', response.mensaje, 'Archivo:', response.archivo, 'Línea:', response.linea);
          toast_s('error', 'Error del servidor: ' + response.mensaje);
          
        } catch (e) {
          console.error('Error al parsear la respuesta de error:', e);
          toast_s('error', 'Error en la solicitud: ' + textStatus);
        }
        if (typeof onError === 'function') {
          onError({textStatus, errorThrown});
        }
      },
      complete: function() {
      //  console.log('Solicitud AJAX completada');
      }
    });
  }
}
