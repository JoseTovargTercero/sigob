import {
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

  let card = `<div class='card slide-up-animation' id='${nombreCard}-form-card'>
        <div class='card-header d-flex justify-content-between'>
            <div class=''>
                <h5 class='mb-0'>Información del credito y su distribución asociada</h5>
                <small class='mt-0 text-muted'>GesTione la información del crédito registrado y su decreto</small>
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
        <div class="row">
        <p>
        <span class="badge bg-secondary">Tipo de crédito: <b>${
          Number(data.tipo_credito) === 0 ? 'FCI' : 'Venezuela Bella'
        }</b></span>
         <span class="badge bg-secondary">Tipo de proyecto: <b>${
           Number(data.tipo_proyecto) === 0 ? 'Transferencia' : 'Compra'
         }</b></span>
          <span class="badge bg-success">Monto total: <b>${separadorLocal(
            data.monto
          )} Bs</b></span>
         </p>

         <p><b>Descripción credito: </b>${data.descripcion_credito}<p>
         <p><b>Descripción proyecto: </b>${data.descripcion_proyecto}<p>
        </div>
        <div class="row">
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
                : '<button class="btn btn-primary" id="${nombreCard}-guardar">Subir Decreto</button>'
            }
        </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  let cardElement = d.getElementById(`${nombreCard}-form-card`)
  let fileInput = d.getElementById('decreto-file')
  let fileError = d.getElementById('decreto-file-error')
  let guardarButton = d.getElementById(`${nombreCard}-guardar`)
  let decretoIframe = d.getElementById('decreto-iframe')

  function closeCard(card) {
    card.remove()
    card.removeEventListener('click', validateClick)
    card.removeEventListener('change', validateFile)
    card.removeEventListener('click', subirDecreto)
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
  }

  async function subirDecreto() {
    if (!decretoFile) {
      toastNotification(
        'Seleccione un archivo PDF.',
        NOTIFICATIONS_TYPES.WARNING
      )
      return
    }

    if (fileError.style.display === 'block') {
      toastNotification(
        'Corrija los errores en el archivo.',
        NOTIFICATIONS_TYPES.WARNING
      )
      return
    }

    const formData = new FormData()
    formData.append('decreto', decretoFile)

    try {
      // Aquí iría tu lógica para subir el archivo al servidor
      // Ejemplo con fetch:
      /*
            const response = await fetch('/tu-ruta-de-subida', {
                method: 'POST',
                body: formData,
            });

            if (response.ok) {
                toastNotification('Decreto subido correctamente.', NOTIFICATIONS_TYPES.SUCCESS);
                closeCard(cardElement);
            } else {
                toastNotification('Error al subir el decreto.', NOTIFICATIONS_TYPES.ERROR);
            }
            */

      //Simulacion de subida Exitosa.
      setTimeout(() => {
        toastNotification(
          'Decreto subido correctamente.',
          NOTIFICATIONS_TYPES.SUCCESS
        )
        closeCard(cardElement)
      }, 1000)
    } catch (error) {
      toastNotification(
        'Error al subir el decreto: ' + error.message,
        NOTIFICATIONS_TYPES.ERROR
      )
    }
  }

  // Manejo de datos recibidos (blob PDF)
  if (data && data.decretoBlob) {
    const pdfUrl = URL.createObjectURL(data.decretoBlob)
    decretoIframe.src = pdfUrl
  }

  if (fileInput) {
    cardElement.addEventListener('change', validateFile)
  }
  if (guardarButton) {
    cardElement.addEventListener('click', subirDecreto)
  }
  cardElement.addEventListener('click', validateClick)
}
