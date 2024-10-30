import {
  eliminarContraloriaId,
  eliminarGobernacionId,
  getContraloriaDataId,
  getGobernacionDataId,
} from '../api/form_informacion.js'
import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'
import { form_informacionContraloriaForm } from '../components/form_informacion/form_informacionContraloriaForm.js'
import { form_informacionGobernacionForm } from '../components/form_informacion/form_informacionGobernacionForm.js'
import { confirmNotification, toastNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import {
  deleteContraloriaRow,
  deleteGobernacionRow,
  loadGobernacionTable,
  validateContraloriaTable,
  validateGobernacionTable,
} from './form_informacionTables.js'

const d = document

export const validateGobernacionView = async () => {
  let btnNewElement = d.getElementById('gobernacion-registrar')

  validateGobernacionTable()

  console.log('hola')

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'gobernacion-registrar') {
      scroll(0, 0)
      form_informacionGobernacionForm({ elementToInsert: 'gobernacion-view' })
    }

    if (e.target.dataset.editarid) {
      scroll(0, 0)
      let data = await getGobernacionDataId(e.target.dataset.editarid)
      console.log(data)

      form_informacionGobernacionForm({
        elementToInsert: 'gobernacion-view',
        data,
      })
    }

    if (e.target.dataset.eliminarid) {
      let row = e.target.closest('tr')
      confirmNotification({
        type: NOTIFICATIONS_TYPES.delete,
        message: '¿Desea eliminar este registro?',
        successFunction: async function () {
          deleteGobernacionRow({ row })
          eliminarGobernacionId(e.target.dataset.eliminarid)
        },
      })
    }
  })
}

export const validateContraloriaView = async () => {
  let btnNewElement = d.getElementById('contraloria-registrar')

  validateContraloriaTable()

  console.log('hola')

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'contraloria-registrar') {
      scroll(0, 0)
      form_informacionContraloriaForm({ elementToInsert: 'contraloria-view' })
    }

    if (e.target.dataset.editarid) {
      scroll(0, 0)
      let data = await getContraloriaDataId(e.target.dataset.editarid)
      console.log(data)

      form_informacionContraloriaForm({
        elementToInsert: 'contraloria-view',
        data,
      })
    }

    if (e.target.dataset.eliminarid) {
      let row = e.target.closest('tr')
      confirmNotification({
        type: NOTIFICATIONS_TYPES.delete,
        message: '¿Desea eliminar este registro?',
        successFunction: async function () {
          deleteContraloriaRow({ row })
          eliminarContraloriaId(e.target.dataset.eliminarid)
        },
      })
    }
  })
}

// function validateEditButtons() {
//   d.getElementById('partida-registrar').removeAttribute('disabled')

//   let editButtons = d.querySelectorAll('[data-editarid][disabled]')

//   if (editButtons.length < 1) return

//   editButtons.forEach((btn) => {
//     if (btn.hasAttribute('disabled')) {
//       btn.removeAttribute('disabled')
//       btn.textContent = 'Editar'
//     }
//   })
// }
