import {
  eliminarAsignacionEnte,
  getAsignacionesEnte,
} from '../api/form_entes.js'
import { getEntePlanOperativo } from '../api/form_planOperativo.js'

import { form_asignacion_entes_card } from '../components/form_asignacion_entes_card.js'

import { form_asignacion_entes_form_card } from '../components/form_asignacion_entes_form_card.js'
import { form_asignacion_entes_monto_card } from '../components/form_asignacion_entes_monto_card.js'

import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'
import { confirmNotification, toastNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import {
  loadAsignacionEntesTable,
  validateAsignacionEntesTable,
} from './form_asignacionEntesTable.js'
import { preValidateAsignacionEntesTable } from './pre_asignacionEntesTable.js'

const d = document

export const preValidateAsignacionEntesView = async () => {
  let ejercicioFiscal = await ejerciciosLista({
    elementToInsert: 'ejercicios-fiscales',
  })

  preValidateAsignacionEntesTable(ejercicioFiscal ? ejercicioFiscal.id : null)

  d.addEventListener('click', async (e) => {
    if (e.target.dataset.validarid) {
      if (
        !ejercicioFiscal.distribucion_partidas ||
        ejercicioFiscal.distribucion_partidas.length < 1
      )
        return toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message:
            'Realice la distribucion presupuestaria del ejercicio seleccionado',
        })

      scroll(0, 0)
      let asignacion = await getAsignacionesEnte(e.target.dataset.validarid)
      form_asignacion_entes_form_card({
        elementToInset: 'asignacion-entes-view',
        asignacion: asignacion,
        ejercicioFiscal,
        actualizar: async function () {
          let res = await actualizarEjercicioFiscal()
        },
      })
    }

    if (e.target.dataset.eliminarid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.delete,
        message:
          '¿Está seguro de eliminar esta asignación? Se eliminarán todas sus distribuciones asociadas.',
        successFunction: async function () {
          let res = await eliminarAsignacionEnte(e.target.dataset.eliminarid)
          if (res.success) {
            loadAsignacionEntesTable(ejercicioFiscal.id)
          }
        },
      })
    }

    if (e.target.dataset.ejercicioid) {
      // QUITAR CARD SI SE CAMBIA EL AÑO FISCAL
      let formCard = d.getElementById('asignacion-entes-form-card')
      let cards = [
        'asignacion-entes-form-card',
        'asignacion-ente-monto-form-card',
      ]

      cards.forEach((el) => {
        if (d.getElementById(el)) {
          d.getElementById(el).remove()
        }
      })
      if (formCard) formCard.remove()

      ejercicioFiscal = await validarEjercicioActual({
        ejercicioTarget: e.target,
      })

      await loadAsignacionEntesTable(ejercicioFiscal.id)
    }

    // REGISTRAR NUEVA ASIGNACIÓN A ENTE
    if (e.target.id === 'entes-asignar') {
      console.log(ejercicioFiscal)
      if (!ejercicioFiscal)
        return toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Cree o seleccione un ejercicio fiscal',
        })

      if (Number(ejercicioFiscal.status) === 0) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Ejercicio fiscal cerrado',
        })
        return
      }

      form_asignacion_entes_card({
        elementToInset: 'asignacion-entes-view',
        ejercicioFiscal,
      })
    }

    if (e.target.dataset.asignarid) {
      let plan = await getEntePlanOperativo(
        e.target.dataset.asignarid,
        ejercicioFiscal.id
      )

      if (!plan) {
        toastNotification({
          message: 'No se encontró plan operativo para este ente',
          type: NOTIFICATIONS_TYPES.fail,
        })
        return
      }

      form_asignacion_entes_monto_card({
        elementToInset: 'asignacion-entes-view',
        enteId: e.target.dataset.asignarid,
        ejercicioFiscal,
        actualizar: async function () {
          let res = await actualizarEjercicioFiscal()
        },
      })
    }

    // if (e.target.id === 'partida-registrar') {
    //   btnNewElement.setAttribute('disabled', true)
    //   form_partida_form_card({ elementToInsert: 'partidas-view' })
    // }

    // if (e.target.dataset.eliminarid) {
    //   confirmNotification({
    //     type: NOTIFICATIONS_TYPES.send,
    //     message: '¿Desea eliminar esta partida?',
    //     successFunction: async function () {
    //       let row = e.target.closest('tr')
    //       eliminarPartida(e.target.dataset.eliminarid)
    //       deletePartidaRow({ row })
    //       if (d.getElementById('partida-form-card')) {
    //         location.reload()
    //       }
    //     },
    //   })
    // }

    // if (e.target.dataset.editarid) {
    //   scroll(0, 0)
    //   //   gastosRegistrarCointaner.classList.add('hide')
    //   e.target.textContent = 'Editando'
    //   e.target.setAttribute('disabled', true)
    //   btnNewElement.setAttribute('disabled', true)

    //   form_partida_form_card({
    //     elementToInsert: 'partidas-view',
    //     id: e.target.dataset.editarid,
    //   })
    // }
  })

  async function actualizarEjercicioFiscal() {
    ejercicioFiscal = await ejerciciosLista({
      elementToInsert: 'ejercicios-fiscales',
    })

    loadAsignacionEntesTable(ejercicioFiscal.id)
  }
}

function validateEditButtons() {
  d.getElementById('partida-registrar').removeAttribute('disabled')

  let editButtons = d.querySelectorAll('[data-editarid][disabled]')

  if (editButtons.length < 1) return

  editButtons.forEach((btn) => {
    if (btn.hasAttribute('disabled')) {
      btn.removeAttribute('disabled')
      btn.textContent = 'Editar'
    }
  })
}
