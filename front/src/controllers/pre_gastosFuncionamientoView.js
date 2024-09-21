import { getGastos, getTiposGastos } from '../api/pre_gastos.js'
import { pre_gastos_form_card } from '../components/pre_gastos_form_card.js'
import { confirmNotification, toastNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import {
  deleteGasto,
  deleteTipoGasto,
  validateGastosTable,
  validateTiposGastosTable,
} from './pre_gastosFuncionamientoTable.js'

const d = document
export const validateGastosView = () => {
  if (!document.getElementById('gastos-view')) return
  validateGastosTable()
  validateTiposGastosTable()

  let gastosForm = d.getElementById('gastos-form')
  let gastosRegistrarCointaner = d.getElementById('gastos-registrar-container')

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'gastos-registrar') {
      gastosRegistrarCointaner.classList.add('hide')
      pre_gastos_form_card({ elementToInsert: 'gastos-view' })
    }

    if (e.target.dataset.tableid) {
      console.log(e.target.dataset)
      mostrarTabla(e.target.dataset.tableid)
      d.querySelectorAll('.nav-link').forEach((el) => {
        el.classList.remove('active')
      })

      e.target.classList.add('active')
    }

    if (e.target.dataset.rechazarid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message:
          'Rechazar este gasto hará que se elimine y reintegre el monto al presupuesto ¿Desea continuar?',
        successFunction: function () {
          let row = e.target.closest('tr')
          deleteGasto({ row })

          toastNotification({
            type: NOTIFICATIONS_TYPES.done,
            message: 'Gasto rechazado',
          })
        },
      })
    }

    if (e.target.dataset.aceptarid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message:
          'Al aceptar este gasto se descontará del presupuesto actual ¿Desea continuar?',
        successFunction: function () {
          toastNotification({
            type: NOTIFICATIONS_TYPES.done,
            message: 'Gasto aceptado y descontado del presupuesto',
          })
        },
      })
    }

    if (e.target.dataset.eliminarid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Desea eliminar este tipo de gasto',
        successFunction: function () {
          let row = e.target.closest('tr')
          deleteTipoGasto({ row })
          toastNotification({
            type: NOTIFICATIONS_TYPES.done,
            message: 'Tipo de gasto eliminado',
          })
        },
      })
    }
  })
  return
}

function mostrarTabla(tablaId) {
  let table1Id = 'gastos-table'
  let table2Id = 'tipos-gastos-table'

  let table1 = d.getElementById(`${table1Id}-container`)
  let table2 = d.getElementById(`${table2Id}-container`)

  if (tablaId === table1Id) {
    table1.classList.add('d-block')
    table1.classList.remove('d-none')
    table2.classList.add('d-none')
    table2.classList.remove('d-block')
  } else if (tablaId === table2Id) {
    table1.classList.add('d-none')
    table1.classList.remove('d-block')
    table2.classList.add('d-block')
    table2.classList.remove('d-none')
  }
}

// función para actualizar presupuesto según sea requerido

function actualizarPresupuesto() {
  let presupuesto = d.getElementById('presupuesto')
}
