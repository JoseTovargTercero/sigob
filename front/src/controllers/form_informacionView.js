import {
  eliminarConsejoId,
  eliminarContraloriaId,
  eliminarDescripcionProgramaId,
  eliminarDirectivoId,
  eliminarGobernacionId,
  eliminarPersonaId,
  eliminarTitulo1Id,
  getConsejoDataId,
  getContraloriaDataId,
  getDescripcionProgramaDataId,
  getDirectivoDataId,
  getGobernacionDataId,
  getPersonaDataId,
  getTitulo1DataId,
} from '../api/form_informacion.js'

import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'
import { form_informacionDirectivoForm } from '../components/form_informacion/form_informacionDirectivoForm.js'
import { form_informacionContraloriaForm } from '../components/form_informacion/form_informacionContraloriaForm.js'
import { form_informacionGobernacionForm } from '../components/form_informacion/form_informacionGobernacionForm.js'
import { confirmNotification, toastNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import {
  deleteConsejoRow,
  deleteContraloriaRow,
  deleteDescripcionProgramaRow,
  deleteDirectivoRow,
  deleteGobernacionRow,
  deleteTitulo1Row,
  loadGobernacionTable,
  validateConsejoTable,
  validateContraloriaTable,
  validateDescripcionProgramaTable,
  validateDirectivoTable,
  validateGobernacionTable,
  validatePersonaTable,
  validateTitulo1Table,
} from './form_informacionTables.js'
import { form_informacionPersonaForm } from '../components/form_informacion/form_informacionPersonaForm.js'
import { form_informacionTitulo1Form } from '../components/form_informacion/form_informacionTitulo1Form.js'
import { form_informacionDescripcionProgramaForm } from '../components/form_informacion/form_informacionDescripcionPrograma.js'
import { form_informacionConsejoForm } from '../components/form_informacion/form_informacionConsejoForm.js'
import { selectTables } from '../api/globalApi.js'

const d = document

export const validateGobernacionView = async () => {
  let btnNewElement = d.getElementById('gobernacion-registrar')

  // validateGobernacionTable()

  let data = await selectTables('informacion_gobernacion')

  form_informacionGobernacionForm({
    elementToInsert: 'gobernacion-view-body',
    data: data[0],
  })

  // d.addEventListener('click', async (e) => {
  //   // if (e.target.id === 'gobernacion-registrar') {
  //   //   scroll(0, 0)
  //   //   form_informacionGobernacionForm({ elementToInsert: 'gobernacion-view' })
  //   // }

  //   if (e.target.dataset.editarid) {
  //     scroll(0, 0)

  //     console.log(data)
  //   }

  //   // if (e.target.dataset.eliminarid) {
  //   //   let row = e.target.closest('tr')
  //   //   confirmNotification({
  //   //     type: NOTIFICATIONS_TYPES.delete,
  //   //     message: '¿Desea eliminar este registro?',
  //   //     successFunction: async function () {
  //   //       deleteGobernacionRow({ row })
  //   //       eliminarGobernacionId(e.target.dataset.eliminarid)
  //   //     },
  //   //   })
  //   // }
  // })
}

export const validateContraloriaView = async () => {
  let btnNewElement = d.getElementById('contraloria-registrar')

  // validateContraloriaTable()

  let data = await selectTables('informacion_contraloria')

  form_informacionContraloriaForm({
    elementToInsert: 'contraloria-view-body',
    data: data[0],
  })

  // d.addEventListener('click', async (e) => {
  // if (e.target.id === 'contraloria-registrar') {
  //   scroll(0, 0)
  //   form_informacionContraloriaForm({ elementToInsert: 'contraloria-view' })
  // }
  // if (e.target.dataset.editarid) {
  //   scroll(0, 0)
  //   let data = await getContraloriaDataId(e.target.dataset.editarid)
  //   console.log(data)
  //   form_informacionContraloriaForm({
  //     elementToInsert: 'contraloria-view',
  //     data,
  //   })
  // }
  // if (e.target.dataset.eliminarid) {
  //   let row = e.target.closest('tr')
  //   confirmNotification({
  //     type: NOTIFICATIONS_TYPES.delete,
  //     message: '¿Desea eliminar este registro?',
  //     successFunction: async function () {
  //       deleteContraloriaRow({ row })
  //       eliminarContraloriaId(e.target.dataset.eliminarid)
  //     },
  //   })
  // }
  // })
}

export const validateConsejoView = async () => {
  let btnNewElement = d.getElementById('consejo-registrar')

  let data = await selectTables('informacion_consejo')

  form_informacionConsejoForm({
    elementToInsert: 'consejo-view-body',
    data: data[0],
  })

  // d.addEventListener('click', async (e) => {
  // if (e.target.id === 'consejo-registrar') {
  //   scroll(0, 0)
  //   form_informacionDirectivoForm({ elementToInsert: 'consejo-view' })
  // }

  // if (e.target.dataset.editarid) {
  //   scroll(0, 0)
  //   let data = await getConsejoDataId(e.target.dataset.editarid)
  //   console.log(data)

  //   form_informacionConsejoForm({
  //     elementToInsert: 'consejo-view',
  //     data,
  //   })
  // }

  // if (e.target.dataset.eliminarid) {
  //   let row = e.target.closest('tr')
  //   confirmNotification({
  //     type: NOTIFICATIONS_TYPES.delete,
  //     message: '¿Desea eliminar este registro?',
  //     successFunction: async function () {
  //       deleteConsejoRow({ row })
  //       eliminarConsejoId(e.target.dataset.eliminarid)
  //     },
  //   })
  // }
  // })
}

export const validateDirectivoView = async () => {
  let btnNewElement = d.getElementById('directivo-registrar')

  validateDirectivoTable()

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'directivo-registrar') {
      scroll(0, 0)
      form_informacionDirectivoForm({ elementToInsert: 'directivo-view' })
    }

    if (e.target.dataset.editarid) {
      scroll(0, 0)
      let data = await getDirectivoDataId(e.target.dataset.editarid)
      console.log(data)

      form_informacionDirectivoForm({
        elementToInsert: 'directivo-view',
        data,
      })
    }

    if (e.target.dataset.eliminarid) {
      let row = e.target.closest('tr')

      confirmNotification({
        type: NOTIFICATIONS_TYPES.delete,
        message: '¿Desea eliminar este registro?',
        successFunction: async function () {
          deleteDirectivoRow({ row })
          eliminarDirectivoId(e.target.dataset.eliminarid)
        },
      })
    }
  })
}

