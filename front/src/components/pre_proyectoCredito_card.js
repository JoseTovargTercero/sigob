import { registrarCredito, registrarDecreto } from '../api/pre_proyectos.js'
import { APP_URL, DECRETOS_URL } from '../api/urlConfig.js'
import {
  confirmNotification,
  hideLoader,
  separadorLocal,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document

export const pre_proyectoCredito_card = ({
  elementToInsert = null,
  data = null,
  close = false,
  reset,
}) => {
  let decretoFile = null
  const maxFileSize = 5 * 1024 * 1024 // 5 MB (ajusta según tus necesidades)
  const allowedFileTypes = ['application/pdf']

  let nombreCard = 'credito-detalle' // Nombre único para la card

  const oldCardElement = d.getElementById(`${nombreCard}-form-card`)
  if (oldCardElement || close) {
    closeCard(oldCardElement)
  }

  let { decreto } = data

  let card = `  <div class='card slide-up-animation' id='${nombreCard}-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>
            Información del credito y su distribución asociada
          </h5>
          <small class='mt-0 text-muted'>
            GesTione la información del crédito registrado y su decreto
          </small>
        </div>
        <button
          data-close='btn-close'
          type='button'
          class='btn btn-danger'
          aria-label='Close'
        >
          &times;
        </button>
      </div>
      <div class='card-body'>
        <div class='row mb-4'>
          <p>
            <span class='badge bg-secondary'>
              Tipo de crédito:
              <b>
                ${Number(data.tipo_credito) === 0 ? 'FCI' : 'Venezuela Bella'}
              </b>
            </span>
            <span class='badge bg-secondary'>
              Tipo de proyecto:
              <b>
                ${Number(data.tipo_proyecto) === 0 ? 'Transferencia' : 'Compra'}
              </b>
            </span>
             <span class='badge bg-secondary'>
              Fecha: <b>
                ${
                  data.fecha
                    ? data.fecha.split('-').reverse().join('-')
                    : 'Compra'
                }
              </b>
            </span>
            <span class='badge bg-success'>
              Monto total: <b>${separadorLocal(data.monto)} Bs</b>
            </span>
          </p>
        </div>
        <div class='row mb-4'>
          <div class='col'>
            <p>
              <b>Descripción credito: </b>${data.descripcion_credito}
            </p>
          </div>
          <div class='col'>
            <p>
              <b>Descripción proyecto: </b>${data.descripcion_proyecto}
            </p>
          </div>
        </div>
        <div class='row'>
          ${
            decreto
              ? '<iframe id="decreto-iframe" style="width: 100%; height: 500px;"></iframe>'
              : '<input type="file" id="decreto-file" accept="application/pdf" class="form-control"><p id="decreto-file-error" class="text-danger slide-up-animation mt-2" style="display: none;">Error: Archivo inválido.</p>'
          }
        </div>
      </div>
      <div class='card-footer'>
        ${
          decreto
            ? ''
            : `<button class="btn btn-primary" id="${nombreCard}-guardar">Subir Decreto</button>`
        }
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  let cardElement = d.getElementById(`${nombreCard}-form-card`)
  let fileInput = d.getElementById('decreto-file')
  let fileError = d.getElementById('decreto-file-error')
  let guardarButton = d.getElementById(`${nombreCard}-guardar`)
  let decretoIframe = d.getElementById('decreto-iframe')

  let base64Archivo = null

  function closeCard(card) {
    card.remove()
    card.removeEventListener('click', validateClick)
    card.removeEventListener('change', validateFile)

    // if (fileInput) {
    // }
    // if (guardarButton) {
    // }
    // return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard(cardElement)
    }

    if (e.target === guardarButton) {
      subirDecreto()
    }
  }

  function validateFile(e) {
    decretoFile = e.target.files[0]
    if (!decretoFile) {
      fileError.textContent = 'Seleccione un archivo.'
      fileError.style.display = 'block'
      return
    }

    if (!allowedFileTypes.includes(decretoFile.type)) {
      fileError.textContent = 'El archivo debe ser un PDF.'
      fileError.style.display = 'block'
      return
    }

    if (decretoFile.size > maxFileSize) {
      fileError.textContent =
        'El archivo excede el tamaño máximo permitido (5MB).'
      fileError.style.display = 'block'
      return
    }

    fileError.style.display = 'none'

    // Convertir a base64

    // const archivo = decretoFile
    // let datos = {}

    if (decretoFile) {
      const reader = new FileReader()
      reader.onloadend = async (e) => {
        // Agregamos el parámetro 'e' para el evento
        base64Archivo = e.target.result.split(',')[1]
      }
      reader.readAsDataURL(decretoFile) // Iniciamos la lectura del archivo
    }
  }

  async function subirDecreto() {
    if (!decretoFile) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Seleccione un archivo PDF.',
      })
      return
    }

    if (fileError.style.display === 'block') {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Corrija los errores en el archivo.',
      })

      return
    }

    let datos = {
      id_credito: data.id_credito,
      archivoBase64: base64Archivo,
      tipo: 'application/pdf',
    }

    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message: '¿Desea registrar el decreto?',
      successFunction: async () => {
        let res = await registrarDecreto(datos)
        if (res.success) {
          closeCard(cardElement)
          reset()
        }
      },
    })
  }

  // Manejo de datos recibidos (blob PDF)
  if (data && data.decreto) {
    decretoIframe.src = `${DECRETOS_URL}/${data.decreto}`
  }

  if (fileInput) {
    cardElement.addEventListener('change', validateFile)
  }

  cardElement.addEventListener('click', validateClick)
}
