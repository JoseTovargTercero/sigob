export const nomCorrectionAlert = ({ message, type }) => {
  return `  <div class='alert alert-${type || 'primary'}' role='alert'>
      ${message || 'Sin correciones pendientes'}
    </div>`
}
