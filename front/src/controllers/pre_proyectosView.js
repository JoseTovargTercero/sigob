import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'
import { pre_proyectoCredito_card } from '../components/pre_proyectoCredito_card.js'
import { pre_proyectosForm_card } from '../components/pre_proyectosForm_card.js'

import { toastNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import {
  loadProyectosTable,
  validateProyectosTable,
} from './pre_proyectosTable.js'

const d = document
const w = window

let dataEjemplo = {
  id_ente: '9',
  monto: '123',
  fecha: '2025-12-31',
  id_ejercicio: '1',
  descripcion_credito: 'descripcion credito',
  distribuciones: [
    {
      id_distribucion: 103,
      monto: 123,
    },
  ],
  tipo_credito: '0',
  tipo_proyecto: '0',
  descripcion_proyecto: {
    value: false,
    message: 'Descripción inválida',
    type: 'textarea',
  },
  decreto: false,
}

export const validateProyectosView = async () => {
  let ejercicioFiscal = await ejerciciosLista({
    elementToInsert: 'ejercicios-fiscales',
  })

  validateProyectosTable()

  loadProyectosTable(ejercicioFiscal.id)

  pre_proyectoCredito_card({
    elementToInsert: 'proyectos-view',
    data: dataEjemplo,
  })

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'proyectos-registrar') {
      if (!ejercicioFiscal) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'No hay un ejercicio fiscal seleccionado',
        })
        return
      }

      if (ejercicioFiscal.distribucion_partidas.length < 1) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'El ejercicio fiscal no posee una distribucion ',
        })
        return
      }
      console.log(ejercicioFiscal)

      pre_proyectosForm_card({
        elementToInsert: 'proyectos-view',
        ejercicioFiscal: ejercicioFiscal,
      })

      // pre_traspasosForm_card({
      //   elementToInsert: 'traspasos-view',
      //   ejercicioFiscal,
      //   recargarEjercicio: async function () {
      //     let ejercicioFiscalElement = d.querySelector(
      //       `[data-ejercicioid="${ejercicioFiscal.id}"]`
      //     )
      //     ejercicioFiscal = await validarEjercicioActual({
      //       ejercicioTarget: ejercicioFiscalElement,
      //     })

      //     loadTraspasosTable(ejercicioFiscal.id)
      //   },
      // })
    }

    if (e.target.dataset.detalleid) {
      // pre_traspasosCard({
      //   elementToInsert: 'traspasos-view',
      //   data: await getTraspaso(e.target.dataset.detalleid),
      //   ejercicioFiscal,
      // })
    }
    if (e.target.dataset.ejercicioid) {
      ejercicioFiscal = await validarEjercicioActual({
        ejercicioTarget: e.target,
      })
    }
  })
}
