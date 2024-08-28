import { getCategorias } from '../api/categorias.js'
import {
  getDependencias,
  sendDependencia,
  updateDependencia,
} from '../api/dependencias.js'
import { confirmNotification, validateInput } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document

let fieldList = {
  dependencia: '',
  cod_dependencia: '',
  id_categoria: '',
}

let fieldListErrors = {
  dependencia: {
    value: true,
    message: 'Campo inválido',
    type: 'text',
  },
  cod_dependencia: {
    value: true,
    message: 'Campo inválido',
    type: 'number',
  },
  id_categoria: {
    value: true,
    message: 'Elija una categoria válida',
    type: 'number',
  },
}

const nom_dependencia_form_card = async ({ elementToInsert, id }) => {
  if (document.getElementById('dependencia-form'))
    document.getElementById('dependencia-form').remove()

  let dependenciaFormCard = `    <form class='p-4' id='dependencia-form'>
  <h6 class='mb-2'>Gestión de dependencias</h6>
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
        Cancelar
      </button>
    </div>
  </div>
</form>`

  document
    .getElementById(elementToInsert)
    .insertAdjacentHTML('afterend', dependenciaFormCard)

  const dependenciaForm = document.getElementById('dependencia-form')

  dependenciaForm.addEventListener('submit', (e) => e.preventDefault())

  let dependenciaData = await getDependencias(id)
  let categorias = await getCategorias()
  insertOptions({ input: 'categorias', data: categorias.mappedData })

  let { cod_dependencia, dependencia, id_categoria } =
    dependenciaData.fullInfo[0]

  let categoria = categorias.mappedData.find(
    (categoria) => categoria.id == id_categoria
  )
  dependenciaForm.dependencia.value = dependencia
  dependenciaForm.cod_dependencia.value = cod_dependencia
  dependenciaForm.id_categoria.value = categoria ? categoria.id : ''

  function insertOptions({ input, data }) {
    const selectElement = d.getElementById(`search-select-${input}`)
    selectElement.innerHTML = `<option value="">Elegir...</option>`
    const fragment = d.createDocumentFragment()
    data.forEach((el) => {
      const option = d.createElement('option')
      option.setAttribute('value', el.id)
      option.textContent = el.name
      fragment.appendChild(option)
    })

    selectElement.appendChild(fragment)
  }

  function validateClick(e) {
    if (e.target.id === 'dependencia-cancelar') {
      d.getElementById('dependencia-form').remove()
      d.removeEventListener('click', validateClick)
      dependenciaForm.removeEventListener('input', validateInputFunction)
    }
    if (e.target.id === 'dependencia-guardar') {
      fieldList = validateInput({
        target: dependenciaForm.dependencia,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[dependenciaForm.dependencia.name],
      })
      fieldList = validateInput({
        target: dependenciaForm.cod_dependencia,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[dependenciaForm.cod_dependencia.name],
      })
      fieldList = validateInput({
        target: dependenciaForm.id_categoria,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[dependenciaForm.id_categoria.name],
      })
      if (Object.values(fieldListErrors).some((el) => el.value)) {
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Necesita llenar todos los campos',
        })
      }
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Desea actualizar esta unidad?',
        successFunction: async function () {
          let res = await updateDependencia({
            informacion: {
              id: id,
              dependencia: dependenciaForm.dependencia.value,
              cod_dependencia: dependenciaForm.cod_dependencia.value,
              id_categoria: dependenciaForm.id_categoria.value,
            },
          })
          d.getElementById('dependencia-form').remove()
          d.removeEventListener('click', validateClick)
          dependenciaForm.removeEventListener('input', validateInputFunction)
          cargarTabla()
        },
      })
    }
  }

  function validateInputFunction(e) {
    fieldList = validateInput({
      target: e.target,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    })
  }

  d.addEventListener('click', validateClick)
  dependenciaForm.addEventListener('input', validateInputFunction)
}

document.addEventListener('click', (e) => {
  if (e.target.dataset.dependencia) {
    console.log(e.target.dataset)

    nom_dependencia_form_card({
      elementToInsert: 'dependencia-card-header',
      id: e.target.dataset.dependencia,
    })
  }
})

function cargarTabla() {
  $.ajax({
    url: url_back,
    type: 'POST',
    data: {
      tabla: true,
    },
    cache: false,
    success: function (response) {
      cont = 1
      $('#table tbody').html('')
      if (response) {
        var data = JSON.parse(response)
        for (columna in data) {
          const dependencia = data[columna]['dependencia']
          const id_dependencia = data[columna]['id_dependencia']
          const cod_dependencia = data[columna]['cod_dependencia']
          const id_categoria = data[columna]['id_categoria']
          const categoria = data[columna]['categoria_nombre']
          const cant_empleados = data[columna]['total_empleados']

          $('#table tbody').append(`<tr>
              <td>${cont++}</td>
              <td><p class="mb-0"><b>${dependencia}</b></p><small class="text-muted">Categoría: ${
            categoria == null
              ? `<a data-dependencia="${id_dependencia}" href="#">Primero debe asignar una categoría</a>`
              : categoria
          }</small></td>
              <td>${cod_dependencia} </td>
              <td>${cant_empleados} </td>
              <td  class="text-center">
              ${
                categoria == null
                  ? ''
                  : `<button onclick="editar('${id_dependencia}', '${dependencia}')" class="btn btn-primary btn-sm"><i class="bx bx-user-plus"></i></button>`
              }


               </td>
            </tr>`)
        }
      }
    },
  })
}
