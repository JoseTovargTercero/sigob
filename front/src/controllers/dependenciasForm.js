import { sendDependencyData } from '../api/empleados.js'
import { confirmNotification, validateInput } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { loadDependenciaTable } from './dependenciasTable.js'

const d = document
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

  d.addEventListener('click', (e) => {
    if (e.target.id === btnNewId) {
      formContainerElement.classList.toggle('hide')
      if (formContainerElement.classList.contains('hide')) {
        btnNewElement.textContent = 'Nueva dependencia'
        formElement.reset()
      } else {
        btnNewElement.textContent = 'Cancelar'
      }
    }
    if (e.target.id === btnSaveId) {
      if (Object.values(fieldListErrors).some((el) => el.value)) {
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Complete todo el formulario antes de avanzar',
        })
      }

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
  })

  return
}
