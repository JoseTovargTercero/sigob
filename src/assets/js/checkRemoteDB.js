let intervalID = null
let estado = 0

function checkRemoteDB() {
  fetch('../../back/sistema_global/check_remote_db.php')
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        localStorage.setItem('alerts', false)

        const error = JSON.parse(data.error)

        const str = error.error
        const regex = /@'([\d\.]+)'/
        const match = str.match(regex)
        let texto_error

        if (match) {
          texto_error =
            "<i class='bx bx-error text-danger'></i> Acceso denegado para <b>" +
            match[1] +
            "</b> <i class='bx bx-error text-danger'></i>"
        } else {
          texto_error =
            " <i class='bx bx-error text-danger'></i> Se ha denegado el acceso <i class='bx bx-error text-danger'></i> "
        }

        document.getElementById('remoteDbError').innerHTML = texto_error

        // Si hay error y el intervalo no está activo, iniciarlo
        if (!intervalID) {
          intervalID = setInterval(checkRemoteDB, 5000)
          console.log('SetInterval iniciado')
        }

        if (estado == 0) {
          loadErrorPage() // Llamada a la función para cargar el HTML con el error
        }

        estado = 1
      } else {
        localStorage.setItem('alerts', true)

        console.log(data.success)
        document.getElementById('remoteDbError').innerText = ''
        // recargar la pagina

        // Si no hay error y el intervalo está activo, detenerlo
        if (intervalID) {
          clearInterval(intervalID)
          intervalID = null // Resetear la variable
          window.location.reload()
        }
      }
    })
    .catch((error) => console.error('Error en la solicitud:', error))
}

// Función para cargar el contenido HTML del error
function loadErrorPage() {
  fetch('../../front/mod_global/global_pagina_error.html')
    .then((response) => response.text())
    .then((html) => {
      document.querySelector('.pc-container').innerHTML = html
      ejecutarScripts(document.querySelector('.pc-container'))
    })
    .catch((error) => console.error('Error al cargar el archivo:', error))
}

// Función para ejecutar los scripts del HTML cargado
function ejecutarScripts(elemento) {
  const scripts = elemento.querySelectorAll('script')
  scripts.forEach((script) => {
    const nuevoScript = document.createElement('script')
    if (script.src) {
      nuevoScript.src = script.src
      nuevoScript.onload = () => console.log(`Script ${script.src} cargado.`)
    } else {
      nuevoScript.textContent = script.textContent
    }
    document.body.appendChild(nuevoScript)
    script.remove()
  })
}

// Ejecutar la verificación al inicio
//checkRemoteDB();
document.addEventListener('DOMContentLoaded', checkRemoteDB)
