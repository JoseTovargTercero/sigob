import { getDependencias } from '../api/dependencias'

const d = document
console.log('hola')

const nom_dependencias_form_card = ({ elementToInsert, id }) => {
  if (document.getElementById('dependencia-form'))
    document.getElementById('dependencia-form').remove()

  let dependenciaFormCard = `    <form class='p-2' id='dependencia-form'>
    <h5 class='mb-0'>Gestión de dependencias</h5>
    <div class='row mx-0'>
      <div class='col-sm'>
        <div class='form-group'>
          <label for='dependencia' class='form-label'>
            NOMBRE
          </label>
          <input
            type='text'
            name='dependencia'
            class='form-control'
            placeholder='Nombre dependencia...'
          />
        </div>
      </div>
      <div class='col-sm'>
        <div class='form-group'>
          <label class='form-label' for='id_dependencia'>
            CODIGO
          </label>
          <input
            type='text'
            name='cod_dependencia'
            class='form-control'
            placeholder='Código dependencia...'
          />
        </div>
      </div>
      <div class='col-sm'>
        <div class='col-sm'>
          <label class='form-label' for='id_categoria'>
            CATEGORIA
          </label>
          <select
            class='form-select employee-select'
            name='id_categoria'
            id='search-select-categorias'
          ></select>
        </div>
      </div>
    </div>
    <div class='row mx-auto'>
      <div class='col-sm-3'>
        <button
          type='button'
          id='dependencia-guardar'
          class='btn btn-primary'
        >
          Guardar
        </button>
         <button
          type='button'
          id='dependencia-cancelar'
          class='btn btn-danger'
        >
          Guardar
        </button>
      </div>
    </div>
  </form>`

  document
    .getElementById(elementToInsert)
    .insertAdjacentHTML('afterend', dependenciaFormCard)

  const dependenciaForm = document.getElementById('dependencia-form')

  dependenciaForm.addEventListener('submit', (e) => e.preventDefault())

  const getCategorias = async (id) => {
    try {
      let res
      if (id) res = await fetch(`${categoriasUrl}?id=${id}`)
      else res = await fetch(categoriasUrl)

      if (!res.ok) throw { status: res.status, statusText: res.statusText }
      const json = await res.json()

      if (json.success) {
        let mappedData = mapData({
          obj: json.success,
          name: 'categoria_nombre',
          id: 'id',
        })

        return { mappedData, fullInfo: json.success }
      }
      if (json.error) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: json.error,
        })
      }
    } catch (e) {
      console.log(e)
      return confirmNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Error al obtener categorias',
      })
    }
  }

  async function validateClick(e) {
    if (e.target.id === 'btn-edit') {
      // EDITAR DEPENDENCIA

      validateEditButtons()

      e.target.textContent = 'Editando'
      e.target.setAttribute('disabled', true)

      let categorias = await getCategorias()
      insertOptions({ input: 'categorias', data: categorias.mappedData })

      if (formContainerElement.classList.contains('hide')) {
        formContainerElement.classList.remove('hide')
        btnNewElement.textContent = 'Cancelar'
      }
      let dependenciaData = await getDependencias(id)

      let { cod_dependencia, dependencia, id_categoria } =
        dependenciaData.fullInfo[0]

      let categoria = categorias.mappedData.find(
        (categoria) => categoria.id == id_categoria
      )

      console.log(categoria, categoria.name)
      formElement.dependencia.value = dependencia
      formElement.cod_dependencia.value = cod_dependencia
      formElement.id_categoria.value = categoria ? categoria.id : ''
    }
  }

  document.addEventListener('click', validateClick)

  return ``
}
