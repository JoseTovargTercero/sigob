import { generarReporte } from '../api/pre_reportes.js'
import {
  confirmNotification,
  hideLoader,
  insertOptions,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
const d = document
let options = {
  sectores: {
    titulo: 'SECTORES',
    subtitulo: 'Resumen de sectores',
    tipo: 'sectores',
    opciones: true,
    form: ` <form id='reportes-form-sectores'>
        <div class='mb-3'>
          <label for='trimestre' class='form-label'>
            Trimestre
          </label>
          <select class='form-select' id='trimestre' name='trimestre'>
            <option value='1'>Primero</option>
            <option value='2'>Segundo</option>
            <option value='3'>Tercero</option>
            <option value='4'>Cuarto</option>
          </select>
        </div>
      </form>`,
    nombre_archivo: 'Reporte de sectores',
  },
  partidas: {
    titulo: 'PARTIDAS',
    subtitulo: 'Resumen de partidas',
    tipo: 'partidas',
    opciones: true,
    form: ` <form id='reportes-form-partidas'>
    <div class='mb-3'>
      <label for='trimestre' class='form-label'>
        Trimestre
      </label>
      <select class='form-select' id='trimestre' name='trimestre'>
        <option value='1'>Primero</option>
        <option value='2'>Segundo</option>
        <option value='3'>Tercero</option>
        <option value='4'>Cuarto</option>
      </select>
    </div>
  </form>`,
    nombre_archivo: 'Reporte de partidas',
  },
  secpro: {
    titulo: 'SECTORES Y PROGRAMAS',
    subtitulo: 'Resumen de sectores y programas',
    tipo: 'secpro',
    opciones: true,
    form: ` <form id='reportes-form-secpro'>
        <div class='mb-3'>
          <label for='trimestre' class='form-label'>
            Trimestre
          </label>
          <select class='form-select' id='trimestre' name='trimestre'>
            <option value='1'>Primero</option>
            <option value='2'>Segundo</option>
            <option value='3'>Tercero</option>
            <option value='4'>Cuarto</option>
          </select>
        </div>
      </form>`,
    nombre_archivo: 'Reporte de programas y partidas',
  },
  compromiso: {
    titulo: 'COMPROMISOS',
    subtitulo: 'Resumen de compromisos',
    tipo: 'compromiso',
    opciones: true,
    form: ` <form id='reportes-form-compromiso'>
        <div class='mb-3'>
          <label for='tipo_tabla' class='form-label'>
            Tipo de Tabla
          </label>
          <div class='form-check'>
            <input
              class='form-check-input reporte-input'
              type='radio'
              name='tipo_tabla'
              id='tipo_tabla_gastos'
              value='gastos'
              checked
            />
            <label class='form-check-label' for='tipo_tabla_gastos'>
              Gastos
            </label>
          </div>
          <div class='form-check'>
            <input
              class='form-check-input reporte-input'
              type='radio'
              name='tipo_tabla'
              id='tipo_tabla_dozavos'
              value='dozavos'
            />
            <label class='form-check-label' for='tipo_tabla_dozavos'>
              Dozavos
            </label>
          </div>
        </div>

        <div class='mb-3'>
          <label for='tipo_fecha' class='form-label'>
            Tipo de Fecha
          </label>

          <div class='form-check'>
            <input
              class='form-check-input reporte-input'
              type='radio'
              name='tipo_fecha'
              id='tipo_fecha_mensual'
              value='mensual'
               checked
            />
            <label class='form-check-label' for='tipo_fecha_mensual'>
              Mensual
            </label>
          </div>
             <div class='form-check'>
            <input
              class='form-check-input reporte-input'
              type='radio'
              name='tipo_fecha'
              id='tipo_fecha_trimestral'
              value='trimestre'
            />
            <label class='form-check-label' for='tipo_fecha_trimestral'>
              Trimestre
            </label>
          </div>


          
        </div>

        <div class='mb-3'>
          <label for='fecha' class='form-label'>
            Fecha
          </label>
          <select class='form-select reporte-input' id='fecha' name='fecha'>
            <option value='0'>Enero</option>
            <option value='1'>Febrero</option>
            <option value='2'>Marzo</option>
            <option value='3'>Abril</option>
            <option value='4'>Mayo</option>
            <option value='5'>Junio</option>
            <option value='6'>Julio</option>
            <option value='7'>Agosto</option>
            <option value='8'>Septiembre</option>
            <option value='9'>Octubre</option>
            <option value='10'>Noviembre</option>
            <option value='11'>Diciembre</option>
          </select>
        </div>
      </form>`,
    nombre_archivo: 'Reportes de compromisos',
  },
}
export const pre_reportesLista = ({ elementToInsert }) => {
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
               <h5 class="mb-0">Visualización de reporte</h5>
               <small class="mt-0 text-muted">Verifique el reporte a descargar</small>
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

export const pre_reporteDocumento = ({
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

  const validarOpciones = () => {
    let opcionValida = options[report].opciones
    if (!opcionValida) {
      return `   <p class="text-center">${options[report].subtitulo}</p>
     <div class="mt-4 alert alert-success text-center">
     <p class="text-center">No se requiere mas información.</p>
     <button class='btn btn-secondary' id="${nombreCard}-descargar">Descargar</button></div>`
    }

    return `${options[report].form} <button class='btn btn-secondary' id="${nombreCard}-descargar">Descargar</button></div>`
  }

  let card = ` <div class='card slide-up-animation'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Configurar reporte</h5>
          <small class='mt-0 text-muted'>
            Configure las opciones para generar el reporte seleccionado
          </small>
        </div>
      </div>

      <div class='card-body slide-up-animation' id='${nombreCard}-form-card'>
        ${
          report
            ? `  
      ${validarOpciones()}
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

  function crearFieldlistDesdeFormulario(idFormulario) {
    const formulario = document.getElementById(idFormulario)
    if (!formulario) {
      console.error('No se encontró el formulario con el ID:', idFormulario)
      return null
    }

    let list = {}
    let listErrors = {}
    const elementosFormulario = formulario.elements

    for (let i = 0; i < elementosFormulario.length; i++) {
      const elemento = elementosFormulario[i]

      // Ignorar elementos que no son inputs
      if (
        elemento.tagName !== 'INPUT' &&
        elemento.tagName !== 'SELECT' &&
        elemento.tagName !== 'TEXTAREA'
      ) {
        continue
      }

      let valor
      const nombre = elemento.name

      // Manejar diferentes tipos de inputs
      if (elemento.type === 'radio') {
        // Solo tomar el valor del radio que está seleccionado
        if (elemento.checked) {
          valor = elemento.value
        } else {
          // Significa que este radio no está seleccionado
          continue
        }
      } else if (elemento.type === 'checkbox') {
        // Para checkboxes, guardar true o false
        valor = elemento.checked ? elemento.value : false // Puedes ajustar esto dependiendo de cómo quieras manejar el valor
      } else {
        valor = elemento.value
      }
      // Validar el valor (puedes agregar más validaciones aquí)
      if (valor === '' || valor === null || valor === undefined) {
        valor = '' // Cadena vacía para valores inválidos
      }

      list = { ...list, [nombre]: valor }
      listErrors = {
        ...listErrors,
        [nombre]: {
          value: true,
          message: 'Campo inválido',
          type: 'text',
        },
      }
    }

    return { list, listErrors }
  }

  if (report && options[report].opciones) {
    let result = crearFieldlistDesdeFormulario(`reportes-form-${report}`)
    fieldList = result.list
    fieldListErrors = result.listErrors
    console.log(fieldList)
  } else {
    fieldList = {}
  }

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
      let data = {
        ejercicio_fiscal: ejercicioId,
        nombreArchivo: options[report].nombre_archivo,
        tipo: report,
        ...fieldList,
      }

      console.log(data)

      generarReporte(data)
    }
  }

  async function validateInputFunction(e) {
    if (report === options.compromiso.tipo && e.target.name === 'tipo_fecha') {
      let inputFecha = d.getElementById('fecha')

      if (e.target.value === 'mensual') {
        inputFecha.innerHTML = `<option value="0">Enero</option>
            <option value="1">Febrero</option>
            <option value="2">Marzo</option>
            <option value="3">Abril</option>
            <option value="4">Mayo</option>
            <option value="5">Junio</option>
            <option value="6">Julio</option>
            <option value="7">Agosto</option>
            <option value="8">Septiembre</option>
            <option value="9">Octubre</option>
            <option value="10">Noviembre</option>
            <option value="11">Diciembre</option>`

        fieldList[inputFecha.name] = '0'
      } else {
        inputFecha.innerHTML = ` <option value="1">Trimestre 1</option>
        <option value="2">Trimestre 2</option>
        <option value="3">Trimestre 3</option>
        <option value="4">Trimestre 4</option>`

        fieldList[inputFecha.name] = '1'
      }
    }

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
