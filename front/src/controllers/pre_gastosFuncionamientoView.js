import {
  aceptarGasto,
  eliminatTipoGasto,
  getGastos,
  getTiposGastos,
  rechazarGasto,
} from '../api/pre_gastos.js'
import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'
import { pre_gastos_form_card } from '../components/pre_gastos_form_card.js'
import { confirmNotification, toastNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import {
  deleteGasto,
  deleteTipoGasto,
  loadGastosTable,
  validateGastosTable,
  validateTiposGastosTable,
} from './pre_gastosFuncionamientoTable.js'

const d = document
export const validateGastosView = async () => {
  if (!document.getElementById('gastos-view')) return

  let ejercicioFiscal = await ejerciciosLista({
    elementToInsert: 'ejercicios-fiscales',
  })

  validateGastosTable({
    id_ejercicio: ejercicioFiscal ? ejercicioFiscal.id : null,
  })
  validateTiposGastosTable()

  let gastosForm = d.getElementById('gastos-form')
  let gastosRegistrarCointaner = d.getElementById('gastos-registrar-container')

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'gastos-registrar') {
      if (!ejercicioFiscal) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'No hay ejercicio fiscal seleccionado',
        })
        return
      }
      gastosRegistrarCointaner.classList.add('hide')
      pre_gastos_form_card({
        elementToInsert: 'gastos-view',
        ejercicioFiscal: ejercicioFiscal,
        recargarEjercicio: async function () {
          let ejercicioFiscalElement = d.querySelector(
            `[data-ejercicioid="${ejercicioFiscal.id}"]`
          )
          ejercicioFiscal = await validarEjercicioActual({
            ejercicioTarget: ejercicioFiscalElement,
          })

          loadGastosTable({ id_ejercicio: ejercicioFiscal.id })
        },
      })
    }

    if (e.target.dataset.tableid) {
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
        successFunction: async function () {
          let res = await rechazarGasto(e.target.dataset.rechazarid)
          if (res.success) {
            loadGastosTable({ id_ejercicio: ejercicioFiscal.id })
          }
        },
      })
    }

    if (e.target.dataset.aceptarid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message:
          'Al aceptar este gasto se descontará del presupuesto actual ¿Desea continuar?',
        successFunction: async function () {
          let res = await aceptarGasto(e.target.dataset.aceptarid)
          if (res.success) {
            loadGastosTable({ id_ejercicio: ejercicioFiscal.id })
          }
        },
      })
    }

    if (e.target.dataset.eliminarid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Desea eliminar este tipo de gasto',
        successFunction: async function () {
          let res = await eliminatTipoGasto(e.target.dataset.eliminarid)
          if (res.success) {
            let row = e.target.closest('tr')
            deleteTipoGasto({ row })

            let formCard = d.getElementById('gastos-form-card')
            if (formCard) formCard.remove()
          }
        },
      })
    }

    if (e.target.dataset.ejercicioid) {
      // QUITAR CARD SI SE CAMBIA EL AÑO FISCAL
      let formCard = d.getElementById('gastos-form-card')
      if (formCard) formCard.remove()

      ejercicioFiscal = await validarEjercicioActual({
        ejercicioTarget: e.target,
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
