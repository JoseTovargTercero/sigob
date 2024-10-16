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
  distribucionPartidas,
  partidas,
}) => {
  console.log(distribucionPartidas, partidas)

  let fieldList = {
    'partida-1': '',
    'partida-2': '',
    'partida-3': '',
    'partida-monto': 0,
  }
  let fieldListErrors = {
    'partida-1': {
      value: true,
      message: 'Partida inválida',
      type: 'partida',
    },
    'partida-2': {
      value: true,
      message: 'Partida inválida',
      type: 'partida',
    },
    'partida-3': {
      value: true,
      message: 'Partida inválida',
      type: 'partida',
    },
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
      return `<option value="${option.partida}">Monto: ${separarMiles(
        option.monto_inicial
      )}</option>`
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
      return `<option value="${option.partida}">${option.descripcion}</option>`
    })
    .join('')

  let card = `    <div class='card slide-up-animation' id='distribucion-modificar-card'>
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
                Monto total:
                <b id='monto-disponible'>Partida no seleccioanda</b>
              </h6>
              <small class='text-muted'>Monto disponible en esta partida</small>
            </div>
            <div class='col'>
              <h6 class='mb-0'>
                Restante:
                <b id='monto-restante'>Ejercicio fiscal no seleccionado</b>
              </h6>
              <small class='text-muted'>Restante de partida a distribuir</small>
            </div>
            <div class='col'>
              <h6 class='mb-0'>
                Asignación:
                <b id='monto-asignado'>Ejercicio fiscal no seleccionado</b>
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
                <label class='form-label'>Partida a modificar</label>
                <input
                  class='form-control partida-input'
                  name='partida-1'
                  type='text'
                  placeholder='Partida a designar monto'
                  id='partida-distribuida'
                  list='partidas-list-distribucion'
                />
              </div>
              <datalist id='partidas-list-distribucion'>
                ${optionsPartidasDistribucion}
              </datalist>
            </div>
            <div class='col'>
              <div class='form-group slide-up-animation'>
                <label class='form-label'>
                  Partida a asignar (distribucion)
                </label>
                <input
                  class='form-control partida-input'
                  name='partida-2'
                  type='text'
                  placeholder='Partida (distribucion)'
                  id='partida-distribucion'
                  list='partidas-list-distribucion'
                />
              </div>

              <div class='form-group d-none slide-up-animation'>
                <label class='form-label'>Partida a asignar (nueva)</label>
                <input
                  class='form-control partida-input'
                  name='partida-3'
                  type='text'
                  placeholder='Partida (nueva)'
                  id='partida-nueva'
                  list='partidas-list-nueva'
                />
                <datalist id='partidas-list-nueva'>
                  ${optionsPartidasListNueva}
                </datalist>
              </div>

              <div>
                <h6 class='mb-0'>
                  Asignado:
                  <b id='partida-2-monto'>Partida no seleccioanda</b>
                </h6>
              </div>
            </div>
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
    let inputCheck = d.getElementById('nueva-partida-check')
    let partidaADistribuir = d.getElementById('partida-distribuida')
    let partidaDistribucion = d.getElementById('partida-distribucion')
    let partidaNueva = d.getElementById('partida-nueva')

    let partidaMonto = d.getElementById('partida-monto')

    let montoDisponibleElement = d.getElementById('monto-disponible')
    let montoRestanteElement = d.getElementById('monto-restante')
    let partidaConMontoElement = d.getElementById('partida-2-monto')

    if (e.target.dataset.close) {
      closeCard()
    }
    if (e.target.id === 'distribucion-modificar-guardar') {
      fieldList = validateInput({
        target: partidaADistribuir,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[partidaADistribuir.name].type,
      })

      if (inputCheck.checked) {
        fieldListErrors[partidaNueva.name].value = true
        fieldListErrors[partidaDistribucion.name].value = false

        fieldList = validateInput({
          target: partidaNueva,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[partidaNueva.name].type,
        })
      } else {
        fieldListErrors[partidaDistribucion.name].value = true
        fieldListErrors[partidaNueva.name].value = false
        fieldList = validateInput({
          target: partidaDistribucion,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[partidaDistribucion.name].type,
        })
      }

      fieldList = validateInput({
        target: partidaMonto,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[partidaMonto.name].type,
      })

      let partida1 = partidas.find(
        (partida) => partida.partida === fieldList['partida-1']
      )
      let partida2 = inputCheck.checked
        ? partidas.find((partida) => partida.partida === fieldList['partida-3'])
        : partidas.find((partida) => partida.partida === fieldList['partida-2'])

      console.log(fieldListErrors)
      console.log(partida1, partida2)

      enviarInformacion()
    }
  }

  function validarCampos(e) {
    let partidaADistribuir = d.getElementById('partida-distribuida')
    let partidaDistribucion = d.getElementById('partida-distribucion')
    let partidaNueva = d.getElementById('partida-nueva')
    let partidaMonto = d.getElementById('partida-monto')

    let montoDisponibleElement = d.getElementById('monto-disponible')
    let montoRestanteElement = d.getElementById('monto-restante')
    let partidaConMontoElement = d.getElementById('partida-2-monto')

    if (e.target === partidaADistribuir) {
      if (
        partidaADistribuir.value === partidaDistribucion.value ||
        partidaADistribuir.value === partidaNueva.value
      ) {
        e.target.value = ''
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message:
            'Está partida ya fue seleccionada, cambie el valor de los otros campos',
        })
      } else {
        let partidaEncontrada = distribucionPartidas.find(
          (partida) => partida.partida === e.target.value
        )
        if (partidaEncontrada) {
          // INICIALIZAR Y REINICIAR VALORES AL CAMBIAR LA PARTIDA A MODIFICAR
          montos.disponible = partidaEncontrada.monto_inicial
          montos.restante = partidaEncontrada.monto_inicial

          partidaMonto.value = 0

          montoDisponibleElement.textContent = montos.disponible
          montoRestanteElement.textContent = montos.restante

          actualizarMontoRestante()
        } else {
          montos.disponible = 0
          montos.restante = 0
          partidaMonto.value = 0

          montoDisponibleElement.textContent = 'Partida no encontrada'
          montoRestanteElement.textContent = 'Partida no encontrada'

          actualizarMontoRestante()
        }
      }
    }

    if (e.target === partidaDistribucion) {
      if (!e.target.value) {
        partidaConMontoElement.textContent = 'Partida no seleccionada'
        return
      }

      if (
        partidaDistribucion.value === partidaADistribuir.value ||
        partidaDistribucion.value === partidaNueva.value
      ) {
        e.target.value = ''
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message:
            'Está partida ya fue seleccionada, cambie el valor de los otros campos',
        })

        return
      } else {
        let partidaEncontrada = distribucionPartidas.find(
          (partida) => partida.partida === e.target.value
        )

        if (partidaEncontrada) {
          partidaConMontoElement.textContent = partidaEncontrada.monto_inicial
        } else {
          partidaConMontoElement.textContent =
            'Esta partida no tiene un monto asignado'
        }

        // if (partidaEncontrada) {
        //   d.getElementById('monto-tal').textContent =
        //     partidaEncontrada.monto_inicial
        // }
      }
    }

    if (e.target === partidaNueva) {
      if (
        partidaNueva.value === partidaADistribuir.value ||
        partidaNueva.value === partidaDistribucion.value
      ) {
        e.target.value = ''
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message:
            'Está partida ya fue seleccionada, cambie el valor de los otros campos',
        })
      }
    }

    if (e.target === partidaMonto) {
      console.log(partidaMonto.value)
      actualizarMontoRestante()
    }
  }

  function actualizarMontoRestante() {
    console.log(montos)
    let partidaMonto = d.getElementById('partida-monto')
    let montoRestanteElement = d.getElementById('monto-restante')
    let montoAsignadoElement = d.getElementById('monto-asignado')

    montos.acumulado = 0

    if (isNaN(Number(partidaMonto.value))) return

    montos.acumulado = Number(partidaMonto.value)
    montos.restante = montos.disponible - montos.acumulado
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
    }
    montoAsignadoElement.textContent = montos.acumulado
  }

  async function validateInputFunction(e) {
    let partidaADistribuir = d.getElementById('partida-distribuida')
    let partidaDistribucion = d.getElementById('partida-distribucion')
    let partidaNueva = d.getElementById('partida-nueva')

    if (e.target.id === 'partida-distribuida') {
    }
    if (e.target.id === 'nueva-partida-check') {
      if (e.target.checked) {
        partidaNueva.parentElement.classList.remove('d-none')
        partidaDistribucion.parentElement.classList.add('d-none')
      } else {
        partidaNueva.parentElement.classList.add('d-none')
        partidaDistribucion.parentElement.classList.remove('d-none')
      }
      partidaNueva.value = ''
      partidaDistribucion.value = ''

      let partidaConMontoElement = d.getElementById('partida-2-monto')
      partidaConMontoElement.textContent = 'Partida no seleccionada'

      actualizarMontoRestante()
    }

    validarCampos(e)

    // fieldList = validateInput({
    //   target: e.target,
    //   fieldList,
    //   fieldListErrors,
    //   type: fieldListErrors[e.target.name].type,
    // })
  }

  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion(data) {}

  formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}
