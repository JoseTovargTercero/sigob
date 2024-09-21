import { getPartidas } from '../api/partidas.js'
import { confirmNotification, toastNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document

export const pre_gastosTipo_form_card = async ({ elementToInsert, data }) => {
  const cardElement = d.getElementById('gastos-tipo-form-card')
  if (cardElement) cardElement.remove()

  let card = `  <div class='card slide-up-animation' id='gastos-tipo-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Registro de nuevo tipo de gasto presupuestario</h5>
          <small class='mt-0 text-muted'>
          Introduzca el nombre para el nuevo tipo de gasto y la partida asociada
          </small>
        </div>
        <button
          data-close='btn-close-report'
          type='button'
          class='btn btn-danger'
          aria-label='Close'
        >
          &times;
        </button>
      </div>
      <div class='card-body'>
        <form id='gastos-tipo-form'>
          <div class='row'>
            <div class='col-sm'>
              <div class='form-group'>
                <label for="nombre" class='form-label'>
                  Nombre para nuevo tipo de gasto
                </label>
                <div class='input-group'>
                <input
                  class='form-control'
                  type='text'
                  name='nombre'
                  id='nombre'
                  placeholder='Nombre...'
                />
                </div>
              </div>
            </div>
            <div class='col-sm'>
              <div class='form-group'>
                <label for="id_partida" class='form-label'>
                 Partida asociada
                </label>
                <input
                  class='form-control'
                  type='text'
                  name='id_partida'
                  placeholder='Partida...'
                  id='id_partida'
                  list='partidas-list'
                />
                <datalist id='partidas-list'></datalist>
              </div>
            </div>
          </div>
        </form>
       
      </div>
      <div class='card-footer'>
      <button class='btn btn-primary' id='gastos-tipo-guardar'>
        Guardar
      </button>
    </div>
    </div>`

  console.log(d.getElementById(elementToInsert))

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  getPartidas().then((res) => {
    let partidasList = d.getElementById('partidas-list')
    console.log(res)
    partidasList.innerHTML = ''
    let options = res.fullInfo
      .map((option) => {
        return `<option value="${option.partida}">${option.descripcion}</option>`
      })
      .join('')

    partidasList.innerHTML = options
    return
  })

  const formElement = d.getElementById('gastos-tipo-form')

  const closeCard = () => {
    let cardElement = d.getElementById('gastos-tipo-form-card')
    let gastosRegistrarCointaner = d.getElementById(
      'gastos-registrar-container'
    )
    gastosRegistrarCointaner.classList.remove('hide')

    cardElement.remove()
    d.removeEventListener('click', validateClick)
    formElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.rechazarid) {
      let id = e.target.dataset.rechazarid
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Seguro de rechazar esta solicitud de dozavo?',
        successFunction: async function () {
          let row = d.querySelector(`[data-detalleid="${id}"]`).closest('tr')

          toastNotification({
            type: NOTIFICATIONS_TYPES.done,
            message: 'Solicitud rechazada',
          })

          deleteSolicitudDozeavo({ row, id })
          closeCard()
        },
      })
    }

    if (e.target.dataset.close) {
      closeCard()
    }
  }

  function validateInputFunction(e) {
    fieldList = validateInput({
      target: e.target,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    })
  }

  formElement.addEventListener('input', validateInputFunction)
  d.addEventListener('click', validateClick)
}
