import { getEntesPlan } from '../api/form_entes.js'
import { getEjecicio, getEjecicios } from '../api/pre_distribucion.js'
import { form_asignacion_entes_form_card } from '../components/form_asignacion_entes_form_card.js'
import { confirmNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { validateAsignacionEntesTable } from './form_asignacionEntesTable.js'
import { validateDistribucionTable } from './form_distribucionTable.js'
const d = document

export const validateAsignacionEntesView = async () => {
  validateAsignacionEntesTable()

  let ejercicioFiscal

  let ejerciciosFiscalesContainer = d.getElementById('ejercicios-fiscales')

  let ejerciciosFiscales = await getEjecicios()

  let fechaActual = new Date().getFullYear()
  console.log(fechaActual)

  let ejerciciosLinks = validarEjerciciosFiscales()

  function validarEjerciciosFiscales() {
    if (!ejerciciosFiscales || ejerciciosFiscales.length === 0) {
      return `<div class='col-sm'>
              <p>
                <a
                
                  class='pointer text-dark'
                  previewlistener='true'
                >
                  No hay ejercicios registrados
                </a>
              </p>
            </div>`
    } else {
      return ejerciciosFiscales.fullInfo
        .map((ejercicio) => {
          let ano = Number(ejercicio.ano)

          if (ano === fechaActual) {
            ejercicioFiscal = ejercicio
            return `  <div class='col-sm-4'>
              <p>
                <a
                  data-ejercicioid='${ejercicio.id}'
                  class='pointer text-decoration-underline text-primary'
                  previewlistener='true'
                >
                  ${ejercicio.ano}
                </a>
              </p>
            </div>`
          } else {
            return `  <div class='col-sm-4'>
              <p>
                <a
                  data-ejercicioid='${ejercicio.id}'
                  class='pointer text-dark'
                  previewlistener='true'
                >
                ${ejercicio.ano}
                </a>
              </p>
            </div>`
          }
        })
        .join('')
    }
  }

  ejerciciosFiscalesContainer.innerHTML = ejerciciosLinks

  d.addEventListener('click', async (e) => {
    if (e.target.dataset.validarid) {
      let plan = await getEntesPlan(Number(e.target.dataset.validarid))
      scroll(0, 0)
      form_asignacion_entes_form_card({
        elementToInset: 'asignacion-entes-view',
        plan: plan,
        ejercicioFiscal,
      })
    }
    if (e.target.dataset.ejercicioid) {
      let links = d.querySelectorAll('[data-ejercicioid]')

      links.forEach((link) => {
        console.log(link)
        link.classList.remove('text-decoration-underline')
        link.classList.remove('text-primary')

        link.classList.add('text-dark')
      })

      e.target.classList.remove('text-dark')

      e.target.classList.add('text-decoration-underline')
      e.target.classList.add('text-primary')

      ejercicioFiscal = await getEjecicio(e.target.dataset.ejercicioid)

      console.log(ejercicioFiscal)
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
