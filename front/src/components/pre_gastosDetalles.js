import { generarCompromisoPdf } from '../api/pre_compromisos.js'
import { aceptarGasto, rechazarGasto } from '../api/pre_gastos.js'
import {
  confirmNotification,
  hideLoader,
  insertOptions,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
const d = document

let obj = {
  id: '6',
  fecha: '2023-12-31',
  nombre_tipo_gasto: 'TIPO DE GASTO',
  tipo_beneficiario: '0',
  partida: '402.00.00.00.0000',
  nombre_partida: null,
  descripcion_partida: 'MATERIALES, SUMINISTROS Y MERCANCÍAS',
  descripcion_gasto: 'asdasdas',
  monto_gasto: '10',
  status_gasto: '0',
  id_ejercicio: '1',
  informacion_beneficiario: {
    id: 9,
    sector: '1',
    programa: '9',
    proyecto: '0',
    actividad: '51',
    ente_nombre: 'TESORERIA',
    tipo_ente: 'J',
  },
  id_compromiso: 4,
  correlativo: 'C00003-2024',
  informacion_distribucion: {
    id: 2,
    id_partida: 1009,
    monto_inicial: '5000',
    id_ejercicio: 1,
    monto_actual: '4855',
    id_sector: 1,
    id_programa: 1,
    id_proyecto: 0,
    id_actividad: 51,
    status: 1,
    status_cerrar: 0,
    sector: '01',
    programa: '01',
  },
  tipo_beneficiario: '0',
}

export const pre_gastosDetalles = ({
  elementToInsert,
  ejercicioFiscal,
  data,
  recargarEjercicio,
}) => {
  let fieldList = { ejemplo: '' }
  let fieldListErrors = {
    ejemplo: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
  }
  console.log(data)

  const generarBeneficiarioInformacion = () => {
    console.log(data.tipo_beneficiario)

    if (Number(data.tipo_beneficiario) === 0) {
      return `  <div>
          <h5>Tipo beneficiario: Ente</h5>
          <h5>
            Nombre beneficiario: ${data.informacion_beneficiario.ente_nombre}
          </h5>
        </div>`
    } else {
      return ` <div>
          <h5>Tipo beneficiario: Empleado</h5>
          <h5>Nombre beneficiario: ${data.informacion_beneficiario.nombres}</h5>
          <h5>Cédula: ${data.informacion_beneficiario.cedula}</h5>
        </div>`
    }
  }

  let sector_programa_proyecto = `${
    data.informacion_distribucion ? data.informacion_distribucion.sector : '0'
  }.${
    data.informacion_distribucion ? data.informacion_distribucion.programa : '0'
  }.${data.id_actividad == 0 ? '00' : data.id_actividad}`

  let nombreCard = 'gastos-detalles'

  const oldCardElement = d.getElementById(`${nombreCard}-form-card`)
  if (oldCardElement) oldCardElement.remove()

  let card = `    <div class='card slide-up-animation' id='${nombreCard}-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>CAMBIAR TEXTO</h5>
          <small class='mt-0 text-muted'>CAMBIAR TEXTO</small>
        </div>
        <button
          data-close='btn-close'
          type='button'
          class='btn btn-danger'
          aria-label='Close'
        >
          &times;
        </button>
      </div>
      <div class='card-body'>
        <h3>Información beneficiario:</h3>${generarBeneficiarioInformacion()}
        <h3>Información de gasto:</h3>
        <h5>Compromiso: ${data.correlativo || 'No obtenido'}</h5>
        <h5>Tipo de gasto: ${data.nombre_tipo_gasto || 'No obtenido'}</h5>
        <h5>Fecha: ${data.fecha || 'No obtenido'}</h5>
       
        <h5>Descripción: ${data.descripcion_gasto || 'No obtenido'}</h5>
         <h5>
          Estado: ${
            Number(data.status_gasto) === 0
              ? ` <span class='btn btn-sm btn-secondary'>Pendiente</span>`
              : Number(data.status_gasto) === 1
              ? `<span class='btn btn-sm btn-success'>Procesado</span>`
              : `<span class='btn btn-sm btn-danger'>Rechazado</span>`
          }
        </h5>
      </div>
      <div class='card-footer d-flex justify-content-center gap-2'>
      ${
        Number(data.status_gasto) === 1
          ? `<button class='btn btn-secondary'
            data-compromisoid='${data.id_compromiso}'>
            Descargar compromiso
          </button>`
          : `<button class='btn btn-primary' data-aceptarid="${data.id}">
          Guardar
        </button>
        <button class='btn btn-danger' data-rechazarid="${data.id}">
          rechazar
        </button>`
      }
        
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  let cardElement = d.getElementById(`${nombreCard}-form-card`)
  let formElement = d.getElementById(`${nombreCard}-form`)

  const closeCard = () => {
    // validateEditButtons()
    let gastosRegistrarCointaner = d.getElementById(
      'gastos-registrar-container'
    )
    gastosRegistrarCointaner.classList.remove('hide')
    cardElement.remove()
    cardElement.removeEventListener('click', validateClick)
    cardElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
    }
    if (e.target.dataset.compromisoid) {
      console.log(e.target.dataset.compromisoid)
      generarCompromisoPdf(e.target.dataset.compromisoid, data.correlativo)
    }

    if (e.target.dataset.aceptarid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message:
          'Al aceptar este gasto se descontará del presupuesto actual ¿Desea continuar?',
        successFunction: async function () {
          console.log(data.id)

          let res = await aceptarGasto(data.id)
          if (res.success) {
            generarCompromisoPdf(
              res.compromiso.id_compromiso,
              res.compromiso.correlativo
            )
            recargarEjercicio()
            closeCard()
          }
        },
      })
    }
    if (e.target.dataset.rechazarid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message:
          'Rechazar este gasto hará que se elimine y reintegre el monto al presupuesto ¿Desea continuar?',
        successFunction: async function () {
          let res = await rechazarGasto(data.id)
          if (res.success) {
            recargarEjercicio()
            closeCard()
          }
        },
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

  cardElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}
