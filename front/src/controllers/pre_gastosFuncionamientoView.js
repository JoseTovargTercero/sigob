import { validateGastosTable } from './pre_gastosFuncionamientoTable.js'

export const validateGastosView = () => {
  if (!document.getElementById('gastos-view')) return
  validateGastosTable()

  return
}
