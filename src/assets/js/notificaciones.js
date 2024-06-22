






/**
 * Wait until the document is fully loaded, then:
 * 1. Select all elements with the class 'custom-dropdown-toggle'.
 * 2. For each toggle element:
 *    - Select the child dropdown menu with the class 'custom-dropdown-menu'.
 *    - If a dropdown menu is found:
 *      - Add a 'click' event listener to the toggle:
 *        - Prevent event propagation.
 *        - Toggle the 'show' class on the dropdown menu.
 *        - Set the dropdown menu's 'left' to 'auto' and 'right' to '0'.
 *      - Add a 'click' event listener to the document:
 *        - If the click is outside the dropdown and toggle, remove the 'show' class from the dropdown menu.
 */


document.addEventListener('DOMContentLoaded', function() {
  const dropdownToggles = document.querySelectorAll('.custom-dropdown-toggle');
  dropdownToggles.forEach(toggle => {
    const dropdownMenu = toggle.querySelector('.custom-dropdown-menu');

    if (dropdownMenu) { 
      toggle.addEventListener('click', function(event) {
        event.stopPropagation();
        dropdownMenu.classList.toggle('show');

        const rect = dropdownMenu.getBoundingClientRect();
        dropdownMenu.style.left = 'auto';
        dropdownMenu.style.right = '0';
      });

      document.addEventListener('click', function(event) {
        if (!dropdownMenu.contains(event.target) && !toggle.contains(event.target)) {
          dropdownMenu.classList.remove('show');
        }
      });
    }
  });
});

const oficinasNombre = {
  'registro_control' : 'Registro y control',
  'nomina' : 'Nómina',
}


function cargarNotificaciones() {
  $.ajax({
    url: '../../back/sistema_global/notificaciones_manejador.php',
    type: 'POST',
    data: {
      tabla: true
    },
    success: function(response) {
      var data = JSON.parse(response);
      if (data.length > 0) {
        $('#notifications_number').html(data.length);
        $('#notifications').html();

        for (var i = 0; i < data.length; i++) {
          var guia = data[i].guia;

          if (guia.trim() == 'http://localhost/sigob/') {
            guia = '';
          }
          //var date = data[i].date;
          var comentario = data[i].comentario;
          var id_notificacion = data[i].id_notificacion;
          var u_oficina = data[i].u_oficina;
          var u_nombre = data[i].u_nombre;


          $('#notifications').append(`
          <li class="ml-0">
                  <a onclick="seguirGuia('`+id_notificacion+`', '`+guia+`')" class="d-flex">
                    <div class="prefijo">
                      <div class="circle">
                        <span class="letter">`+u_oficina[0].toUpperCase()+`</span>
                      </div>
                    </div>
                    <div>
                      <p class="mb-0">`+oficinasNombre[u_oficina]+`</p>
                      <small class="text-muted"><b>`+u_nombre+`</b>: `+comentario+`</small>
                    </div>
                  </a>
                </li>
          `);
        }

      }else{
        $('#badge_notifications_number').remove();
      }
    }
  });
}
cargarNotificaciones()

function seguirGuia(notificacion, guia){
  $.ajax({
    url: '../../back/sistema_global/notificaciones_manejador.php',
    type: 'POST',
    data: {
      visto: true,
      notificacion: notificacion
    },
    success: function(response) {
      var data = JSON.parse(response);

      if (data.text == 'ok') {
        location.href=guia;
      }
    }
  });
}


