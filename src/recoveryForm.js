const d = document

const recoveryForm = d.getElementById('recovery-form')
const recoveryFormPart1 = d.getElementById('recovery-form-part-1')
const recoveryFormPart2 = d.getElementById('recovery-form-part-2')
const recoveryFormPart3 = d.getElementById('recovery-form-part-3')
const nextBtn = d.getElementById('btn-next')
const previusBtn = d.getElementById('btn-previus')
const consultBtn = d.getElementById('btn-consult')

recoveryForm.addEventListener('submit', (e) => {
  e.preventDefault()
})

d.addEventListener('click', (e) => {
  if (e.target === nextBtn) {
    console.log('SIGUIENTE')
  }
  if (e.target === previusBtn) {
    if (
      [recoveryFormPart1, recoveryFormPart2, recoveryFormPart3].some((el) =>
        el.classList.constains('hide')
      )
    ) {
    }
  }
  if (e.target === consultBtn) {
    nextBtn.classList.remove('hide')
    previusBtn.classList.remove('hide')
    consultBtn.classList.add('hide')
  }
})

const nextForm = (form) => {}

const searchEmail = async (email) => {
  let isValid
  setTimeout(async () => {
    isValid = true
  }, 1000)

  return isValid
  // try {
  //   let res = await fetch('consultar_correo.php', { method: 'POST' })

  //   if (!res.ok) return false
  //   else return true
  // } catch (e) {
  //   return confirmNotification({
  //     type: NOTIFICATIONS_TYPES.fail,
  //     message: 'Error al obtener empleados',
  //   })
  // }
}
