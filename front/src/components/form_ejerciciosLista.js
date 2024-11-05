import { getEjecicio, getEjecicios } from '../api/pre_distribucion.js'

const d = document
export const ejerciciosLista = async ({ elementToInsert, ejercicioFiscal }) => {
  const ejerciciosListContainer = d.getElementById('ejercicios-fiscales')

  let ejerciciosFiscales = await getEjecicios()

  let fechaActual = new Date().getFullYear()

  const ejercicioGuardado = localStorage.getItem('ejercicioSeleccionado')

  // Si hay un ejercicio almacenado, usamos ese; de lo contrario, usamos el año actual
  const ejercicioSeleccionado = ejercicioGuardado || fechaActual

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
  let ejerciciosMapeados = ejerciciosFiscales.fullInfo
    .sort((a, b) => a.ano - b.ano)
    .map((ejercicio) => {
      let ano = Number(ejercicio.ano)

      if (ano === Number(ejercicioSeleccionado)) {
        ejercicioActual = ejercicio
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
      } else {
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
      }
    })
    .join('')

  d.getElementById(elementToInsert).innerHTML = ejerciciosMapeados

  return ejercicioActual
    ? ejercicioActual
    : ejerciciosFiscales.fullInfo.length > 0
    ? ejerciciosFiscales.fullInfo[0]
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
