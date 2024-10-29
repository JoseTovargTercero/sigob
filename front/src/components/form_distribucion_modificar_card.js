import { modificarMontoDistribucion } from '../api/pre_distribucion.js'
import {
  confirmNotification,
  hideLoader,
  insertOptions,
  separarMiles,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
const d = document

export const form_distribucion_modificar_form_card = ({
  elementToInset,
  ejercicioFiscal,
  partidas,
  sectores,
}) => {
  let distribucionPartidas = ejercicioFiscal.distribucion_partidas
  console.log(distribucionPartidas, partidas, sectores)

  let fieldList = {
    'partida-1': '',
    'partida-2': '',
    id_sector: '',
    'partida-monto': 0,
  }
  let fieldListErrors = {
    'partida-monto': {
      value: true,
      message: 'Monto inválido',
      type: 'number',
    },
  }

  let montos = { disponible: 0, restante: 0, asignado: 0, acumulado: 0 }
  const oldCardElement = d.getElementById('distribucion-modificar-card')
  if (oldCardElement) oldCardElement.remove()

  let optionsPartidasDistribucion = distribucionPartidas
    .map((option) => {
      let sector_codigo = `${option.sector_informacion.sector}.${option.sector_informacion.programa}.${option.sector_informacion.proyecto}`

      return `<option value="${option.id}">${sector_codigo} - ${option.partida}</option>`
    })
    .join('')

  let optionsPartidasListNueva = partidas
    .filter(
      (option) =>
        !distribucionPartidas.some(
          (partida) => partida.partida === option.partida
        )
    )
    .map((option) => {
      return `<option value="${option.id}">${option.partida}</option>`
    })
    .join('')

  let optionsSectores = sectores.map((option) => {
    let sector_codigo = `${option.sector}.${option.programa}.${option.proyecto}`

    return `<option value="${option.id}">${sector_codigo}</option>`
  })

  let selectDistribucion = `<div class='form-group slide-up-animation'>
      <label for='partida-distribucion' class='form-label'>
        Partida a asignar (distribucion)
      </label>
      <select
        class='form-select partida-input chosen-select-2'
        name='partida-2'
        id='partida-distribucion'
      >
        <option value="">Elegir...</option>
        ${optionsPartidasDistribucion}
      </select>
    </div>
    
              <h6 class='mb-0'>
                Asignado:
                <b id='partida-2-monto'>Partida no seleccionada</b>
              </h6>
    
    `

  let selectNuevo = `    <div class='row'>
      <div class='col'>
        <div class='form-group slide-up-animation'>
          <label for='partida-nueva' class='form-label'>
            Sector
          </label>
          <select
            class='form-select partida-input chosen-select-3'
            name='id_sector'
            id='id_sector'
          >
            <option value=''>Elegir...</option>${optionsSectores}
          </select>
        </div>
      </div>
      <div class='col'>
        <div class='form-group slide-up-animation'>
          <label for='partida-nueva' class='form-label'>
            Partida a asignar (nueva)
          </label>
          <select
            class='form-select partida-input chosen-select-2'
            name='partida-2'
            id='partida-nueva'
          >
            <option value=''>Elegir...</option>${optionsPartidasListNueva}
          </select>
        </div>
      </div>
    </div>`

  let card = ` <div class='card slide-up-animation' id='distribucion-modificar-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Modificar valor entre partidas</h5>
          <small class='mt-0 text-muted'>
            Modifique el valor entre partidas antes de que cierre el ejercicio
            fiscal
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
        <form id='distribucion-modificar-form-card'>
          <div class='row mb-4'>
            <div class='col'>
              <h6 class='mb-0'>
                Monto disponible:
                <b id='monto-disponible'>Partida no seleccionada</b>
              </h6>
              <small class='text-muted'>Monto disponible en esta partida</small>
            </div>
            <div class='col'>
              <h6 class='mb-0'>
                Restante:
                <b id='monto-restante'>Partida no seleccionada</b>
              </h6>
              <small class='text-muted'>Restante de partida a distribuir</small>
            </div>
            <div class='col'>
              <h6 class='mb-0'>
                Asignación:
                <b id='monto-asignado'>Monto no asignado</b>
              </h6>
              <small class='text-muted'>
                Monto total asignado a nueva partida
              </small>
            </div>
          </div>

          <div class='row mb-4'>
            <div class='form-check form-switch'>
              <input
                class='form-check-input'
                type='checkbox'
                id='nueva-partida-check'
              />
              <label class='form-check-label' for='nueva-partida-check'>
                ¿Desea añadir nueva partida?
              </label>
            </div>
          </div>

          <div class='row'>
            <div class='col'>
              <div class='form-group'>
                <label for='partida-distribuida' class='form-label'>
                  Partida a modificar
                </label>
                <select
                  class='form-select partida-input chosen-select'
                  name='partida-1'
                  id='partida-distribuida'
                >
                <option value="">Elegir...</option>
                  ${optionsPartidasDistribucion}
                </select>
              </div>
            </div>
            <div class='col' id='select-change'>
              ${selectDistribucion}
            </div>
           
          </div>
          <div class='row'>
          <div class='col'>
              <div class='form-group partida-input'>
                <label class='form-label'>Monto a asignar</label>
                <input
                  class='form-control partida-input partida-monto'
                  type='number'
                  name='partida-monto'
                  id='partida-monto'
                  placeholder='Monto a asignar...'
                />
              </div>
            </div>
            
          </div>
        </form>
      </div>
      <div class='card-footer'>
        <button class='btn btn-primary' id='distribucion-modificar-guardar'>
          Guardar
        </button>
      </div>
    </div>`

  d.getElementById(elementToInset).insertAdjacentHTML('afterbegin', card)

  $('.chosen-select')
    .chosen()
    .change(function (obj, result) {
      console.log('changed: %o', obj)
      let value = result.selected
      fieldList['partida-1'] = value

      if (!value) {
        // ACTUALIZAR MONTOS CON LA NUEVA PARTIDA A DISTRIBUIR
        montos.disponible = 0
        montos.restante = montos.disponible

        d.getElementById('monto-disponible').textContent = montos.disponible
        d.getElementById('monto-restante').textContent = montos.restante
        return
      }

      let partidaEncontrada = distribucionPartidas.find(
        (partida) => Number(partida.id) === Number(value)
      )

      // ACTUALIZAR MONTOS CON LA NUEVA PARTIDA A DISTRIBUIR
      montos.disponible = Number(partidaEncontrada.monto_actual)
      montos.restante = montos.disponible

      d.getElementById('monto-disponible').textContent = montos.disponible
      d.getElementById('monto-restante').textContent = montos.restante
    })

  $('.chosen-select-2')
    .chosen()
    .change(function (obj, result) {
      fieldList['partida-2'] = result.selected
      let value = result.selected

      let checkbox = d.getElementById('nueva-partida-check')

      if (!checkbox.checked) {
        let partidaEncontrada = distribucionPartidas.find(
          (partida) => Number(partida.id) === Number(value)
        )

        let partidaConMontoElement = d.getElementById('partida-2-monto')
        partidaConMontoElement.textContent = partidaEncontrada
          ? partidaEncontrada.monto_actual
          : 'Seleccione una partida'
      }
    })

  let cardElement = d.getElementById('distribucion-modificar-card')
  let formElement = d.getElementById('distribucion-modificar-form-card')

  //   let partidasListDistribucion = d.getElementById(`partidas-list-distribucion`)
  //   let partidasListNueva = d.getElementById(`partidas-list-nueva`)
  //  ` partidasListDistribucion.innerHTML = ''
  //   partidasListNueva.innerHTML = ''`

  //   partidasListDistribucion.innerHTML = optionsPartidasDistribucion
  //   optionsPartidasListNueva.innerHTML = optionsPartidasListNueva

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
    }
    if (e.target.id === 'distribucion-modificar-guardar') {
      let checkInput = d.getElementById('nueva-partida-check')

      if (!fieldList['partida-1']) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Elija la distribucion emisora',
        })
        return
      }

      if (!fieldList['partida-2']) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Elija la distribucion o partida destino',
        })
        return
      }

      if (!fieldList['partida-monto']) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Seleccione el monto a asignar',
        })
        return
      }

      let data
      if (checkInput.checked) {
        if (!fieldList.id_sector) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: 'Seleccione el sector para continuar',
          })
          return
        }

        data = {
          id_ejercicio: ejercicioFiscal.id,
          id_distribucion1: fieldList['partida-1'],
          id_distribucion2: null,
          id_sector: fieldList.id_sector,
          id_partida: fieldList['partida-2'],
          monto: fieldList['partida-monto'],
        }
      } else {
        data = {
          id_ejercicio: ejercicioFiscal.id,
          id_distribucion1: fieldList['partida-1'],
          id_distribucion2: fieldList['partida-2'],
          id_sector: null,
          id_partida: null,
          monto: fieldList['partida-monto'],
        }
      }

      enviarInformacion(data)
    }
  }

  function actualizarMontoRestante() {
    console.log(montos)
    let partidaMonto = d.getElementById('partida-monto')
    let montoRestanteElement = d.getElementById('monto-restante')
    let montoAsignadoElement = d.getElementById('monto-asignado')

    montos.acumulado = 0

    if (isNaN(Number(partidaMonto.value))) return

    if (Number(montos.disponible) === 0) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Elija una partida para actualizar el monto disponible',
      })
      return
    }

    montos.acumulado = Number(partidaMonto.value)
    montos.restante = Number(montos.disponible) - Number(montos.acumulado)

    if (montos.restante < 0) montos.restante = 0

    if (montos.restante < 0) {
      montoRestanteElement.innerHTML = `<span class="px-2 rounded text-red-600 bg-red-100">${montos.restante}</span>`
    }
    if (montos.restante > 0) {
      montoRestanteElement.innerHTML = `<span class="px-2 rounded text-green-600 bg-green-100">${montos.restante}</span>`
    }
    if (montos.restante === 0) {
      montoRestanteElement.innerHTML = `<span class="class="px-2 rounded text-secondary">${montos.restante}</span>`
      partidaMonto.value = montos.disponible
      montos.acumulado = montos.disponible
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'No se puede superar el total disponible',
      })
    }
    montoAsignadoElement.textContent = montos.acumulado
  }

  async function validateInputFunction(e) {
    let partidaADistribuir = d.getElementById('partida-distribuida')
    let partidaDistribucion = d.getElementById('partida-distribucion')
    let partidaNueva = d.getElementById('partida-nueva')
    let partidaContainer = d.getElementById('select-change')

    if (e.target.id === 'partida-distribuida') {
    }
    if (e.target.id === 'nueva-partida-check') {
      if (e.target.checked) {
        partidaContainer.innerHTML = selectNuevo
      } else {
        fieldList.id_sector = null
        partidaContainer.innerHTML = selectDistribucion
      }
      $('.chosen-select-2').chosen('destroy')
      $('.chosen-select-3').chosen('destroy')

      $('.chosen-select-2')
        .chosen()
        .change(function (obj, result) {
          fieldList['partida-2'] = result.selected
          let value = result.selected

          let checkbox = d.getElementById('nueva-partida-check')

          if (!checkbox.checked) {
            let partidaEncontrada = distribucionPartidas.find(
              (partida) => Number(partida.id) === Number(value)
            )

            let partidaConMontoElement = d.getElementById('partida-2-monto')
            partidaConMontoElement.textContent = partidaEncontrada
              ? partidaEncontrada.monto_actual
              : 'Seleccione una partida'
          }
        })
      $('.chosen-select-3')
        .chosen()
        .change(function (obj, result) {
          fieldList.id_sector = result.selected
          console.log('changed: %o', result)
        })

      actualizarMontoRestante()
      return
    }

    if (e.target.id === 'partida-monto') {
      console.log(e.target.value)
      actualizarMontoRestante()

      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }

    console.log(fieldList)
  }

  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion(data) {
    console.log(data)
    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message: '¿Desea registrar esta distribución presupuestaria?',
      successFunction: async function () {
        let res = await modificarMontoDistribucion(data)
        if (res.success) {
          closeCard()
          loadAsignacionEntesTable(ejercicioFiscal.id)
        }
      },
    })
  }

  formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}