export const validatePersonaView = async () => {
  let btnNewElement = d.getElementById('persona-registrar')

  validatePersonaTable()

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'persona-registrar') {
      scroll(0, 0)
      form_informacionPersonaForm({ elementToInsert: 'persona-view' })
    }

    if (e.target.dataset.editarid) {
      scroll(0, 0)
      let data = await getPersonaDataId(e.target.dataset.editarid)
      console.log(data)

      form_informacionPersonaForm({
        elementToInsert: 'persona-view',
        data,
      })
    }

    if (e.target.dataset.eliminarid) {
      let row = e.target.closest('tr')

      confirmNotification({
        type: NOTIFICATIONS_TYPES.delete,
        message: '¿Desea eliminar este registro?',
        successFunction: async function () {
          deleteTitulo1Row({ row })
          eliminarPersonaId(e.target.dataset.eliminarid)
        },
      })
    }
  })
}

export const validateTitulo1View = async () => {
  let btnNewElement = d.getElementById('titulo-1-registrar')

  validateTitulo1Table()

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'titulo-1-registrar') {
      scroll(0, 0)
      form_informacionTitulo1Form({ elementToInsert: 'titulo-1-view' })
    }

    if (e.target.dataset.editarid) {
      scroll(0, 0)
      let data = await getTitulo1DataId(e.target.dataset.editarid)
      console.log(data)

      form_informacionTitulo1Form({
        elementToInsert: 'titulo-1-view',
        data,
      })
    }

    if (e.target.dataset.eliminarid) {
      let row = e.target.closest('tr')

      confirmNotification({
        type: NOTIFICATIONS_TYPES.delete,
        message: '¿Desea eliminar este registro?',
        successFunction: async function () {
          deleteTitulo1Row({ row })
          eliminarTitulo1Id(e.target.dataset.eliminarid)
        },
      })
    }
  })
}
export const validateDescripcionProgramaView = async () => {
  let btnNewElement = d.getElementById('descripcion-programa-registrar')

  validateDescripcionProgramaTable()

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'descripcion-programa-registrar') {
      scroll(0, 0)
      form_informacionDescripcionProgramaForm({
        elementToInsert: 'descripcion-programa-view',
      })
    }

    if (e.target.dataset.editarid) {
      scroll(0, 0)
      let data = await getDescripcionProgramaDataId(e.target.dataset.editarid)
      console.log(data)

      form_informacionDescripcionProgramaForm({
        elementToInsert: 'descripcion-programa-view',
        data,
      })
    }

    if (e.target.dataset.eliminarid) {
      let row = e.target.closest('tr')

      confirmNotification({
        type: NOTIFICATIONS_TYPES.delete,
        message: '¿Desea eliminar este registro?',
        successFunction: async function () {
          deleteDescripcionProgramaRow({ row })
          eliminarDescripcionProgramaId(e.target.dataset.eliminarid)
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
