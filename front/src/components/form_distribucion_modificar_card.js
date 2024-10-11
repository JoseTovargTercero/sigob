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
  let fieldList = { ejemplo: '' }
  let fieldListErrors = {
    ejemplo: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
  }
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
    .map((option) => {
      return `<option value="${option.partida}">${option.descripcion}</option>`
    })
    .join('')

  let card = `   <div class='card slide-up-animation' id='distribucion-modificar-card'>
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
                <b id='monto-total'>Partida no seleccioanda</b>
              </h6>
              <small class='text-muted'>Monto disponible en esta partida</small>
            </div>
            <div class='col'>
              <h6 class='mb-0'>
                Monto restante:
                <b id='monto-restante'>Ejercicio fiscal no seleccionado</b>
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
                  class='form-control'
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
                  class='form-control'
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
                  class='form-control'
                  name='partida-2'
                  type='text'
                  placeholder='Partida (nueva)'
                  id='partida-nueva'
                  list="partidas-list-nueva"
                />
                <datalist id='partidas-list-nueva'>
                  ${optionsPartidasListNueva}
                </datalist>
              </div>
            </div>
            <div class='col'>
              <div class='form-group'>
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
    if (e.target.dataset.close) {
      closeCard()
    }
  }

  async function validateInputFunction(e) {
    if (e.target.id === 'partida-distribuida') {
      let partidaEncontrada = distribucionPartidas.find(
        (partida) => partida.partida === e.target.value
      )
      if (partidaEncontrada) {
        d.getElementById('monto-total').textContent =
          partidaEncontrada.monto_inicial
      }
    }
    if (e.target.id === 'nueva-partida-check') {
      let partidaNueva = d.getElementById('partida-nueva')
      let partidaDistribucion = d.getElementById('partida-distribucion')
      if (e.target.checked) {
        partidaNueva.parentElement.classList.remove('d-none')
        partidaDistribucion.parentElement.classList.add('d-none')
      } else {
        partidaNueva.parentElement.classList.add('d-none')
        partidaDistribucion.parentElement.classList.remove('d-none')
      }
    }

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
