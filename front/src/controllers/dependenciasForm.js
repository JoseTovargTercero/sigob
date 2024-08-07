import {
  deleteDependencyData,
  getDependencyData,
  sendDependencyData,
  updateDependencyData,
} from '../api/empleados.js'
import { confirmNotification, validateInput } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { loadDependenciaTable } from './dependenciasTable.js'

const d = document
let id
export function validateDependenciaForm({
  formId,
  formContainerId,
  btnNewId,
  btnSaveId,
  fieldList,
  fieldListErrors,
}) {
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
        btnNewElement.textContent = 'Nueva dependencia'
        formElement.reset()
        // Resetear ID
        id = ''
      } else {
        btnNewElement.textContent = 'Cancelar'
      }
    }

    if (e.target.id === 'btn-delete') {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.delete,
        message: '¿Desea borrar esta dependencia?',
        successFunction: async function () {
          await deleteDependencyData(e.target.dataset.id)
          loadDependenciaTable()
        },
      })
    }
    if (e.target.id === 'btn-edit') {
      // EDITAR DEPENDENCIA
      id = e.target.dataset.id

      validateEditButtons()

      e.target.textContent = 'Editando'
      e.target.setAttribute('disabled', true)

      if (formContainerElement.classList.contains('hide')) {
        formContainerElement.classList.remove('hide')
        btnNewElement.textContent = 'Cancelar'
      }
      let dependenciaData = await getDependencyData(id)

      let { cod_dependencia, dependencia } = dependenciaData.fullInfo[0]
      formElement.dependencia.value = dependencia
      formElement.cod_dependencia.value = cod_dependencia
    }

    // GUARDAR DEPENDENCIA

    if (e.target.id === btnSaveId) {
      fieldList = validateInput({
        target: formElement.dependencia,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[formElement.dependencia.name],
      })
      fieldList = validateInput({
        target: formElement.cod_dependencia,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[formElement.dependencia.name],
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
          message: `Deseas actualizar esta dependencia a: "${formElement.dependencia.value} - ${formElement.cod_dependencia.value}?`,
          successFunction: async function () {
            await updateDependencyData({
              data: {
                id_dependencia: id,
                dependencia: fieldList.dependencia,
                cod_dependencia: fieldList.cod_dependencia,
              },
            })

            // RESETEAR FORMULARIO
            formContainerElement.classList.add('hide')
            btnNewElement.textContent = 'Nueva dependencia'
            formElement.reset()
            id = ''
            // Recargar tabla
            loadDependenciaTable()
          },
        })
      } else {
        confirmNotification({
          type: NOTIFICATIONS_TYPES.send,
          successFunction: async function () {
            // ENVIAR DEPENDENCIA
            await sendDependencyData({
              newDependency: {
                dependencia: formElement.dependencia.value,
                cod_dependencia: formElement.cod_dependencia.value,
              },
            })

            // RESETEAR FORMULARIO
            formContainerElement.classList.add('hide')
            btnNewElement.textContent = 'Nueva dependencia'
            formElement.reset()

            // Recargar tabla
            loadDependenciaTable()
          },
          message: `Deseas guardar la dependencia "${formElement.dependencia.value} - ${formElement.cod_dependencia.value}
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
