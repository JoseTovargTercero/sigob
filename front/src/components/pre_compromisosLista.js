import { generarReporte } from '../api/pre_reportes.js'
import { validateInput } from '../helpers/helpers.js'

const d = document
let options = {
  dozavos: {
    titulo: 'DOZAVOS',
    subtitulo: 'Compromisos de dozavos',
    tipo: 'dozavos',
    nombre_archivo: 'Reporte de partidas',
  },
  gastos: {
    titulo: 'GASTOS',
    subtitulo: 'Compromisos de gastos',
    tipo: 'gastos',

    nombre_archivo: 'Reporte de programas y partidas',
  },
}

export const pre_compromisosLista = ({ elementToInsert }) => {
  let nombreCard = 'reporte-list'

  const oldCardElement = d.getElementById(`${nombreCard}-form-card`)
  if (oldCardElement) {
    closeCard(oldCardElement)
  }

  const crearLista = () => {
    let listItems = Object.values(options).map((option) => {
      return `  <li
          class='list-group-item list-group-item-action pointer '
          data-tab-ids='${option.tipo}'
        >
          <b>${option.titulo}:</b> ${option.subtitulo}
        </li>`
    })

    return listItems.join('')
  }

  let card = `<div class='card slide-up-animation' id='${nombreCard}-form-card'>
      <div class='card-header d-flex justify-content-between'>
        
           <div class="">
               <h5 class="mb-0">Visualización de reporte de compromisos</h5>
               <small class="mt-0 text-muted">Verifique el tipo del compromiso a descargar</small>
           </div>
        
       
      </div>
      <div class='card-body'>
       <div
          class='list-group mt-3'
          style='height: 100vh !important;'
          role='tablist'
        >${crearLista()}</div>
      
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  let cardElement = d.getElementById(`${nombreCard}-form-card`)
  let formElement = d.getElementById(`${nombreCard}-form`)

  function closeCard(card) {
    // validateEditButtons()
    card.remove()
    card.removeEventListener('click', validateClick)
    card.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard(cardElement)
    }
  }

  async function validateInputFunction(e) {}

  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion(data) {}

  //   formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

export const pre_compromisoDocumento = ({
  elementToInsert,
  report,
  ejercicioId,
}) => {
  let fieldList = { ejemplo: '' }
  let fieldListErrors = {
    ejemplo: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
  }

  let nombreCard = 'reporte-documento'

  const oldCardElement = d.getElementById(`${nombreCard}-form-card`)
  if (oldCardElement) {
    closeCard(oldCardElement)
  }

  let card = ` <div class='card slide-up-animation'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Histórico de gastos realizados</h5>
          <small class='mt-0 text-muted'>
            Visualice el historial de gastos de funcionamiento
          </small>
        </div>
      </div>

      <div class='card-body slide-up-animation' id='${nombreCard}-form-card'>
        ${
          report
            ? `  
      <p class="text-center">${options[report].subtitulo}</p>
     <div class="mt-4 alert alert-success text-center"><p class="text-center">No se requiere mas información.</p>
     <button class='btn btn-secondary' id="${nombreCard}-descargar">Descargar</button></div>
      
  `
            : ` <div
          class='card-body d-flex justify-content-center align-items-center'
          id='reportes-container'
        >
          <div class='alert alert-info'>
            <p class='text-center m-0'>Elija alguno de los reportes disponibles</p>
          </div>
        </div>`
        }
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  let cardElement = d.getElementById(`${nombreCard}-form-card`)

  function closeCard(card) {
    // validateEditButtons()
    card.remove()
    card.removeEventListener('click', validateClick)
    card.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard(cardElement)
    }

    if (e.target.id === `${nombreCard}-descargar`) {
      generarReporte({
        ejercicio_fiscal: ejercicioId,
        nombreArchivo: options[report].nombre_archivo,
        tipo: report,
      })
    }
  }

  async function validateInputFunction(e) {
    fieldList = validateInput({
      target: e.target,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    })
  }

  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion(data) {}

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}
