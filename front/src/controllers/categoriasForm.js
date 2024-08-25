import { getCategorias, sendCategoria, updateCategoria } from '../api/categorias.js'
  import { confirmNotification, validateInput } from '../helpers/helpers.js'
  import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { confirmDeleteCategoria, loadCategoriasTable } from './categoriasTable.js'

  
  const d = document
  let id

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
    }
}

  export function validateCategoriaForm() {
    let formId = 'categoria-form'
    let formContainerId = 'categoria-form-container'
    let btnNewId = 'categoria-nueva'
    let btnSaveId = 'categoria-guardar'

    const formElement = d.getElementById(formId)
    const formContainerElement = d.getElementById(formContainerId)
    const btnNewElement = d.getElementById(btnNewId)
  
    formElement.addEventListener('input', (e) => {
      fieldList = validateInput({
        target: e.target,
        fieldList: fieldList,
        fieldListErrors: fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    })
  
    formElement.addEventListener('change', (e) => {
      fieldList = validateInput({
        target: e.target,
        fieldList: fieldList,
        fieldListErrors: fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    })
  
    d.addEventListener('click', async (e) => {
      if (e.target.id === btnNewId) {
        formContainerElement.classList.toggle('hide')
        if (formContainerElement.classList.contains('hide')) {
          validateEditButtons()
          btnNewElement.textContent = 'Nueva categoria'
          formElement.reset()
          // Resetear ID
          id = ''
        } else {
          btnNewElement.textContent = 'Cancelar'
        }
      }
  
      if (e.target.id === 'btn-delete') {
        let fila = e.target.closest('tr')
  
        confirmDeleteCategoria({ id: e.target.dataset.id, row: fila })
      }
      if (e.target.id === 'btn-edit') {
        // EDITAR categoria
        id = e.target.dataset.id
  
        validateEditButtons()
  
        e.target.textContent = 'Editando'
        e.target.setAttribute('disabled', true)
  
        if (formContainerElement.classList.contains('hide')) {
          formContainerElement.classList.remove('hide')
          btnNewElement.textContent = 'Cancelar'
        }
        let categoriaData = await getCategorias(id)
  
        let { categoria_nombre, categoria } = categoriaData.fullInfo[0]
        formElement.categoria_nombre.value = categoria_nombre
        formElement.categoria.value = categoria
      }
  
      // GUARDAR CATEGORIA
  
      if (e.target.id === btnSaveId) {
        fieldList = validateInput({
          target: formElement.categoria_nombre,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[formElement.categoria_nombre.name],
        })
        fieldList = validateInput({
          target: formElement.categoria,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[formElement.categoria.name],
        })
        if (Object.values(fieldListErrors).some((el) => el.value)) {
          return confirmNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: 'Necesita llenar todos los campos',
          })
        }
  
        if (id) {
          confirmNotification({
            type: NOTIFICATIONS_TYPES.send,
            message: `Deseas actualizar esta categoria a: "${formElement.categoria_nombre.value} - ${formElement.categoria.value}?`,
            successFunction: async function () {
              await updateCategoria({
                informacion: {
                  id: id,
                  categoria_nombre: fieldList.categoria_nombre,
                  categoria: fieldList.categoria,
                },
              })
  
              // RESETEAR FORMULARIO
              formContainerElement.classList.add('hide')
              btnNewElement.textContent = 'Nueva categoria'
              formElement.reset()
              id = ''
              // Recargar tabla
              loadCategoriasTable()
            },
          })
        } else {
          confirmNotification({
            type: NOTIFICATIONS_TYPES.send,
            successFunction: async function () {
              // ENVIAR CATEGORIA
              await sendCategoria({
                informacion: {
                    categoria_nombre:formElement.categoria_nombre.value,
                    categoria: formElement.categoria.value,
                },
              })
  
              // RESETEAR FORMULARIO
              formContainerElement.classList.add('hide')
              btnNewElement.textContent = 'Nueva categoria'
              formElement.reset()
  
              // Recargar tabla
              loadCategoriasTable()
            },
            message: `Deseas guardar la categoria "${formElement.categoria_nombre.value} - ${formElement.categoria.value}
            "`,
          })
        }
      }
    })
  
    function validateEditButtons() {
      let editButtons = d.querySelectorAll('[data-id][disabled]')
  
      editButtons.forEach((btn) => {
        if (btn.hasAttribute('disabled')) {
          btn.removeAttribute('disabled')
          btn.textContent = 'Editar'
        }
      })
    }
  
    return
  }
  