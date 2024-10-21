import { asignarMontoEnte, getEnte } from '../api/form_entes.js'
import { loadAsignacionEntesTable } from '../controllers/form_asignacionEntesTable.js'
import {
  confirmNotification,
  hideLoader,
  insertOptions,
  separarMiles,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { form_asignacion_entes_card } from './form_asignacion_entes_card.js'
const d = document

export const form_asignacion_entes_monto_card = async ({
  elementToInset,
  enteId,
  ejercicioFiscal,
}) => {
  let fieldList = { monto: '' }
  let fieldListErrors = {
    monto: {
      value: true,
      message: 'Monto inválido',
      type: 'number',
    },
  }

  let montos = {
    total: ejercicioFiscal.situado,
    restante: ejercicioFiscal.restante,
    distribuido: ejercicioFiscal.distribuido,
    acumulado: 0,
  }

  let ente = await getEnte(enteId)
  console.log(ejercicioFiscal)

  const oldCardElement = d.getElementById('asignacion-ente-monto-form-card')
  if (oldCardElement) oldCardElement.remove()

  let card = ` <div class='card slide-up-animation' id='asignacion-ente-monto-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Asignar monto presupuestario a ente</h5>
          <small class='mt-0 text-muted'>
            Asigne un monto y visualice el restante del situado presupuestario
          </small>
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
       
        <h5>Nombre: ${ente.ente_nombre}</h5>
        <h6>
          Tipo: ${ente.tipo_ente === 'J' ? 'Jurídico' : 'Descentralizado'}
        </h6>
        <div class='d-flex gap-2 justify-content-between'>
          <h6>
            Monto disponible ejercicio fiscal: ${separarMiles(
              ejercicioFiscal.situado
            )}
          </h6>
          <h6>
            Monto total asignado: <b id='monto-total-asignado'>0</b>
          </h6>
        </div>
        <form id='asignacion-ente-monto-form'>
          <div class='row'>
            <div class='form-group'>
              <label class="form-label" for='monto'>MONTO A ASIGNAR</label>
              <input type="number" name='monto' id='monto' class='form-control' placeholder='Asignar monto a ente "${
                ente.ente_nombre
              }"'/>
            </div>
          </div>
        </form>
      </div>
      <div class='card-footer'>
        <button class='btn btn-primary' id='asignacion-ente-monto-guardar'>
          Guardar
        </button>
      </div>
    </div>`

  d.getElementById(elementToInset).insertAdjacentHTML('afterbegin', card)

  let cardElement = d.getElementById('asignacion-ente-monto-form-card')
  let formElement = d.getElementById('asignacion-ente-monto-form')

  const closeCard = () => {
    // validateEditButtons()
    cardElement.remove()
    cardElement.removeEventListener('click', validateClick)
    cardElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
      form_asignacion_entes_card({
        elementToInset: 'asignacion-entes-view',
        ejercicioFiscal,
      })
    }

    if (e.target.id === 'asignacion-ente-monto-guardar') {
      enviarInformacion()
    }
  }

  async function validateInputFunction(e) {
    if (e.target.id === 'monto') {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })

      //   actualizarMontoRestante()

      if (Number(e.target.value) > Number(ejercicioFiscal.situado)) {
        e.target.value = ejercicioFiscal.situado
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message:
            'No se puede superar el monto disponible del ejercicio fiscal',
        })
      }
      d.getElementById('monto-total-asignado').textContent = e.target.value
      if (!e.target.value) {
        d.getElementById('monto-total-asignado').textContent = 0
      }
    }
  }

  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion() {
    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message: `¿Desea asignar ${separarMiles(
        fieldList.monto
      )} Bs. a este ente?`,
      successFunction: async function () {
        let res = await asignarMontoEnte({
          id_ejercicio: ejercicioFiscal.id,
          monto_total: fieldList.monto,
          id_ente: ente.id,
        })

        if (res.success) {
          form_asignacion_entes_card({
            elementToInset: 'asignacion-entes-view',
            ejercicioFiscal,
          })
          loadAsignacionEntesTable(ejercicioFiscal.id)
          closeCard()
        }
      },
    })
  }

  function actualizarMontoRestante() {
    let montoElement = d.getElementById('monto-total-asignado')

    let inpu = d.getElementById('monto')

    // REINICIAR MONTO ACUMULADO
    montos.acumulado = 0

    montos.acumulado += inpu.value

    let diferenciaSolicitado = montos.total_solicitado - montos.acumulado

    if (montos.acumulado > montos.distribuido) {
      montoElement.innerHTML = `<span class="text-danger">${montos.acumulado}</span>`
      return
    }

    if (diferenciaSolicitado < 0) {
      montoElement.innerHTML = `<span class="text-warning">${montos.acumulado}</span>`
      return
    }
    if (diferenciaSolicitado > 0) {
      montoElement.innerHTML = `<span class="text-success">${montos.acumulado}</span>`
      return
    }
    if (diferenciaSolicitado === 0) {
      montoElement.innerHTML = `<span class="text-secondary">${montos.acumulado}</span>`
      return
    }
  }

  formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}
