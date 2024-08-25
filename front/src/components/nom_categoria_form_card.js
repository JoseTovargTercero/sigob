import { getCategorias, sendCategoria } from '../api/categorias.js'
import { confirmNotification, validateInput } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document

let fieldList = {
  categoria_nombre: '',
  categoria: '',
}

let fieldListErrors = {
  categoria_nombre: {
    value: true,
    message: 'Nombre para categoria inválido',
    type: 'text',
  },
  categoria: {
    value: true,
    message: 'Categoría inválida',
    type: 'categoria',
  },
}

export const nom_categoria_form_card = ({ elementToInsert, reloadSelect }) => {
  let cardElement = d.getElementById('modal-category')
  if (cardElement) cardElement.remove()

  let card = ` <div id='modal-category' class='modal-window'>
      <div class='modal-box short slide-up-animation'>
        <header class='modal-box-header'>
          <h4>AÑADIR NUEVA Categoria</h4>
          <button
            id='btn-close-modal-category'
            type='button'
            class='btn btn-danger'
            aria-label='Close'
          >
            &times;
          </button>
        </header>

        <div class='modal-box-content'>
          <form id='employee-categoria-form'>
            <div class='row mx-0'>
              <div class='col-sm'>
                <input
                  class='form-control'
                  type='text'
                  name='categoria_nombre'
                  placeholder='Nombre de la categoria...'
                  id='categoria_nombre'
                />
              </div>
              <div class='col-sm'>
                <input
                  type='text'
                  class=' form-control'
                  name='categoria'
                  id='categoria'
                  placeholder='Categoria...'
                />
              </div>
            </div>
          </form>
        </div>

        <div class='modal-box-footer'>
          <button class='btn btn-primary' id='category-save-btn'>
            GUARDAR CATEGORIA
          </button>
        </div>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('beforeend', card)

  let formElement = d.getElementById('employee-categoria-form')
  const closeModalCard = () => {
    let cardElement = d.getElementById('modal-category')
    console.log(cardElement)
    cardElement.remove()
    d.removeEventListener('click', validateClick)
    formElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.id === 'category-save-btn') {
      fieldList = validateInput({
        target: formElement.categoria_nombre,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[formElement.categoria_nombre.name].type,
      })
      fieldList = validateInput({
        target: formElement.categoria,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[formElement.categoria.name].type,
      })
      if (Object.values(fieldListErrors).some((el) => el.value)) {
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Necesita llenar todos los campos',
        })
      }

      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Seguro de registrar esta categoria?',
        successFunction: async function () {
          sendCategoria({
            informacion: {
              categoria_nombre: formElement.categoria_nombre.value,
              categoria: formElement.categoria.value,
            },
          }).then((res) => {
            closeModalCard()
            reloadSelect()
          })
        },
      })
    }

    if (e.target.id === 'btn-close-modal-category') {
      console.log('hola')
      closeModalCard()
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
