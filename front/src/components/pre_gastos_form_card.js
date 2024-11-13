import { selectTables } from '../api/globalApi.js'
import { getTiposGastos } from '../api/pre_gastos.js'

import {
  confirmNotification,
  insertOptions,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { pre_gastosTipo_form_card } from './pre_gastoTipo_form_card.js'

const d = document

export const pre_gastos_form_card = async ({ elementToInsert, data }) => {
  const cardElement = d.getElementById('gastos-form-card')
  if (cardElement) cardElement.remove()

  let fieldList = { tipo_beneficiario: '', id_beneficiario: '' }
  let fieldListErrors = {
    tipo_beneficiario: {
      value: true,
      message: 'Elija un tipo de beneficiario',
      type: 'text',
    },
  }

  const cargarBeneficiarios = async () => {
    let selectBeneficiarios = d.getElementById('search-select-beneficiario')
    let data
    let options = [`<option>Elegir...</option>`]
    if (fieldList.tipo_beneficiario === '0') {
      data = await selectTables('entes_dependencias')
      data.forEach((ente) => {
        let option = `<option value='${ente.id}'>${ente.actividad} - ${ente.ente_nombre}</option>`
        options.push(option)
      })
    } else {
      data = await selectTables('empleados')
      console.log(data)
      data.forEach((empleado) => {
        let option = `<option value='${empleado.id}'>${empleado.nombres} - ${empleado.cedula}</option>`
        options.push(option)
      })
    }

    selectBeneficiarios.innerHTML = options.join('')

    $('#search-select-beneficiario').trigger('chosen:updated')
  }

  const cargarTiposGastos = async () => {
    let selectTiposGastos = d.getElementById('search-select-tipo-gasto')
    let data = await getTiposGastos()
    let options = [`<option>Elegir...</option>`]

    console.log(data)
    data.forEach((gasto) => {
      let option = `<option value='${gasto.id}'>${gasto.nombre}</option>`
      options.push(option)
    })

    selectTiposGastos.innerHTML = options.join('')

    $('#search-select-tipo-gasto').trigger('chosen:updated')
  }

  let card = `<div class='card slide-up-animation' id='gastos-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Registro de nuevo gasto presupuestario</h5>
          <small class='mt-0 text-muted'>
            Introduzca el tipo de gasto y montó para ser procesado
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
        <form id='gastos-form'>
          <div class='row'>
            <div class='col-sm'>
              <div class='form-group'>
                <label class='form-label'>Tipo de gasto</label>
                <div class='input-group'>
                  <div class='w-80'>
                    <select
                      class='form-select'
                      name='tipo_gasto'
                      id='search-select-tipo-gasto'
                    ></select>
                  </div>
                  <div class='input-group-prepend'>
                    <button
                      type='button'
                      id='add-tipo-gasto'
                      class='input-group-text btn btn-primary'
                    >
                      +
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class='col-sm'>
              <div class='form-group'>
                <label class='form-label'>Monto</label>
                <input
                  class='form-control'
                  type='text'
                  name='monto'
                  id='monto'
                  placeholder='00,00 Bs'
                />
              </div>
            </div>
          </div>
          <div class='row'>
            <div class='col-sm'>
              <div class='form-group'>
                <label class='form-label'>Tipo de beneficiario</label>
                <select
                  class='form-select chosen-tipo-beneficiario'
                  name='tipo_beneficiario'
                  id='search-select-tipo-beneficiario'
                >
                  <option value='' selected>Elegir...</option>
                  <option value='0'>Ente</option>
                  <option value='1'>Empleado</option>
                </select>
              </div>
            </div>
            <div class='col-sm'>
              <div class='form-group'>
                <label for='search-select-beneficiario' class='form-label'>Tipo de beneficiario</label>
                <select
                  class='form-select chosen-tipo-beneficiario'
                  name='beneficiario'
                  id='search-select-beneficiario'
                ></select>
              </div>
            </div>
          </div>
          <div class='row'>
            <div class='col-sm'>
              <div class='form-group'>
                <label class='form-label'>Fecha del gasto</label>
                <input
                  class='form-control'
                  type='date'
                  name='fecha'
                  id='fecha'
                />
              </div>
            </div>
            <div class='col-sm'>
              <div class='form-group'>
                <label class='form-label'>DESCRIPCIóN</label>
                <textarea
                  class='form-control'
                  name=''
                  id=''
                  placeholder='Se registra un gasto de...'
                  rows='1'
                ></textarea>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class='card-footer'>
        <button class='btn btn-primary' id='gastos-guardar'>
          Guardar
        </button>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  $('#search-select-beneficiario')
    .chosen()
    .change(function (obj, result) {
      console.log()
    })

  $('#search-select-tipo-gasto')
    .chosen()
    .change(function (obj, result) {
      console.log()
    })

  cargarTiposGastos()

  // cargarBeneficiarios()

  const formElement = d.getElementById('gastos-form')

  const closeCard = () => {
    let cardElement = d.getElementById('gastos-form-card')

    cardElement.remove()
    d.removeEventListener('click', validateClick)
    formElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
      let gastosRegistrarCointaner = d.getElementById(
        'gastos-registrar-container'
      )
      gastosRegistrarCointaner.classList.remove('hide')
    }
    if (e.target.id === 'add-tipo-gasto') {
      closeCard()

      pre_gastosTipo_form_card({ elementToInsert: 'gastos-view' })
    }

    if (e.target.id === 'gastos-guardar') {
      enviarInformacion()
    }
  }

  function enviarInformacion() {
    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message: '¿Desea registrar este gasto?',
      successFunction: function () {
        registrarGasto({ data: fieldList })
      },
    })
  }

  function validateInputFunction(e) {
    fieldList = validateInput({
      target: e.target,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    })

    console.log(fieldList)

    cargarBeneficiarios()
  }

  formElement.addEventListener('input', validateInputFunction)
  d.addEventListener('click', validateClick)
}
