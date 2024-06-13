export function tableListCard({ data, columms }) {
  let th = '',
    tr = ''

  columms.forEach((column) => {
    th += `<th>${column}</th>`
  })

  data.forEach((row) => {
    let td = ''
    let rowNuws = Object.values(row)
    rowNuws.forEach((el) => {
      td += `<td>${el}</td>`
    })

    tr += `<tr>${td}</tr>`
  })

  return `<div class='card size-change-animation w-75 mx-auto' id="table-list-card">
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
        <tbody>${tr}</tbody>
      </table>
      </div>
    </div>`
}
