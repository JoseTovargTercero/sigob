export const nomCorrectionAlert = ({ message, type }) => {
  return `<div class='alert alert-${
    type || 'primary'
  }' role='alert' id="employee-correcion">
      ${message || 'Sin correciones pendientes'}
    </div>`
}
