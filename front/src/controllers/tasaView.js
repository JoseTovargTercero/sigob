import {
  actualizarTasa,
  crearTasa,
  obtenerTasa,
  obtenerHistorialTasa,
  actualizarTasaManual,
} from '../api/tasa..js'
import { nomTasaCard } from '../components/nom_tasa_card.js'
import {
  confirmNotification,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { inicializarTasaTable, loadTasaTable } from './tasaTable.js'

const d = document

let fieldList = {
  'tasa-input': '0',
}

let fieldListErrors = {
  'tasa-input': {
    value: true,
    message: 'Tasa inválida (mínimo 0.001)',
    type: 'tasa',
  },
}

export async function validateTasaActual() {
  const tasaViewElement = d.getElementById('tasa-view')
  if (!tasaViewElement) return

  inicializarTasaTable()

  let tasaCreada
  let tasaDelDia

  tasaDelDia = await obtenerTasa()
  if (!tasaDelDia) {
    tasaCreada = crearTasa()
    nomTasaCard({ elementToInsert: 'tasa-card-body', tasaDelDia: tasaCreada })
  } else {
    nomTasaCard({ elementToInsert: 'tasa-card-body', tasaDelDia: tasaDelDia })
  }

  let tasaForm = d.getElementById('tasa-form')
  let tasaFormContainer = d.getElementById('tasa-form-container')
  let tasaHeaderValor = d.getElementById('tasa-valor')

  if (!tasaForm) return

  tasaForm.addEventListener('submit', (e) => e.preventDefault())

  tasaForm.addEventListener('input', (e) => {
    if (e.target.name === 'tasa-input') {
      console.log('asd')
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }
  })

  tasaForm.addEventListener('focusout', (e) => {
    if (e.target.name === 'tasa-input') {
      console.log('asd')
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }
  })

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'tasa-actualizar-manual') {
      // let tasaForm = d.getElementById('tasa-form')

      tasaFormContainer.classList.toggle('hide')
      if (tasaFormContainer.classList.contains('hide')) {
        e.target.textContent = 'Editar'
        tasaForm.reset()
      } else {
        e.target.textContent = 'Cancelar'
      }

      // let actualizar = await actualizarTasa({ informacion: { valor: 1000 } })
      // if (!actualizar || actualizar.error) return

      // let tasaActualizada = await obtenerTasa()

      // loadTasaTable()

      // nomTasaCard({
      //   elementToInsert: 'tasa-card-body',
      //   tasaDelDia: tasaActualizada,
      // })
    }

    if (e.target.id === 'tasa-guardar') {
      fieldList = validateInput({
        target: tasaForm['tasa-input'],
        fieldList,
        fieldListErrors,
        type: fieldListErrors[tasaForm['tasa-input'].name].type,
      })

      if (Object.values(fieldListErrors).some((el) => el.value)) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Tasa inválida',
        })
        return
      }

      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Está seguro de actualizar la tasa?',
        successFunction: async function () {
          let respuesta = await actualizarTasaManual({
            informacion: { valor: fieldList['tasa-input'] },
          })

          // RECARGAR HISTORIAL DE TASA
          loadTasaTable()

          // ACTUALIZAR CARD DE TASA

          let tasa = await obtenerTasa()
          nomTasaCard({
            elementToInsert: 'tasa-card-body',
            tasaDelDia: tasa,
          })
          tasaFormContainer.classList.add('hide')
          console.log(tasa)
          tasaHeaderValor.textContent = `${tasa.valor} Bs.`

          fieldList['tasa-input'] = 0
          fieldListErrors['tasa-input'].value = true
        },
      })
    }

    if (e.target.id === 'tasa-actualizar-automatico') {
      let actualizar = await actualizarTasa()
      if (!actualizar || actualizar.error) return

      let tasaActualizada = await obtenerTasa()

      loadTasaTable()

      tasaHeaderValor.textContent = `${tasaActualizada.valor} Bs.`
      tasaFormContainer.classList.add('hide')

      nomTasaCard({
        elementToInsert: 'tasa-card-body',
        tasaDelDia: tasaActualizada,
      })
    }
  })

  return
}
