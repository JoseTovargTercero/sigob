import { pre_gastos_form_card } from '../components/pre_gastos_form_card.js'
import { validateGastosTable } from './pre_gastosFuncionamientoTable.js'

const d = document
export const validateGastosView = () => {
  if (!document.getElementById('gastos-view')) return
  validateGastosTable()

  let gastosForm = d.getElementById('gastos-form')
  let gastosRegistrarCointaner = d.getElementById('gastos-registrar-container')

  d.addEventListener('click', (e) => {
    if (e.target.id === 'gastos-registrar') {
      gastosRegistrarCointaner.classList.add('hide')
      pre_gastos_form_card({ elementToInsert: 'gastos-view' })
    }
  })
  return
}
