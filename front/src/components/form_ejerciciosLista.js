import { getEjecicio, getEjecicios } from '../api/pre_distribucion.js'

const d = document
export const ejerciciosLista = async ({ elementToInsert, ejercicioFiscal }) => {
  const ejerciciosListContainer = d.getElementById('ejercicios-fiscales')

  let ejerciciosFiscales = await getEjecicios()

  console.log(ejerciciosFiscales)

  let fechaActual = new Date().getFullYear()

  let ejercicioActualId

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

      if (ano === fechaActual) {
        ejercicioActualId = ejercicio.id
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

  let ejercicioActual = await getEjecicio(ejercicioActualId)

  return ejercicioActual
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

  return ejercicioFiscal
}
