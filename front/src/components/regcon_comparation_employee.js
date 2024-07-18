import { getRegConEmployeeData } from '../api/empleados.js'

export async function nom_comparation_employee({
  empleadosEliminados,
  empleadosNuevos,
}) {
  let empleadosNuevosInformacion =
    empleadosNuevos &&
    (await Promise.all(empleadosNuevos.map((id) => getRegConEmployeeData(id))))

  let empleadosEliminadosInformacion =
    empleadosEliminados &&
    (await Promise.all(
      empleadosEliminados.map((id) => getRegConEmployeeData(id))
    ))

  // let empleadosEliminadosInformacionPeticion = await Promise.all(
  //   empleadosEliminadosInformacion
  // )

  // let empleadosNuevosInformacionPeticion = await Promise.all(
  //   empleadosNuevosInformacion
  // )

  let th

  let mappedRows = empleadosEliminadosInformacion.map((row) => {
    if (!row) return false
    let td = ''

    Object.values(row).forEach((el) => {
      td += `<td>${el}</td>`
    })

    return `<tr>${td}</tr>`
  })

  console.log(mappedRows)

  return `div class='card size-change-animation w-75 mx-auto' id="table-list-card">
  <div class='card-header py-2'>
    <div class='d-flex align-items-center justify-content-between'>
      <div>
        <h5 class='mb-0'>Petición de nómina a pagar</h5>
      </div>
      <button class='btn btn-danger' id='close-request-list'>
        Cerrar
      </button>
    </div>
  </div>
  <div class="card-body">
  <table
    class='table table-sm table-responsive mx-auto'
    style='width: fit-content'
  >
    <thead>${th}</thead>
    <tbody>${empleadosEliminadosInformacion}</tbody>
  </table>
  </div>
</div>`
}
