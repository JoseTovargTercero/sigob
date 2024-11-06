import { selectTables } from '../api/globalApi.js'
import { getEjecicio, getEjecicios } from '../api/pre_distribucion.js'

const d = document
export const ejerciciosLista = async ({ elementToInsert, ejercicioFiscal }) => {
  const ejerciciosListContainer = d.getElementById('ejercicios-fiscales')

  let ejerciciosFiscales = await getEjecicios()
  console.log(ejerciciosFiscales)

  const ejercicioGuardado = localStorage.getItem('ejercicioSeleccionado')

  let fechaActual = new Date().getFullYear()
  let ejercicioFechaActual = ejercicioGuardado
    ? ejerciciosFiscales.find(
        (ejercicio) => Number(ejercicio.ano) === Number(ejercicioGuardado)
      )
    : ejerciciosFiscales.find(
        (ejercicio) => Number(ejercicio.ano) === fechaActual
      )

  console.log(ejerciciosFiscales)

  console.log(ejercicioFechaActual)

  console.log(ejercicioGuardado)

  // Si hay un ejercicio almacenado, usamos ese; de lo contrario, usamos el año actual
  let ejercicioSeleccionado = ejercicioFechaActual

  let ejercicioActual

  if (!ejerciciosFiscales || ejerciciosFiscales.length === 0) {
    d.getElementById(elementToInsert).innerHTML = `<div class='col-sm'>
            <p>
              <a
              
                class='pointer text-dark'
                previewlistener='true'
              >
                No hay ejercicios registrados
              </a>
            </p>
          </div>`
    return
  }
  let ejerciciosMapeados = ejerciciosFiscales
    .sort((a, b) => a.ano - b.ano)
    .map((ejercicio, index) => {
      let ano = Number(ejercicio.ano)

      if (ejercicioFechaActual) {
        if (ano === Number(ejercicioFechaActual.ano)) {
          return `  <div class='col-sm-4'>
              <p>
                <a
                  data-ejercicioid='${ejercicio.id}'
                  class='pointer text-decoration-underline text-primary'
                  previewlistener='true'
                >
                  ${ejercicio.ano}
                </a>
              </p>
            </div>`
        }
      } else {
        if (index === 0) {
          // SI NO HAY UN EJERCICIO CON EL AÑO ACTUAL Y NO ESTÁ GUARDADO EN EL LOCAL STORAGE, SE UTILIZA EL PRIMER EJERCICIO REGISTRADO

          ejercicioSeleccionado = ejercicio
          return `  <div class='col-sm-4'>
          <p>
            <a
              data-ejercicioid='${ejercicio.id}'
              class='pointer text-decoration-underline text-primary'
              previewlistener='true'
            >
              ${ejercicio.ano}
            </a>
          </p>
        </div>`
        }
      }

      return `  <div class='col-sm-4'>
            <p>
              <a
                data-ejercicioid='${ejercicio.id}'
                class='pointer text-dark'
                previewlistener='true'
              >
              ${ejercicio.ano}
              </a>
            </p>
          </div>`
    })
    .join('')

  d.getElementById(elementToInsert).innerHTML = ejerciciosMapeados

  return ejercicioSeleccionado
    ? ejercicioSeleccionado
    : ejerciciosFiscales.length > 0
    ? ejerciciosFiscales[0]
    : null
}

export const validarEjercicioActual = async ({ ejercicioTarget }) => {
  let links = d.querySelectorAll('[data-ejercicioid]')

  links.forEach((link) => {
    link.classList.remove('text-decoration-underline')
    link.classList.remove('text-primary')

    link.classList.add('text-dark')
  })

  ejercicioTarget.classList.remove('text-dark')

  ejercicioTarget.classList.add('text-decoration-underline')
  ejercicioTarget.classList.add('text-primary')

  let ejercicioFiscal = await getEjecicio(ejercicioTarget.dataset.ejercicioid)
  localStorage.setItem('ejercicioSeleccionado', Number(ejercicioFiscal.ano))

  return ejercicioFiscal
}
