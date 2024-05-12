const d = document

function validateInput(fieldList = {}, e) {
  fieldList = {
    ...fieldList,
    [e.target.name]: convertStringOrNumber(e.target.value),
  }
  console.log(fieldList)
  return fieldList
}

const convertStringOrNumber = (string) =>
  isNaN(Number(string)) ? string : Number(string)

// MEJORAR ESTA VALIDACIÓN UTILIZANDO MENOS EVENT LISTENERS
function validateModal(e, { btnOpenId, btnCloseId, modalClass }) {
  const modalElement = d.querySelector(`.${modalClass}`)
  if (e.target.id === btnOpenId) {
    if (modalElement.classList.contains('hide')) {
      modalElement.classList.remove('hide')
    }
  }

  if (e.target.id === btnCloseId) {
    if (!modalElement.classList.contains('hide')) {
      modalElement.classList.add('hide')
    }
  }
}

export { validateInput, validateModal }
