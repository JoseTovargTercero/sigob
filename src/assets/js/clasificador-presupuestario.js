let clasificador = {};

function getPartidas() {
  $.ajax({
    url: "../../back/modulo_nomina/nom_lista_partidas.php",
    type: "POST",
    success: function (response) {
      if (response.error) {
        console.log(response);
      } else {
        let data = response.success;

        // Suponiendo que response es un array de objetos con las propiedades 'partida' y 'descripcion'
        data.forEach(function (item) {
          $("#partidas").append(
            '<option value="' +
              item.partida +
              '">' +
              item.descripcion +
              "</option>"
          );
          clasificador[item.partida] = item.descripcion;
        });
      }
    },
    error: function (xhr, status, error) {
      console.log(error);
    },
  });
}

getPartidas();
